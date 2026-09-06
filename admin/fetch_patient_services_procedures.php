<?php
require_once(__DIR__ . '/../Connections/Conn.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('cleanInput')) {
    function cleanInput($data) {
        if (is_array($data)) return array_map('cleanInput', $data);
        return htmlspecialchars(stripslashes(trim((string)$data)), ENT_QUOTES, 'UTF-8');
    }
}

header('Content-Type: application/json');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Fetch hospital settings for permissions
$hdStmt = $db->query("SELECT frontdesk_can_book_procedures, frontdesk_can_book_medical_services FROM hospital_details LIMIT 1");
$hdSettings = $hdStmt->fetch(PDO::FETCH_ASSOC);

$canBookProcedures = (!empty($_SESSION['rights']) && in_array($_SESSION['rights'], ['DR', 'MD', 'SA'])) || (!empty($hdSettings['frontdesk_can_book_procedures']) && $hdSettings['frontdesk_can_book_procedures'] == 1);
$canBookMedServices = (!empty($_SESSION['rights']) && in_array($_SESSION['rights'], ['DR', 'MD', 'SA'])) || (!empty($hdSettings['frontdesk_can_book_medical_services']) && $hdSettings['frontdesk_can_book_medical_services'] == 1);
$canEditOutcomesNotes = (!empty($_SESSION['rights']) && in_array($_SESSION['rights'], ['DR', 'MD', 'SA']));
$canHandleBilling = (!empty($_SESSION['biller']) && $_SESSION['biller'] == 1) || (!empty($_SESSION['rights']) && in_array($_SESSION['rights'], ['CA', 'SA']));

