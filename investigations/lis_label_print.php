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

    $barcode = $_GET['barcode'] ?? null;
    $specimens = !empty($order['clinos_specimens_json']) ? json_decode($order['clinos_specimens_json'], true) : null;

    if (($barcode === 'all' || empty($barcode)) && is_array($specimens) && count($specimens) > 1) {
        // Multi-specimen label rendering: generate label card for each physical tube specimen
        if (preg_match('/<section class="label">.*?<\/section>/s', $labelHtml, $match)) {
            $template = $match[0];
            $allSections = [];
            foreach ($specimens as $sp) {
                $sec = $template;
                $targetBarcode = $sp['barcode'] ?? ($sp['sample_id'] ?? '');
                $tests = is_array($sp['tests'] ?? null) ? implode(', ', $sp['tests']) : ($sp['tests'] ?? '');
                $specKey = str_replace('_', ' ', $sp['specimen_key'] ?? '');

                if (!empty($targetBarcode)) {
                    $sec = preg_replace('/<div class="barcode-digits">.*?<\/div>/s', '<div class="barcode-digits">' . htmlspecialchars($targetBarcode) . '</div>', $sec);
                }
                if (!empty($tests)) {
                    $sec = preg_replace('/<div class="tests">.*?<\/div>/s', '<div class="tests"><span class="label-key">[' . htmlspecialchars($specKey) . ']</span> ' . htmlspecialchars($tests) . '</div>', $sec);
                }
                $allSections[] = $sec;
            }
            $combined = implode("\n", $allSections);
            $labelHtml = preg_replace('/<section class="label">.*?<\/section>/s', $combined, $labelHtml);

            if (strpos($labelHtml, 'page-break-after') === false) {
                $labelHtml = str_replace('</style>', ".label { page-break-after: always; break-after: page; }\n</style>", $labelHtml);
            }
        }
    } elseif (!empty($barcode) && $barcode !== 'all' && is_array($specimens)) {
        // Single specimen tube targeted by barcode
        foreach ($specimens as $sp) {
            $targetBarcode = $sp['barcode'] ?? ($sp['sample_id'] ?? '');
            if ($targetBarcode === $barcode) {
                $labelHtml = preg_replace('/<div class="barcode-digits">.*?<\/div>/s', '<div class="barcode-digits">' . htmlspecialchars($targetBarcode) . '</div>', $labelHtml);
                $tests = is_array($sp['tests'] ?? null) ? implode(', ', $sp['tests']) : ($sp['tests'] ?? '');
                $specKey = str_replace('_', ' ', $sp['specimen_key'] ?? '');
                if (!empty($tests)) {
                    $labelHtml = preg_replace('/<div class="tests">.*?<\/div>/s', '<div class="tests"><span class="label-key">[' . htmlspecialchars($specKey) . ']</span> ' . htmlspecialchars($tests) . '</div>', $labelHtml);
                }
                break;
            }
        }
    }

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
