<?php

/**
 * Standalone Migration Script: Gastro to eMedic (webmedic)
 * 
 * Run via CLI: php migrate_gastro_to_emedic.php
 */

// --- CONFIGURATION ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '18781875';

$db_gastro_name = 'gastro';
$db_emedic_name = 'webmedic_gastro';

$default_admin_rights = 'DR';
$default_appt_status = 'discharge';
$default_dept = 'General';

echo "=================================================\n";
echo "   GASTRO -> EMEDIC DATABASE MIGRATION SCRIPT    \n";
echo "=================================================\n\n";

try {
    // Connect to Gastro DB
    $db_gastro = new PDO("mysql:host=$db_host;dbname=$db_gastro_name;charset=utf8mb4", $db_user, $db_pass);
    $db_gastro->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connect to eMedic DB
    $db_emedic = new PDO("mysql:host=$db_host;dbname=$db_emedic_name;charset=utf8mb4", $db_user, $db_pass);
    $db_emedic->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "[*] Connected to both databases successfully.\n";
} catch (PDOException $e) {
    die("[!] Database Connection Failed: " . $e->getMessage() . "\n");
}

// Memory Maps
$user_map = [];    // gastro_user_id => webmedic_admin_id
$patient_map = []; // gastro_patient_id => new_hospital_no

$db_emedic->beginTransaction();

