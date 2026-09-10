<?php
session_start();
include("../Connections/Conn.php");
require_once __DIR__ . '/../inc/lis/LisService.php';

header('Content-Type: application/json');

try {
    $username = $_SESSION['uname'] ?? $_SESSION['fullname'] ?? 'system_user';
    $labrequestNo = $_POST['labrequest_no'] ?? $_GET['labrequest_no'] ?? null;

    $success = LisService::markNotificationsAsSeen($db, $username, $labrequestNo);

    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Notifications marked as seen.' : 'Failed to mark as seen.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
