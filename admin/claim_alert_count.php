<?php
session_start();
include("../Connections/Conn.php");

try {

    $query = "
        SELECT COUNT(DISTINCT hospital_no) AS total
        FROM patient_ap_services
        WHERE pay_mode = 'Claim'
        AND invoice_status = 0
        AND paystatus = 0
     AND invoice_status = 0
        AND process_claim = 0
                AND date_entry >= NOW() - INTERVAL 2 DAY
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($row);
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
