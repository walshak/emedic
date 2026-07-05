<?php
require_once '../Connections/Conn.php'; // <-- adjust to your DB connection file

// ==========================
// DATE FILTERS
// ==========================
$currentDate = date('Y-m-d');
$start_date = !empty($_GET['from_date']) ? $_GET['from_date'] : $currentDate;
$end_date   = !empty($_GET['to_date'])   ? $_GET['to_date']   : $currentDate;

$params = [$start_date, $end_date];

// ==========================
// INSURANCE FILTER
// ==========================
$insuranceSQL = '';

if (!empty($_GET['hmo_nhis'])) {
    $insuranceNames = (array) $_GET['hmo_nhis'];

    // If "all" is selected
    if (in_array('all', $insuranceNames)) {
        $insuranceSQL = "
            AND i.insurance_type IN ('PHIS', 'Corporate', 'NHIS')
            AND i.status = 'active'
        ";
    } else {
        $placeholders = implode(',', array_fill(0, count($insuranceNames), '?'));
        $insuranceSQL = "
            AND i.insurance_name IN ($placeholders)
            AND i.insurance_type IN ('PHIS', 'Corporate', 'NHIS')
            AND i.status = 'active'
        ";
        $params = array_merge($params, $insuranceNames);
    }
}

// ==========================
// SQL QUERY
// ==========================
$sql = "
SELECT 
    pas.hospital_no,
    pas.item_services,
    pas.pay,
    pas.claim_amt,
    pas.transact_date,
    COALESCE(i.insurance_name, 'Unknown') AS insurance_name
FROM patient_ap_services pas
LEFT JOIN enrollee e ON pas.hospital_no = e.hospital_no
LEFT JOIN insurance_tbl i ON e.hmo_no = i.insurance_no
WHERE DATE(pas.transact_date) BETWEEN ? AND ?
  AND pas.paystatus = 1
  AND pas.serv_group IN ('laboratory', 'radiology')
  $insuranceSQL
ORDER BY i.insurance_name, pas.item_services
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================
// BUILD SUMMARY
// ==========================
$summary = [];
$grandPay = 0;
$grandClaim = 0;

foreach ($results as $row) {
    $ins  = $row['insurance_name'];
    $item = $row['item_services'];

    if (!isset($summary[$ins])) {
        $summary[$ins] = [
            'services' => [],
            'sub_total_pay' => 0,
            'sub_total_claim' => 0
        ];
    }

    if (!isset($summary[$ins]['services'][$item])) {
        $summary[$ins]['services'][$item] = ['count' => 0, 'pay' => 0, 'claim' => 0];
    }

    $summary[$ins]['services'][$item]['count']++;
    $summary[$ins]['services'][$item]['pay']   += $row['pay'];
    $summary[$ins]['services'][$item]['claim'] += $row['claim_amt'];

    $summary[$ins]['sub_total_pay']   += $row['pay'];
    $summary[$ins]['sub_total_claim'] += $row['claim_amt'];

    $grandPay   += $row['pay'];
    $grandClaim += $row['claim_amt'];
}

// ==========================
// CSV OUTPUT
// ==========================
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=investigation_report_' . date('Ymd') . '.csv');

$output = fopen('php://output', 'w');

// Header
fputcsv($output, [
    'SN',
    'Hospital No',
    'Investigation',
    'Pay',
    'Claim Amount',
    'Date',
    'Insurance'
]);

$sn = 1;
foreach ($results as $row) {
    fputcsv($output, [
        $sn++,
        $row['hospital_no'],
        $row['item_services'],
        number_format($row['pay'], 2),
        number_format($row['claim_amt'], 2),
        date('d/m/Y', strtotime($row['transact_date'])),
        $row['insurance_name']
    ]);
}

// Summary
fputcsv($output, []);
fputcsv($output, ['SUMMARY']);

foreach ($summary as $insurance => $group) {
    fputcsv($output, [$insurance]);
    fputcsv($output, ['Investigation', 'Count', 'Total Paid', 'Claim Amount']);

    foreach ($group['services'] as $test => $values) {
        fputcsv($output, [
            $test,
            $values['count'],
            number_format($values['pay'], 2),
            number_format($values['claim'], 2)
        ]);
    }

    fputcsv($output, [
        'SUBTOTAL',
        '',
        number_format($group['sub_total_pay'], 2),
        number_format($group['sub_total_claim'], 2)
    ]);

    fputcsv($output, []);
}

// Grand total
fputcsv($output, [
    'GRAND TOTAL',
    '',
    number_format($grandPay, 2),
    number_format($grandClaim, 2)
]);

fclose($output);
exit;
