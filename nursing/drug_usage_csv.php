<?php
include("../Connections/Conn.php");

$searchdrug_inv = $_POST['searchdrug_inv'];
$dept_id  = $_POST['dept_id'];
$ddset    = $_POST['ddset'];
$start    = $_POST['start'];
$to       = $_POST['to'];
$setdate  = $_POST['setdate'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=drug_usage_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

/* CSV headers */
fputcsv($output, array(
    'App No',
    'Hospital No',
    'Service Group',
    'Service Name',
    'Qty',
    'Claim Amount',
    'Paid Amount',
    'Prepared / Dispensed By',
    'Date'
));

if ($ddset == 1) {
    $sql = "
        SELECT * FROM patient_ap_services
        WHERE paystatus = '1'
        AND drug_sn = :drug
        AND dept_id = :dept_id
        AND DATE(date_entry) BETWEEN :start AND :to
        ORDER BY item_services
    ";
} else {
    $sql = "
        SELECT * FROM patient_ap_services
        WHERE paystatus = '1'
        AND drug_sn = :drug
        AND dept_id = :dept_id
        AND DATE(date_entry) = :setdate
        ORDER BY item_services
    ";
}

$stt = $db->prepare($sql);
$stt->bindValue(':drug', $searchdrug_inv);
$stt->bindValue(':dept_id', $dept_id);

if ($ddset == 1) {
    $stt->bindValue(':start', $start);
    $stt->bindValue(':to', $to);
} else {
    $stt->bindValue(':setdate', $setdate);
}

$stt->execute();

$pvt   = 0;
$claim = 0;

while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {

    if ($row['claim_amt'] == 0 && $row['pay'] > 0) {
        $pvt += $row['pay'];
    }
    if ($row['claim_amt'] > 0 && $row['pay'] == 0) {
        $claim += $row['claim_amt'];
    }

    fputcsv($output, array(
        $row['app_no'],
        $row['hospital_no'],
        $row['serv_group'],
        $row['item_services'],
        $row['qty'],
        number_format($row['claim_amt'], 2, '.', ''),
        number_format($row['pay'], 2, '.', ''),
        $row['prepared_by'] . ' / ' . $row['dsp_by'],
        date('d-M-Y H:i:s', strtotime($row['date_entry']))
    ));
}

/* Totals */
fputcsv($output, array(
    'TOTAL (CASH)',
    '',
    '',
    '',
    '',
    '',
    number_format($pvt, 2, '.', ''),
    '',
    ''
));

fputcsv($output, array(
    'TOTAL (CLAIM)',
    '',
    '',
    '',
    '',
    number_format($claim, 2, '.', ''),
    '',
    '',
    ''
));

fclose($output);
exit;
