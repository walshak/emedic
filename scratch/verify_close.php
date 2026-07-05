<?php
require_once('Connections/Conn.php');

echo "=== FISCAL YEAR VERIFICATION ===\n\n";

// 1. Check Fiscal Years
$stmt = $db->query("SELECT * FROM chart_fiscal_year ORDER BY id DESC LIMIT 2");
$years = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Recent Fiscal Years:\n";
foreach ($years as $y) {
    echo "ID: {$y['id']} | Begin: {$y['begin']} | End: {$y['end']} | Closed: {$y['closed']}\n";
}

if (count($years) < 2) {
    die("Error: Not enough fiscal years found.\n");
}

$new_year = $years[0];
$old_year = $years[1];

if ($new_year['closed'] != 0) {
    echo "[!] WARNING: New year is closed!\n";
} else {
    echo "[+] New year is open.\n";
}

if ($old_year['closed'] != 1) {
    echo "[!] WARNING: Old year is not closed!\n";
} else {
    echo "[+] Old year is closed.\n";
}

echo "\n=== OLD YEAR CLOSING ENTRIES ===\n";
$stmt = $db->prepare("SELECT SUM(dr_amt) as total_dr, SUM(cr_amt) as total_cr FROM chart_ledger WHERE lg_ref_no = ? AND fiscal_year = ?");
$stmt->execute(['FY_CLOSE_' . $old_year['id'], $old_year['id']]);
$close_bal = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total Debits:  " . number_format($close_bal['total_dr'], 2) . "\n";
echo "Total Credits: " . number_format($close_bal['total_cr'], 2) . "\n";
if ($close_bal['total_dr'] == $close_bal['total_cr'] && $close_bal['total_dr'] > 0) {
    echo "[+] Closing entry balances perfectly.\n";
} else {
    echo "[!] WARNING: Closing entry is unbalanced or zero!\n";
}

echo "\n=== OLD YEAR ACCOUNT BALANCES (INCLUDING YEAREND) ===\n";
$sql = "
    SELECT cg.class_id, cc.class_name, 
    SUM(CASE WHEN cl.transc_type = 'DEBIT' THEN cl.dr_amt ELSE 0 END) as total_dr,
    SUM(CASE WHEN cl.transc_type = 'CREDIT' THEN cl.cr_amt ELSE 0 END) as total_cr,
    SUM(CASE WHEN cl.transc_type = 'DEBIT' THEN cl.dr_amt ELSE -cl.cr_amt END) as net_balance
    FROM chart_ledger cl
    JOIN chart_accounts ca ON cl.account_no = ca.account_code
    JOIN chart_groups cg ON ca.account_group = cg.id
    JOIN chart_class cc ON cg.class_id = cc.cid
    WHERE cl.fiscal_year = ?
    GROUP BY cg.class_id, cc.class_name
";
$stmt = $db->prepare($sql);
$stmt->execute([$old_year['id']]);
$class_balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($class_balances as $cb) {
    echo str_pad($cb['class_name'], 12) . " | Net Bal: " . str_pad(number_format($cb['net_balance'], 2), 12) . " | DR: " . number_format($cb['total_dr'], 2) . " | CR: " . number_format($cb['total_cr'], 2) . "\n";
    if (in_array($cb['class_id'], [4, 5]) && abs($cb['net_balance']) > 0.01) {
        echo "[!] WARNING: Income/Expense account not zeroed out properly!\n";
    }
}

echo "\n=== NEW YEAR OPENING BALANCES ===\n";
$stmt = $db->prepare("SELECT SUM(dr_amt) as total_dr, SUM(cr_amt) as total_cr FROM chart_ledger WHERE lg_ref_no = ? AND fiscal_year = ?");
$stmt->execute(['FY_OPEN_' . $new_year['id'], $new_year['id']]);
$open_bal = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total Debits:  " . number_format($open_bal['total_dr'], 2) . "\n";
echo "Total Credits: " . number_format($open_bal['total_cr'], 2) . "\n";
if ($open_bal['total_dr'] == $open_bal['total_cr'] && $open_bal['total_dr'] > 0) {
    echo "[+] Opening entry balances perfectly.\n";
} else {
    echo "[!] WARNING: Opening entry is unbalanced or zero!\n";
}

echo "\n=== NEW YEAR ACCOUNT BALANCES (SHOULD EQUAL OPENING BALANCES) ===\n";
$stmt = $db->prepare($sql);
$stmt->execute([$new_year['id']]);
$new_class_balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($new_class_balances as $cb) {
    echo str_pad($cb['class_name'], 12) . " | Net Bal: " . str_pad(number_format($cb['net_balance'], 2), 12) . " | DR: " . number_format($cb['total_dr'], 2) . " | CR: " . number_format($cb['total_cr'], 2) . "\n";
    if (in_array($cb['class_id'], [4, 5]) && abs($cb['net_balance']) > 0.01) {
        echo "[!] WARNING: Income/Expense accounts carried forward! They should be zero.\n";
    }
}

echo "\n=== RETAINED EARNINGS TRACE (Acc 2204) ===\n";
$stmt = $db->prepare("SELECT fiscal_year, app_no, transc_type, dr_amt, cr_amt, date_entry2 FROM chart_ledger WHERE account_no = 2204 ORDER BY fiscal_year ASC, date_entry ASC");
$stmt->execute();
$re_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($re_entries as $re) {
    echo "FY: {$re['fiscal_year']} | App: " . str_pad($re['app_no'], 10) . " | {$re['transc_type']} | DR: {$re['dr_amt']} | CR: {$re['cr_amt']} | Date: {$re['date_entry2']}\n";
}

?>
