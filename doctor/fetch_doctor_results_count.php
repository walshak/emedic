<?php
session_start();
include("../Connections/Conn.php");

try {

    $doctor_id = $_SESSION['id'];

    $query = "
        SELECT COUNT(*) AS new_count 
        FROM lab_manage 
        WHERE data_capture_status = 'approve' 
        AND created_by = :doctor_id
        AND result_date >= NOW() - INTERVAL 20 MINUTE
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($row);
} catch (PDOException $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
