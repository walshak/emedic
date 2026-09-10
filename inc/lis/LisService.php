<?php
require_once __DIR__ . '/LisDriverFactory.php';

/**
 * Class LisService
 * High-level orchestration service for LIS mappings, dispatches, label retrieval, and result ingestion.
 */
class LisService {

    /**
     * Check if a specific EMR lab test is mapped and active for LIS dispatch
     */
    public static function getMappedCanonicalCode(PDO $db, $testId, string $testName): ?string {
        if (!LisDriverFactory::isLisEnabled($db)) {
            return null;
        }

        $config = LisDriverFactory::getConfig($db);
        $provider = $config['provider_driver'] ?? 'clinos';

        $stmt = $db->prepare("SELECT canonical_code FROM lis_test_mappings 
                              WHERE (lab_scan_id = :test_id OR emr_test_name = :test_name) 
                              AND lis_provider = :provider 
                              AND is_active = 1 
                              LIMIT 1");
        $stmt->execute([
            ':test_id' => $testId,
            ':test_name' => $testName,
            ':provider' => $provider
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['canonical_code'] : null;
    }

    /**
     * Dispatch a batch of selected lab orders as a single LIS order
     */
    public static function dispatchBatchOrders(PDO $db, array $labrequestNos, string $patientNo, string $notes = ''): array {
        if (!LisDriverFactory::isLisEnabled($db)) {
            return ['success' => false, 'error' => 'LIS Integration is currently disabled.'];
        }

        if (empty($labrequestNos)) {
            return ['success' => false, 'error' => 'No lab requests selected for dispatch.'];
        }

        $labrequestNos = array_values(array_filter(array_map('trim', $labrequestNos)));
        if (empty($labrequestNos)) {
            return ['success' => false, 'error' => 'No valid lab request numbers provided.'];
        }

        $placeholders = implode(',', array_fill(0, count($labrequestNos), '?'));
        $stmt = $db->prepare("SELECT labrequest_no, patient, test_id, test_name, request_note FROM lab_manage WHERE labrequest_no IN ($placeholders)");
        $stmt->execute($labrequestNos);
        $labRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($labRows)) {
            return ['success' => false, 'error' => 'Selected lab requests not found in database.'];
        }

        $orderedTests = [];
        $mappedRequestNos = [];

        foreach ($labRows as $row) {
            $reqNo = $row['labrequest_no'];
            $testId = $row['test_id'];
            $testName = $row['test_name'];

            $canonicalCode = self::getMappedCanonicalCode($db, $testId, $testName);
            if ($canonicalCode) {
                $orderedTests[] = $canonicalCode;
                $mappedRequestNos[] = $reqNo;
            }
        }

        if (empty($orderedTests)) {
            return ['success' => false, 'error' => 'None of the selected tests are mapped to External LIS.'];
        }

        $orderedTests = array_values(array_unique($orderedTests));
        $primaryOrderNo = $labrequestNos[0];

        if (empty($patientNo) && !empty($labRows[0]['patient'])) {
            $patientNo = $labRows[0]['patient'];
        }

        $pStmt = $db->prepare("SELECT surname, fname, oname, gender, dob, phone, email FROM enrollee WHERE hospital_no = :patient LIMIT 1");
        $pStmt->execute([':patient' => $patientNo]);
        $patient = $pStmt->fetch(PDO::FETCH_ASSOC);

        $fullname = $patient ? trim(($patient['surname'] ?? '') . ' ' . ($patient['fname'] ?? '') . ' ' . ($patient['oname'] ?? '')) : 'Patient ' . $patientNo;
        $sex = strtoupper(substr($patient['gender'] ?? 'U', 0, 1));
        if ($sex !== 'M' && $sex !== 'F') {
            $sex = 'U';
        }
        $dob = !empty($patient['dob']) && $patient['dob'] !== '0000-00-00' ? $patient['dob'] : null;
        $phone = !empty($patient['phone']) ? $patient['phone'] : null;
        $email = !empty($patient['email']) ? $patient['email'] : null;

        $orderPayload = [
            'external_order_id' => $primaryOrderNo,
            'external_patient_id' => $patientNo,
            'patient_name' => $fullname,
            'sex' => $sex,
            'ordered_tests' => $orderedTests,
            'notes' => !empty($notes) ? $notes : 'Requested from EMR'
        ];

        if ($dob) $orderPayload['date_of_birth'] = $dob;
        if ($phone) $orderPayload['phone_number'] = $phone;
        if ($email) $orderPayload['email'] = $email;

        try {
            $driver = LisDriverFactory::getDriver($db);
            $response = $driver->createOrder($orderPayload);

            $orderId = $response['order_id'] ?? null;
            $orderUid = $response['order_uid'] ?? null;
            $labelUrl = $response['label_url'] ?? null;
            $specimensJson = isset($response['specimens']) ? json_encode($response['specimens']) : null;
            $alreadyExisted = !empty($response['already_existed']);

            $config = LisDriverFactory::getConfig($db);
            $provider = $config['provider_driver'] ?? 'clinos';

            $insStmt = $db->prepare("INSERT INTO lis_orders 
                (labrequest_no, external_order_id, lis_provider, clinos_order_id, clinos_order_uid, clinos_label_url, clinos_specimens_json, status, error_log)
                VALUES 
                (:labrequest_no, :external_order_id, :lis_provider, :clinos_order_id, :clinos_order_uid, :clinos_label_url, :clinos_specimens_json, 'sent', NULL)
                ON DUPLICATE KEY UPDATE 
                clinos_order_id = VALUES(clinos_order_id),
                clinos_order_uid = VALUES(clinos_order_uid),
                clinos_label_url = VALUES(clinos_label_url),
                clinos_specimens_json = VALUES(clinos_specimens_json),
                status = 'sent',
                error_log = NULL");

            foreach ($mappedRequestNos as $reqNo) {
                $insStmt->execute([
                    ':labrequest_no' => $reqNo,
                    ':external_order_id' => $primaryOrderNo,
                    ':lis_provider' => $provider,
                    ':clinos_order_id' => $orderId,
                    ':clinos_order_uid' => $orderUid,
                    ':clinos_label_url' => $labelUrl,
                    ':clinos_specimens_json' => $specimensJson
                ]);
            }

            $msg = $alreadyExisted
                ? "Order already exists on External LIS. Synced " . count($mappedRequestNos) . " test(s) (Order ID: {$orderId})"
                : "Successfully dispatched " . count($mappedRequestNos) . " test(s) to External LIS (Order ID: {$orderId})";

            return [
                'success' => true,
                'already_existed' => $alreadyExisted,
                'message' => $msg,
                'dispatched_count' => count($mappedRequestNos),
                'response' => $response,
                'clinos_order_id' => $orderId,
                'clinos_order_uid' => $orderUid,
                'clinos_label_url' => $labelUrl
            ];

        } catch (Exception $e) {
            $extracted = self::extractOrderDetailsFromError($e->getMessage());

            if ($extracted && !empty($extracted['order_id'])) {
                $orderId = $extracted['order_id'];
                $orderUid = $extracted['order_uid'] ?? null;
                $labelUrl = $extracted['label_url'] ?? null;

                $config = LisDriverFactory::getConfig($db);
                $provider = $config['provider_driver'] ?? 'clinos';

                $insStmt = $db->prepare("INSERT INTO lis_orders 
                    (labrequest_no, external_order_id, lis_provider, clinos_order_id, clinos_order_uid, clinos_label_url, status, error_log)
                    VALUES 
                    (:labrequest_no, :external_order_id, :lis_provider, :clinos_order_id, :clinos_order_uid, :clinos_label_url, 'sent', NULL)
                    ON DUPLICATE KEY UPDATE 
                    clinos_order_id = VALUES(clinos_order_id),
                    clinos_order_uid = VALUES(clinos_order_uid),
                    clinos_label_url = VALUES(clinos_label_url),
                    status = 'sent',
                    error_log = NULL");

                foreach ($mappedRequestNos as $reqNo) {
                    $insStmt->execute([
                        ':labrequest_no' => $reqNo,
                        ':external_order_id' => $primaryOrderNo,
                        ':lis_provider' => $provider,
                        ':clinos_order_id' => $orderId,
                        ':clinos_order_uid' => $orderUid,
                        ':clinos_label_url' => $labelUrl
                    ]);
                }

                return [
                    'success' => true,
                    'already_existed' => true,
                    'message' => "Order already exists on External LIS. Synced " . count($mappedRequestNos) . " test(s) (Order ID: {$orderId})",
                    'dispatched_count' => count($mappedRequestNos),
                    'clinos_order_id' => $orderId,
                    'clinos_order_uid' => $orderUid,
                    'clinos_label_url' => $labelUrl
                ];
            }

            $errStmt = $db->prepare("INSERT INTO lis_orders 
                (labrequest_no, external_order_id, lis_provider, status, error_log) 
                VALUES (:labrequest_no, :external_order_id, 'clinos', 'failed', :error_log)
                ON DUPLICATE KEY UPDATE status = 'failed', error_log = VALUES(error_log)");

            foreach ($mappedRequestNos as $reqNo) {
                $errStmt->execute([
                    ':labrequest_no' => $reqNo,
                    ':external_order_id' => $primaryOrderNo,
                    ':error_log' => $e->getMessage()
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get specimen tube details and assigned tests for a dispatched LIS order
     */
    public static function getSpecimensForOrder(PDO $db, string $identifier): array {
        if (!LisDriverFactory::isLisEnabled($db)) {
            return ['success' => false, 'error' => 'LIS Integration is currently disabled.'];
        }

        $stmt = $db->prepare("SELECT * FROM lis_orders WHERE clinos_order_id = :id1 OR labrequest_no = :id2 OR external_order_id = :id3 LIMIT 1");
        $stmt->execute([
            ':id1' => $identifier,
            ':id2' => $identifier,
            ':id3' => $identifier
        ]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return ['success' => false, 'error' => 'LIS order not found for identifier: ' . $identifier];
        }

        $clinosOrderId = $order['clinos_order_id'] ?? $order['external_order_id'];
        $clinosOrderUid = $order['clinos_order_uid'] ?? '';
        $labelUrl = $order['clinos_label_url'] ?? '';
        $extOrderId = $order['external_order_id'] ?? $order['labrequest_no'];

        $specimensRaw = json_decode($order['clinos_specimens_json'] ?? '[]', true);
        if (!is_array($specimensRaw)) {
            $specimensRaw = [];
        }

        $labStmt = $db->prepare("SELECT l.labrequest_no, l.test_id, l.test_name, m.canonical_code, m.lis_provider 
                                 FROM lab_manage l 
                                 LEFT JOIN lis_test_mappings m ON (m.lab_scan_id = l.test_id OR LOWER(TRIM(m.emr_test_name)) = LOWER(TRIM(l.test_name))) AND m.is_active = 1
                                 WHERE l.labrequest_no IN (
                                     SELECT labrequest_no FROM lis_orders WHERE clinos_order_id = :cid OR external_order_id = :eid
                                 ) OR l.labrequest_no = :lno");
        $labStmt->execute([
            ':cid' => $clinosOrderId,
            ':eid' => $extOrderId,
            ':lno' => $extOrderId
        ]);
        $labItems = $labStmt->fetchAll(PDO::FETCH_ASSOC);

        $specimenTestMap = [];
        foreach ($labItems as $item) {
            $testName = $item['test_name'];
            $canonical = strtoupper($item['canonical_code'] ?? '');

            if (in_array($canonical, ['ALBUMIN_SERUM', 'LIVER_FUNCTION_TEST', 'FLP', 'EUC', 'LFT', 'CRP', 'AMYLASE_URINE', 'AMA', 'CA19_9', 'CEA', 'CREATININE_SERUM', 'CALCIUM'])) {
                $specimenTestMap['SERUM_PLASMA'][] = $testName;
            } elseif (in_array($canonical, ['BLOOD_GROUP', 'FBC', 'GENOTYPE', 'HAEMOGLOBIN_GENOTYPE', 'PCV', 'ESR', 'MALARIA_PARASITE'])) {
                $specimenTestMap['WHOLE_BLOOD'][] = $testName;
            } elseif (in_array($canonical, ['URINALYSIS', 'URINE_MCS'])) {
                $specimenTestMap['URINE'][] = $testName;
            } else {
                $specimenTestMap['DEFAULT'][] = $testName;
            }
        }

        $formattedSpecimens = [];

        if (!empty($specimensRaw)) {
            foreach ($specimensRaw as $sp) {
                $spKey = strtoupper($sp['specimen_key'] ?? ($sp['specimen_type'] ?? 'DEFAULT'));
                $barcode = $sp['barcode'] ?? ($sp['sample_id'] ?? '');
                $status = $sp['status'] ?? 'EXPECTED';

                $assignedTests = $specimenTestMap[$spKey] ?? ($specimenTestMap['DEFAULT'] ?? []);
                if (empty($assignedTests) && !empty($labItems)) {
                    $assignedTests = array_column($labItems, 'test_name');
                }
                $assignedTests = array_values(array_unique($assignedTests));

                $formattedSpecimens[] = [
                    'id' => $sp['id'] ?? '',
                    'specimen_key' => $spKey,
                    'barcode' => $barcode,
                    'sample_id' => $sp['sample_id'] ?? $barcode,
                    'status' => $status,
                    'collection_status' => ($status === 'COLLECTED' || $status === 'RECEIVED') ? 'COLLECTED' : 'NOT COLLECTED',
                    'tests' => !empty($assignedTests) ? implode(', ', $assignedTests) : 'Assigned Tests'
                ];
            }
        } else {
            $formattedSpecimens[] = [
                'id' => '1',
                'specimen_key' => 'SPECIMEN TUBE',
                'barcode' => $clinosOrderId,
                'sample_id' => $clinosOrderId,
                'status' => 'EXPECTED',
                'collection_status' => 'NOT COLLECTED',
                'tests' => implode(', ', array_column($labItems, 'test_name')) ?: 'Ordered Tests'
            ];
        }

        return [
            'success' => true,
            'clinos_order_id' => $clinosOrderId,
            'clinos_order_uid' => $clinosOrderUid,
            'clinos_label_url' => $labelUrl,
            'specimens' => $formattedSpecimens
        ];
    }

    /**
     * Dispatch lab order to LIS if enabled and test is mapped
     */
    public static function dispatchOrderIfMapped(PDO $db, string $labrequestNo, string $patientNo, $testId, string $testName, string $notes = ''): ?array {
        $canonicalCode = self::getMappedCanonicalCode($db, $testId, $testName);
        if (!$canonicalCode) {
            return null; // Not mapped or LIS disabled
        }

        // Fetch patient details from enrollee table
        $pStmt = $db->prepare("SELECT surname, fname, oname, gender, dob, phone, email FROM enrollee WHERE hospital_no = :patient LIMIT 1");
        $pStmt->execute([':patient' => $patientNo]);
        $patient = $pStmt->fetch(PDO::FETCH_ASSOC);

        $fullname = $patient ? trim(($patient['surname'] ?? '') . ' ' . ($patient['fname'] ?? '') . ' ' . ($patient['oname'] ?? '')) : 'Patient ' . $patientNo;
        $sex = strtoupper(substr($patient['gender'] ?? 'U', 0, 1));
        if ($sex !== 'M' && $sex !== 'F') {
            $sex = 'U';
        }
        $dob = !empty($patient['dob']) && $patient['dob'] !== '0000-00-00' ? $patient['dob'] : null;
        $phone = !empty($patient['phone']) ? $patient['phone'] : null;
        $email = !empty($patient['email']) ? $patient['email'] : null;

        $orderPayload = [
            'external_order_id' => $labrequestNo,
            'external_patient_id' => $patientNo,
            'patient_name' => $fullname,
            'sex' => $sex,
            'ordered_tests' => [$canonicalCode],
            'notes' => !empty($notes) ? $notes : 'Requested from EMR'
        ];

        if ($dob) $orderPayload['date_of_birth'] = $dob;
        if ($phone) $orderPayload['phone_number'] = $phone;
        if ($email) $orderPayload['email'] = $email;

        try {
            $driver = LisDriverFactory::getDriver($db);
            $response = $driver->createOrder($orderPayload);

            $orderId = $response['order_id'] ?? null;
            $orderUid = $response['order_uid'] ?? null;
            $labelUrl = $response['label_url'] ?? null;
            $specimensJson = isset($response['specimens']) ? json_encode($response['specimens']) : null;
            $alreadyExisted = !empty($response['already_existed']);

            // Log in lis_orders table
            $insStmt = $db->prepare("INSERT INTO lis_orders 
                (labrequest_no, external_order_id, lis_provider, clinos_order_id, clinos_order_uid, clinos_label_url, clinos_specimens_json, status, error_log)
                VALUES 
                (:labrequest_no, :external_order_id, :lis_provider, :clinos_order_id, :clinos_order_uid, :clinos_label_url, :clinos_specimens_json, 'sent', NULL)
                ON DUPLICATE KEY UPDATE 
                clinos_order_id = VALUES(clinos_order_id),
                clinos_order_uid = VALUES(clinos_order_uid),
                clinos_label_url = VALUES(clinos_label_url),
                clinos_specimens_json = VALUES(clinos_specimens_json),
                status = 'sent',
                error_log = NULL");

            $config = LisDriverFactory::getConfig($db);
            $provider = $config['provider_driver'] ?? 'clinos';

            $insStmt->execute([
                ':labrequest_no' => $labrequestNo,
                ':external_order_id' => $labrequestNo,
                ':lis_provider' => $provider,
                ':clinos_order_id' => $orderId,
                ':clinos_order_uid' => $orderUid,
                ':clinos_label_url' => $labelUrl,
                ':clinos_specimens_json' => $specimensJson
            ]);

            $msg = $alreadyExisted
                ? "Order already exists on External LIS. Synced local record (Order ID: {$orderId})"
                : "Successfully dispatched order to External LIS (Order ID: {$orderId})";

            return [
                'success' => true,
                'already_existed' => $alreadyExisted,
                'message' => $msg,
                'response' => $response,
                'clinos_order_id' => $orderId,
                'clinos_order_uid' => $orderUid,
                'clinos_label_url' => $labelUrl
            ];

        } catch (Exception $e) {
            $extracted = self::extractOrderDetailsFromError($e->getMessage());

            if ($extracted && !empty($extracted['order_id'])) {
                $orderId = $extracted['order_id'];
                $orderUid = $extracted['order_uid'] ?? null;
                $labelUrl = $extracted['label_url'] ?? null;

                $config = LisDriverFactory::getConfig($db);
                $provider = $config['provider_driver'] ?? 'clinos';

                $insStmt = $db->prepare("INSERT INTO lis_orders 
                    (labrequest_no, external_order_id, lis_provider, clinos_order_id, clinos_order_uid, clinos_label_url, status, error_log)
                    VALUES 
                    (:labrequest_no, :external_order_id, :lis_provider, :clinos_order_id, :clinos_order_uid, :clinos_label_url, 'sent', NULL)
                    ON DUPLICATE KEY UPDATE 
                    clinos_order_id = VALUES(clinos_order_id),
                    clinos_order_uid = VALUES(clinos_order_uid),
                    clinos_label_url = VALUES(clinos_label_url),
                    status = 'sent',
                    error_log = NULL");

                $insStmt->execute([
                    ':labrequest_no' => $labrequestNo,
                    ':external_order_id' => $labrequestNo,
                    ':lis_provider' => $provider,
                    ':clinos_order_id' => $orderId,
                    ':clinos_order_uid' => $orderUid,
                    ':clinos_label_url' => $labelUrl
                ]);

                return [
                    'success' => true,
                    'already_existed' => true,
                    'message' => "Order already exists on External LIS. Synced local record (Order ID: {$orderId})",
                    'clinos_order_id' => $orderId,
                    'clinos_order_uid' => $orderUid,
                    'clinos_label_url' => $labelUrl
                ];
            }

            // Log hard error in lis_orders
            $errStmt = $db->prepare("INSERT INTO lis_orders 
                (labrequest_no, external_order_id, lis_provider, status, error_log) 
                VALUES (:labrequest_no, :external_order_id, 'clinos', 'failed', :error_log)
                ON DUPLICATE KEY UPDATE status = 'failed', error_log = VALUES(error_log)");
            $errStmt->execute([
                ':labrequest_no' => $labrequestNo,
                ':external_order_id' => $labrequestNo,
                ':error_log' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract order metadata from an exception message if it contains a 409 duplicate JSON payload
     */
    public static function extractOrderDetailsFromError(string $errorMessage): ?array {
        if (preg_match('/\{.*\}/s', $errorMessage, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                $detailData = $decoded['detail']['detail'] ?? ($decoded['detail'] ?? $decoded);
                if (is_array($detailData) && !empty($detailData['clinos_order_id'])) {
                    $orderUid = $detailData['clinos_order_uid'] ?? null;
                    return [
                        'order_id' => $detailData['clinos_order_id'],
                        'order_uid' => $orderUid,
                        'label_url' => $orderUid ? "/api/v1/lab/orders/{$orderUid}/label" : null
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Pull and process results from LIS (Non-cron, application-level)
     */
    public static function syncResults(PDO $db, int $limit = 100): array {
        if (!LisDriverFactory::isLisEnabled($db)) {
            return ['status' => 'disabled', 'items_processed' => 0];
        }

        $config = LisDriverFactory::getConfig($db);
        $afterId = (int)($config['last_polled_after_id'] ?? 0);

        $driver = LisDriverFactory::getDriver($db);
        $resultData = $driver->pullResults($afterId, $limit);

        $items = $resultData['items'] ?? [];
        $nextAfterId = $resultData['next_after_id'] ?? $afterId;

        $processedCount = 0;

        foreach ($items as $item) {
            $eventId = $item['event_id'] ?? ($item['id'] ?? null);
            if (!$eventId) continue;

            // Idempotency check
            $chkStmt = $db->prepare("SELECT id FROM lis_event_logs WHERE event_id = :event_id LIMIT 1");
            $chkStmt->execute([':event_id' => $eventId]);
            if ($chkStmt->rowCount() > 0) {
                continue; // Already processed
            }

            $payload = $item['payload'] ?? $item;
            $orderInfo = $payload['order'] ?? [];
            $extOrderId = $orderInfo['external_order_id'] ?? null;
            $resultsList = $payload['results'] ?? [];
            $validation = $payload['validation'] ?? [];

            $status = 'processed';
            $errorMsg = null;

            if ($extOrderId) {
                // Check if matching lab request exists
                $labStmt = $db->prepare("SELECT sn, patient FROM lab_manage WHERE labrequest_no = :ext_id LIMIT 1");
                $labStmt->execute([':ext_id' => $extOrderId]);
                $labRow = $labStmt->fetch(PDO::FETCH_ASSOC);

                if ($labRow) {
                    $validatedBy = $validation['validated_by'] ?? 'ClinOS System';
                    $validatedAt = !empty($validation['validated_at']) ? date('Y-m-d H:i:s', strtotime($validation['validated_at'])) : date('Y-m-d H:i:s');
                    $isApproved = strtoupper($validation['status'] ?? '') === 'VALIDATED';

                    $orderNotesVal = trim($validation['notes'] ?? $validation['comments'] ?? $validation['comment'] ?? $orderInfo['notes'] ?? $payload['notes'] ?? $payload['comment'] ?? $payload['result_comment'] ?? '');

                    foreach ($resultsList as $r) {
                        $code = $r['code'] ?? '';
                        $name = $r['name'] ?? $code;
                        $value = $r['value'] ?? '—';
                        $unit = $r['unit'] ?? ($r['units'] ?? '');
                        $refRange = $r['reference_range'] ?? ($r['referenceRange'] ?? '');
                        $flag = $r['flag'] ?? '';
                        $itemNote = trim($r['comment'] ?? $r['comments'] ?? $r['note'] ?? $r['notes'] ?? $r['remark'] ?? $r['remarks'] ?? $r['clinical_note'] ?? $r['clinical_notes'] ?? '');

                        $commentVal = $flag ? "Flag: {$flag}" : '';
                        if (!empty($itemNote)) {
                            $commentVal = $commentVal ? "{$commentVal} | Note: {$itemNote}" : "Note: {$itemNote}";
                        }

                        $valueWithUnit = (!empty($unit) && strpos($value, $unit) === false) ? trim("{$value} {$unit}") : $value;

                        $summaryLines[] = "{$name}: {$valueWithUnit}" . ($refRange ? " (Ref: {$refRange})" : "") . ($flag ? " [{$flag}]" : "");

                        // Save into lab_result table
                        $chkRes = $db->prepare("SELECT sn FROM lab_result WHERE lab_no = :lab_no AND field_no = :field_no LIMIT 1");
                        $chkRes->execute([':lab_no' => $extOrderId, ':field_no' => $code]);

                        if ($chkRes->rowCount() == 0) {
                            $insRes = $db->prepare("INSERT INTO lab_result 
                                (field_no, field_name, field_value, field_ref, lab_no, test_name, result_date, entered_by, comment, notes)
                                VALUES
                                (:field_no, :field_name, :field_value, :field_ref, :lab_no, :test_name, :result_date, :entered_by, :comment, :notes)");
                            $insRes->execute([
                                ':field_no' => $code,
                                ':field_name' => $name,
                                ':field_value' => $valueWithUnit,
                                ':field_ref' => $refRange,
                                ':lab_no' => $extOrderId,
                                ':test_name' => $name,
                                ':result_date' => $validatedAt,
                                ':entered_by' => $validatedBy,
                                ':comment' => $commentVal,
                                ':notes' => $itemNote
                            ]);
                        } else {
                            $updRes = $db->prepare("UPDATE lab_result 
                                SET field_value = :field_value, field_ref = :field_ref, result_date = :result_date, entered_by = :entered_by, comment = :comment, notes = :notes
                                WHERE lab_no = :lab_no AND field_no = :field_no");
                            $updRes->execute([
                                ':field_value' => $valueWithUnit,
                                ':field_ref' => $refRange,
                                ':result_date' => $validatedAt,
                                ':entered_by' => $validatedBy,
                                ':comment' => $commentVal,
                                ':notes' => $itemNote,
                                ':lab_no' => $extOrderId,
                                ':field_no' => $code
                            ]);
                        }
                    }

                    $resultNoteText = implode("\n", $summaryLines);
                    $newStatus = $isApproved ? 'approve' : 'result';

                    // Update lab_manage
                    $updLab = $db->prepare("UPDATE lab_manage SET 
                        result_note = :result_note,
                        result_comment = :result_comment,
                        lab_sci_name = :lab_sci_name,
                        result_date = :result_date,
                        entered_by = :entered_by,
                        approved_by = :approved_by,
                        data_capture_status = :status
                        WHERE labrequest_no = :labrequest_no");

                    $updLab->execute([
                        ':result_note' => $resultNoteText,
                        ':result_comment' => $orderNotesVal,
                        ':lab_sci_name' => $validatedBy,
                        ':result_date' => $validatedAt,
                        ':entered_by' => $validatedBy,
                        ':approved_by' => $isApproved ? $validatedBy : null,
                        ':status' => $newStatus,
                        ':labrequest_no' => $extOrderId
                    ]);

                    // Update lis_orders status
                    $updLisOrd = $db->prepare("UPDATE lis_orders SET status = 'validated' WHERE labrequest_no = :labrequest_no");
                    $updLisOrd->execute([':labrequest_no' => $extOrderId]);

                    $processedCount++;
                }
            }

            // Log event in lis_event_logs
            $logStmt = $db->prepare("INSERT INTO lis_event_logs (event_id, external_order_id, after_id, payload_json, status, error_message)
                VALUES (:event_id, :external_order_id, :after_id, :payload_json, :status, :error_message)");
            $logStmt->execute([
                ':event_id' => $eventId,
                ':external_order_id' => $extOrderId ?? 'UNKNOWN',
                ':after_id' => $item['id'] ?? null,
                ':payload_json' => json_encode($payload),
                ':status' => $status,
                ':error_message' => $errorMsg
            ]);
        }

        // Advance cursor position in lis_config
        $updConfig = $db->prepare("UPDATE lis_config SET last_polled_after_id = :next_after_id, last_poll_timestamp = NOW() WHERE id = 1");
        $updConfig->execute([':next_after_id' => $nextAfterId]);

        return [
            'status' => 'success',
            'items_processed' => $processedCount,
            'next_after_id' => $nextAfterId
        ];
    }

    /**
     * Render structured ClinOS Result HTML card component for EMR display
     */
    public static function renderResultCardHTML(array $eventPayload): string {
        $patient = $eventPayload['patient'] ?? [];
        $order = $eventPayload['order'] ?? [];
        $validation = $eventPayload['validation'] ?? [];
        $results = $eventPayload['results'] ?? [];

        $rowsHtml = '';
        foreach ($results as $r) {
            $code = htmlspecialchars($r['code'] ?? '');
            $name = htmlspecialchars($r['name'] ?? $code);
            $value = htmlspecialchars($r['value'] ?? '—');
            $unit = htmlspecialchars($r['unit'] ?? ($r['units'] ?? '—'));
            $ref = htmlspecialchars($r['reference_range'] ?? ($r['referenceRange'] ?? '—'));
            $flag = strtoupper(trim($r['flag'] ?? ''));

            $flagBadge = '—';
            if (!empty($flag)) {
                $badgeColor = in_array($flag, ['H', 'HH', 'CRITICAL']) ? '#ef4444' : (in_array($flag, ['L', 'LL']) ? '#3b82f6' : '#10b981');
                $flagBadge = "<span style='background: {$badgeColor}; color: #ffffff; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;'>{$flag}</span>";
            }

            $rowsHtml .= "<tr>
                <td><strong>{$name}</strong><br><small style='color:#64748b;'>{$code}</small></td>
                <td style='font-weight: 700; font-size: 14px;'>{$value}</td>
                <td>{$unit}</td>
                <td style='color:#64748b;'>{$ref}</td>
                <td>{$flagBadge}</td>
            </tr>";
        }

        $patientName = htmlspecialchars($patient['name'] ?? 'Patient');
        $patientId = htmlspecialchars($patient['external_patient_id'] ?? '—');
        $orderId = htmlspecialchars($order['external_order_id'] ?? ($order['clinos_order_id'] ?? '—'));
        $validatedBy = htmlspecialchars($validation['validated_by'] ?? 'Laboratory Scientist');
        $validatedAt = htmlspecialchars($validation['validated_at'] ?? '');
        $status = htmlspecialchars($validation['status'] ?? 'VALIDATED');

        return "
        <div style='font-family: Inter, Arial, sans-serif; max-width: 850px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; overflow: hidden; color: #1e293b; margin: 15px 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
            <div style='padding: 16px 20px; background: #f0fdfa; border-bottom: 1px solid #ccfbf1;'>
                <h4 style='margin: 0; color: #0f766e; font-weight: 700;'><i class='fa fa-flask'></i> External LIS Laboratory Report</h4>
                <div style='margin-top: 4px; font-size: 12px; color: #475569;'>
                    <strong>{$patientName}</strong> &middot; EMR: <code>{$patientId}</code> &middot; Order: <code>{$orderId}</code>
                </div>
            </div>
            <table class='table' style='width: 100%; border-collapse: collapse; margin-bottom: 0;'>
                <thead>
                    <tr style='background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase;'>
                        <th style='padding: 10px 14px;'>Test</th>
                        <th style='padding: 10px 14px;'>Result</th>
                        <th style='padding: 10px 14px;'>Unit</th>
                        <th style='padding: 10px 14px;'>Reference Range</th>
                        <th style='padding: 10px 14px;'>Flag</th>
                    </tr>
                </thead>
                <tbody style='font-size: 13px;'>
                    " . ($rowsHtml ?: "<tr><td colspan='5' style='padding: 14px;'>No result details available.</td></tr>") . "
                </tbody>
            </table>
            <div style='padding: 10px 20px; background: #fafafa; color: #64748b; font-size: 12px; border-top: 1px solid #f1f5f9;'>
                Status: <strong>{$status}</strong> &middot; Validated by: <strong>{$validatedBy}</strong> {$validatedAt}
            </div>
        </div>";
    }

    /**
     * Automatically scan and dispatch queued mapped lab orders to LIS in background
     */
    public static function autoDispatchPendingOrders(PDO $db, int $limit = 5): array {
        if (!LisDriverFactory::isLisEnabled($db)) {
            return ['status' => 'disabled', 'dispatched_count' => 0];
        }

        $config = LisDriverFactory::getConfig($db);
        $provider = $config['provider_driver'] ?? 'clinos';

        // Query queued lab requests from last 48 hrs that are mapped and not yet sent/validated
        $sql = "SELECT lm.labrequest_no, lm.patient, lm.test_id, lm.test_name, lm.request_note
                FROM lab_manage lm
                INNER JOIN lis_test_mappings m 
                    ON (m.lab_scan_id = lm.test_id OR m.emr_test_name = lm.test_name)
                    AND m.lis_provider = :provider 
                    AND m.is_active = 1
                LEFT JOIN lis_orders lo 
                    ON lo.labrequest_no = lm.labrequest_no
                WHERE lm.data_capture_status = 'queue'
                  AND lm.request_date >= NOW() - INTERVAL 48 HOUR
                  AND (lo.id IS NULL OR lo.status IN ('pending', 'failed'))
                GROUP BY lm.labrequest_no
                ORDER BY lm.sn ASC
                LIMIT :limit";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':provider', $provider, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $dispatched = 0;
        $errors = [];

        foreach ($rows as $row) {
            $labrequestNo = $row['labrequest_no'];
            $patientNo    = $row['patient'];
            $testId       = $row['test_id'];
            $testName     = $row['test_name'];
            $notes        = $row['request_note'] ?? '';

            try {
                $res = self::dispatchOrderIfMapped($db, $labrequestNo, $patientNo, $testId, $testName, $notes);
                if ($res && !empty($res['success'])) {
                    $dispatched++;
                } elseif ($res && !empty($res['error'])) {
                    $errors[] = "Req #{$labrequestNo}: {$res['error']}";
                }
            } catch (Exception $e) {
                $errors[] = "Req #{$labrequestNo}: {$e->getMessage()}";
                error_log("Auto-Dispatch Exception for Req #{$labrequestNo}: " . $e->getMessage());
            }
        }

        return [
            'status' => 'success',
            'dispatched_count' => $dispatched,
            'errors' => $errors
        ];
    }

    /**
     * Get unseen LIS result notifications for a specific user within last 48 hours
     */
    public static function getUnseenNotifications(PDO $db, string $username, int $hours = 48): array {
        if (!LisDriverFactory::isLisEnabled($db)) {
            return [];
        }

        $sql = "SELECT lm.labrequest_no, lm.patient AS hospital_no, lm.test_name, lm.section, lm.result_date, lm.approved_by, lm.entered_by, lm.data_capture_status, lo.status AS lis_status, lo.clinos_order_id
                FROM lab_manage lm
                INNER JOIN lis_orders lo ON lo.labrequest_no = lm.labrequest_no
                LEFT JOIN lis_notifications_seen s 
                    ON s.labrequest_no = lm.labrequest_no 
                    AND s.username = :username 
                    AND s.event_type = 'result_received'
                WHERE s.id IS NULL
                  AND lm.data_capture_status IN ('result', 'approve')
                  AND lm.result_date >= NOW() - INTERVAL :hours HOUR
                ORDER BY lm.result_date DESC";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':hours', $hours, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark LIS notifications as seen for a specific user
     */
    public static function markNotificationsAsSeen(PDO $db, string $username, ?string $labrequestNo = null): bool {
        if (empty($username)) return false;

        if (!empty($labrequestNo)) {
            $stmt = $db->prepare("INSERT IGNORE INTO lis_notifications_seen (username, labrequest_no, event_type) VALUES (:username, :labrequest_no, 'result_received')");
            return $stmt->execute([':username' => $username, ':labrequest_no' => $labrequestNo]);
        } else {
            // Mark all current unseen results from last 48 hrs as seen
            $unseen = self::getUnseenNotifications($db, $username, 48);
            if (empty($unseen)) return true;

            $insStmt = $db->prepare("INSERT IGNORE INTO lis_notifications_seen (username, labrequest_no, event_type) VALUES (:username, :labrequest_no, 'result_received')");
            foreach ($unseen as $item) {
                $insStmt->execute([
                    ':username' => $username,
                    ':labrequest_no' => $item['labrequest_no']
                ]);
            }
            return true;
        }
    }
}

