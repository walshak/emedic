<?php
include_once '../../Connections/Conn.php';
include_once 'functions.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=all_journals_export.csv');

// Get GET data (for download)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$prep = isset($_GET['prep']) ? $_GET['prep'] : '';
$gl_acct = isset($_GET['gl_acct']) ? $_GET['gl_acct'] : '';
$DR_CR = isset($_GET['DR_CR']) ? $_GET['DR_CR'] : '';
$account_payable = isset($_GET['account_payable']) ? $_GET['account_payable'] : '';
$hmo_dues = isset($_GET['hmo_dues']) ? $_GET['hmo_dues'] : '';
$account_recievables = isset($_GET['account_recievables']) ? $_GET['account_recievables'] : '';
$patient_deposit = isset($_GET['patient_deposit']) ? $_GET['patient_deposit'] : '';

$sql = "SELECT chart_ledger.lg_ref_no as ref, 
                chart_ledger.prepared_by, 
                chart_ledger.date_entry2, 
                chart_ledger.account_no, 
                chart_ledger.item_services, 
                chart_ledger.dr_amt, 
                chart_ledger.cr_amt, 
                chart_ledger.transc_type, 
                chart_ledger.insurance_no, 
                chart_ledger.hospital_no, 
                chart_ledger.can_delete, 
                chart_ledger.sn, 
                CONCAT(enrollee.surname,', ', enrollee.fname, ' (', enrollee.hospital_no, ')') as client
            FROM chart_ledger 
            LEFT JOIN enrollee ON enrollee.hospital_no = chart_ledger.hospital_no
            WHERE DATE(chart_ledger.date_entry2) BETWEEN ? AND ?";
$params = array($start_date, $end_date);

if (!empty($prep)) {
    $sql .= " AND prepared_by = ?";
    $params[] = $prep;
}
if (!empty($gl_acct)) {
    $sql .= " AND account_no IN ($gl_acct)";
}
if (!empty($DR_CR)) {
    $sql .= " AND transc_type = ?";
    $params[] = $DR_CR;
}
if (!empty($account_payable)) {
    $sql .= " AND insurance_no = ? AND account_no = 2124";
    $params[] = $account_payable;
}
if (!empty($hmo_dues)) {
    $sql .= " AND insurance_no = ? AND account_no = 1114";
    $params[] = $hmo_dues;
}
if (!empty($account_recievables)) {
    $sql .= " AND hospital_no = ? AND account_no = 1114";
    $params[] = $account_recievables;
}
if (!empty($patient_deposit)) {
    $sql .= " AND hospital_no = ? AND account_no = 2121";
    $params[] = $patient_deposit;
}

$sql .= " ORDER BY chart_ledger.date_entry2 ASC, chart_ledger.sn ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);

$output = fopen('php://output', 'w');


// CSV header as requested order (without Can Delete & SN)
fputcsv($output, [
    'S/N',
    'Account',
    'Date',
    'Description',
    'Client',
    'DR',
    'CR',
    'DR_CR Difference',
    'Entry by',
    'Ref',
    'Account Code',
    'Type',
    'Hospital No',
    'Insurance No'
]);


$sn = 1;
$runningDR = 0;
$runningCR = 0;
$last_ref = null;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Visual separator for new group
    if ($last_ref !== null && $row['ref'] !== $last_ref) {
        fputcsv($output, [str_repeat('-', 10) . ' Group: ' . $row['ref'] . ' ' . str_repeat('-', 10)]);
    }
    $last_ref = $row['ref'];

    // Prepend account code in square brackets
    $account_label = '';
    if ($row['account_no'] == 2124) {
        $account_label = '[' . $row['account_no'] . '] ' . getInsuranceName($row['insurance_no']);
    } else {
        $account_label = '[' . $row['account_no'] . '] ' . getAccountName($row['account_no']);
    }

    $dr = $row['transc_type'] == 'DEBIT' ? floatval($row['dr_amt']) : 0;
    $cr = $row['transc_type'] == 'CREDIT' ? floatval($row['cr_amt']) : 0;
    $runningDR += $dr;
    $runningCR += $cr;
    $dr_cr_diff = $runningDR - $runningCR;

    fputcsv($output, [
        $sn,
        $account_label,
        $row['date_entry2'],
        $row['item_services'],
        $row['client'],
        $dr,
        $cr,
        $dr_cr_diff,
        $row['prepared_by'],
        $row['ref'],
        $row['account_no'],
        $row['transc_type'],
        $row['hospital_no'],
        $row['insurance_no']
    ]);
    $sn++;
}
fclose($output);
exit;
