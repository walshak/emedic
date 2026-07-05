<?php

if (isset($_POST['fullname'])) {
    echo 'dsssssssssssssssss';
    exit;
}

exit;


session_start();
require_once('Connections/Conn.php');


$consultant_id = $_SESSION['id'];

$query = "SELECT hospital_no, name, procedures, consultant_name, sDate 
          FROM procedures 
          WHERE consultant_id = ? 
          AND DATEDIFF(sDate, NOW()) <= 5 
          AND DATEDIFF(sDate, NOW()) >= 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

$procedures = [];
while ($row = $result->fetch_assoc()) {
    $procedures[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($procedures);