try {
    if ($action === 'fetch_procedures') {
        $hosp_no = isset($_REQUEST['hosp_no']) ? cleanInput($_REQUEST['hosp_no']) : '';
        $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
        $status_filter = isset($_REQUEST['status_filter']) ? trim($_REQUEST['status_filter']) : 'all';
        $page = isset($_REQUEST['page']) ? max(1, intval($_REQUEST['page'])) : 1;
        $limit = isset($_REQUEST['limit']) ? max(1, intval($_REQUEST['limit'])) : 5;
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE pr.hospital_no = :hosp_no";
        $params = [':hosp_no' => $hosp_no];

        if (!empty($search)) {
            $whereClause .= " AND (pr.procedures LIKE :search OR pr.consultant_name LIKE :search OR pr.prepared_by LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($status_filter === 'paid') {
            $whereClause .= " AND pas.paystatus = 1";
        } elseif ($status_filter === 'unpaid') {
            $whereClause .= " AND (pas.paystatus = 0 OR pas.paystatus IS NULL)";
        } elseif ($status_filter === 'completed') {
            $whereClause .= " AND pr.status = 1";
        } elseif ($status_filter === 'pending') {
            $whereClause .= " AND (pr.status = 0 OR pr.status IS NULL)";
        }

        // Total count query
        $countSql = "SELECT COUNT(*) FROM procedures pr LEFT JOIN patient_ap_services pas ON pr.sale_no = pas.sn $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = max(1, ceil($totalRecords / $limit));

        // Data query
        $dataSql = "SELECT pr.*, pas.paystatus, pas.pay, pas.claim_amt, pas.pay_mode, pas.drug_status 
                    FROM procedures pr 
                    LEFT JOIN patient_ap_services pas ON pr.sale_no = pas.sn 
                    $whereClause 
                    ORDER BY pr.sn DESC LIMIT $limit OFFSET $offset";
        $dataStmt = $db->prepare($dataSql);
        $dataStmt->execute($params);
        $records = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 200,
            'data' => $records,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'can_book_procedures' => $canBookProcedures,
            'can_edit_outcomes_notes' => $canEditOutcomesNotes,
            'can_handle_billing' => $canHandleBilling
        ]);
        exit;
    }

    if ($action === 'fetch_med_services') {
        $hosp_no = isset($_REQUEST['hosp_no']) ? cleanInput($_REQUEST['hosp_no']) : '';
        $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
        $status_filter = isset($_REQUEST['status_filter']) ? trim($_REQUEST['status_filter']) : 'all';
        $page = isset($_REQUEST['page']) ? max(1, intval($_REQUEST['page'])) : 1;
        $limit = isset($_REQUEST['limit']) ? max(1, intval($_REQUEST['limit'])) : 5;
        $offset = ($page - 1) * $limit;

        $whereClause = "WHERE pas.hospital_no = :hosp_no AND pas.serv_group = 'Medical Services' AND (pas.cat_type != 'Procedure' OR pas.cat_type IS NULL)";
        $params = [':hosp_no' => $hosp_no];

        if (!empty($search)) {
            $whereClause .= " AND (pas.item_services LIKE :search OR ns.consultant_name LIKE :search OR pas.prepared_by LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($status_filter === 'paid') {
            $whereClause .= " AND pas.paystatus = 1";
        } elseif ($status_filter === 'unpaid') {
            $whereClause .= " AND (pas.paystatus = 0 OR pas.paystatus IS NULL)";
        } elseif ($status_filter === 'completed') {
            $whereClause .= " AND (pas.drug_status = 1 OR ns.isCompleted = 1)";
        } elseif ($status_filter === 'pending') {
            $whereClause .= " AND pas.drug_status = 0 AND (ns.isCompleted IS NULL OR ns.isCompleted = 0)";
        }

        $countSql = "SELECT COUNT(*) FROM patient_ap_services pas LEFT JOIN notes_services ns ON (pas.sn = ns.app_service_id OR pas.sn = ns.app_service_tbl_id) $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        $totalPages = max(1, ceil($totalRecords / $limit));

        $dataSql = "SELECT pas.*, ns.id as note_id, ns.notes, ns.consultant_id, ns.consultant_name, ns.isCompleted, ns.template_name, ns.prepared_by as ns_prepared_by 
                    FROM patient_ap_services pas 
                    LEFT JOIN notes_services ns ON (pas.sn = ns.app_service_id OR pas.sn = ns.app_service_tbl_id) 
                    $whereClause 
                    ORDER BY pas.sn DESC LIMIT $limit OFFSET $offset";
        $dataStmt = $db->prepare($dataSql);
        $dataStmt->execute($params);
        $records = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 200,
            'data' => $records,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'can_book_med_services' => $canBookMedServices,
            'can_edit_outcomes_notes' => $canEditOutcomesNotes,
            'can_handle_billing' => $canHandleBilling
        ]);
        exit;
    }

    if ($action === 'book_procedure') {
        if (!$canBookProcedures) {
            echo json_encode(['status' => 403, 'message' => 'Hospital setting does not permit Frontdesk to book procedures.']);
            exit;
        }

        $hosp_no = cleanInput($_POST['hosp_no']);
        $app_no = isset($_POST['app_no']) && !empty($_POST['app_no']) ? cleanInput($_POST['app_no']) : 'PROC-' . date('YmdHis');
        $item_sn = intval($_POST['item_sn']);
        $procedure_name = cleanInput($_POST['procedure_name']);
        $date_timee = cleanInput($_POST['date_timee']);
        $consultant_id = isset($_POST['consultant_id']) ? intval($_POST['consultant_id']) : 0;
        $consultant_name = isset($_POST['consultant_name']) ? cleanInput($_POST['consultant_name']) : '';
        $indication = isset($_POST['indication']) ? cleanInput($_POST['indication']) : '';
        $require_theater = isset($_POST['require_theater']) ? cleanInput($_POST['require_theater']) : 'No';
        $theater_select = isset($_POST['theater_select']) ? cleanInput($_POST['theater_select']) : '';
        $procedure_time = isset($_POST['procedure_time']) ? cleanInput($_POST['procedure_time']) : '';
        $cost = floatval($_POST['cost']);
        $pay_mode = isset($_POST['pay_mode']) ? cleanInput($_POST['pay_mode']) : 'Cash';

        // Fetch patient info
        $patStmt = $db->prepare("SELECT surname, fname, insurance FROM enrollee WHERE hospital_no = ? LIMIT 1");
        $patStmt->execute([$hosp_no]);
        $pat = $patStmt->fetch(PDO::FETCH_ASSOC);
        $patient_name = $pat ? $pat['surname'] . ' ' . $pat['fname'] : 'Patient';
        $patient_insurance = $pat ? $pat['insurance'] : 'Private';

        $setdate = date('Y-m-d H:i:s');
        $invoice_no = date('m') . sprintf('%006d', mt_rand(0, 99999));
        $one = 1;
        $serv_group = 'Medical Services';
        $cat_type = 'Procedure';
        $dept_id = '0';
        $prepared_by = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Frontdesk';
        $created_by = isset($_SESSION['id']) ? $_SESSION['id'] : 1;

        $db->beginTransaction();

        $sql = $db->prepare("INSERT INTO patient_ap_services (app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, invoice_status, invoice_no, prepared_by, created_by, date_entry, pay, pay_mode) 
                            VALUES (:app_no, :hospital_no, :serv_group, :cat_type, :dept_id, :drug_sn, :item_services, :hosp_price, :claim_amt, 0, :qty, :invoice_status, :invoice_no, :prepared_by, :created_by, :date_entry, :pay, :pay_mode)");
        $sql->execute([
            ':app_no' => $app_no,
            ':hospital_no' => $hosp_no,
            ':serv_group' => $serv_group,
            ':cat_type' => $cat_type,
            ':dept_id' => $dept_id,
            ':drug_sn' => $item_sn,
            ':item_services' => $procedure_name,
            ':hosp_price' => $cost,
            ':claim_amt' => ($patient_insurance !== 'Private' ? $cost : 0),
            ':qty' => $one,
            ':invoice_status' => $one,
            ':invoice_no' => $invoice_no,
            ':prepared_by' => $prepared_by,
            ':created_by' => $created_by,
            ':date_entry' => $setdate,
            ':pay' => $cost,
            ':pay_mode' => $pay_mode
        ]);
        $lastId = $db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO procedures(app_no, hospital_no, name, insurance_no, insurance_type, interest, primary_diag, dept_id, referral, prepared_by, date_entry, sDate, procedures, service_id, cost, created_by, consultant_id, consultant_name, require_theater, theater, procedure_time, sale_no) 
                            VALUES (?, ?, ?, ?, ?, 0, ?, ?, 'IN', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $app_no, $hosp_no, $patient_name, '', $patient_insurance, $indication, $dept_id, $prepared_by, $setdate, $date_timee, $procedure_name, $item_sn, $cost, $created_by, $consultant_id, $consultant_name, $require_theater, $theater_select, $procedure_time, $lastId
        ]);

        $db->commit();
        echo json_encode([
            'status' => 200, 
            'message' => 'Procedure booked successfully!',
            'can_handle_billing' => $canHandleBilling
        ]);
        exit;
    }

    if ($action === 'book_med_service') {
        if (!$canBookMedServices) {
            echo json_encode(['status' => 403, 'message' => 'Hospital setting does not permit Frontdesk to book medical services.']);
            exit;
        }

        $hosp_no = cleanInput($_POST['hosp_no']);
        $app_no = isset($_POST['app_no']) && !empty($_POST['app_no']) ? cleanInput($_POST['app_no']) : 'MEDS-' . date('YmdHis');
        $item_sn = intval($_POST['item_sn']);
        $service_name = cleanInput($_POST['service_name']);
        $cost = floatval($_POST['cost']);
        $consultant_id = isset($_POST['consultant_id']) ? intval($_POST['consultant_id']) : 0;
        $consultant_name = isset($_POST['consultant_name']) ? cleanInput($_POST['consultant_name']) : '';
        $notes = isset($_POST['notes']) ? cleanInput($_POST['notes']) : '';
        $pay_mode = isset($_POST['pay_mode']) ? cleanInput($_POST['pay_mode']) : 'Cash';

        $setdate = date('Y-m-d H:i:s');
        $invoice_no = date('m') . sprintf('%006d', mt_rand(0, 99999));
        $one = 1;
        $prepared_by = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'Frontdesk';
        $created_by = isset($_SESSION['id']) ? $_SESSION['id'] : 1;

        $db->beginTransaction();

        $sql = $db->prepare("INSERT INTO patient_ap_services (app_no, hospital_no, serv_group, cat_type, dept_id, drug_sn, item_services, hosp_price, claim_amt, interest, qty, invoice_status, invoice_no, prepared_by, created_by, date_entry, pay, pay_mode) 
                            VALUES (:app_no, :hospital_no, 'Medical Services', 'Medical Services', '0', :drug_sn, :item_services, :hosp_price, 0, 0, 1, 1, :invoice_no, :prepared_by, :created_by, :date_entry, :pay, :pay_mode)");
        $sql->execute([
            ':app_no' => $app_no,
            ':hospital_no' => $hosp_no,
            ':drug_sn' => $item_sn,
            ':item_services' => $service_name,
            ':hosp_price' => $cost,
            ':invoice_no' => $invoice_no,
            ':prepared_by' => $prepared_by,
            ':created_by' => $created_by,
            ':date_entry' => $setdate,
            ':pay' => $cost,
            ':pay_mode' => $pay_mode
        ]);
        $lastId = $db->lastInsertId();

        $nStmt = $db->prepare("INSERT INTO notes_services (hospital_no, app_no, notes, consultant_id, consultant_name, isCompleted, service, app_service_tbl_id, app_service_id, prepared_by, updated_by_name, created_by, created_at, status) 
                              VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, '1')");
        $nStmt->execute([$hosp_no, $app_no, $notes, $consultant_id, $consultant_name, $service_name, $item_sn, $lastId, $prepared_by, $prepared_by, $created_by, $setdate]);

        $db->commit();
        echo json_encode([
            'status' => 200, 
            'message' => 'Medical service booked successfully!',
            'can_handle_billing' => $canHandleBilling
        ]);
        exit;
    }

    if ($action === 'save_procedure_outcome') {
        if (!$canEditOutcomesNotes) {
            echo json_encode(['status' => 403, 'message' => 'Receptionist / Non-doctor staff cannot edit clinical notes or procedure outcomes.']);
            exit;
        }

        $procedure_sn = intval($_POST['procedure_sn']);
        $post_op_results = cleanInput($_POST['post_op_results']);
        $findings = isset($_POST['findings']) ? cleanInput($_POST['findings']) : '';
        $incision = isset($_POST['incision']) ? cleanInput($_POST['incision']) : '';
        $anaesthetia_type = isset($_POST['anaesthetia_type']) ? cleanInput($_POST['anaesthetia_type']) : '';
        $status = isset($_POST['is_completed']) && $_POST['is_completed'] == '1' ? 1 : 0;

        $stmt = $db->prepare("UPDATE procedures SET post_op_results = ?, Findings = ?, Incision = ?, anaesthetia_type = ?, status = ? WHERE sn = ?");
        $stmt->execute([$post_op_results, $findings, $incision, $anaesthetia_type, $status, $procedure_sn]);

        echo json_encode(['status' => 200, 'message' => 'Procedure outcome updated successfully!']);
        exit;
    }

    if ($action === 'save_med_service_notes') {
        if (!$canEditOutcomesNotes) {
            echo json_encode(['status' => 403, 'message' => 'Receptionist / Non-doctor staff cannot edit clinical notes or outcomes.']);
            exit;
        }

        $app_service_tbl_id = intval($_POST['app_service_tbl_id']);
        $notes = cleanInput($_POST['notes']);
        $consultant_id = isset($_POST['consultant_id']) ? intval($_POST['consultant_id']) : 0;
        $consultant_name = isset($_POST['consultant_name']) ? cleanInput($_POST['consultant_name']) : '';
        $is_completed = isset($_POST['is_completed']) && $_POST['is_completed'] == '1' ? 1 : 0;

        $chkStmt = $db->prepare("SELECT id FROM notes_services WHERE app_service_id = ? OR app_service_tbl_id = ? LIMIT 1");
        $chkStmt->execute([$app_service_tbl_id, $app_service_tbl_id]);
        if ($chkStmt->rowCount() > 0) {
            $noteId = $chkStmt->fetchColumn();
            $stmt = $db->prepare("UPDATE notes_services SET notes = ?, consultant_id = ?, consultant_name = ?, isCompleted = ?, updated_by_name = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$notes, $consultant_id, $consultant_name, $is_completed, $_SESSION['fullname'], $noteId]);
        } else {
            $svcStmt = $db->prepare("SELECT item_services, drug_sn, hospital_no, app_no, prepared_by FROM patient_ap_services WHERE sn = ? LIMIT 1");
            $svcStmt->execute([$app_service_tbl_id]);
            $svcInfo = $svcStmt->fetch(PDO::FETCH_ASSOC);

            $setdate = date('Y-m-d H:i:s');
            $created_by = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
            $preparedByVal = (!empty($svcInfo) && !empty($svcInfo['prepared_by'])) ? $svcInfo['prepared_by'] : $_SESSION['fullname'];
            
            $stmt = $db->prepare("INSERT INTO notes_services (hospital_no, app_no, notes, consultant_id, consultant_name, isCompleted, service, app_service_tbl_id, app_service_id, prepared_by, updated_by_name, created_by, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1')");
            $stmt->execute([$svcInfo['hospital_no'], $svcInfo['app_no'], $notes, $consultant_id, $consultant_name, $is_completed, $svcInfo['item_services'], $svcInfo['drug_sn'], $app_service_tbl_id, $preparedByVal, $_SESSION['fullname'], $created_by, $setdate]);
        }

        if ($is_completed === 1) {
            $upd = $db->prepare("UPDATE patient_ap_services SET drug_status = 1, dsp_by = ? WHERE sn = ?");
            $upd->execute([$_SESSION['fullname'], $app_service_tbl_id]);
        }

        echo json_encode(['status' => 200, 'message' => 'Medical service notes updated successfully!']);
        exit;
    }

    if ($action === 'pay_service_procedure') {
        if (!$canHandleBilling) {
            echo json_encode(['status' => 403, 'message' => 'Staff does not have billing/accounts rights to process payment.']);
            exit;
        }

        $service_sn = intval($_POST['service_sn']);
        $pay_mode = cleanInput($_POST['pay_mode']); // 'Cash' or 'Wallet'
        $hosp_no = cleanInput($_POST['hosp_no']);

        $stmt = $db->prepare("SELECT pay, hosp_price, paystatus FROM patient_ap_services WHERE sn = ? LIMIT 1");
        $stmt->execute([$service_sn]);
        $svc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$svc) {
            echo json_encode(['status' => 404, 'message' => 'Record not found.']);
            exit;
        }

        if ($svc['paystatus'] == 1) {
            echo json_encode(['status' => 400, 'message' => 'Service is already marked as paid.']);
            exit;
        }

        $amount = floatval($svc['pay'] > 0 ? $svc['pay'] : $svc['hosp_price']);

        if ($pay_mode === 'Wallet') {
            // Check patient balance
            $balStmt = $db->prepare("SELECT sum(amount) as balance FROM patient_wallet WHERE hospital_no = ?");
            $balStmt->execute([$hosp_no]);
            $bal = $balStmt->fetchColumn();

            if ($bal < $amount) {
                echo json_encode(['status' => 400, 'message' => "Insufficient wallet balance (Current: $bal). Required: $amount"]);
                exit;
            }

            $db->beginTransaction();
            // Deduct from wallet
            $wStmt = $db->prepare("INSERT INTO patient_wallet (hospital_no, amount, description, date_entry, created_by) VALUES (?, ?, 'Payment for procedure/service', NOW(), ?)");
            $wStmt->execute([$hosp_no, -$amount, $_SESSION['fullname']]);

            // Update service status
            $uStmt = $db->prepare("UPDATE patient_ap_services SET paystatus = 1, pay_mode = 'Wallet', drug_status = 1, dsp_by = ? WHERE sn = ?");
            $uStmt->execute([$_SESSION['fullname'], $service_sn]);

            $db->commit();
        } else {
            // Mark Cash / Paid
            $uStmt = $db->prepare("UPDATE patient_ap_services SET paystatus = 1, pay_mode = 'Cash', drug_status = 1, dsp_by = ? WHERE sn = ?");
            $uStmt->execute([$_SESSION['fullname'], $service_sn]);
        }

        echo json_encode(['status' => 200, 'message' => 'Payment processed successfully!']);
        exit;
    }

    echo json_encode(['status' => 400, 'message' => 'Invalid action requested.']);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 500, 'message' => 'Error: ' . $e->getMessage()]);
}
