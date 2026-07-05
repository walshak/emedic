<?php
session_start();
include("../Connections/Conn.php");

try {

    $query = "
        SELECT COUNT(DISTINCT hospital_no) AS total
        FROM patient_ap_services
        WHERE serv_group = 'Pharmacy'
        AND invoice_status = 0
        AND date_entry >= NOW() - INTERVAL 30 MINUTE
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($row);
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
