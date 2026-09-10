<?php
/**
 * LIS Integration Workflow Bootstrap & Seeding Test Script
 * 
 * Usage:
 *   php scratch/test_lis_workflow.php
 *   php scratch/test_lis_workflow.php --emr-key="YOUR_EMR_KEY" --cat-key="YOUR_CATALOGUE_KEY" --base-url="https://zmkc-livs.shares.zrok.io"
 */

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

echo "=======================================================\n";
echo "  emedic LIS Integration Workflow Test & Bootstrap\n";
echo "=======================================================\n\n";

// 1. Bootstrap Connection & LIS Classes
require_once __DIR__ . '/../Connections/Conn.php';
require_once __DIR__ . '/../inc/lis/LisService.php';

// Parse CLI options
$options = getopt("", ["emr-key:", "cat-key:", "base-url:", "patient-no:", "canonical-code:"]);

$baseUrl = $options['base-url'] ?? 'https://zmkc-livs.shares.zrok.io';
$emrKey = $options['emr-key'] ?? null;
$catKey = $options['cat-key'] ?? null;
$patientNo = $options['patient-no'] ?? null;
$canonicalCode = $options['canonical-code'] ?? 'CREATININE_SERUM';

// 2. Check or Update Config
$config = LisDriverFactory::getConfig($db, true);

