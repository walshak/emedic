<?php
include("../Connections/Conn.php");

$ddset   = $_POST['ddset'];
$dept_id = $_POST['dept_id'];
$start   = $_POST['start'];
$to      = $_POST['to'];
$staff   = $_POST['staff'];

$setdate = date('Y-m-d');

/* CSV headers */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=credit_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

/* CSV column titles */
fputcsv($output, array(
    'AP No',
    'Hospital No',
    'Department',
    'Service / Consumable',
    'Qty',
    'Claim Amount',
    'Pay Amount',
    'Prepared / Dispensed By',
    'Date'
));

/* Main query (same logic as report) */
if ($ddset == 1) {
    $q = $db->query("
        SELECT DISTINCT hospital_no
        FROM patient_ap_services
        WHERE cr='1'
          AND dept_id='$dept_id'
          $staff
          AND paystatus='0'
          AND DATE(date_entry) BETWEEN '$start' AND '$to'
    ");
} else {
    $q = $db->query("
        SELECT DISTINCT hospital_no
        FROM patient_ap_services
        WHERE cr='1'
          AND dept_id='$dept_id'
          $staff
          AND paystatus='0'
          AND DATE(date_entry)='$setdate'
    ");
}

while ($r = $q->fetch(PDO::FETCH_ASSOC)) {

    $hospital_no = $r['hospital_no'];

    if ($ddset == 1) {
        $rs = $db->query("
            SELECT *
            FROM patient_ap_services
            WHERE cr='1'
              AND paystatus='0'
              AND hospital_no='$hospital_no'
              AND dept_id='$dept_id'
              $staff
              AND DATE(date_entry) BETWEEN '$start' AND '$to'
        ");
    } else {
        $rs = $db->query("
            SELECT *
            FROM patient_ap_services
            WHERE cr='1'
              AND paystatus='0'
              AND hospital_no='$hospital_no'
              AND dept_id='$dept_id'
              $staff
              AND DATE(date_entry)='$setdate'
        ");
    }

    while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {

        fputcsv($output, array(
            $row['app_no'],
            $hospital_no,
            $row['serv_group'],
            $row['item_services'],
            $row['qty'],
            $row['claim_amt'],
            $row['pay'],
            $row['prepared_by'] . ' / ' . $row['dsp_by'],
            date('d-M-Y h:i:s a', strtotime($row['date_entry']))
        ));
    }
}

fclose($output);
exit;
