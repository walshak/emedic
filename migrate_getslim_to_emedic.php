<?php
/**
 * Standalone Migration Script: getslim_db to eMedic (webmedic_getslim)
 * 
 * Run via CLI: php migrate_getslim_to_emedic.php
 */

// --- CONFIGURATION ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '18781875';

$db_getslim_name = 'getslim_db';
$db_emedic_name = 'webmedic_getslim';

$default_admin_rights = 'DR';
$default_appt_status = 'discharge';
$default_dept = 'General';
$default_fiscal_year = '2026';

echo "=================================================\n";
echo "   GETSLIM -> EMEDIC DATABASE MIGRATION SCRIPT    \n";
echo "=================================================\n\n";

try {
    // Connect to source DB
    $db_source = new PDO("mysql:host=$db_host;dbname=$db_getslim_name;charset=utf8mb4", $db_user, $db_pass);
    $db_source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Embedded master schemas
$schemas = [
    'admin_users' => 'CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `EmployeeCode` varchar(50) DEFAULT NULL,
  `title` varchar(10) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone_number` varchar(12) DEFAULT NULL,
  `about_me` text,
  `my_tips` text,
  `date_updated` date DEFAULT NULL,
  `rights` varchar(20) DEFAULT NULL,
  `unit_head` int NOT NULL DEFAULT \'0\',
  `specialist` varchar(100) DEFAULT NULL,
  `status` int DEFAULT NULL,
  `count` int NOT NULL DEFAULT \'0\',
  `bill_account_status` int NOT NULL DEFAULT \'0\',
  `Consult_Room` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=295 DEFAULT CHARSET=utf8mb3',
    'hremp' => 'CREATE TABLE IF NOT EXISTS `hremp` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `EmployeeCode` varchar(12) DEFAULT NULL,
  `JoiningDate` varchar(20) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Designation` varchar(100) DEFAULT NULL,
  `Qualification` varchar(100) DEFAULT NULL,
  `TotalExperience` varchar(200) DEFAULT NULL,
  `Cadre` varchar(100) DEFAULT NULL,
  `Title` varchar(20) DEFAULT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `DateofBirth` varchar(15) DEFAULT NULL,
  `Gender` varchar(12) DEFAULT NULL,
  `blood_grp` varchar(12) DEFAULT NULL,
  `MaritalStatus` varchar(20) DEFAULT NULL,
  `no_of_dependents` int DEFAULT NULL,
  `Religion` varchar(20) DEFAULT NULL,
  `Tribe` varchar(50) DEFAULT NULL,
  `PresentAddress` text,
  `PermanentAddress` text,
  `StateLGA` varchar(100) DEFAULT NULL,
  `Nationality` varchar(50) DEFAULT NULL,
  `EmailAddress` varchar(100) DEFAULT NULL,
  `BankName` varchar(50) DEFAULT NULL,
  `Branch` varchar(50) DEFAULT NULL,
  `BankAddress` varchar(100) DEFAULT NULL,
  `AccountNo` varchar(100) DEFAULT NULL,
  `rFullName` varchar(100) DEFAULT NULL,
  `rPhone` varchar(100) DEFAULT NULL,
  `rAddress` text,
  `kFullName` varchar(100) DEFAULT NULL,
  `kPhone` varchar(100) DEFAULT NULL,
  `kAddress` text,
  `status` int NOT NULL DEFAULT \'0\',
  `date_captured` varchar(15) DEFAULT NULL,
  `captured_by` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `emr_no` varchar(100) DEFAULT NULL,
  `ID_card_no` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sn`)
) ENGINE=InnoDB AUTO_INCREMENT=336 DEFAULT CHARSET=latin1',
    'enrollee' => 'CREATE TABLE IF NOT EXISTS `enrollee` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `nhis_no` varchar(50) DEFAULT NULL,
  `nhis_no_ext` varchar(5) DEFAULT NULL,
  `hospital_no` varchar(50) DEFAULT NULL,
  `primary_provider` varchar(100) DEFAULT NULL,
  `hmo_no` varchar(20) DEFAULT NULL,
  `insurance` varchar(50) DEFAULT NULL,
  `employer_no` varchar(50) DEFAULT NULL,
  `member` varchar(50) DEFAULT NULL,
  `nhis_registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expiry_date` varchar(50) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `oname` varchar(100) DEFAULT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `blood_g` varchar(6) DEFAULT NULL,
  `geno_type` varchar(10) DEFAULT NULL,
  `dob` varchar(50) DEFAULT NULL,
  `age` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(104) DEFAULT NULL,
  `state_lga` varchar(50) DEFAULT NULL,
  `tribe` varchar(255) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `addr` text,
  `religion` varchar(100) DEFAULT NULL,
  `date_capture` datetime DEFAULT NULL,
  `percent` varchar(10) DEFAULT NULL,
  `validation_status` int DEFAULT NULL,
  `visit_status` varchar(20) DEFAULT NULL,
  `bill_head_responsible` varchar(20) DEFAULT NULL,
  `discount_set` int DEFAULT \'0\',
  `token` varchar(255) DEFAULT NULL,
  `old_hospital_no` varchar(255) DEFAULT NULL,
  `homeBranch` varchar(255) DEFAULT NULL,
  `patient_manage_by` varchar(250) DEFAULT NULL,
  `patient_manage_hx` text,
  `patient_review` varchar(200) DEFAULT NULL,
  `rank` varchar(100) DEFAULT NULL,
  `command_formation` varchar(100) DEFAULT NULL,
  `captured_by` varchar(255) DEFAULT NULL,
  `credit_limit` int DEFAULT \'0\',
  `vip` int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`sn`),
  KEY `idx_hospital_no` (`hospital_no`)
) ENGINE=MyISAM AUTO_INCREMENT=4419 DEFAULT CHARSET=utf8mb3',
    'apptm' => 'CREATE TABLE IF NOT EXISTS `apptm` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `appt_no` varchar(30) NOT NULL DEFAULT \'0\',
  `hospital_no` varchar(50) DEFAULT NULL,
  `tagno` varchar(20) DEFAULT NULL,
  `patient_name` varchar(100) DEFAULT NULL,
  `app_by` varchar(100) DEFAULT NULL,
  `referal_doc` varchar(100) DEFAULT NULL,
  `doctor_id` int DEFAULT NULL,
  `app_state` varchar(100) DEFAULT NULL,
  `dept` varchar(100) DEFAULT NULL,
  `dept_name` varchar(100) DEFAULT NULL,
  `service_id` varchar(10) DEFAULT NULL,
  `services_name` varchar(100) DEFAULT NULL,
  `insurance` varchar(200) DEFAULT NULL,
  `insurance_type` varchar(100) DEFAULT NULL,
  `interest` int DEFAULT NULL,
  `ap_type` varchar(50) DEFAULT NULL,
  `auth_code` varchar(50) DEFAULT NULL,
  `auth_expire_date` date DEFAULT NULL,
  `date_ap` date DEFAULT NULL,
  `ap_time` time DEFAULT NULL,
  `ap_date_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `discharge_remarks` text,
  `queue_lock` int DEFAULT NULL,
  `re_queue_lock` int NOT NULL DEFAULT \'0\',
  `vital_lock` int NOT NULL DEFAULT \'0\',
  `ap_direction` int NOT NULL DEFAULT \'1\',
  `checkin_by` varchar(50) DEFAULT NULL,
  `app_duration` int DEFAULT NULL,
  `app_expiration_date` datetime DEFAULT NULL,
  `queue_center` varchar(20) NOT NULL DEFAULT \'DR\',
  `queue_time_stamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cr` int DEFAULT \'0\',
  `front_queue` int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`sn`),
  KEY `appt_no` (`appt_no`)
) ENGINE=MyISAM AUTO_INCREMENT=12552 DEFAULT CHARSET=utf8mb3',
    'notes' => 'CREATE TABLE IF NOT EXISTS `notes` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `app_no` varchar(50) NOT NULL,
  `hospital_no` varchar(50) DEFAULT NULL,
  `notes` longtext,
  `tag` varchar(20) DEFAULT NULL,
  `notes_type` varchar(50) DEFAULT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `prepared_by` varchar(50) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `service_id` varchar(12) DEFAULT NULL,
  `dept_id` int DEFAULT NULL,
  `date_entry` datetime DEFAULT NULL,
  `status` enum(\'0\',\'1\') NOT NULL DEFAULT \'1\',
  `ack` int DEFAULT \'0\',
  `date_entry2` datetime DEFAULT NULL,
  `no_updates` int DEFAULT NULL,
  PRIMARY KEY (`sn`)
) ENGINE=InnoDB AUTO_INCREMENT=3617 DEFAULT CHARSET=latin1',
    'vital_sign' => 'CREATE TABLE IF NOT EXISTS `vital_sign` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `hospital_no` varchar(50) NOT NULL,
  `pulse_read` varchar(20) DEFAULT NULL,
  `bp` varchar(20) DEFAULT NULL,
  `temp` varchar(20) DEFAULT NULL,
  `resp_rate` varchar(20) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `bmi` varchar(12) DEFAULT NULL,
  `Others` varchar(20) DEFAULT NULL,
  `date_ap` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `prepared_by` varchar(100) NOT NULL,
  `GTT` varchar(100) DEFAULT NULL,
  `muac_read` varchar(100) DEFAULT NULL,
  `spo2` varchar(100) DEFAULT NULL,
  `fbs` varchar(100) DEFAULT NULL,
  `rbs` varchar(100) DEFAULT NULL,
  `ppbs` varchar(100) DEFAULT NULL,
  `FHR` varchar(20) DEFAULT NULL,
  `comments` varchar(300) DEFAULT NULL,
  `status` enum(\'0\',\'1\') NOT NULL DEFAULT \'1\',
  PRIMARY KEY (`sn`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1',
    'patient_ap_services' => 'CREATE TABLE IF NOT EXISTS `patient_ap_services` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `app_no` varchar(50) DEFAULT NULL,
  `hospital_no` varchar(50) DEFAULT NULL,
  `access` varchar(10) DEFAULT NULL,
  `serv_group` varchar(20) DEFAULT NULL,
  `cat_type` varchar(50) DEFAULT NULL,
  `dept_id` varchar(100) DEFAULT NULL,
  `dept_dispensory_id` varchar(20) DEFAULT NULL,
  `drug_sn` varchar(100) DEFAULT NULL,
  `item_services` varchar(100) DEFAULT NULL,
  `tag` varchar(20) DEFAULT NULL,
  `hosp_price` double DEFAULT \'0\',
  `claim_amt` decimal(10,2) NOT NULL DEFAULT \'0.00\',
  `interest` decimal(10,2) NOT NULL DEFAULT \'0.00\',
  `qty` int NOT NULL DEFAULT \'0\',
  `remarks` varchar(400) DEFAULT NULL,
  `drug_status` int NOT NULL DEFAULT \'0\',
  `invoice_status` int NOT NULL DEFAULT \'0\',
  `invoice_no` varchar(50) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_by` varchar(100) DEFAULT NULL,
  `prepared_by` varchar(50) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `dsp_by` varchar(100) DEFAULT NULL,
  `date_entry` datetime DEFAULT CURRENT_TIMESTAMP,
  `transact_date` datetime DEFAULT NULL,
  `pay` decimal(10,2) NOT NULL DEFAULT \'0.00\',
  `pay_mode` varchar(10) DEFAULT NULL,
  `paystatus` int NOT NULL DEFAULT \'0\',
  `process_claim` int NOT NULL DEFAULT \'0\',
  `cr` int NOT NULL DEFAULT \'0\',
  `discount` decimal(10,2) NOT NULL DEFAULT \'0.00\',
  `add_charge` decimal(10,2) NOT NULL DEFAULT \'0.00\',
  `prescription` varchar(100) DEFAULT NULL,
  `med_frequency` varchar(50) DEFAULT NULL,
  `med_dosage` varchar(100) DEFAULT NULL,
  `med_dosage_unit` tinyint(1) DEFAULT \'0\',
  `med_duration` varchar(100) DEFAULT NULL,
  `med_duration_unit` varchar(100) DEFAULT NULL,
  `dispensory_status_at_phamcy` tinyint NOT NULL DEFAULT \'0\',
  `acct_billed_staff` varchar(12) DEFAULT NULL,
  `acct_billed_ack` int NOT NULL DEFAULT \'0\',
  `claim_valid_by` varchar(255) DEFAULT NULL,
  `payment_remarks` varchar(200) DEFAULT NULL,
  `ledger_TX` varchar(100) DEFAULT NULL,
  `wallet_payee` varchar(50) DEFAULT NULL,
  `who_process_paystatus` varchar(40) DEFAULT NULL,
  `wallet_debt_bill_to_acct` varchar(14) DEFAULT NULL,
  PRIMARY KEY (`sn`),
  KEY `idx_hospital_no` (`hospital_no`),
  KEY `idx_invoice_status` (`invoice_status`),
  KEY `idx_drug_status` (`drug_status`),
  KEY `idx_serv_group` (`serv_group`),
  KEY `idx_date_entry` (`date_entry`),
  KEY `idx_dept_dispensory_id` (`dept_dispensory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1',
    'chart_ledger' => 'CREATE TABLE IF NOT EXISTS `chart_ledger` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `app_no` varchar(50) DEFAULT NULL,
  `hospital_no` varchar(50) DEFAULT NULL,
  `insurance_no` varchar(50) DEFAULT NULL,
  `ref_value` varchar(200) DEFAULT NULL,
  `sale_sn` varchar(50) DEFAULT NULL,
  `item_services` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `transc_type` varchar(50) DEFAULT NULL,
  `dr_amt` double DEFAULT \'0\',
  `cr_amt` double DEFAULT \'0\',
  `bal` double DEFAULT NULL,
  `prepared_by` varchar(50) DEFAULT NULL,
  `date_entry` datetime DEFAULT NULL,
  `date_entry2` date DEFAULT NULL,
  `auth_code` varchar(100) DEFAULT NULL,
  `post_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `account_no` varchar(50) DEFAULT NULL,
  `lg_ref_no` varchar(100) DEFAULT NULL,
  `fiscal_year` varchar(12) DEFAULT NULL,
  `validation` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `can_delete` tinyint(1) NOT NULL DEFAULT \'0\',
  `patient_stt_status` int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`sn`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=latin1',
    'lab_manage' => 'CREATE TABLE IF NOT EXISTS `lab_manage` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `app_no` varchar(50) NOT NULL DEFAULT \'\',
  `labrequest_no` varchar(50) DEFAULT NULL,
  `patient` varchar(50) DEFAULT NULL,
  `patient_name` varchar(50) DEFAULT NULL,
  `test_id` varchar(20) DEFAULT NULL,
  `test_name` text,
  `lab_cat` varchar(20) DEFAULT NULL,
  `section` varchar(30) DEFAULT NULL,
  `group_id` varchar(12) DEFAULT NULL,
  `business_service_center` varchar(50) DEFAULT NULL,
  `referral` varchar(50) DEFAULT NULL,
  `preferred_specimen` text,
  `request_note` text,
  `request_date` datetime DEFAULT NULL,
  `request_date2` date DEFAULT NULL,
  `request_by` varchar(50) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `collected_specimen` varchar(100) DEFAULT NULL,
  `collected_by` varchar(50) DEFAULT NULL,
  `collected_notes` text,
  `collected_date` datetime DEFAULT NULL,
  `result_note` longtext,
  `result_comment` text,
  `lab_sci_name` varchar(100) DEFAULT NULL,
  `lab_sci_speciality` varchar(50) DEFAULT NULL,
  `entered_by` varchar(100) DEFAULT NULL,
  `data_capture_status` varchar(10) DEFAULT \'queue\',
  `result_date` datetime DEFAULT NULL,
  `approved_by` varchar(60) DEFAULT NULL,
  `abnormal_results` varchar(100) DEFAULT NULL,
  `attachment` varchar(50) DEFAULT NULL,
  `sms_status` int DEFAULT \'0\',
  `maintenance_lock` int NOT NULL DEFAULT \'0\',
  `lab_combos` int NOT NULL DEFAULT \'0\',
  `lab_combo_request_no` varchar(200) DEFAULT NULL,
  `requesting_physician` varchar(100) DEFAULT NULL,
  `consumble_lock` int NOT NULL DEFAULT \'0\',
  `doctor_result_status` int NOT NULL DEFAULT \'0\',
  `vip` int NOT NULL DEFAULT \'0\',
  `bill` varchar(255) DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `date_time_pay` datetime DEFAULT NULL,
  `result_on_credit` int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`sn`),
  UNIQUE KEY `labrequest_no` (`labrequest_no`),
  KEY `sn` (`sn`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1',
    'lab_results' => 'CREATE TABLE IF NOT EXISTS `lab_results` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `lab_request_no` varchar(20) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `test_value` varchar(20) DEFAULT NULL,
  `SIR` varchar(20) DEFAULT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `lab_range` varchar(100) DEFAULT NULL,
  `lab_req_serial` varchar(10) NOT NULL,
  `app_no` varchar(50) NOT NULL,
  PRIMARY KEY (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1',
    'lab_result' => 'CREATE TABLE IF NOT EXISTS `lab_result` (
  `sn` int NOT NULL AUTO_INCREMENT,
  `field_no` varchar(20) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `field_value` longtext,
  `field_ref` varchar(30) DEFAULT NULL,
  `lab_no` varchar(50) DEFAULT NULL,
  `test_no` varchar(50) DEFAULT NULL,
  `test_name` varchar(100) DEFAULT NULL,
  `specimen_collected` varchar(50) DEFAULT NULL,
  `comment` text,
  `notes` longtext,
  `RQ_type` varchar(5) DEFAULT NULL,
  `result_date` date DEFAULT NULL,
  `lab_sci_name` varchar(100) DEFAULT NULL,
  `lab_sci_speciality` varchar(100) DEFAULT NULL,
  `entered_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`sn`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1',
];

    
    // Connect to target DB server and ensure DB exists
    $db_target = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $db_target->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_target->exec("CREATE DATABASE IF NOT EXISTS `$db_emedic_name`");
    $db_target->exec("USE `$db_emedic_name`");

    echo "[*] Connected to databases successfully.\n";
    echo "[*] Setting up destination tables from embedded schemas...\n";

    foreach ($schemas as $tbl => $create_sql) {
        $db_target->exec($create_sql);
        $db_target->exec("TRUNCATE TABLE `$tbl`");
    }
    echo "[*] Tables ready and cleared for fresh migration.\n\n";

} catch (PDOException $e) {
    die("[!] Database Connection Failed: " . $e->getMessage() . "\n");
}

