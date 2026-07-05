<?php
// DB setup
$host = 'localhost';
$dbname = 'mluth3';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $db = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Get filter inputs
$app_no = isset($_POST['app_no']) ? trim($_POST['app_no']) : '';
$hospital_no = isset($_POST['hospital_no']) ? trim($_POST['hospital_no']) : '';
$serv_group = isset($_POST['serv_group']) ? trim($_POST['serv_group']) : '';
$cat_type = isset($_POST['cat_type']) ? trim($_POST['cat_type']) : '';
$dept_id = isset($_POST['dept_id']) ? trim($_POST['dept_id']) : '';
$invoice_by = isset($_POST['invoice_by']) ? trim($_POST['invoice_by']) : '';
$prepared_by = isset($_POST['prepared_by']) ? trim($_POST['prepared_by']) : '';
$dsp_by = isset($_POST['dsp_by']) ? trim($_POST['dsp_by']) : '';
$date_start = isset($_POST['date_start']) ? trim($_POST['date_start']) : '';
$date_end = isset($_POST['date_end']) ? trim($_POST['date_end']) : '';
$pay_mode = isset($_POST['pay_mode']) ? trim($_POST['pay_mode']) : '';
$paystatus = isset($_POST['paystatus']) ? trim($_POST['paystatus']) : '';
$process_claim = isset($_POST['process_claim']) ? trim($_POST['process_claim']) : '';
$acct_billed_staff = isset($_POST['acct_billed_staff']) ? trim($_POST['acct_billed_staff']) : '';
$who_process_paystatus = isset($_POST['who_process_paystatus']) ? trim($_POST['who_process_paystatus']) : '';
$date_field = isset($_POST['date_field']) ? trim($_POST['date_field']) : '';

// Build the SQL query based on filters
$whereClauses = [];
$params = [];

if ($app_no !== '') {
    $whereClauses[] = "app_no LIKE :app_no";
    $params[':app_no'] = '%' . $app_no . '%';
}
if ($hospital_no !== '') {
    $whereClauses[] = "hospital_no LIKE :hospital_no";
    $params[':hospital_no'] = '%' . $hospital_no . '%';
}
if ($serv_group !== '') {
    $whereClauses[] = "serv_group = :serv_group";
    $params[':serv_group'] = $serv_group;
}
if ($cat_type !== '') {
    $whereClauses[] = "cat_type = :cat_type";
    $params[':cat_type'] = $cat_type;
}
if ($dept_id !== '') {
    $whereClauses[] = "dept_id = :dept_id";
    $params[':dept_id'] = $dept_id;
}
if ($invoice_by !== '') {
    $whereClauses[] = "invoice_by LIKE :invoice_by";
    $params[':invoice_by'] = '%' . $invoice_by . '%';
}
if ($prepared_by !== '') {
    $whereClauses[] = "prepared_by LIKE :prepared_by";
    $params[':prepared_by'] = '%' . $prepared_by . '%';
}
if ($dsp_by !== '') {
    $whereClauses[] = "dsp_by LIKE :dsp_by";
    $params[':dsp_by'] = '%' . $dsp_by . '%';
}
if ($pay_mode !== '') {
    $whereClauses[] = "pay_mode = :pay_mode";
    $params[':pay_mode'] = $pay_mode;
}
if ($paystatus !== '') {
    $whereClauses[] = "paystatus = :paystatus";
    $params[':paystatus'] = $paystatus;
}
if ($process_claim !== '') {
    $whereClauses[] = "process_claim = :process_claim";
    $params[':process_claim'] = $process_claim;
}
if ($acct_billed_staff !== '') {
    $whereClauses[] = "acct_billed_staff = :acct_billed_staff";
    $params[':acct_billed_staff'] = $acct_billed_staff;
}
if ($who_process_paystatus !== '') {
    $whereClauses[] = "who_process_paystatus = :who_process_paystatus";
    $params[':who_process_paystatus'] = $who_process_paystatus;
}
if ($date_start !== '' && $date_end !== '') {
    if ($date_field === 'transact_date') {
        $whereClauses[] = "transact_date BETWEEN :date_start AND :date_end";
    } else {
        $whereClauses[] = "date_entry BETWEEN :date_start AND :date_end";
    }
    $params[':date_start'] = $date_start . ' 00:00:00';
    $params[':date_end'] = $date_end . ' 23:59:59';
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Fetch results from the patient_ap_services table
$sql = "SELECT * FROM patient_ap_services $whereSql";
$stmt = $db->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$results = $stmt->fetchAll();

// Generate CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="patient_ap_services.csv"');

$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, [
    'SN',
    'App No',
    'Hospital No',
    'Access',
    'Service Group',
    'Category Type',
    'Department ID',
    'Dispensory ID',
    'Drug SN',
    'Item Services',
    'tag',
    'Hospital Price',
    'Claim Amount',
    'Interest',
    'Quantity',
    'Remarks',
    'Drug Status',
    'Invoice Status',
    'Invoice No',
    'Invoice Date',
    'Invoice By',
    'Prepared By',
    'Created By',
    'Dispensed By',
    'Date Entry',
    'Transact Date',
    'Pay',
    'Pay Mode',
    'Pay Status',
    'Process Claim',
    'CR',
    'Discount',
    'Add Charge',
    'Prescription',
    'med_frequency',
    'med_dosage',
    'med_dosage_unit',
    'med_duration',
    'med_duration_unit',
    'Dispensory Status at Pharmacy',
    'Acct Billed Staff',
    'Acct Billed Ack',
    'Claim Valid By',
    'Payment Remarks',
    'Ledger TX',
    'Wallet Payee',
    'Who Process Pay Status',
    'Wallet Debt Bill to Acct'
]);

// Add data rows
foreach ($results as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