if ($emrKey || $catKey) {
    $emrKey = $emrKey ?: ($config['emr_key'] ?? '');
    $catKey = $catKey ?: ($config['catalogue_key'] ?? '');
    
    $upd = $db->prepare("UPDATE lis_config SET 
        is_enabled = 1,
        provider_driver = 'clinos',
        base_url = :base_url,
        emr_key = :emr_key,
        catalogue_key = :cat_key
        WHERE id = 1");
    $upd->execute([':base_url' => $baseUrl, ':emr_key' => $emrKey, ':cat_key' => $catKey]);
    echo "[✓] Updated LIS Configuration in database with provided credentials.\n";
    $config = LisDriverFactory::getConfig($db, true);
} else {
    echo "[i] Using existing LIS configuration from database...\n";
}

if (empty($config['emr_key']) || empty($config['catalogue_key'])) {
    echo "\n[!] WARNING: LIS API Keys are missing in lis_config table.\n";
    echo "    Run this script with --emr-key and --cat-key flags, or save them in admin/lis_settings.php.\n";
    echo "    Example: php scratch/test_lis_workflow.php --emr-key=\"KEY1\" --cat-key=\"KEY2\"\n\n";
}

// Ensure LIS is enabled for test
$db->exec("UPDATE lis_config SET is_enabled = 1 WHERE id = 1");
$config = LisDriverFactory::getConfig($db, true);

// 3. Pick a Patient
if (!$patientNo) {
    $pStmt = $db->query("SELECT hospital_no, surname, fname FROM enrollee ORDER BY hospital_no ASC LIMIT 1");
    $patient = $pStmt->fetch(PDO::FETCH_ASSOC);
    if (!$patient) {
        die("[X] Error: No patients found in enrollee table.\n");
    }
    $patientNo = $patient['hospital_no'];
    $patientName = $patient['surname'] . ' ' . $patient['fname'];
} else {
    $patientName = "Patient " . $patientNo;
}
echo "[✓] Selected Patient: {$patientName} (Hospital No: {$patientNo})\n";

// 4. Seed / Ensure a Test Mapping
$tStmt = $db->query("SELECT sn, test FROM lab_scan ORDER BY sn ASC LIMIT 1");
$testRow = $tStmt->fetch(PDO::FETCH_ASSOC);
if (!$testRow) {
    die("[X] Error: No tests found in lab_scan table.\n");
}

$labScanId = $testRow['sn'];
$testName = $testRow['test'];

$mapStmt = $db->prepare("INSERT INTO lis_test_mappings (lab_scan_id, emr_test_name, canonical_code, lis_provider, is_active)
    VALUES (:lab_scan_id, :emr_test_name, :canonical_code, 'clinos', 1)
    ON DUPLICATE KEY UPDATE canonical_code = VALUES(canonical_code), is_active = 1");
$mapStmt->execute([
    ':lab_scan_id' => $labScanId,
    ':emr_test_name' => $testName,
    ':canonical_code' => $canonicalCode
]);
echo "[✓] Seeded Test Mapping: EMR '{$testName}' (ID: {$labScanId}) -> LIS '{$canonicalCode}'\n";

// 5. Create a Mock Lab Order in EMR
$appNo = date('dmy');
$sn = mt_rand(1000, 9999);
$labrequestNo = 'LBS' . date('d') . $patientNo . sprintf('%06d', $sn);
$setdate = date('Y-m-d H:i:s');
$setdate2 = date('Y-m-d');

$insLab = $db->prepare("INSERT INTO lab_manage 
    (app_no, labrequest_no, patient, patient_name, test_id, test_name, section, request_date, request_date2, request_by, data_capture_status)
    VALUES
    (:app_no, :labrequest_no, :patient, :patient_name, :test_id, :test_name, 'Laboratory', :request_date, :request_date2, 'LIS Bootstrap Test', 'queue')");
$insLab->execute([
    ':app_no' => $appNo,
    ':labrequest_no' => $labrequestNo,
    ':patient' => $patientNo,
    ':patient_name' => $patientName,
    ':test_id' => $labScanId,
    ':test_name' => $testName,
    ':request_date' => $setdate,
    ':request_date2' => $setdate2
]);

echo "[✓] Created EMR Lab Request: {$labrequestNo}\n";

// 6. Dispatch Order to LIS API
echo "\n--- Dispatching Order to LIS API ---\n";
try {
    $dispatchResult = LisService::dispatchOrderIfMapped($db, $labrequestNo, $patientNo, $labScanId, $testName, "Bootstrap test order");

    if ($dispatchResult && !empty($dispatchResult['success'])) {
        echo "[✓] SUCCESS: Order dispatched to ClinOS!\n";
        echo "    ClinOS Order ID  : " . ($dispatchResult['clinos_order_id'] ?? 'N/A') . "\n";
        echo "    ClinOS Order UID : " . ($dispatchResult['clinos_order_uid'] ?? 'N/A') . "\n";
        echo "    Label URL        : " . ($dispatchResult['clinos_label_url'] ?? 'N/A') . "\n";

        // 7. Test Tube Label Download
        if (!empty($dispatchResult['clinos_label_url'])) {
            echo "\n--- Testing Tube Label Fetching ---\n";
            try {
                $driver = LisDriverFactory::getDriver($db);
                $labelHtml = $driver->getLabelContent($dispatchResult['clinos_label_url']);
                echo "[✓] Tube label HTML downloaded successfully! Length: " . strlen($labelHtml) . " bytes.\n";
            } catch (Exception $labelEx) {
                echo "[!] Tube label download warning: " . $labelEx->getMessage() . "\n";
            }
        }
    } else {
        $errorMsg = $dispatchResult['error'] ?? 'Unknown dispatch error';
        echo "[X] DISPATCH FAILED: {$errorMsg}\n";
    }
} catch (Exception $e) {
    echo "[X] DISPATCH EXCEPTION: " . $e->getMessage() . "\n";
}

// 8. Test Result Polling
echo "\n--- Testing Non-Cron Result Sync ---\n";
try {
    $syncResult = LisService::syncResults($db);
    echo "[✓] Result Sync Executed:\n";
    echo "    Status          : " . ($syncResult['status'] ?? 'N/A') . "\n";
    echo "    Items Processed : " . ($syncResult['items_processed'] ?? 0) . "\n";
    echo "    Next After ID   : " . ($syncResult['next_after_id'] ?? 0) . "\n";
} catch (Exception $e) {
    echo "[X] SYNC EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n=======================================================\n";
echo "  Bootstrap Test Completed!\n";
echo "=======================================================\n";
