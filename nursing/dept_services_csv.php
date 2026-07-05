<?php
include("../Connections/Conn.php");

$dept_id = $_POST['dept_id'];
$ddset   = $_POST['ddset'];
$start   = $_POST['start'];
$to      = $_POST['to'];
$setdate = $_POST['setdate'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=dept_services_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

/* CSV headers */
fputcsv($output, array(
    'App No',
    'Hospital No',
    'Service Group',
    'Item',
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
        AND dept_id = :dept_id
        AND DATE(date_entry) BETWEEN :start AND :to
        ORDER BY item_services
    ";
} else {
    $sql = "
        SELECT * FROM patient_ap_services
        WHERE paystatus = '1'
        AND dept_id = :dept_id
        AND DATE(date_entry) = :setdate
        ORDER BY item_services
    ";
}

$stt = $db->prepare($sql);
$stt->bindValue(':dept_id', $dept_id);

if ($ddset == 1) {
    $stt->bindValue(':start', $start);
    $stt->bindValue(':to', $to);
} else {
    $stt->bindValue(':setdate', $setdate);
}

$stt->execute();

while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {

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

fclose($output);
exit;
