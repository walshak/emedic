<?php
include("../../Connections/Conn.php");
session_start();
$username = $_SESSION['username'];

if ($_SESSION['rights'] != 'MD') {
    exit;
}

$stmt2 = $db->query("SELECT * FROM admin_users_rights WHERE username='$username' AND see_med_rpt=1 AND see_statistic_rpt=1");
if ($stmt2->rowCount() == 0) {
    exit;
}


// Get filter inputs from GET parameters
$filter_preparer = isset($_GET['prepared_by']) ? trim($_GET['prepared_by']) : '';
$notes_search = isset($_GET['notes_search']) ? trim($_GET['notes_search']) : '';
$filter_notes_type = isset($_GET['notes_type']) ? trim($_GET['notes_type']) : '';
$filter_dept_id = isset($_GET['dept_id']) ? trim($_GET['dept_id']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : '';
$date_end = isset($_GET['date_end']) ? trim($_GET['date_end']) : '';
$table_name = isset($_GET['table_name']) ? trim($_GET['table_name']) : '';

// Build WHERE clause
$whereClauses = [];
$params = [];

if ($filter_preparer !== '') {
    $whereClauses[] = "a.fullname = :prepared_by";
    $params[':prepared_by'] = $filter_preparer;
} else {
    // If no preparer selected, no data to fetch
    die("Error: 'prepared_by' parameter is required.");
}

if ($notes_search !== '') {
    $whereClauses[] = "n.notes LIKE :notes_search";
    $params[':notes_search'] = '%' . $notes_search . '%';
}
if ($filter_notes_type !== '') {
    $whereClauses[] = "n.notes_type = :notes_type";
    $params[':notes_type'] = $filter_notes_type;
}
if ($filter_dept_id !== '') {
    $whereClauses[] = "n.dept_id = :dept_id";
    $params[':dept_id'] = $filter_dept_id;
}
if ($filter_status !== '') {
    $whereClauses[] = "n.status = :status";
    $params[':status'] = $filter_status;
}

if ($table_name == "notes_services") {
    if ($date_start !== '') {
        $whereClauses[] = "n.created_at >= :date_start";
        $params[':date_start'] = $date_start . ' 00:00:00';
    }
    if ($date_end !== '') {
        $whereClauses[] = "n.created_at <= :date_end";
        $params[':date_end'] = $date_end . ' 23:59:59';
    }
} else {
    if ($date_start !== '') {
        $whereClauses[] = "n.date_entry >= :date_start";
        $params[':date_start'] = $date_start . ' 00:00:00';
    }
    if ($date_end !== '') {
        $whereClauses[] = "n.date_entry <= :date_end";
        $params[':date_end'] = $date_end . ' 23:59:59';
    }
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}


$null = '';
$action_to_take = 'download';
$setdatetime = date("Y-m-d H:i:s");
$sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn,descriptions,staff_name,patient_id,action,date_and_time) 
    VALUES (:item_sn,:descriptions,:staff_name,:patient_id,:action,:date_and_time)");
$sql->bindParam(':item_sn', $null, PDO::PARAM_STR);
$sql->bindParam(':descriptions', $whereSql, PDO::PARAM_STR);
$sql->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
$sql->bindParam(':patient_id', $notes_search, PDO::PARAM_STR);
$sql->bindParam(':action', $action_to_take, PDO::PARAM_STR);
$sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
$sql->execute();


// Fetch notes with patient info

if ($table_name == "notes_services") {

    $notesh = " AND notes!=''";
    $sql = "SELECT n.*, a.fullname,
               CONCAT(p.surname, ', ', p.fname, ' ', p.oname) AS patient_name, 
               p.hospital_no as patient_hospital_no
        FROM notes_services n
        INNER JOIN admin_users a ON a.id = n.created_by
        LEFT JOIN enrollee p ON n.hospital_no = p.hospital_no
        $whereSql $notesh
        ORDER BY n.created_at DESC";
} else {
    $sql = "SELECT n.*, d.department, a.fullname,
               CONCAT(p.surname, ', ', p.fname, ' ', p.oname) AS patient_name, 
               p.hospital_no as patient_hospital_no
        FROM notes n
        INNER JOIN admin_users a ON a.id = n.created_by
        LEFT JOIN department d ON n.dept_id = d.sn
        LEFT JOIN enrollee p ON n.hospital_no = p.hospital_no
        $whereSql
        ORDER BY n.date_entry DESC";
}


$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$notes = $stmt->fetchAll();

if (!$notes) {
    die("No data found for the selected filters.");
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="patient_notes.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Output CSV column headers
fputcsv($output, [
    'Patient Name',
    'Hospital Number',
    'Notes',
    'Department',
    'Date and Time',
    'Prepared By'
]);

// Output each row of data
foreach ($notes as $note) {
    // Strip tags from notes to avoid HTML in CSV
    $plainNotes = strip_tags($note['notes']);

    if ($table_name == "notes") {
        $department = $note['department'];
        $date_entry = date('jS M Y, H:i:s', strtotime($note['date_entry']));
    } else {
        $department = '';
        $date_entry = date('jS M Y, H:i:s', strtotime($note['created_at']));
    }
    fputcsv($output, [
        $note['patient_name'],
        $note['patient_hospital_no'],
        $plainNotes,
        $department,
        $date_entry,
        $note['fullname']
    ]);
}

// Close output stream
fclose($output);
exit;
