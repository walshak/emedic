<?php

include("../Connections/Conn.php");

$dept_id   = $_POST['dept_id'];
$start     = $_POST['start'];
$to        = $_POST['to'];
$paystatus = $_POST['paystatus'];

/* CSV headers */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=dept_visit_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

/* CSV column titles */
fputcsv($output, array(
    'Hospital No',
    'Total Visits',
    'Claim Amount',
    'Paid Amount'
));

$sql = "
SELECT DISTINCT hospital_no
FROM patient_ap_services
WHERE paystatus = :paystatus
AND dept_id = :dept_id
AND DATE(date_entry) BETWEEN :start AND :to
ORDER BY hospital_no
";

$stt = $db->prepare($sql);
$stt->bindValue(':paystatus', $paystatus);
$stt->bindValue(':dept_id', $dept_id);
$stt->bindValue(':start', $start);
$stt->bindValue(':to', $to);
$stt->execute();

$TotalP_claim = 0;
$Tp_pay = 0;
$Tvisit = 0;

while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {

    $hospital_no = $row['hospital_no'];

    /* Sum claim & pay */
    $stt2 = $db->prepare("
        SELECT 
            SUM(claim_amt) AS P_claim,
            SUM(pay) AS p_pay
        FROM patient_ap_services
        WHERE paystatus = :paystatus
        AND dept_id = :dept_id
        AND hospital_no = :hospital_no
        AND DATE(date_entry) BETWEEN :start AND :to
    ");
    $stt2->execute(array(
        ':paystatus' => $paystatus,
        ':dept_id' => $dept_id,
        ':hospital_no' => $hospital_no,
        ':start' => $start,
        ':to' => $to
    ));
    $sum = $stt2->fetch(PDO::FETCH_ASSOC);

    /* Visit count */
    $stt_visit = $db->prepare("
        SELECT COUNT(DISTINCT app_no) AS visits
        FROM patient_ap_services
        WHERE hospital_no = :hospital_no
        AND paystatus = :paystatus
        AND dept_id = :dept_id
        AND DATE(date_entry) BETWEEN :start AND :to
    ");
    $stt_visit->execute(array(
        ':hospital_no' => $hospital_no,
        ':paystatus' => $paystatus,
        ':dept_id' => $dept_id,
        ':start' => $start,
        ':to' => $to
    ));
    $visit = $stt_visit->fetch(PDO::FETCH_ASSOC);

    $TotalP_claim += $sum['P_claim'];
    $Tp_pay += $sum['p_pay'];
    $Tvisit += $visit['visits'];

    fputcsv($output, array(
        $hospital_no,
        $visit['visits'],
        number_format($sum['P_claim'], 2, '.', ''),
        number_format($sum['p_pay'], 2, '.', '')
    ));
}

/* Totals row */
fputcsv($output, array(
    'TOTAL',
    $Tvisit,
    number_format($TotalP_claim, 2, '.', ''),
    number_format($Tp_pay, 2, '.', '')
));

fclose($output);
exit;
