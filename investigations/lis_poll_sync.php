<?php
session_start();
include("../Connections/Conn.php");
require_once __DIR__ . '/../inc/lis/LisService.php';

header('Content-Type: application/json');

try {
    if (!LisDriverFactory::isLisEnabled($db)) {
        echo json_encode([
            'status' => 'disabled',
            'message' => 'LIS Integration is currently disabled.'
        ]);
        exit;
    }

    $config = LisDriverFactory::getConfig($db);
    $lastPollTime = isset($config['last_poll_timestamp']) ? strtotime($config['last_poll_timestamp']) : 0;
    $currentTime = time();
    $force = isset($_GET['force']) && $_GET['force'] == 1;

    // Rate-limit check: Throttle non-forced auto polling to once per 30 seconds per facility/server
    if (!$force && ($currentTime - $lastPollTime) < 30) {
        echo json_encode([
            'status' => 'throttled',
            'message' => 'Polling skipped (throttled). Last poll was less than 30 seconds ago.',
            'seconds_since_last' => ($currentTime - $lastPollTime)
        ]);
        exit;
    }

    // Execute auto-dispatching for pending mapped lab orders
    $autoDispatchRes = LisService::autoDispatchPendingOrders($db);

    // Execute result ingestion
    $res = LisService::syncResults($db);

    $res['auto_dispatched'] = $autoDispatchRes['dispatched_count'] ?? 0;
    if (!empty($autoDispatchRes['errors'])) {
        $res['dispatch_errors'] = $autoDispatchRes['errors'];
    }

    echo json_encode($res);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