try {
    // -------------------------------------------------------------------------
    // PHASE 1: USERS & HR MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[1] Starting Users & HR Migration...\n";
    $stmt = $db_gastro->query("SELECT * FROM users");
    $gastro_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users_added = 0;
    foreach ($gastro_users as $g_user) {
        // Check if user already exists in webmedic.admin_users by username
        $check = $db_emedic->prepare("SELECT id FROM admin_users WHERE username = ?");
        $check->execute([$g_user['UserName']]);

        if ($check->rowCount() > 0) {
            $existing_admin = $check->fetch(PDO::FETCH_ASSOC);
            $admin_id = $existing_admin['id'];
        } else {
            // Create in admin_users
            $emp_code = "GST-EMP-" . $g_user['id'];
            $insert_admin = $db_emedic->prepare("INSERT INTO admin_users (EmployeeCode, fullname, username, password, rights, status) VALUES (?, ?, ?, ?, ?, 1)");
            $insert_admin->execute([
                $emp_code,
                $g_user['FullName'],
                $g_user['UserName'],
                md5('staff123'),
                $default_admin_rights
            ]);
            $admin_id = $db_emedic->lastInsertId();

            // Create in hremp
            $names = explode(' ', $g_user['FullName'], 2);
            $fname = isset($names[0]) ? $names[0] : '';
            $lname = isset($names[1]) ? $names[1] : '';

            $insert_hr = $db_emedic->prepare("INSERT INTO hremp (EmployeeCode, FirstName, LastName, username, status) VALUES (?, ?, ?, ?, 1)");
            $insert_hr->execute([
                $emp_code,
                $fname,
                $lname,
                $g_user['UserName']
            ]);

            $users_added++;
        }

        // Save mapping
        $user_map[$g_user['id']] = $admin_id;
    }
    echo "    - Processed " . count($gastro_users) . " users. Added {$users_added} new users.\n";

    // -------------------------------------------------------------------------
    // PHASE 2: PATIENTS MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[2] Starting Patients Migration...\n";

    // Function to generate robust sequential hospital number
    function getNextHospitalNo($db_emedic)
    {
        static $current_max = null;
        if ($current_max === null) {
            $stmt = $db_emedic->query("SELECT MAX(CAST(hospital_no AS UNSIGNED)) as max_hosp FROM enrollee WHERE hospital_no REGEXP '^[0-9]+$'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_max = $row['max_hosp'] ? (int)$row['max_hosp'] : 0;
        }
        $current_max++;
        return sprintf("%06d", $current_max);
    }

    $stmt = $db_gastro->query("SELECT * FROM in_patients");
    $gastro_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $patients_added = 0;
    foreach ($gastro_patients as $g_patient) {
        $new_hospital_no = getNextHospitalNo($db_emedic);

        $insert_pat = $db_emedic->prepare("INSERT INTO enrollee 
            (hospital_no, old_hospital_no, fname, surname, dob, gender, email, addr, state_lga, phone, date_capture, captured_by, visit_status, validation_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Migration Script', 'New', 1)");

        $insert_pat->execute([
            $new_hospital_no,
            $g_patient['HospitalNumber'],
            $g_patient['FirstName'],
            $g_patient['LastName'],
            $g_patient['dob'],
            $g_patient['Sex'],
            $g_patient['Email'],
            $g_patient['Address'],
            $g_patient['State'],
            $g_patient['PhoneNumber'],
            $g_patient['created_at'] ?: date('Y-m-d H:i:s')
        ]);

        $patient_map[$g_patient['id']] = $new_hospital_no;
        $patients_added++;
    }
    echo "    - Migrated {$patients_added} legacy patients and generated sequential hospital numbers.\n";

    // -------------------------------------------------------------------------
    // PHASE 3: APPOINTMENTS MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[3] Starting Appointments Migration...\n";

    $stmt = $db_gastro->query("SELECT * FROM appointments");
    $gastro_appts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $appts_added = 0;
    $appt_map = []; // gastro_appt_id => new_appt_no

    // Function to generate robust sequential appointment number
    function getNextApptNo($db_emedic)
    {
        static $current_max = null;
        if ($current_max === null) {
            $stmt = $db_emedic->query("SELECT MAX(CAST(appt_no AS UNSIGNED)) as max_appt FROM apptm WHERE appt_no REGEXP '^[0-9]+$'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_max = $row['max_appt'] ? (int)$row['max_appt'] : 0;
        }
        $current_max++;
        return sprintf("%06d", $current_max);
    }

    foreach ($gastro_appts as $g_appt) {
        $new_appt_no = getNextApptNo($db_emedic);
        $hosp_no = isset($patient_map[$g_appt['PatientID']]) ? $patient_map[$g_appt['PatientID']] : null;
        $doc_id = isset($user_map[$g_appt['UserID']]) ? $user_map[$g_appt['UserID']] : null;

        if (!$hosp_no) continue; // Skip if patient mapping failed (orphaned record)

        $date_ap = substr($g_appt['begin'], 0, 10);
        $time_ap = substr($g_appt['begin'], 11, 8);

        $insert_appt = $db_emedic->prepare("INSERT INTO apptm 
            (appt_no, hospital_no, date_ap, ap_time, ap_date_time, status, app_state, doctor_id, dept, front_queue) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

        $insert_appt->execute([
            $new_appt_no,
            $hosp_no,
            $date_ap,
            $time_ap,
            $g_appt['begin'],
            $default_appt_status,
            'Consultation',
            $doc_id,
            $default_dept
        ]);

        $appt_map[$g_appt['id']] = [
            'appt_no' => $new_appt_no,
            'hospital_no' => $hosp_no,
            'doctor_id' => $doc_id
        ];
        $appts_added++;
    }
    echo "    - Migrated {$appts_added} appointments.\n";

    // -------------------------------------------------------------------------
    // PHASE 4: CONSULTATIONS & NOTES MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[4] Starting Consultations & Notes Migration...\n";

    $stmt = $db_gastro->query("SELECT * FROM consultations");
    $gastro_consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $notes_added = 0;

    foreach ($gastro_consultations as $g_cons) {
        $mapped_appt = isset($appt_map[$g_cons['Ap_ID']]) ? $appt_map[$g_cons['Ap_ID']] : null;
        if (!$mapped_appt) continue; // Skip orphaned

        $appt_no = $mapped_appt['appt_no'];
        $hosp_no = $mapped_appt['hospital_no'];
        $doc_id = $mapped_appt['doctor_id'];

        // Fetch doctor name for 'prepared_by'
        $doc_name = 'Migration Script';
        if ($doc_id) {
            $dn = $db_emedic->query("SELECT fullname FROM admin_users WHERE id = " . (int)$doc_id)->fetchColumn();
            if ($dn) $doc_name = $dn;
        }

        $date_entry = $g_cons['created_at'] ?: date('Y-m-d H:i:s');

        // 1. Consolidate Clinical Note (History, Physical Exam, Plan)
        $clinical_note_html = "";
        
        $history = trim($g_cons['History']);
        if (!empty($history) && strtolower($history) !== 'null') {
            $clinical_note_html .= "<p><h3>History:</h3><br>" . nl2br(htmlspecialchars($history)) . "</p>";
        }
        
        $physical = trim($g_cons['PysicalExam']);
        if (!empty($physical) && strtolower($physical) !== 'null') {
            $clinical_note_html .= "<p><h3>Physical Exam:</h3><br>" . nl2br(htmlspecialchars($physical)) . "</p>";
        }
        
        $plan = trim($g_cons['Plan']);
        if (!empty($plan) && strtolower($plan) !== 'null') {
            $clinical_note_html .= "<p><h3>Plan:</h3><br>" . nl2br(htmlspecialchars($plan)) . "</p>";
        }

        if (!empty($clinical_note_html)) {
            $insert_note = $db_emedic->prepare("INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, created_by, date_entry, status) 
                                                VALUES (?, ?, ?, 'DR', 'C', ?, ?, ?, '1')");
            $insert_note->execute([$appt_no, $hosp_no, $clinical_note_html, $doc_name, $doc_id, $date_entry]);
            $notes_added++;
        }

        // 2. Extract Diagnosis into separate note (notes_type 'D')
        $diagnosis = trim($g_cons['Diagnosis']);
        if (!empty($diagnosis) && strtolower($diagnosis) !== 'null') {
            $diagnosis_html = "<div width='100%'><i><strong>Diagnosis: </strong></i><br> " . htmlspecialchars($diagnosis) . " <br></div>";
            $insert_diag = $db_emedic->prepare("INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, created_by, date_entry, status) 
                                                VALUES (?, ?, ?, 'DR', 'D', ?, ?, ?, '1')");
            $insert_diag->execute([$appt_no, $hosp_no, $diagnosis_html, $doc_name, $doc_id, $date_entry]);
            $notes_added++;
        }
    }
    echo "    - Migrated and consolidated {$notes_added} clinical note fragments.\n";

    // Commit Transaction
    $db_emedic->commit();
    echo "\n[SUCCESS] Migration completed perfectly! All data committed to database.\n";
} catch (Exception $e) {
    $db_emedic->rollBack();
    echo "\n[ERROR] Migration failed and was safely rolled back.\n";
    echo "Error Details: " . $e->getMessage() . "\n";
    echo "At Line: " . $e->getLine() . "\n";
}
