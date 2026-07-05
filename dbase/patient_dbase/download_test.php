<?php
// DB setup - same as above
$host = 'localhost';
$dbname = 'mluth3';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
);

try {
    $db = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

require_once 'PHPExcel/Classes/PHPExcel.php';

// Receive GET parameters, same filters as search file
$patient_search = isset($_GET['patient_search']) ? trim($_GET['patient_search']) : '';
$date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : '';
$date_end = isset($_GET['date_end']) ? trim($_GET['date_end']) : '';
$specimen_collected = isset($_GET['specimen_collected']) ? trim($_GET['specimen_collected']) : '';
$request_by = isset($_GET['request_by']) ? trim($_GET['request_by']) : '';
$approved_by = isset($_GET['approved_by']) ? trim($_GET['approved_by']) : '';
$lab_sci_name = isset($_GET['lab_sci_name']) ? trim($_GET['lab_sci_name']) : '';
$lab_sci_speciality = isset($_GET['lab_sci_speciality']) ? trim($_GET['lab_sci_speciality']) : '';
$lab_cat = isset($_GET['lab_cat']) ? trim($_GET['lab_cat']) : '';

// Validate required date range
if ($date_start === '' || $date_end === '') {
    die("Date range is required");
}

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

$sql = "SELECT r.field_value, r.test_name, r.specimen_collected, r.result_date, r.lab_sci_name, 
               r.lab_sci_speciality, r.entered_by, l.patient, l.patient_name, l.section, 
               l.request_date2, l.request_by, l.approved_by, d.department as lab_cat_name 
        FROM lab_result r 
        INNER JOIN lab_manage l ON l.labrequest_no = r.lab_no 
        LEFT JOIN department d ON l.lab_cat = d.sn
        $whereSql ORDER BY r.result_date DESC";

$stmt = $db->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$results = $stmt->fetchAll();

if (!$results) {
    die('No data found');
}

// Output headers for CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=lab_investigations.csv');

$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, [
    'Request Date',
    'Patient',
    'Patient Name',
    'Test Name',
    'Specimen Collected',
    'Field Value',
    'Request By',
    'Approved By',
    'Lab Scientist Name',
    'Lab Scientist Speciality'
]);

// Output rows
foreach ($results as $row) {
    fputcsv($output, [
        date('jS M Y', strtotime($row['request_date2'])),
        $row['patient'],
        $row['patient_name'],
        $row['test_name'],
        $row['specimen_collected'],
        $row['field_value'],
        $row['request_by'],
        $row['approved_by'],
        $row['lab_sci_name'],
        $row['lab_sci_speciality'],
    ]);
}

fclose($output);
exit;
