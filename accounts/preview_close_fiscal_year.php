<?php
session_start();
require_once('../Connections/Conn.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$current_year_id = filter_input(INPUT_POST, 'current_year_id', FILTER_VALIDATE_INT);

if (!$current_year_id) {
    die(json_encode(['success' => false, 'message' => 'Invalid fiscal year ID']));
}

try {
    function getPreviewClassTotal($db, $class_id, $year_id) {
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

        $stmt = $db->prepare($sql);
        $stmt->execute(array($class_id, $year_id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total']);
    }
    
    function getPreviewBalances($db, $year_id) {
        $sql = "
            SELECT 
                SUM(CASE WHEN cg.class_id = 1 THEN
                    (CASE WHEN transc_type = 'DEBIT' THEN dr_amt WHEN transc_type = 'CREDIT' THEN -cr_amt END)
                ELSE 0 END) as assets,
                SUM(CASE WHEN cg.class_id = 2 THEN
                    (CASE WHEN transc_type = 'DEBIT' THEN dr_amt WHEN transc_type = 'CREDIT' THEN -cr_amt END)
                ELSE 0 END) as liabilities,
                SUM(CASE WHEN cg.class_id = 3 THEN
                    (CASE WHEN transc_type = 'DEBIT' THEN dr_amt WHEN transc_type = 'CREDIT' THEN -cr_amt END)
                ELSE 0 END) as equity
            FROM chart_ledger cl
            JOIN chart_accounts ca ON cl.account_no = ca.account_code
            JOIN chart_groups cg ON ca.account_group = cg.id
            WHERE cg.class_id IN (1, 2, 3)
            AND cl.fiscal_year = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$year_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    $income_total = getPreviewClassTotal($db, 4, $current_year_id);
    $expense_total = getPreviewClassTotal($db, 5, $current_year_id);
    $retained_earnings = $income_total + $expense_total;
    
    $balances = getPreviewBalances($db, $current_year_id);
    $assets = floatval(isset($balances['assets']) ? $balances['assets'] : 0);
    $liabilities = floatval(isset($balances['liabilities']) ? $balances['liabilities'] : 0);
    $equity = floatval(isset($balances['equity']) ? $balances['equity'] : 0);

    echo json_encode([
        'success' => true,
        'data' => [
            'income' => $income_total,
            'expenses' => $expense_total,
            'net_income' => $retained_earnings,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'retained_earnings_entry' => abs($retained_earnings)
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
