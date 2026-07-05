<?php
session_start();
// close_fiscal_year.php
require_once('../Connections/Conn.php');

// Verify that the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get and validate the input
$current_year_id = filter_input(INPUT_POST, 'current_year_id', FILTER_VALIDATE_INT);
$new_year_start = htmlspecialchars(trim(filter_input(INPUT_POST, 'new_year_start')), ENT_QUOTES, 'UTF-8');
$new_year_end = htmlspecialchars(trim(filter_input(INPUT_POST, 'new_year_end')), ENT_QUOTES, 'UTF-8');


// Validate inputs
if (!$current_year_id || !$new_year_start || !$new_year_end) {
    die(json_encode(['success' => false, 'message' => 'Invalid input parameters']));
}

// Validate dates
if (strtotime($new_year_end) <= strtotime($new_year_start)) {
    die(json_encode(['success' => false, 'message' => 'End date must be after start date']));
}

class FiscalYearClosing
{
    private $db;
    private $current_year_id;
    private $new_year_id;
    private $new_year_start;
    private $new_year_end;
    private $user;

    public function __construct($db, $current_year_id, $new_year_start, $new_year_end, $user = 'SYSTEM')
    {
        $this->db = $db;
        $this->current_year_id = $current_year_id;
        $this->new_year_start = $new_year_start;
        $this->new_year_end = $new_year_end;
        $this->user = $user;
    }

