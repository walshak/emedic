<?php
session_start();
include("../Connections/Conn.php");

try {

    $section = isset($_SESSION['section']) ? $_SESSION['section'] : '';

    $query = "
        SELECT 
            SUM(
                CASE 
                    WHEN admission.hospital_no IS NULL 
                    AND lm.request_date >= NOW() - INTERVAL 20 MINUTE
                    THEN 1 ELSE 0 
                END
            ) AS outpatient_count,

            SUM(
                CASE 
                    WHEN admission.hospital_no IS NOT NULL 
                    AND lm.request_date >= NOW() - INTERVAL 1 DAY
                    THEN 1 ELSE 0 
                END
            ) AS inpatient_count

        FROM lab_manage lm

        LEFT JOIN admission 
            ON lm.patient = admission.hospital_no 
            AND admission.adm_status = 3

        WHERE lm.data_capture_status = 'queue'
        AND lm.section = ?
    ";

    $stmt = $db->prepare($query);
    $stmt->execute(array($section));

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Safe values (PHP 5 compatible)
    $outpatient = isset($row['outpatient_count']) ? (int)$row['outpatient_count'] : 0;
    $inpatient  = isset($row['inpatient_count']) ? (int)$row['inpatient_count'] : 0;

    echo json_encode(array(
        "outpatient" => $outpatient,
        "inpatient"  => $inpatient,
        "total"      => $outpatient + $inpatient
    ));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
