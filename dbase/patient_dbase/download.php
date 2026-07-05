<?php

include("../../Connections/Conn.php");
session_start();
$username = $_SESSION['username'];

if ($_SESSION['rights'] != 'MD') {
    exit;
}

$stmt2 = $db->query("SELECT * FROM admin_users_rights WHERE username='$username' AND see_med_rpt=1");
if ($stmt2->rowCount() == 0 && $_SESSION['rights'] == 'MD') {
    exit;
}


// Get filters
$hospital_no = isset($_GET['hospital_no']) ? trim($_GET['hospital_no']) : '';
$notes_search = isset($_GET['notes_search']) ? trim($_GET['notes_search']) : '';
$filter_rights = isset($_GET['rights']) ? trim($_GET['rights']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$filter_preparer = isset($_GET['prepared_by']) ? trim($_GET['prepared_by']) : '';
$table_name = isset($_GET['table_name']) ? trim($_GET['table_name']) : '';

// Build WHERE clause
$where = array();
$params = array();

if ($hospital_no !== '') {
    $where[] = "n.hospital_no = :hospital_no";
    $params[':hospital_no'] = $hospital_no;
}
if ($notes_search !== '') {
    $where[] = "n.notes LIKE :notes_search";
    $params[':notes_search'] = '%' . $notes_search . '%';
}
if ($filter_rights !== '') {
    $where[] = "a.rights = :rights";
    $params[':rights'] = $filter_rights;
}
if ($filter_status !== '') {
    $where[] = "n.status = :status";
    $params[':status'] = $filter_status;
}
if ($filter_preparer !== '') {
    $where[] = "a.fullname = :prepared_by";
    $params[':prepared_by'] = $filter_preparer;
}

$where_clause = '';
if (!empty($where)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where);
}


$null = '';
$action_to_take = 'download';
$setdatetime = date("Y-m-d H:i:s");
$sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn,descriptions,staff_name,patient_id,action,date_and_time) 
    VALUES (:item_sn,:descriptions,:staff_name,:patient_id,:action,:date_and_time)");
$sql->bindParam(':item_sn', $null, PDO::PARAM_STR);
$sql->bindParam(':descriptions', $where_clause, PDO::PARAM_STR);
$sql->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
$sql->bindParam(':patient_id', $hospital_no, PDO::PARAM_STR);
$sql->bindParam(':action', $action_to_take, PDO::PARAM_STR);
$sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
$sql->execute();


if ($table_name == "notes_services") {
    $notes = " AND notes!=''";
    $sql = "SELECT n.*,e.Designation, a.rights, a.fullname
    FROM $table_name n
    INNER JOIN admin_users a ON a.id = n.created_by
    LEFT JOIN hremp e ON a.EmployeeCode = e.EmployeeCode
    $where_clause  $notes 
    ORDER BY n.created_at DESC";
} else {
    $sql = "SELECT n.*, d.department, e.Designation, a.rights, a.fullname
        FROM $table_name n
        INNER JOIN admin_users a ON a.id = n.created_by
        LEFT JOIN department d ON n.dept_id = d.sn
        LEFT JOIN hremp e ON a.EmployeeCode = e.EmployeeCode
        $where_clause
        ORDER BY n.date_entry DESC";
}
$stmt = $db->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll();

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="notes.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, ['#', 'EMR Number', 'Notes', 'Prepared By', 'Department', 'Rights', 'Date']);

// Add notes data to CSV
foreach ($notes as $i => $note) {
    // Convert notes text to plain text removing HTML tags to avoid breaking CSV
    $plainNotes = strip_tags($note['notes']);

    if ($table_name == "notes") {
        $department = $note['department'];
        $date_entry = date('jS M Y', strtotime($note['date_entry']));
    } else {
        $department = '';
        $date_entry = date('jS M Y', strtotime($note['created_at']));
    }

    fputcsv($output, [
        $i + 1,
        $note['app_no'],
        $plainNotes,
        $note['fullname'],
        $department,
        $note['rights'],
        $date_entry
    ]);
}

// Close output stream
fclose($output);
exit;
