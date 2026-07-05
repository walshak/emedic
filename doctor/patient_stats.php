<?php
// patient_stats.php
header('Content-Type: application/json');
include("../Connections/Conn.php");
session_start();

if (isset($_POST['hospital_no'], $_POST['appt_no'], $_POST['patient_manage_by'])) {
    $hospital_no = $_POST['hospital_no'];
    $appointment_number = $_POST['appt_no'];
    $patient_manage_by = $_POST['patient_manage_by'];
    $mgt_by = 'Manage Patient';
    $doctor_id = $_SESSION['id'];
    $current_id = $_SESSION['id'];
    $current_fullname = $_SESSION['fullname'];
    $fullname = '';
    $manage_status = 0;
    $patient_manage_hx = '';

    try {
        // ----------------- Visits + Last Visit -----------------
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) AS total_visits,
                MAX(ap_date_time) AS last_visit
            FROM apptm
            WHERE hospital_no = :hospital_no
            AND appt_no != :appt_no
        ");
        $stmt->execute([
            ':hospital_no' => $hospital_no,
            ':appt_no'     => $appointment_number
        ]);
        $visit_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // ----------------- Admissions -----------------
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total_admissions
            FROM admission
            WHERE hospital_no = :hospital_no
            AND adm_status = '4'
        ");
        $stmt->execute([':hospital_no' => $hospital_no]);
        $admission_counter = $stmt->fetchColumn();

        // ----------------- Manage By Logic -----------------
        if (is_numeric($patient_manage_by)) {
            $stmt = $db->prepare("SELECT fullname FROM admin_users WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $patient_manage_by, PDO::PARAM_INT);
            $stmt->execute();
            $rw = $stmt->fetch(PDO::FETCH_ASSOC);
            $fullname = $rw ? $rw['fullname'] : $patient_manage_by;
        } else {
            $fullname = $patient_manage_by; // fallback if stored as text
        }

        // Case 1: current user manages
        if ($patient_manage_by == $current_id || $patient_manage_by == $current_fullname) {
            $mgt_by = 'Edit';
            $manage_status = 2;
            $doctor_id = ''; // current user manages
        }
        // Case 2: someone else manages
        elseif (!empty($patient_manage_by)) {
            $mgt_by = 'Change';
            $manage_status = 1;
            $doctor_id = $current_id;
        }

        // History
        if ($manage_status == 1 || $manage_status == 2) {
            if ($patient_manage_by != $current_id) {
                $patient_manage_hx .= "<strong>Previous Manager: </strong>" . htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8') . '<br>';
            }
        }


        if (!empty($visit_data['last_visit'])) {
            $visit_data_ =   date("jS M Y h:i a", strtotime($visit_data['last_visit']));
        } else {
            $visit_data_ = null;
        }

        $admission_ = null;
        if (strtoupper($_SESSION['h_code']) !== 'ZMKC') {
            $stmt = $db->prepare("SELECT 1 
                          FROM diagnosis 
                          WHERE item = 'Birth injury to femur (ICD10: P132)' 
                            AND description = '1' 
                          LIMIT 1");
            $stmt->execute();
            if ($stmt->fetchColumn()) {
                $admission_ = 1; // admission disabled flag
            }
        }
        // ----------------- JSON Response -----------------
        echo json_encode([
            'success' => true,
            'visits' => $visit_data['total_visits'],
            'last_visit' => $visit_data_,
            'admissions' => $admission_counter,
            'admission_status' => $admission_,
            'manage_by' => [
                'fullname' => $fullname,
                'mgt_by' => $mgt_by,
                'manage_status' => $manage_status,
                'doctor_id' => $doctor_id,
                'history' => $patient_manage_hx
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
}
