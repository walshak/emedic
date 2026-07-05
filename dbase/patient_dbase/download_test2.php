<?php
include("../../Connections/Conn.php");
session_start();

if ($_SESSION['rights'] != 'MD') {
    exit;
}
$username = $_SESSION['username'];
$stmt2 = $db->query("SELECT * FROM admin_users_rights WHERE username='$username' AND see_med_rpt=1 AND see_statistic_rpt=1");
if ($stmt2->rowCount() == 0) {
    exit;
}

// Load PHPExcel
require_once __DIR__ . '/PHPExcel/Classes/PHPExcel.php';

// Filters from GET
$patient_search = isset($_GET['patient_search']) ? trim($_GET['patient_search']) : '';
$date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : '';
$date_end = isset($_GET['date_end']) ? trim($_GET['date_end']) : '';
$specimen_collected = isset($_GET['specimen_collected']) ? trim($_GET['specimen_collected']) : '';
$request_by = isset($_GET['request_by']) ? trim($_GET['request_by']) : '';
$approved_by = isset($_GET['approved_by']) ? trim($_GET['approved_by']) : '';
$lab_sci_name = isset($_GET['lab_sci_name']) ? trim($_GET['lab_sci_name']) : '';
$lab_sci_speciality = isset($_GET['lab_sci_speciality']) ? trim($_GET['lab_sci_speciality']) : '';
$lab_cat = isset($_GET['lab_cat']) ? trim($_GET['lab_cat']) : '';

// Validate required filters
if ($date_start === '' || $date_end === '') {
    die("Error: Date range is required.");
}

// Build dynamic SQL
$whereClauses = [];
$params = [];

$whereClauses[] = "r.result_date BETWEEN :date_start AND :date_end";
$params[':date_start'] = $date_start . ' 00:00:00';
$params[':date_end'] = $date_end . ' 23:59:59';

if ($patient_search !== '') {
    $whereClauses[] = "l.patient LIKE :patient_search";
    $params[':patient_search'] = '%' . $patient_search . '%';
}
if ($specimen_collected !== '') {
    $whereClauses[] = "r.specimen_collected = :specimen_collected";
    $params[':specimen_collected'] = $specimen_collected;
}
if ($request_by !== '') {
    $whereClauses[] = "l.request_by = :request_by";
    $params[':request_by'] = $request_by;
}
if ($approved_by !== '') {
    $whereClauses[] = "l.approved_by = :approved_by";
    $params[':approved_by'] = $approved_by;
}
if ($lab_sci_name !== '') {
    $whereClauses[] = "r.lab_sci_name = :lab_sci_name";
    $params[':lab_sci_name'] = $lab_sci_name;
}
if ($lab_sci_speciality !== '') {
    $whereClauses[] = "r.lab_sci_speciality = :lab_sci_speciality";
    $params[':lab_sci_speciality'] = $lab_sci_speciality;
}
if ($lab_cat !== '') {
    $whereClauses[] = "l.lab_cat = :lab_cat";
    $params[':lab_cat'] = $lab_cat;
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// SQL query
$sql = "SELECT r.field_value, r.test_name, r.specimen_collected, r.result_date, r.lab_sci_name, 
               r.lab_sci_speciality, r.entered_by, l.patient, l.patient_name, l.section, 
               l.request_date2, l.request_by, l.approved_by, d.department as lab_cat_name 
        FROM lab_result r 
        INNER JOIN lab_manage l ON l.labrequest_no = r.lab_no 
        LEFT JOIN department d ON l.lab_cat = d.sn
        $whereSql ORDER BY r.result_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

if (empty($results)) {
    die("No records found for the given filters.");
}

// Create Excel file
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();

// Write headers
$headers = array_keys($results[0]);
$col = 0;
foreach ($headers as $header) {
    $sheet->setCellValueByColumnAndRow($col++, 1, $header);
}

// Write data rows
$rowIndex = 2;
foreach ($results as $row) {
    $col = 0;
    foreach ($row as $cell) {
        $sheet->setCellValueByColumnAndRow($col++, $rowIndex, $cell);
    }
    $rowIndex++;
}

// Auto-size columns
foreach (range('A', $sheet->getHighestColumn()) as $colLetter) {
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}

// Clean any output buffers before sending headers
if (ob_get_length()) {
    ob_clean();
}

// Output headers
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="lab_results.xlsx"');
header('Cache-Control: max-age=0');

// Save to output
$writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$writer->save('php://output');
exit;
