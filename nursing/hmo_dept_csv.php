<?php
include("../Connections/Conn.php");

$hmo_list = $_POST['hmo_nhis'];
$hmo_type = $_POST['hmo_type'];
$dept_id  = $_POST['dept_id'];
$start    = $_POST['start'];
$to       = $_POST['to'];
$ddset    = $_POST['ddset'];
$setdate  = $_POST['setdate'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=hmo_department_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

/* CSV Header */
fputcsv($output, array(
    'HMO Name',
    'App No',
    'Hospital No',
    'Item Name',
    'Qty',
    'Claim Amount',
    'Paid Amount',
    'Prepared / Dispensed By',
    'Date'
));

$Tclaim_hmo = 0;
$Tpay_hmo   = 0;

foreach ($hmo_list as $hmo_nhis_no) {

    $parts = explode("__", $hmo_nhis_no);
    $hmo_no   = $parts[0];
    $hmo_name = $parts[1];

    if ($ddset == 1) {
        $search_date = " AND DATE(ap.date_entry) BETWEEN :start AND :to";
    } else {
        $search_date = " AND DATE(ap.date_entry) = :setdate";
    }

    $sql = "
        SELECT enl.hospital_no, ap.*
        FROM enrollee AS enl
        INNER JOIN patient_ap_services AS ap
            ON ap.hospital_no = enl.hospital_no
        WHERE enl.hmo_no = :hmo_no
        AND enl.insurance = :insur
        AND ap.dept_id = :dept_id
        AND ap.paystatus = '1'
        $search_date
    ";

    $stt = $db->prepare($sql);
    $stt->bindValue(':hmo_no', $hmo_no);
    $stt->bindValue(':insur', $hmo_type);
    $stt->bindValue(':dept_id', $dept_id);

    if ($ddset == 1) {
        $stt->bindValue(':start', $start);
        $stt->bindValue(':to', $to);
    } else {
        $stt->bindValue(':setdate', $setdate);
    }

    $stt->execute();

    $claim_hmo = 0;
    $pay_hmo   = 0;

    while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {

        $claim_hmo += $row['claim_amt'];
        $pay_hmo   += $row['pay'];

        $Tclaim_hmo += $row['claim_amt'];
        $Tpay_hmo   += $row['pay'];

        fputcsv($output, array(
            $hmo_name,
            $row['app_no'],
            $row['hospital_no'],
            $row['item_services'],
            $row['qty'],
            number_format($row['claim_amt'], 2, '.', ''),
            number_format($row['pay'], 2, '.', ''),
            $row['prepared_by'] . ' / ' . $row['dsp_by'],
            date('d-M-Y H:i:s', strtotime($row['date_entry']))
        ));
    }

    /* HMO subtotal */
    if ($claim_hmo > 0 || $pay_hmo > 0) {
        fputcsv($output, array(
            $hmo_name . ' TOTAL',
            '',
            '',
            '',
            '',
            number_format($claim_hmo, 2, '.', ''),
            number_format($pay_hmo, 2, '.', ''),
            '',
            ''
        ));
    }
}

/* Grand total */
fputcsv($output, array(
    'GRAND TOTAL',
    '',
    '',
    '',
    '',
    number_format($Tclaim_hmo, 2, '.', ''),
    number_format($Tpay_hmo, 2, '.', ''),
    '',
    ''
));

fclose($output);
exit;
