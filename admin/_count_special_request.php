<?php
include("../Connections/Conn.php");

if (isset($_POST['get_dashboard_counts'])) {

    $dob_count = 0;
    $see_specialist_count = 0;
    $apptm_fellowup = 0;

    /* ---------------- Birthday Count ---------------- */
    try {

        $stmt = $db->prepare("
            SELECT COUNT(*) AS birthday_count
            FROM enrollee
            WHERE SUBSTRING(dob,6,5) = ?
        ");

        $stmt->execute([date('m-d')]);
        $dob_count = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
    }



    /* ---------------- Specialist Reminder Count ---------------- */
    try {

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM notes
            WHERE ack = 0
            AND status = '1'
            AND notes_type = 'REQ_REMINDER'
        ");

        $stmt->execute();
        $see_specialist_count = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
    }



    /* ---------------- Appointment Followup ---------------- */
    try {

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM apptm_fellowup
            WHERE status = 0
            AND DATE(date_time) = ?
        ");

        $stmt->execute([date('Y-m-d')]);
        $apptm_fellowup = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
    }



    $both_count = $see_specialist_count + $apptm_fellowup;

    echo json_encode([
        'status' => 200,
        'see_specialist_count' => $both_count,
        'apptm_fellowup' => $apptm_fellowup,
        'dob_count' => $dob_count
    ]);

    exit;
}
