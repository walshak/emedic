<?php
session_start();
include("../Connections/Conn.php");
require_once __DIR__ . '/../inc/lis/LisService.php';

$labrequest_no = $_GET['labrequest_no'] ?? ($_GET['rq_no'] ?? null);
$order_id = $_GET['order_id'] ?? null;

$identifier = $order_id ?: $labrequest_no;

if (!LisDriverFactory::isLisEnabled($db)) {
    die("Error: LIS integration is currently disabled.");
}

if (empty($identifier)) {
    die("Error: Missing lab request number or order ID.");
}

try {
    $stmt = $db->prepare("SELECT * FROM lis_orders WHERE clinos_order_id = :id1 OR labrequest_no = :id2 OR external_order_id = :id3 LIMIT 1");
    $stmt->execute([
        ':id1' => $identifier,
        ':id2' => $identifier,
        ':id3' => $identifier
    ]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order || empty($order['clinos_label_url'])) {
        die("Error: No ClinOS tube label found for this order. Ensure the order was successfully placed to ClinOS.");
    }

    $driver = LisDriverFactory::getDriver($db);
    $labelHtml = $driver->getLabelContent($order['clinos_label_url']);

    // Wrap label content with print trigger if not already included
    if (strpos($labelHtml, '<script>') === false) {
        $labelHtml .= '<script>window.onload = function() { window.print(); };</script>';
    }

    header('Content-Type: text/html; charset=utf-8');
    echo $labelHtml;

} catch (Exception $e) {
    http_response_code(500);
    echo "<h3>Error Printing Label</h3><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
