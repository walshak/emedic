<?php
require_once('Connections/Conn.php');

class TestFiscalYearClosing
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

    public function close()
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT closed FROM chart_fiscal_year WHERE id = ?");
            $stmt->execute(array($this->current_year_id));
            $year = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($year['closed'] == 1) {
                throw new Exception("Fiscal year is already closed");
            }

            $income_total = $this->calculateClassTotal(4);
            $expense_total = $this->calculateClassTotal(5);
            $retained_earnings = $income_total + $expense_total;

            echo "Income Total: $income_total\n";
            echo "Expense Total: $expense_total\n";
            echo "Retained Earnings: $retained_earnings\n\n";

            $this->closeAccountClass(4, $income_total);
            $this->closeAccountClass(5, $expense_total);
            $this->createRetainedEarningsEntry($retained_earnings);

            $stmt = $this->db->prepare("UPDATE chart_fiscal_year SET closed = 1 WHERE id = ?");
            $stmt->execute(array($this->current_year_id));

            $stmt = $this->db->prepare("INSERT INTO chart_fiscal_year (begin, end, closed) VALUES (?, ?, 0)");
            $stmt->execute(array($this->new_year_start, $this->new_year_end));
            $this->new_year_id = $this->db->lastInsertId();

            $this->carryForwardBalances($this->new_year_id);

            echo "== LEDGER ENTRIES GENERATED ==\n";
            $stmt = $this->db->prepare("SELECT app_no, account_no, transc_type, dr_amt, cr_amt, bal, fiscal_year, date_entry2 FROM chart_ledger WHERE lg_ref_no LIKE 'FY_CLOSE_%' OR lg_ref_no LIKE 'FY_OPEN_%'");
            $stmt->execute();
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($entries as $entry) {
                echo str_pad($entry['app_no'], 12) . " | Acc: " . str_pad($entry['account_no'], 6) . " | " . str_pad($entry['transc_type'], 6) . " | DR: " . str_pad($entry['dr_amt'], 8) . " | CR: " . str_pad($entry['cr_amt'], 8) . " | Bal: " . str_pad($entry['bal'], 8) . " | FY: {$entry['fiscal_year']} | Date: {$entry['date_entry2']}\n";
            }
            
            $stmt = $this->db->prepare("SELECT SUM(dr_amt) as dr, SUM(cr_amt) as cr FROM chart_ledger WHERE lg_ref_no = ?");
            $stmt->execute(['FY_CLOSE_' . $this->current_year_id]);
            $bal = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "\nFY_CLOSE Total DR: {$bal['dr']} | Total CR: {$bal['cr']}\n";

            $stmt = $this->db->prepare("SELECT SUM(dr_amt) as dr, SUM(cr_amt) as cr FROM chart_ledger WHERE lg_ref_no = ?");
            $stmt->execute(['FY_OPEN_' . $this->new_year_id]);
            $bal = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "FY_OPEN Total DR: {$bal['dr']} | Total CR: {$bal['cr']}\n";

            echo "\nRolling back transaction to prevent permanent changes...\n";
            $this->db->rollBack();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            echo "Error: " . $e->getMessage() . "\n";
            return false;
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
        if ($total == 0) return;

        $stmt = $this->db->prepare("SELECT begin, end FROM chart_fiscal_year WHERE id = ?");
        $stmt->execute(array($this->current_year_id));
        $fiscalYear = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastDayOfYear = $fiscalYear['end'];

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
            if ($account['account_balance'] == 0) continue;

            $balance = floatval($account['account_balance']);
            $transType = ($balance > 0) ? 'DEBIT' : 'CREDIT';
            $drAmt = ($transType == 'DEBIT') ? abs($balance) : 0;
            $crAmt = ($transType == 'CREDIT') ? abs($balance) : 0;

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
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )";

            $description = ($class_id == 4) ? 'Income closing to Retained Earnings' : 'Expense closing to Retained Earnings';
            $closingTimestamp = date('Y-m-d H:i:s', strtotime($lastDayOfYear . ' 23:59:59'));
            
            $stmt = $this->db->prepare($insertSql);
            $stmt->execute(array(
                $description,
                $transType,
                $drAmt,
                $crAmt,
                $closingTimestamp,
                $lastDayOfYear,
                $closingTimestamp,
                $account['account_code'],
                'FY_CLOSE_' . $this->current_year_id,
                $this->current_year_id
            ));
        }
    }

    private function createRetainedEarningsEntry($amount)
    {
        if ($amount == 0) return;

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
            WHERE cg.class_id IN (1, 2, 3)
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
        }
    }
}

$test = new TestFiscalYearClosing($db, 1, '2026-07-06', '2027-07-05');
$test->close();

?>
