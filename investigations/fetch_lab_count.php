<?php
session_start();
include("../Connections/Conn.php");
require_once __DIR__ . '/../inc/lis/LisService.php';

try {
    $section = isset($_SESSION['section']) ? $_SESSION['section'] : '';
    $username = $_SESSION['uname'] ?? $_SESSION['fullname'] ?? 'system_user';

    // 1. Query pending EMR lab requests (Outpatient vs Inpatient)
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

    $outpatient = isset($row['outpatient_count']) ? (int)$row['outpatient_count'] : 0;
    $inpatient  = isset($row['inpatient_count']) ? (int)$row['inpatient_count'] : 0;

    // 2. Check LIS Enablement & Fast Local Notification Counts
    $lisEnabled = LisDriverFactory::isLisEnabled($db);
    $lisUnseenCount = 0;
    $lisDispatchedCount = 0;

    if ($lisEnabled) {
        // Get unseen results count for current user (ultra-fast indexed local DB query)
        $unseenItems = LisService::getUnseenNotifications($db, $username, 48);
        $lisUnseenCount = count($unseenItems);

        // Get active in-progress LIS orders count
        $lisOrdStmt = $db->query("SELECT COUNT(*) AS active_cnt FROM lis_orders WHERE status IN ('sent', 'specimen_received')");
        if ($lisOrdStmt && $lisOrdRow = $lisOrdStmt->fetch(PDO::FETCH_ASSOC)) {
            $lisDispatchedCount = (int)($lisOrdRow['active_cnt'] ?? 0);
        }
    }

    echo json_encode(array(
        "outpatient"            => $outpatient,
        "inpatient"             => $inpatient,
        "total"                 => $outpatient + $inpatient,
        "lis_enabled"           => $lisEnabled,
        "lis_unseen_results"    => $lisUnseenCount,
        "lis_dispatched_count"  => $lisDispatchedCount,
        "lis_unseen_total"      => $lisUnseenCount
    ));
} catch (PDOException $e) {
    echo json_encode(array("error" => $e->getMessage()));
}
