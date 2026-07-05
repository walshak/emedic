<?php
include("../Connections/Conn.php");
$start = $_POST['start'];
$to    = $_POST['to'];

/* CSV headers */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=labour_summary_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

/* Column headers */
fputcsv($output, array(
    'Hospital No',
    'Induction',
    'Delivery',
    'Perineum',
    'Placenta',
    'Cord',
    'Blood Loss (ml)',
    'Infant Status',
    'Mother BP',
    'Mother Pulse',
    'Mother Uterus',
    'Delivery Date',
    'Discharge Date',
    'Delivered By'
));

$sql = "SELECT * FROM labour_summary 
        WHERE DATE(delivery_date) BETWEEN :start AND :to";

$stt = $db->prepare($sql);
$stt->bindValue(':start', $start);
$stt->bindValue(':to', $to);
$stt->execute();

while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {

    fputcsv($output, array(
        $row['hos_no'],
        $row['induction'],
        $row['delivery'],
        $row['perineum'],
        $row['placenta'],
        $row['cord'],
        $row['blood_loss'],
        $row['infant_status'],
        $row['mother_bp'],
        $row['mother_pulse'],
        $row['mother_uterus'],
        date('d-m-Y', strtotime($row['delivery_date'])),
        date('d-m-Y', strtotime($row['discharge_date'])),
        $row['delivered_by']
    ));
}

fclose($output);
exit;