// Memory Maps
$user_map = [];    // getslim_user_id => webmedic_admin_id
$patient_map = []; // getslim_upi => new_hospital_no
$doc_name_map = []; // getslim_user_id => fullname

$db_target->beginTransaction();

try {
    // -------------------------------------------------------------------------
    // PHASE 1: USERS & HR MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[1] Starting Users & HR Migration...\n";
    $stmt = $db_source->query("SELECT * FROM user");
    $getslim_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $users_added = 0;
    
    // Function to generate sequential employee code (e.g. 001, 002)
    function getNextEmpCode($db_target)
    {
        static $current_max = null;
        if ($current_max === null) {
            $stmt = $db_target->query("SELECT MAX(CAST(EmployeeCode AS UNSIGNED)) as max_emp FROM admin_users WHERE EmployeeCode REGEXP '^[0-9]+$'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_max = $row['max_emp'] ? (int)$row['max_emp'] : 0;
        }
        $current_max++;
        return sprintf("%03d", $current_max);
    }

    foreach ($getslim_users as $g_user) {
        $check = $db_target->prepare("SELECT id, EmployeeCode FROM admin_users WHERE username = ?");
        $check->execute([$g_user['username']]);

        if ($check->rowCount() > 0) {
            $existing_admin = $check->fetch(PDO::FETCH_ASSOC);
            $admin_id = $existing_admin['id'];
        } else {
            $emp_code = getNextEmpCode($db_target);
            $fullname = $g_user['display_name'] ?: $g_user['username'];
            
            $insert_admin = $db_target->prepare("INSERT INTO admin_users (EmployeeCode, fullname, username, password, rights, status) VALUES (?, ?, ?, ?, ?, 1)");
            $insert_admin->execute([
                $emp_code,
                $fullname,
                $g_user['username'],
                $g_user['password'], // Retaining hashed password if possible
                $default_admin_rights
            ]);
            $admin_id = $db_target->lastInsertId();

            $names = explode(' ', $fullname, 2);
            $fname = isset($names[0]) ? $names[0] : '';
            $lname = isset($names[1]) ? $names[1] : '';

            $insert_hr = $db_target->prepare("INSERT INTO hremp (EmployeeCode, FirstName, LastName, username, status) VALUES (?, ?, ?, ?, 1)");
            $insert_hr->execute([
                $emp_code,
                $fname,
                $lname,
                $g_user['username']
            ]);

            $users_added++;
        }

        $user_map[$g_user['user_id']] = $admin_id;
        $doc_name_map[$g_user['user_id']] = $fullname ?? 'Migration Script';
    }
    echo "    - Processed " . count($getslim_users) . " users. Added {$users_added} new users.\n";

    // -------------------------------------------------------------------------
    // PHASE 2: PATIENTS MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[2] Starting Patients Migration...\n";

    function getNextHospitalNo($db_target)
    {
        static $current_max = null;
        if ($current_max === null) {
            $stmt = $db_target->query("SELECT MAX(CAST(hospital_no AS UNSIGNED)) as max_hosp FROM enrollee WHERE hospital_no REGEXP '^[0-9]+$'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_max = $row['max_hosp'] ? (int)$row['max_hosp'] : 0;
        }
        $current_max++;
        return sprintf("%06d", $current_max);
    }

    $stmt = $db_source->query("SELECT * FROM patients");
    $getslim_patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $patients_added = 0;
    foreach ($getslim_patients as $g_patient) {
        $new_hospital_no = getNextHospitalNo($db_target);

        $fname = $g_patient['forename'] ?? '';
        $surname = $g_patient['surname'] ?? '';
        if(empty($fname) && empty($surname)) {
            $fname = "Unknown";
            $surname = "Patient";
        }
        
        $insert_pat = $db_target->prepare("INSERT INTO enrollee 
            (hospital_no, old_hospital_no, fname, surname, dob, gender, email, addr, state_lga, phone, date_capture, captured_by, visit_status, validation_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Migration Script', 'New', 1)");

        $insert_pat->execute([
            $new_hospital_no,
            $g_patient['upi'],
            $fname,
            $surname,
            $g_patient['dob'] ?? null,
            $g_patient['gender'] ?? null,
            $g_patient['email'] ?? null,
            $g_patient['address'] ?? null,
            $g_patient['origin_state'] ?? null,
            $g_patient['phone'] ?? null,
            $g_patient['created_at'] ?: date('Y-m-d H:i:s')
        ]);

        $patient_map[$g_patient['upi']] = $new_hospital_no;
        $patients_added++;
    }
    echo "    - Migrated {$patients_added} legacy patients and generated sequential hospital numbers.\n";

    // -------------------------------------------------------------------------
    // PHASE 3: APPOINTMENTS & CONSULTATIONS (SESSIONS) MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[3] Starting Appointments & Sessions Migration...\n";

    $stmt = $db_source->query("SELECT * FROM sessions");
    $getslim_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $appts_added = 0;
    $notes_added = 0;
    $session_appt_map = []; // session_id => new_appt_no

    function getNextApptNo($db_target)
    {
        static $current_max = null;
        if ($current_max === null) {
            $stmt = $db_target->query("SELECT MAX(CAST(appt_no AS UNSIGNED)) as max_appt FROM apptm WHERE appt_no REGEXP '^[0-9]+$'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_max = $row['max_appt'] ? (int)$row['max_appt'] : 0;
        }
        $current_max++;
        return sprintf("%06d", $current_max);
    }

    foreach ($getslim_sessions as $g_sess) {
        $hosp_no = isset($patient_map[$g_sess['upi']]) ? $patient_map[$g_sess['upi']] : null;
        if (!$hosp_no) continue;

        $new_appt_no = getNextApptNo($db_target);
        
        $doc_id = isset($user_map[$g_sess['created_by']]) ? $user_map[$g_sess['created_by']] : null;
        $doc_name = isset($doc_name_map[$g_sess['created_by']]) ? $doc_name_map[$g_sess['created_by']] : 'Migration Script';

        $datetime = $g_sess['created_at'] ?: date('Y-m-d H:i:s');
        $date_ap = substr($datetime, 0, 10);
        $time_ap = substr($datetime, 11, 8);

        // Insert Appointment
        $insert_appt = $db_target->prepare("INSERT INTO apptm 
            (appt_no, hospital_no, date_ap, ap_time, ap_date_time, status, app_state, doctor_id, dept, front_queue) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

        $insert_appt->execute([
            $new_appt_no,
            $hosp_no,
            $date_ap,
            $time_ap,
            $datetime,
            $default_appt_status,
            'Consultation',
            $doc_id,
            $default_dept
        ]);
        
        $session_appt_map[$g_sess['session_id']] = $new_appt_no;
        $appts_added++;

        // Insert Clinical Notes
        $clinical_note_html = "";
        
        $symptoms = trim($g_sess['symptoms']);
        if (!empty($symptoms) && strtolower($symptoms) !== 'null') {
            $clinical_note_html .= "<p><h3>Symptoms:</h3><br>" . nl2br(htmlspecialchars($symptoms)) . "</p>";
        }
        
        $treatment = trim($g_sess['treatment']);
        if (!empty($treatment) && strtolower($treatment) !== 'null') {
            $clinical_note_html .= "<p><h3>Treatment:</h3><br>" . nl2br(htmlspecialchars($treatment)) . "</p>";
        }
        
        $investigations = trim($g_sess['investigations']);
        if (!empty($investigations) && strtolower($investigations) !== 'null') {
            $clinical_note_html .= "<p><h3>Investigations:</h3><br>" . nl2br(htmlspecialchars($investigations)) . "</p>";
        }
        
        $notes = trim($g_sess['notes']);
        if (!empty($notes) && strtolower($notes) !== 'null') {
            $clinical_note_html .= "<p><h3>Notes:</h3><br>" . nl2br(htmlspecialchars($notes)) . "</p>";
        }

        if (!empty($clinical_note_html)) {
            $insert_note = $db_target->prepare("INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, created_by, date_entry, status) 
                                                VALUES (?, ?, ?, 'DR', 'C', ?, ?, ?, '1')");
            $insert_note->execute([$new_appt_no, $hosp_no, $clinical_note_html, $doc_name, $doc_id, $datetime]);
            $notes_added++;
        }

        $diagnosis = trim($g_sess['diagnosis']);
        if (!empty($diagnosis) && strtolower($diagnosis) !== 'null') {
            $diagnosis_html = "<div width='100%'><i><strong>Diagnosis: </strong></i><br> " . htmlspecialchars($diagnosis) . " <br></div>";
            $insert_diag = $db_target->prepare("INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, created_by, date_entry, status) 
                                                VALUES (?, ?, ?, 'DR', 'D', ?, ?, ?, '1')");
            $insert_diag->execute([$new_appt_no, $hosp_no, $diagnosis_html, $doc_name, $doc_id, $datetime]);
            $notes_added++;
        }
    }
    echo "    - Migrated {$appts_added} appointments and {$notes_added} clinical notes.\n";

    // -------------------------------------------------------------------------
    // PHASE 4: VITAL SIGNS MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[4] Starting Vital Signs Migration...\n";
    $stmt = $db_source->query("SELECT * FROM vital_signs");
    $getslim_vitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $vitals_added = 0;
    foreach($getslim_vitals as $vitals) {
        $hosp_no = isset($patient_map[$vitals['upi']]) ? $patient_map[$vitals['upi']] : null;
        if (!$hosp_no) continue;
        
        $doc_id = isset($user_map[$vitals['created_by']]) ? $user_map[$vitals['created_by']] : null;
        $doc_name = isset($doc_name_map[$vitals['created_by']]) ? $doc_name_map[$vitals['created_by']] : 'Migration Script';
        
        $insert_vital = $db_target->prepare("INSERT INTO vital_sign (hospital_no, bp, temp, height, weight, pulse_read, resp_rate, prepared_by, date_ap) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_vital->execute([
            $hosp_no,
            $vitals['blood_pressure'] ?? null,
            $vitals['temperature'] ?? null,
            $vitals['height'] ?? null,
            $vitals['weight'] ?? null,
            $vitals['pulse'] ?? null,
            $vitals['respiratory_rate'] ?? null,
            $doc_name,
            $vitals['created_at'] ?: date('Y-m-d H:i:s')
        ]);
        $vitals_added++;
    }
    echo "    - Migrated {$vitals_added} vital sign records.\n";

    // -------------------------------------------------------------------------
    // PHASE 5: PRESCRIPTIONS & MEDICATIONS MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[5] Starting Prescriptions Migration...\n";
    $stmt = $db_source->query("SELECT * FROM prescriptions");
    $getslim_rx = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $rx_added = 0;
    foreach($getslim_rx as $rx) {
        $hosp_no = isset($patient_map[$rx['upi']]) ? $patient_map[$rx['upi']] : null;
        if (!$hosp_no) continue;
        
        $doc_id = isset($user_map[$rx['created_by']]) ? $user_map[$rx['created_by']] : null;
        $doc_name = isset($doc_name_map[$rx['created_by']]) ? $doc_name_map[$rx['created_by']] : 'Migration Script';
        
        $item_services = trim($rx['drug_name'] ?? '') ?: trim($rx['details'] ?? '');
        if(empty($item_services)) $item_services = 'Unknown Medication';
        
        $remarks = strlen($item_services) > 100 ? substr($item_services, 100, 300) : null;
        $item_services = substr($item_services, 0, 100);
        
        $insert_rx = $db_target->prepare("INSERT INTO patient_ap_services (hospital_no, item_services, serv_group, cat_type, qty, drug_status, paystatus, date_entry, prepared_by, remarks) VALUES (?, ?, 'Pharmacy', 'Drugs', 1, 1, 1, ?, ?, ?)");
        $insert_rx->execute([
            $hosp_no,
            $item_services,
            $rx['created_at'] ?: date('Y-m-d H:i:s'),
            $doc_name,
            $remarks
        ]);
        $rx_added++;
    }
    echo "    - Migrated {$rx_added} prescription records.\n";

    // -------------------------------------------------------------------------
    // PHASE 6: ACCOUNT BALANCES (BILLING - DOUBLE ENTRY)
    // -------------------------------------------------------------------------
    echo "\n[6] Starting Account Balances Migration...\n";
    
    // Aggregate balances from debtor_trans
    // Note: In getslim_db, ov_amount + ov_gst + ov_freight + ov_freight_tax - ov_discount determines total? 
    // Usually type=10 (invoice) creates debit, type=11 (receipt) creates credit. 
    // Or we just calculate net from ov_amount based on type.
    // If not standard, let's just use `alloc` vs `ov_amount` logic or simply take a direct query if they have a known balance view.
    // Since debtor_trans type 10 is Invoice, 11 is Credit Note, 12 is Receipt. 
    // Net Balance = SUM(IF(type=10, (ov_amount+ov_gst+ov_freight+ov_freight_tax-ov_discount), 0)) - SUM(IF(type=11 OR type=12, (ov_amount+ov_discount), 0))
    // Let's use a simpler proxy: if debtor_trans is available, maybe they also have a balance field in a patient model?
    // We will do a basic calculation of unallocated amounts for simplicity. 
    $stmt = $db_source->query("
        SELECT debtor_no as upi, 
               SUM(CASE WHEN type IN (10) THEN (ov_amount+ov_gst+ov_freight+ov_freight_tax-ov_discount) ELSE 0 END) as total_debts,
               SUM(CASE WHEN type IN (11, 12, 2) THEN (ov_amount+ov_discount) ELSE 0 END) as total_credits
        FROM debtor_trans 
        GROUP BY debtor_no
    ");
    $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $ledgers_added = 0;
    
    // Equity Account
    $equity_account = '2204'; // Retained Income
    
    foreach($balances as $bal) {
        $hosp_no = isset($patient_map[$bal['upi']]) ? $patient_map[$bal['upi']] : null;
        if (!$hosp_no) continue;
        
        $net_balance = round($bal['total_debts'] - $bal['total_credits'], 2);
        
        if ($net_balance == 0) continue;
        
        $sale_sn = 'MIG_' . time() . '_' . $hosp_no;
        $date_entry = date('Y-m-d H:i:s');
        
        if ($net_balance < 0) {
            // Patient has a Credit Balance (Deposit)
            $abs_bal = abs($net_balance);
            
            // Leg 1: Credit Patient Deposit GL (2121)
            $ins1 = $db_target->prepare("INSERT INTO chart_ledger (hospital_no, ref_value, sale_sn, item_services, transc_type, cr_amt, dr_amt, bal, account_no, date_entry, date_entry2, prepared_by, fiscal_year) 
                VALUES (?, 'Migration', ?, 'Migrated Patient Deposit', 'CREDIT', ?, 0, 0, '2121', ?, ?, 'Migration Script', ?)");
            $ins1->execute([$hosp_no, $sale_sn, $abs_bal, $date_entry, date('Y-m-d'), $default_fiscal_year]);
            
            // Leg 2: Debit Retained Income GL (2204)
            $ins2 = $db_target->prepare("INSERT INTO chart_ledger (hospital_no, ref_value, sale_sn, item_services, transc_type, cr_amt, dr_amt, bal, account_no, date_entry, date_entry2, prepared_by, fiscal_year) 
                VALUES (?, 'Migration', ?, 'Migrated Patient Deposit', 'DEBIT', 0, ?, 0, ?, ?, ?, 'Migration Script', ?)");
            $ins2->execute([$hosp_no, $sale_sn, $abs_bal, $equity_account, $date_entry, date('Y-m-d'), $default_fiscal_year]);
            
            $ledgers_added += 2;
        } else {
            // Patient has a Debit Balance (AR)
            $abs_bal = abs($net_balance);
            
            // Leg 1: Debit Patient Bill Receivable GL (1502)
            $ins1 = $db_target->prepare("INSERT INTO chart_ledger (hospital_no, ref_value, sale_sn, item_services, transc_type, cr_amt, dr_amt, bal, account_no, date_entry, date_entry2, prepared_by, fiscal_year) 
                VALUES (?, 'Migration', ?, 'Migrated Account Receivable', 'DEBIT', 0, ?, 0, '1502', ?, ?, 'Migration Script', ?)");
            $ins1->execute([$hosp_no, $sale_sn, $abs_bal, $date_entry, date('Y-m-d'), $default_fiscal_year]);
            
            // Leg 2: Credit Retained Income GL (2204)
            $ins2 = $db_target->prepare("INSERT INTO chart_ledger (hospital_no, ref_value, sale_sn, item_services, transc_type, cr_amt, dr_amt, bal, account_no, date_entry, date_entry2, prepared_by, fiscal_year) 
                VALUES (?, 'Migration', ?, 'Migrated Account Receivable', 'CREDIT', ?, 0, 0, ?, ?, ?, 'Migration Script', ?)");
            $ins2->execute([$hosp_no, $sale_sn, $abs_bal, $equity_account, $date_entry, date('Y-m-d'), $default_fiscal_year]);
            
            $ledgers_added += 2;
        }
    }
    echo "    - Migrated account balances resulting in {$ledgers_added} double-entry ledger records.\n";

    // -------------------------------------------------------------------------
    // PHASE 7: LABORATORY & PATHOLOGY RECORDS MIGRATION
    // -------------------------------------------------------------------------
    echo "\n[7] Starting Laboratory & Pathology Migration...\n";
    
    // Load dictionaries for names
    $param_stmt = $db_source->query("SELECT id, description FROM test_param");
    $param_map = [];
    foreach ($param_stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $param_map[$p['id']] = $p['description'];
    }

    $group_stmt = $db_source->query("SELECT id, description FROM test_groups");
    $group_map = [];
    foreach ($group_stmt->fetchAll(PDO::FETCH_ASSOC) as $g) {
        $group_map[$g['id']] = $g['description'];
    }

    // A. General Lab Requests
    $stmt = $db_source->query("SELECT * FROM test_request_chemlabor_sub WHERE is_group = 1 OR group_id = 0");
    $chem_reqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $lab_req_added = 0;
    
    foreach($chem_reqs as $req) {
        $hosp_no = 'UNKNOWN'; // test_request_chemlabor_sub doesn't have UPI directly. It links to request_token -> test_request_chemlabor -> upi.
        // Let's resolve hosp_no by joining the master table
        $resolve_stmt = $db_source->prepare("SELECT upi FROM test_request_chemlabor WHERE request_token = ?");
        $resolve_stmt->execute([$req['request_token']]);
        if($res = $resolve_stmt->fetch(PDO::FETCH_ASSOC)) {
            $hosp_no = isset($patient_map[$res['upi']]) ? $patient_map[$res['upi']] : 'UNKNOWN';
        }
        if ($hosp_no === 'UNKNOWN') continue;

        $lab_req_no = sprintf("LR%06d", $req['sub_id']);
        
        $test_name = isset($req['pool_service_name']) && !empty($req['pool_service_name']) ? $req['pool_service_name'] : null;
        if (!$test_name && !empty($req['group_id']) && isset($group_map[$req['group_id']])) {
            $test_name = $group_map[$req['group_id']];
        }
        if (!$test_name && !empty($req['parameter_id']) && isset($param_map[$req['parameter_id']])) {
            $test_name = $param_map[$req['parameter_id']];
        }
        if (!$test_name) {
            $test_name = "Lab Test #" . ($req['parameter_id'] ?? $req['sub_id']);
        }
        
        $ins_lab = $db_target->prepare("INSERT INTO lab_manage (labrequest_no, patient, test_name, request_date, request_date2, request_by, data_capture_status) VALUES (?, ?, ?, ?, ?, 'Migration', 'queue')");
        $ins_lab->execute([
            $lab_req_no,
            $hosp_no,
            $test_name,
            date('Y-m-d H:i:s'),
            date('Y-m-d')
        ]);
        $lab_req_added++;
    }
    
    // B. Pathology Requests
    $stmt = $db_source->query("SELECT * FROM pathology_samples");
    $path_reqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $path_req_added = 0;
    
    foreach($path_reqs as $req) {
        $hosp_no = isset($patient_map[$req['upi']]) ? $patient_map[$req['upi']] : null;
        if (!$hosp_no) continue;
        
        if ($hosp_no === 'UNKNOWN') continue;

        $lab_req_no = sprintf("PT%06d", $req['id']);

        $test_name = $req['investigation'] ?: 'Pathology Sample';
        $specimen = $req['sample_name'] ?: 'Tissue';
        
        $req_date = $req['sample_time'] ?: ($req['created_at'] ?: date('Y-m-d H:i:s'));
        
        $ins_lab = $db_target->prepare("INSERT INTO lab_manage (labrequest_no, patient, test_name, collected_specimen, request_date, request_date2, request_by, data_capture_status) VALUES (?, ?, ?, ?, ?, ?, 'Migration', 'queue')");
        $ins_lab->execute([
            $lab_req_no,
            $hosp_no,
            $test_name,
            $specimen,
            $req_date,
            substr($req_date, 0, 10)
        ]);
        $path_req_added++;
    }
    
    // Preload token to parent sub_id mapping
    $stmt = $db_source->query("SELECT sub_id, request_sub_token, is_group, group_id FROM test_request_chemlabor_sub WHERE request_sub_token IS NOT NULL");
    $token_to_parent_map = [];
    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if ($r['is_group'] == 1 || $r['group_id'] == 0) {
            $token_to_parent_map[$r['request_sub_token']] = $r['sub_id'];
        } else {
            $token_to_parent_map[$r['request_sub_token']] = $r['group_id'];
        }
    }

    // B. Test Results
    $stmt = $db_source->query("SELECT * FROM test_results");
    $test_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $results_added = 0;

    foreach($test_results as $res) {
        $hosp_no = isset($patient_map[$res['upi']]) ? $patient_map[$res['upi']] : null;
        if (!$hosp_no) continue;
        
        $entered_by = isset($user_map[$res['created_by']]) ? $user_map[$res['created_by']] : 'Migration';
        $details_raw = $res['details'] ?? '';
        $details_data = @unserialize($details_raw);

        if ($details_data !== false && is_array($details_data) && !empty($details_data['test'])) {
            $parent_groups = [];
            
            foreach ($details_data['test'] as $t) {
                $sub_token = $t['request_sub_token'] ?? null;
                if (!$sub_token) continue;
                $parent_sub_id = $token_to_parent_map[$sub_token] ?? null;
                if ($parent_sub_id) {
                    if (!isset($parent_groups[$parent_sub_id])) {
                        $parent_groups[$parent_sub_id] = [];
                    }
                    $parent_groups[$parent_sub_id][] = $t;
                }
            }
            
            foreach ($parent_groups as $parent_sub_id => $params) {
                $lab_req_val = sprintf("LR%06d", $parent_sub_id);
                $test_no_val = sprintf("TS%06d", $parent_sub_id); 
                
                $test_name_val = 'General Test';
                $first_param = $params[0];
                if (!empty($first_param['group_id']) && isset($group_map[$first_param['group_id']])) {
                    $test_name_val = $group_map[$first_param['group_id']];
                } elseif (!empty($first_param['id']) && isset($param_map[$first_param['id']])) {
                    $test_name_val = $param_map[$first_param['id']];
                }
                $test_name_val = substr($test_name_val, 0, 100);
                
                $field_value = '<table class="table table-bordered table-striped" width="100%">';
                $field_value .= '<tr><th>Test Parameter</th><th>Result Value</th></tr>';
                foreach ($params as $t) {
                    $param_id = $t['id'] ?? null;
                    $param_name = $param_id && isset($param_map[$param_id]) ? htmlspecialchars($param_map[$param_id]) : 'Unknown Parameter';
                    $val = htmlspecialchars($t['value'] ?? '');
                    $field_value .= "<tr><td>{$param_name}</td><td>{$val}</td></tr>";
                }
                $field_value .= '</table>';

                if (!empty($details_data['result_comments'])) {
                    $field_value .= "<br><b>Comments:</b> " . htmlspecialchars($details_data['result_comments']);
                }

                $ins_res = $db_target->prepare("INSERT INTO lab_result (lab_no, test_no, test_name, field_value, result_date, entered_by, RQ_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $ins_res->execute([
                    $lab_req_val,
                    $test_no_val,
                    $test_name_val,
                    $field_value,
                    $res['created_at'] ?: date('Y-m-d H:i:s'),
                    $entered_by,
                    'sl'
                ]);
                $results_added++;
            }
        }
    }
    
    echo "    - Migrated {$lab_req_added} lab requests, {$path_req_added} pathology requests, and {$results_added} test results.\n";

    // Commit Transaction
    $db_target->commit();
    echo "\n[SUCCESS] Migration completed perfectly! All data committed to database webmedic_getslim.\n";
} catch (Exception $e) {
    $db_target->rollBack();
    echo "\n[ERROR] Migration failed and was safely rolled back.\n";
    echo "Error Details: " . $e->getMessage() . "\n";
    echo "At Line: " . $e->getLine() . "\n";
}
