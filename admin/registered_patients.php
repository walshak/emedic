<?php

include('../Connections/Conn.php');
session_start();

if (!isset($_GET['registered'])) {
    echo "Missing filter.";
    exit;
}

$filter = $_GET['registered'];
$dateWhere = "";
$params = [];

// ================================
// FILTER CONDITIONS
// ================================
switch ($filter) {

    case 'today':
        $filter_type = date("Y-m-d");
        $dateWhere = "DATE(date_capture) = :filter_date";
        $params = [':filter_date' => $filter_type];
        break;

    case 'yesterday':
        $filter_type = date("Y-m-d", strtotime("-1 day"));
        $dateWhere = "DATE(date_capture) = :filter_date";
        $params = [':filter_date' => $filter_type];
        break;

    case 'week':
        $start_date = date("Y-m-d", strtotime("-7 days"));
        $end_date   = date("Y-m-d");
        $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
        $params = [':start' => $start_date, ':end' => $end_date];
        break;

    case 'month':
        $start_date = date("Y-m-01");
        $end_date   = date("Y-m-t");
        $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
        $params = [':start' => $start_date, ':end' => $end_date];
        break;

    case 'quarter':
        $current_month = date('n');
        $quarter = ceil($current_month / 3);
        $start_month = ($quarter - 1) * 3 + 1;
        $start_date = date("Y-$start_month-01");
        $end_date = date("Y-m-t", strtotime($start_date . " +2 months"));
        $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
        $params = [':start' => $start_date, ':end' => $end_date];
        break;

    case 'year':
        $start_date = date("Y-01-01");
        $end_date   = date("Y-12-31");
        $dateWhere = "DATE(date_capture) BETWEEN :start AND :end";
        $params = [':start' => $start_date, ':end' => $end_date];
        break;

    default:
        echo "Invalid filter.";
        exit;
}

// ================================
// SEND CSV HEADERS
// ================================
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=enrollee_report.csv");

$output = fopen("php://output", "w");

// ================================
// CSV HEADER TITLES
// ================================
fputcsv($output, [
    'SN',
    'Hospital No',
    'HMO No',
    'Insurance',
    'Surname',
    'First Name',
    'Other Name',
    'Full Name',
    'Occupation',
    'Gender',
    'Marital Status',
    'Blood Group',
    'Date of Birth',
    'Age',
    'Phone',
    'Email',
    'State/LGA',
    'Tribe',
    'Nationality',
    'Address',
    'Religion',
    'Date Captured',
    'Credit Limit'
]);

// ================================
// FULL FIELD SQL QUERY
// ================================
$sql = "
    SELECT 
        sn, hospital_no, hmo_no, insurance, surname, fname, oname, fullname,
        occupation, gender, marital_status, blood_g, dob, age, phone, email,
        state_lga, tribe, nationality, addr, religion, date_capture, credit_limit
    FROM enrollee
    WHERE $dateWhere
    ORDER BY sn
";

// Use prepared statements
$stmt = $db->prepare($sql);
$stmt->execute($params);

// ================================
// WRITE TO CSV
// ================================
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
