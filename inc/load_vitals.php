<?php
session_start();
include("../Connections/Conn.php");

// Set the number of results per page
$results_per_page = 2;
$hospital_no = '000005'; // Example hospital number

// Get the current page number from the AJAX request, default to 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Prepare the SQL statement with pagination
$vitals__stmt = $db->prepare("SELECT * FROM vital_sign WHERE hospital_no = :hospital_no AND status = '1' ORDER BY sn DESC LIMIT :limit OFFSET :offset");
$vitals__stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$vitals__stmt->bindParam(':limit', $results_per_page, PDO::PARAM_INT);
$vitals__stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$vitals__stmt->execute();

// Fetch the results
$_vitals_ = $vitals__stmt->fetchAll(PDO::FETCH_ASSOC);
$total_vitals = $db->query("SELECT COUNT(*) FROM vital_sign WHERE hospital_no = '$hospital_no' AND status = '1'")->fetchColumn();
$total_pages = ceil($total_vitals / $results_per_page);

// Return JSON data
echo json_encode(['vitals' => $_vitals_, 'total_pages' => $total_pages]);
exit;
