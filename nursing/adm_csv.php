<?php
include("../Connections/Conn.php");

$admission_type = $_POST['admission_type'];
$room           = $_POST['room'];
$start          = $_POST['start'];
$to             = $_POST['to'];

if ($room != '') {
    $room_phase = " AND room_bed_sn = :room";
} else {
    $room_phase = '';
}

/* CSV headers */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=admission_report_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

/* CSV column headers */
fputcsv($output, array(
    'Hospital No',
    'Room/Bed',
    'Doctor',
    'Reason',
    'Condition',
    'Admission Date',
    'Discharge Date',
    'Nursing Consumables',
    'Nursing Services',
    'Pharmacy',
    'Investigation',
    'Medical Services',
    'Other Services',
    'Accommodation',
    'Claim',
    'Paid',
    'Credit'
));

$sql = "
SELECT * FROM admission 
WHERE adm_status = :adm_status
$room_phase
AND DATE(date_admit) BETWEEN :start AND :to
ORDER BY date_admit
";

$stt = $db->prepare($sql);
$stt->bindValue(':adm_status', $admission_type);
$stt->bindValue(':start', $start);
$stt->bindValue(':to', $to);

if ($room != '') {
    $stt->bindValue(':room', $room);
}

$stt->execute();

while ($row = $stt->fetch(PDO::FETCH_ASSOC)) {

    $hospital_no   = $row['hospital_no'];
    $date_admit    = $row['date_admit'];
    $date_discharge = ($admission_type == '3')
        ? date("Y-m-d H:i:s")
        : $row['date_discharge'];

    /* Totals */
    $other = $medical = $invest = $nursing = $nur_com = $pharm = $accommodation = 0;
    $claim = $paid = $credit = 0;

    $stt2 = $db->prepare("
        SELECT * FROM patient_ap_services
        WHERE hospital_no = :hospital_no
        AND DATE(date_entry) BETWEEN :da AND :dd
    ");
    $stt2->execute(array(
        ':hospital_no' => $hospital_no,
        ':da' => $date_admit,
        ':dd' => $date_discharge
    ));

    while ($s = $stt2->fetch(PDO::FETCH_ASSOC)) {

        $pay = ($s['claim_amt'] > 0) ? $s['claim_amt'] : $s['pay'];

        if ($s['paystatus'] == 1) {
            switch ($s['serv_group']) {
                case 'Other Services':
                case 'Consultation':
                case 'Registration':
                    $other += $pay;
                    break;
                case 'Medical Services':
                    $medical += $pay;
                    break;
                case 'Laboratory':
                case 'Radiology':
                    $invest += $pay;
                    break;
                case 'Nursing Services':
                    $nursing += $pay;
                    break;
                case 'Nursing Consumable':
                    $nur_com += $pay;
                    break;
                case 'Pharmacy':
                    $pharm += $pay;
                    break;
                case 'Bed Space/Accommodation':
                    $accommodation += $pay;
                    break;
            }
        }

        if ($s['claim_amt'] > 0 && $s['pay'] == 0 && $s['paystatus'] == 1) {
            $claim += $s['claim_amt'];
        }

        if ($s['claim_amt'] == 0 && $s['pay'] > 0 && $s['paystatus'] == 1) {
            $paid += $s['pay'];
        }

        if (($s['drug_status'] == 1 || $s['cr'] == 1) && $s['paystatus'] == 0) {
            $credit += $s['pay'];
        }
    }

    /* Write CSV row */
    fputcsv($output, array(
        $hospital_no,
        $row['room_bed'],
        $row['doc_incharge'],
        $row['reason_adm'],
        $row['cond'],
        date('Y-m-d', strtotime($date_admit)),
        date('Y-m-d', strtotime($date_discharge)),
        $nur_com,
        $nursing,
        $pharm,
        $invest,
        $medical,
        $other,
        $accommodation,
        $claim,
        $paid,
        $credit
    ));
}

fclose($output);
exit;
