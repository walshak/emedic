<?php
include("../Connections/Conn.php");

// Fetch detailed entries for a service
if (isset($_GET['med_service_detail_name'])) {
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
    $query = "
            SELECT
            hospital_no,
            app_no,
            consultant_name,
            notes,
            isCompleted,
            created_at
            FROM
            notes_services
            WHERE
            service = :service AND
            DATE(created_at) BETWEEN :start AND :end
            ORDER BY
            created_at DESC
            ";
    $result = $db->prepare($query);
    // print_r($result);

    $result->execute([':service' => $_GET['med_service_detail_name'], ':start' => $startDate, ':end' => $endDate]);
    $entries = $result->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($entries);
    exit;
}

if (isset($_GET['specialist'])) {

    $specialist = $_GET['specialist'];
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    $query = "
        SELECT 
            appt_no, 
            checkin_by,
            referal_doc,
            patient_name, 
            DATE(date_ap) AS date_ap, 
            status 
        FROM 
            apptm 
        WHERE 
            services_name = :specialist AND 
            DATE(date_ap) BETWEEN :start AND :end 
        ORDER BY 
            date_ap DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute([':specialist' => $specialist, ':start' => $startDate, ':end' => $endDate]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($details);
    exit;
}

if (isset($_GET['discharge_status'])) {
    $status = isset($_GET['discharge_status']) ? $_GET['discharge_status'] : '';
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    $stmt = $db->prepare("
                SELECT hospital_no, 
                    app_no, 
                    date_admit,
                    doc_incharge, 
                    discharge_name,
                    date_discharge, 
                    discharge_note 
                FROM admission 
                WHERE discharge_status = :status 
                AND DATE(date_discharge) BETWEEN :start AND :end
            ");
    $stmt->execute([':status' => $status, ':start' => $startDate, ':end' => $endDate]);
    // print_r($stmt);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if (isset($_GET['conclusion_status'])) {
    $status = $_GET['conclusion_status'];
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    $stmt = $db->prepare("
                SELECT 
                    appt_no, 
                    patient_name, 
                    checkin_by,
                    referal_doc, 
                    app_by, 
                    date_ap, 
                    ap_time 
                FROM apptm 
                WHERE status = :status AND date_ap BETWEEN :start AND :end
            ");
    $stmt->execute([':status' => $status, ':start' => $startDate, ':end' => $endDate]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
}

if (isset($_GET['admit_status'])) {
    $status = isset($_GET['admit_status']) ? $_GET['admit_status'] : '';
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    if ($status != 'Death') {
        $sql = "SELECT hospital_no, 
                    app_no, 
                    date_admit, 
                    doc_incharge, 
                    discharge_name,
                    date_discharge, 
                    discharge_note 
                FROM admission 
                WHERE adm_status = '4'
                AND DATE(date_discharge) BETWEEN :start AND :end";

        if ($status == "All Admissions") {
            $sql .= " AND (admit_type = 'admit_p' OR admit_type = 'admit_o') ";
        } else if ($status == 'Ward Admissions') {
            $sql .= " AND admit_type = 'admit_p' ";
        } else if ($status == 'A & E Admissions') {
            $sql .= " AND admit_type = 'admit_o' ";
        }
    } else {
        $sql = "SELECT hospital_no, 
                    app_no, 
                    date_admit, 
                    doc_incharge, 
                    discharge_name,
                    date_discharge, 
                    discharge_note 
                FROM admission 
                WHERE adm_status = '4'
                AND discharge_status = 'Death'
                AND DATE(date_discharge) BETWEEN :start AND :end";
    }
    $stmt = $db->prepare($sql);
    $stmt->execute([':start' => $startDate, ':end' => $endDate]);
    // print_r($stmt);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
