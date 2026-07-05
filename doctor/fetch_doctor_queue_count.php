
<?php
session_start();
include("../Connections/Conn.php");

try {

    $now = date('Y-m-d H:i:s');

    $params = [];

    if (!empty($_SESSION['specialist'])) {

        $SQL_STRING = " (app_by = ? OR app_by = ? OR doctor_id = ?) ";
        $params[] = $_SESSION['username'];
        $params[] = $_SESSION['specialist'];
        $params[] = $_SESSION['id'];
    } else {

        $SQL_STRING = " (app_by = ? OR referal_doc = ? OR doctor_id = ?) ";
        $params[] = $_SESSION['username'];
        $params[] = $_SESSION['username'];
        $params[] = $_SESSION['id'];
    }

    $sql = "
        SELECT COUNT(*) AS total
        FROM apptm
        WHERE app_expiration_date >= ?
        AND $SQL_STRING
        AND status NOT IN ('discharge','cancelled')
        AND (queue_lock = 0 OR re_queue_lock = 0)
        AND queue_time_stamp BETWEEN DATE_SUB(?, INTERVAL 12 HOUR) AND ?
    ";

    $finalParams = array_merge([$now], $params, [$now, $now]);

    $stmt = $db->prepare($sql);
    $stmt->execute($finalParams);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($row);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
