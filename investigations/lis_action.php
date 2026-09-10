<?php
include("../Connections/Conn.php");
require_once(__DIR__ . '/../inc/lis/LisService.php');

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'dispatch' || $action === 'retry') {
    $labrequest_no = trim($_POST['labrequest_no'] ?? '');
    $patient_no = trim($_POST['patient_no'] ?? '');
    $test_id = trim($_POST['test_id'] ?? '');
    $test_name = trim($_POST['test_name'] ?? '');
    $request_note = trim($_POST['request_note'] ?? '');

    if (empty($labrequest_no) || empty($patient_no)) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }

    $res = LisService::dispatchOrderIfMapped($db, $labrequest_no, $patient_no, $test_id, $test_name, $request_note);

    if (!$res) {
        echo json_encode(['success' => false, 'error' => 'Test is not mapped to External LIS or LIS is disabled.']);
    } elseif ($res['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Successfully dispatched order to External LIS (Order ID: ' . ($res['clinos_order_id'] ?? 'N/A') . ')',
            'clinos_order_id' => $res['clinos_order_id'] ?? null,
            'label_url' => $res['clinos_label_url'] ?? null
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $res['error'] ?? 'Dispatch failed.']);
    }
    exit;
}

if ($action === 'dispatch_batch') {
    $patient_no = trim($_POST['patient_no'] ?? '');
    $labrequest_nos_raw = $_POST['labrequest_nos'] ?? [];
    $request_note = trim($_POST['request_note'] ?? '');

    if (is_string($labrequest_nos_raw)) {
        $labrequest_nos = array_filter(array_map('trim', explode(',', $labrequest_nos_raw)));
    } else {
        $labrequest_nos = (array)$labrequest_nos_raw;
    }

    if (empty($labrequest_nos)) {
        echo json_encode(['success' => false, 'error' => 'Please select at least one investigation to send to LIS.']);
        exit;
    }

    $res = LisService::dispatchBatchOrders($db, $labrequest_nos, $patient_no, $request_note);

    if ($res['success']) {
        echo json_encode([
            'success' => true,
            'message' => $res['message'],
            'dispatched_count' => $res['dispatched_count'] ?? count($labrequest_nos),
            'clinos_order_id' => $res['clinos_order_id'] ?? null,
            'label_url' => $res['clinos_label_url'] ?? null
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $res['error'] ?? 'Batch dispatch failed.']);
    }
    exit;
}

if ($action === 'get_specimens') {
    $identifier = trim($_POST['order_id'] ?? ($_GET['order_id'] ?? ($_POST['labrequest_no'] ?? ($_GET['labrequest_no'] ?? ''))));
    if (empty($identifier)) {
        echo json_encode(['success' => false, 'error' => 'Missing order ID or request number']);
        exit;
    }

    $res = LisService::getSpecimensForOrder($db, $identifier);
    echo json_encode($res);
    exit;
}

if ($action === 'poll' || $action === 'sync') {
    $res = LisService::syncResults($db);
    echo json_encode([
        'success' => true,
        'message' => 'LIS Sync completed: ' . ($res['items_processed'] ?? 0) . ' item(s) processed.',
        'items_processed' => $res['items_processed'] ?? 0,
        'status' => $res['status'] ?? 'ok'
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