    private function logAction($action_type, $description, $account = null, $dr_amt = 0, $cr_amt = 0, $status = 'SUCCESS', $additional_info = null)
    {
        $sql = "
            INSERT INTO chart_closing_log (
                fiscal_year_id, 
                new_fiscal_year_id,
                action_type,
                description,
                account_affected,
                debit_amount,
                credit_amount,
                user,
                status,
                additional_info
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(
            $this->current_year_id,
            $this->new_year_id,
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

    public function close()
    {
        try {
            $this->db->beginTransaction();

            $this->logAction('START_CLOSING', 'Starting fiscal year closing process');

            // Check if year is already closed
            $stmt = $this->db->prepare("SELECT closed FROM chart_fiscal_year WHERE id = ?");
            $stmt->execute(array($this->current_year_id));
            $year = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($year['closed'] == 1) {
                $this->logAction('ERROR', 'Fiscal year already closed', null, 0, 0, 'FAILED');
                throw new Exception("Fiscal year is already closed");
            }

            // Calculate totals
            $income_total = $this->calculateClassTotal(4);
            $this->logAction(
                'CALCULATION',
                'Calculated total income',
                null,
                0,
                $income_total,
                'SUCCESS',
                "Total income for year: " . number_format($income_total, 2)
            );

            $expense_total = $this->calculateClassTotal(5);
            $this->logAction(
                'CALCULATION',
                'Calculated total expenses',
                null,
                $expense_total,
                0,
                'SUCCESS',
                "Total expenses for year: " . number_format($expense_total, 2)
            );

            $retained_earnings = $income_total + $expense_total;
            $this->logAction(
                'CALCULATION',
                'Calculated retained earnings',
                null,
                0,
                0,
                'SUCCESS',
                "Net retained earnings: " . number_format($retained_earnings, 2)
            );

            // Close income accounts
            $this->closeAccountClass(4, $income_total);

            // Close expense accounts
            $this->closeAccountClass(5, $expense_total);

            // Create retained earnings entry
            $this->createRetainedEarningsEntry($retained_earnings);

            // Mark current year as closed
            $stmt = $this->db->prepare("UPDATE chart_fiscal_year SET closed = 1 WHERE id = ?");
            $stmt->execute(array($this->current_year_id));
            $this->logAction('UPDATE', 'Marked current fiscal year as closed');

            // Create new fiscal year
            $stmt = $this->db->prepare("INSERT INTO chart_fiscal_year (begin, end, closed) VALUES (?, ?, 0)");
            $stmt->execute(array($this->new_year_start, $this->new_year_end));
            $this->new_year_id = $this->db->lastInsertId();
            $this->logAction(
                'CREATE',
                'Created new fiscal year',
                null,
                0,
                0,
                'SUCCESS',
                "New fiscal year ID: {$this->new_year_id}, Period: {$this->new_year_start} to {$this->new_year_end}"
            );

            // Carry forward balance sheet accounts
            $this->carryForwardBalances($this->new_year_id);

            $this->logAction('COMPLETE', 'Fiscal year closing process completed successfully');

            $this->db->commit();
            return array('success' => true, 'message' => 'Fiscal year closed successfully', 'new_year_id' => $this->new_year_id);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logAction('ERROR', $e->getMessage(), null, 0, 0, 'FAILED');
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    private function calculateClassTotal($class_id)
    {
        $sql = "
            SELECT COALESCE(SUM(
                CASE 
                    WHEN transc_type = 'CREDIT' THEN cr_amt
                    WHEN transc_type = 'DEBIT' THEN -dr_amt
                END
            ), 0) as total
            FROM chart_ledger cl
            JOIN chart_accounts ca ON cl.account_no = ca.account_code
            JOIN chart_groups cg ON ca.account_group = cg.id
            WHERE cg.class_id = ? 
            AND cl.fiscal_year = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($class_id, $this->current_year_id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total']);
    }

    private function closeAccountClass($class_id, $total)
    {
        if ($total == 0) {
            $this->logAction(
                'SKIP',
                'Skipped closing class - zero balance',
                null,
                0,
                0,
                'SUCCESS',
                "Class ID: $class_id has no balance to close"
            );
            return;
        }

        // Get fiscal year dates
        $stmt = $this->db->prepare("SELECT begin, end FROM chart_fiscal_year WHERE id = ?");
        $stmt->execute(array($this->current_year_id));
        $fiscalYear = $stmt->fetch(PDO::FETCH_ASSOC);

        $lastDayOfYear = $fiscalYear['end'];
        $firstDayOfNewYear = $this->new_year_start;

        $sql = "
            SELECT DISTINCT ca.account_code, ca.account_name, 
            COALESCE(SUM(
                CASE 
                    WHEN cl.transc_type = 'CREDIT' THEN cl.cr_amt
                    WHEN cl.transc_type = 'DEBIT' THEN -cl.dr_amt
                END
            ), 0) as account_balance
            FROM chart_accounts ca
            JOIN chart_groups cg ON ca.account_group = cg.id
            LEFT JOIN chart_ledger cl ON ca.account_code = cl.account_no 
                AND cl.fiscal_year = ?
            WHERE cg.class_id = ?
            GROUP BY ca.account_code, ca.account_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($this->current_year_id, $class_id));
        $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($accounts as $account) {
            if ($account['account_balance'] == 0) {
                continue; // Skip accounts with zero balance
            }

            // Create closing entry to zero out the account
            $balance = floatval($account['account_balance']);
            $transType = ($balance > 0) ? 'DEBIT' : 'CREDIT';
            $drAmt = ($transType == 'DEBIT') ? abs($balance) : 0;
            $crAmt = ($transType == 'CREDIT') ? abs($balance) : 0;

            // Insert the closing entry - using last day of fiscal year
            $insertSql = "
                INSERT INTO chart_ledger (
                    app_no, ref_value, item_services, transc_type, 
                    dr_amt, cr_amt, bal, prepared_by, date_entry, 
                    date_entry2, post_stamp, account_no, lg_ref_no, 
                    fiscal_year
                ) VALUES (
                    'YEAREND',
                    'Fiscal Year Closing Entry',
                    ?,
                    ?,
                    ?,
                    ?,
                    0,
                    'SYSTEM',
                    ?,  -- date_entry (timestamp)
                    ?,  -- date_entry2 (date)
                    ?,  -- post_stamp (timestamp)
                    ?,
                    ?,
                    ?
                )";

            $description = ($class_id == 4) ?
                'Income closing to Retained Earnings' :
                'Expense closing to Retained Earnings';

            // Set the closing entry to the last day of the fiscal year at 23:59:59
            $closingTimestamp = date('Y-m-d H:i:s', strtotime($lastDayOfYear . ' 23:59:59'));
            $closingDate = $lastDayOfYear;

            $stmt = $this->db->prepare($insertSql);
            $stmt->execute(array(
                $description,
                $transType,
                $drAmt,
                $crAmt,
                $closingTimestamp,  // date_entry
                $closingDate,       // date_entry2
                $closingTimestamp,  // post_stamp
                $account['account_code'],
                'FY_CLOSE_' . $this->current_year_id,
                $this->current_year_id
            ));

            $this->logAction(
                'CLOSE_ACCOUNT',
                ($class_id == 4 ? 'Income account closing' : 'Expense account closing'),
                $account['account_code'],
                $drAmt,
                $crAmt,
                'SUCCESS',
                "Account: {$account['account_name']} - Balance zeroed: " . number_format(abs($balance), 2) .
                    " (Closed on: $closingDate)"
            );
        }

        // Verify all accounts are zeroed out in the old fiscal year
        $verificationSql = "
            SELECT SUM(
                CASE 
                    WHEN transc_type = 'CREDIT' THEN cr_amt
                    WHEN transc_type = 'DEBIT' THEN -dr_amt
                END
            ) as final_balance
            FROM chart_ledger cl
            JOIN chart_accounts ca ON cl.account_no = ca.account_code
            JOIN chart_groups cg ON ca.account_group = cg.id
            WHERE cg.class_id = ? 
            AND cl.fiscal_year = ?";

        $stmt = $this->db->prepare($verificationSql);
        $stmt->execute(array($class_id, $this->current_year_id));
        $finalBalance = $stmt->fetch(PDO::FETCH_ASSOC)['final_balance'];

        if (abs($finalBalance) > 0.01) { // Allow for small rounding differences
            throw new Exception("Account class $class_id failed to zero out properly. Remaining balance: $finalBalance");
        }
    }
    private function createRetainedEarningsEntry($amount)
    {
        if ($amount == 0) {
            $this->logAction('SKIP', 'Skipped retained earnings entry - zero balance');
            return;
        }

        // Get fiscal year dates
        $stmt = $this->db->prepare("SELECT end FROM chart_fiscal_year WHERE id = ?");
        $stmt->execute(array($this->current_year_id));
        $lastDayOfYear = $stmt->fetch(PDO::FETCH_ASSOC)['end'];
        $closingTimestamp = date('Y-m-d H:i:s', strtotime($lastDayOfYear . ' 23:59:59'));

        $sql = "
            INSERT INTO chart_ledger (
                app_no, ref_value, item_services, transc_type, 
                dr_amt, cr_amt, bal, prepared_by, date_entry, 
                date_entry2, post_stamp, account_no, lg_ref_no, 
                fiscal_year
            ) VALUES (
                'YEAREND',
                'Fiscal Year Closing Entry',
                'Net Income to Retained Earnings',
                ?,
                ?,
                ?,
                ?,
                'SYSTEM',
                ?,
                ?,
                ?,
                '2204',
                ?,
                ?
            )";

        $transType = ($amount > 0) ? 'CREDIT' : 'DEBIT';
        $drAmt = ($amount < 0) ? abs($amount) : 0;
        $crAmt = ($amount > 0) ? $amount : 0;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(
            $transType,
            $drAmt,
            $crAmt,
            abs($amount),
            $closingTimestamp,
            $lastDayOfYear,
            $closingTimestamp,
            'FY_CLOSE_' . $this->current_year_id,
            $this->current_year_id
        ));

        $this->logAction(
            'RETAINED_EARNINGS',
            'Posted retained earnings entry',
            '2204',
            $drAmt,
            $crAmt,
            'SUCCESS',
            "Net amount: " . number_format($amount, 2)
        );
    }

    private function carryForwardBalances($new_year_id)
    {
        $sql = "
            SELECT 
                account_no,
                SUM(CASE 
                    WHEN transc_type = 'DEBIT' THEN dr_amt
                    WHEN transc_type = 'CREDIT' THEN -cr_amt
                END) as balance
            FROM chart_ledger cl
            JOIN chart_accounts ca ON cl.account_no = ca.account_code
            JOIN chart_groups cg ON ca.account_group = cg.id
            WHERE cg.class_id IN (1, 2, 3)  -- Assets, Liabilities, and Equity
            AND cl.fiscal_year = ?
            GROUP BY account_no
            HAVING balance != 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($this->current_year_id));
        $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($balances as $balance) {
            $insertSql = "
                INSERT INTO chart_ledger (
                    app_no, ref_value, item_services, transc_type, 
                    dr_amt, cr_amt, bal, prepared_by, date_entry, 
                    date_entry2, post_stamp, account_no, lg_ref_no, 
                    fiscal_year
                ) VALUES (
                    'YEARSTART',
                    'Opening Balance',
                    'Balance carried forward',
                    ?,
                    ?,
                    ?,
                    ?,
                    'SYSTEM',
                    ?,
                    ?,
                    NOW(),
                    ?,
                    ?,
                    ?
                )";

            $amount = floatval($balance['balance']);
            $transType = ($amount > 0) ? 'DEBIT' : 'CREDIT';
            $drAmt = ($amount > 0) ? abs($amount) : 0;
            $crAmt = ($amount < 0) ? abs($amount) : 0;

            $stmt = $this->db->prepare($insertSql);
            $stmt->execute(array(
                $transType,
                $drAmt,
                $crAmt,
                abs($amount),
                $this->new_year_start,
                $this->new_year_start,
                $balance['account_no'],
                'FY_OPEN_' . $new_year_id,
                $new_year_id
            ));

            $this->logAction(
                'CARRY_FORWARD',
                'Carried forward balance to new year',
                $balance['account_no'],
                $drAmt,
                $crAmt,
                'SUCCESS',
                "Opening balance: " . number_format(abs($amount), 2)
            );
        }
    }

    public function getClosingLog($includeDetails = false)
    {
        $sql = "
            SELECT * FROM chart_closing_log 
            WHERE fiscal_year_id = ? 
            ORDER BY closing_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array($this->current_year_id));

        if ($includeDetails) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
        }
    }
}

try {
    // Create instance of FiscalYearClosing class
    $yearClosing = new FiscalYearClosing(
        $db,
        $current_year_id,
        $new_year_start,
        $new_year_end,
        ($_SESSION['fullame']) ? $_SESSION['fullname'] : 'SYSTEM'
    );

    // Perform the closing
    $result = $yearClosing->close();

    // Return the result
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error closing fiscal year: ' . $e->getMessage()
    ]);
}
