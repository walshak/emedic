<?php
session_start();
include("../Connections/Conn.php"); // make sure $db is your PDO connection

try {

    $query = "
        SELECT COUNT(*) AS new_count 
        FROM lab_manage 
        WHERE data_capture_status = 'approve' 
        AND result_date >= NOW() - INTERVAL 4 HOUR
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($row);
} catch (PDOException $e) {

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
