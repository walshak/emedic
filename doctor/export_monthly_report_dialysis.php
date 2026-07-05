<?php
include("../Connections/Conn.php");

// ======== GET MONTH ========
if (!isset($_GET['month']) || empty($_GET['month'])) {
    exit('Error: Month not specified.');
}
$monthYear = $_GET['month'] . '%';

// ======== CSV HEADERS ========
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=dialysis_monthly_report.csv');
header('Pragma: no-cache');
header('Expires: 0');

// ======== OPEN OUTPUT STREAM ========
$out = fopen('php://output', 'w');

// ======== TITLE ========
fputcsv($out, ['DIALYSIS MONTHLY REPORT']);
fputcsv($out, ['Month:', $_GET['month']]);
fputcsv($out, []); // blank line

// ================= PER-PATIENT SECTION =================
fputcsv($out, ['PER PATIENT SUMMARY']);
fputcsv($out, ['Hospital No', 'Patient Name', 'Request Type', 'HD/HDF', 'PLEX', 'ERYTHR', 'IRON', 'BLOOD']);

$sqlPatient = "
SELECT 
    d.hospital_no,
    d.patient_name,
    d.request_type,
    COUNT(DISTINCT d.id) AS total_sessions,
    SUM(CASE WHEN pas.item_services LIKE '%Plasmapheresis%' THEN 1 ELSE 0 END) AS total_plex,
    SUM(CASE WHEN pas.item_services LIKE '%Erythropioetin%' THEN 1 ELSE 0 END) AS total_erythr,
    SUM(CASE WHEN pas.item_services LIKE '%iron%' THEN 1 ELSE 0 END) AS total_iron,
    SUM(CASE WHEN pas.item_services LIKE '%Blood Transfusion%' THEN 1 ELSE 0 END) AS total_blood
FROM dialysis d
LEFT JOIN patient_ap_services pas
    ON pas.hospital_no = d.hospital_no
    AND pas.date_entry LIKE :pasMonth
    AND pas.paystatus='1'
WHERE d.date_marked_completed LIKE :dialMonth
AND d.status='1'
GROUP BY d.hospital_no
ORDER BY d.patient_name ASC
";

$stmt = $db->prepare($sqlPatient);
$stmt->execute([
    'pasMonth'  => $monthYear,
    'dialMonth' => $monthYear
]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [
        $row['hospital_no'],
        $row['patient_name'],
        $row['request_type'],
        $row['total_sessions'],
        $row['total_plex'],
        $row['total_erythr'],
        $row['total_iron'],
        $row['total_blood']
    ]);
}

// ================= BLANK LINES =================
fputcsv($out, []);
fputcsv($out, []);

// ================= REQUEST-TYPE SUMMARY =================
fputcsv($out, ['SUMMARY BY REQUEST TYPE']);
fputcsv($out, ['Request Type', 'HD/HDF', 'PLEX', 'ERYTHR', 'IRON', 'BLOOD']);

$sqlType = "
SELECT 
    d.request_type,
    COUNT(DISTINCT d.id) AS total_sessions,
    SUM(CASE WHEN pas.item_services LIKE '%Plasmapheresis%' THEN 1 ELSE 0 END) AS total_plex,
    SUM(CASE WHEN pas.item_services LIKE '%Erythropioetin%' THEN 1 ELSE 0 END) AS total_erythr,
    SUM(CASE WHEN pas.item_services LIKE '%iron%' THEN 1 ELSE 0 END) AS total_iron,
    SUM(CASE WHEN pas.item_services LIKE '%Blood Transfusion%' THEN 1 ELSE 0 END) AS total_blood
FROM dialysis d
LEFT JOIN patient_ap_services pas
    ON pas.hospital_no = d.hospital_no
    AND pas.date_entry LIKE :pasMonth
    AND pas.paystatus='1'
WHERE d.date_marked_completed LIKE :dialMonth
AND d.status='1'
GROUP BY d.request_type
ORDER BY d.request_type ASC
";

$stmtType = $db->prepare($sqlType);
$stmtType->execute([
    'pasMonth'  => $monthYear,
    'dialMonth' => $monthYear
]);

while ($row = $stmtType->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, [
        $row['request_type'],
        $row['total_sessions'],
        $row['total_plex'],
        $row['total_erythr'],
        $row['total_iron'],
        $row['total_blood']
    ]);
}

// ======== CLOSE OUTPUT ========
fclose($out);
exit;
