<?php
session_start();
require_once('../Connections/Conn.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get and validate the input
$fiscal_year_id = filter_input(INPUT_POST, 'fiscal_year_id', FILTER_VALIDATE_INT);

if (!$fiscal_year_id) {
    die(json_encode(['success' => false, 'message' => 'Invalid fiscal year ID']));
}

class FiscalYearRollback
{
    private $db;
    private $fiscal_year_id;
    private $next_year_id;
    private $user;

    public function __construct($db, $fiscal_year_id, $user = 'SYSTEM')
    {
        $this->db = $db;
        $this->fiscal_year_id = $fiscal_year_id;
        $this->user = $user;
    }

    private function logAction($action_type, $description, $account = null, $dr_amt = 0, $cr_amt = 0, $status = 'SUCCESS', $additional_info = null)
    {
        $sql = "
            INSERT INTO chart_closing_log (
                fiscal_year_id,
                action_type,
                description,
                account_affected,
                debit_amount,
                credit_amount,
                user,
                status,
                additional_info
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(
            $this->fiscal_year_id,
            $action_type,
            $description,
            $account,
            $dr_amt,
            $cr_amt,
            $this->user,
            $status,
            $additional_info
        ));
    }

    public function rollback()
    {
        try {
            $this->db->beginTransaction();

            $this->logAction('START_ROLLBACK', 'Starting fiscal year rollback process');

            // Verify the year is closed and get next year ID
            $stmt = $this->db->prepare("
                SELECT fy1.closed, fy2.id as next_year_id, fy2.closed as next_year_closed
                FROM chart_fiscal_year fy1
                LEFT JOIN chart_fiscal_year fy2 ON fy2.begin > fy1.end
                WHERE fy1.id = ?
                ORDER BY fy2.begin ASC
                LIMIT 1
            ");
            $stmt->execute(array($this->fiscal_year_id));
            $yearInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$yearInfo['closed']) {
                throw new Exception("Cannot rollback: Fiscal year is not closed");
            }

            $this->next_year_id = $yearInfo['next_year_id'];

            if (!$this->next_year_id) {
                throw new Exception("Cannot find next fiscal year");
            }

            if ($yearInfo['next_year_closed']) {
                throw new Exception("Cannot rollback: Next fiscal year is already closed");
            }

            // Check if there are any transactions in the next fiscal year besides opening entries
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM chart_ledger
                WHERE fiscal_year = ?
                AND app_no != 'YEARSTART'
            ");
            $stmt->execute(array($this->next_year_id));
            $transCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            if ($transCount > 0) {
                throw new Exception("Cannot rollback: Next fiscal year already has transactions");
            }

            // Remove all closing entries from the current fiscal year
            $stmt = $this->db->prepare("
                DELETE FROM chart_ledger
                WHERE fiscal_year = ?
                AND app_no = 'YEAREND'
            ");
            $stmt->execute(array($this->fiscal_year_id));
            $this->logAction('DELETE', 'Removed closing entries from current fiscal year');

            // Remove all opening entries from the next fiscal year
            $stmt = $this->db->prepare("
                DELETE FROM chart_ledger
                WHERE fiscal_year = ?
                AND app_no = 'YEARSTART'
            ");
            $stmt->execute(array($this->next_year_id));
            $this->logAction('DELETE', 'Removed opening entries from next fiscal year');

            // Delete the next fiscal year
            $stmt = $this->db->prepare("
                DELETE FROM chart_fiscal_year
                WHERE id = ?
            ");
            $stmt->execute(array($this->next_year_id));
            $this->logAction('DELETE', 'Deleted next fiscal year');

            // Reopen the current fiscal year
            $stmt = $this->db->prepare("
                UPDATE chart_fiscal_year
                SET closed = 0
                WHERE id = ?
            ");
            $stmt->execute(array($this->fiscal_year_id));
            $this->logAction('UPDATE', 'Reopened current fiscal year');

            // Verify the rollback
            $this->verifyRollback();

            $this->logAction('COMPLETE', 'Fiscal year rollback completed successfully');

            $this->db->commit();
            return array('success' => true, 'message' => 'Fiscal year rollback completed successfully');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logAction('ERROR', $e->getMessage(), null, 0, 0, 'FAILED');
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    private function verifyRollback()
    {
        // Verify no closing entries remain
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM chart_ledger
            WHERE fiscal_year = ?
            AND app_no = 'YEAREND'
        ");
        $stmt->execute(array($this->fiscal_year_id));
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            throw new Exception("Verification failed: Closing entries still exist");
        }

        // Verify fiscal year is open
        $stmt = $this->db->prepare("
            SELECT closed
            FROM chart_fiscal_year
            WHERE id = ?
        ");
        $stmt->execute(array($this->fiscal_year_id));
        if ($stmt->fetch(PDO::FETCH_ASSOC)['closed'] == 1) {
            throw new Exception("Verification failed: Fiscal year still marked as closed");
        }

        // Verify next fiscal year is deleted
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM chart_fiscal_year
            WHERE id = ?
        ");
        $stmt->execute(array($this->next_year_id));
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            throw new Exception("Verification failed: Next fiscal year still exists");
        }
    }
}

try {
    // Create instance of FiscalYearRollback class
    $yearRollback = new FiscalYearRollback(
        $db,
        $fiscal_year_id,
        ($_SESSION['fullname']) ? $_SESSION['fullname'] : 'SYSTEM'
    );

    // Perform the rollback
    $result = $yearRollback->rollback();

    // Return the result
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error rolling back fiscal year: ' . $e->getMessage()
    ]);
}
