<?php
session_start();
include("../Connections/Conn.php");

try {

    $now = date('Y-m-d H:i:s');

    $params = array();

    // =========================
    // DOCTOR FILTER LOGIC
    // =========================
    if (isset($_GET['c']) && base64_decode($_GET['c']) === 'other_doc') {

        $SQL_STRING = " (app_by != ? AND dept = ?) ";
        $params[] = $_SESSION['username'];
        $params[] = $_SESSION['dept_id'];
    } elseif (!empty($_SESSION['specialist'])) {

        $SQL_STRING = " (app_by = ? OR app_by = ? OR doctor_id = ?) ";
        $params[] = $_SESSION['username'];
        $params[] = $_SESSION['specialist'];
        $params[] = $_SESSION['id'];
    } else {

        $SQL_STRING = " (app_by = ? OR referal_doc = ? OR doctor_id = ?) ";
        $params[] = $_SESSION['username'];
        $params[] = $_SESSION['username'];
        $params[] = $_SESSION['id'];
    }

    // =========================
    // MAIN QUERY
    // =========================
    $sql = "
        SELECT *
        FROM apptm
        WHERE app_expiration_date >= ?
        AND $SQL_STRING
        AND status NOT IN ('discharge','cancelled')
        AND (queue_lock = 0 OR re_queue_lock = 0)
        AND queue_time_stamp BETWEEN DATE_SUB(?, INTERVAL 12 HOUR) AND ?
        ORDER BY sn DESC
    ";

    $finalParams = array_merge(
        array($now),   // first ?
        $params,       // doctor filters
        array($now, $now) // BETWEEN
    );

    $stmt = $db->prepare($sql);
    $stmt->execute($finalParams);

    // =========================
    // OUTPUT TABLE ROWS
    // =========================
    $output = '';
    $sn = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $appointment_number = $row['appt_no'];

        // =========================
        // CHECK PAYMENT STATUS
        // =========================
        $payStmt = $db->prepare("
            SELECT serv_group, paystatus 
            FROM patient_ap_services 
            WHERE app_no = ? AND serv_group = 'Consultation'
        ");
        $payStmt->execute(array($appointment_number));

        $payData = $payStmt->fetch(PDO::FETCH_ASSOC);

        // PHP 5 SAFE (NO ??)
        $servGroup = isset($payData['serv_group']) ? $payData['serv_group'] : null;
        $paystatus = isset($payData['paystatus']) ? $payData['paystatus'] : 0;

        $hasPaid = ($paystatus > 0);
        $disabled = $hasPaid ? "" : "disabled";
        $title = $hasPaid ? "View Patient" : "Un-Paid";
        $Color = $hasPaid ? "success" : "danger";

        // Allow credit consultation
        if ($title == "Un-Paid" && isset($row['cr']) && $row['cr'] > 0) {
            $disabled = "";
            $title = "Consult On-Credit";
        }

        // Only Consultation rows
        if ($servGroup == 'Consultation') {

            $sn++;

            $ap_date_time = $row['ap_date_time'];

            // Format date safely
            $timeAgo = date('d M H:i', strtotime($ap_date_time));

            // Optional: highlight long waiting patients
            $waitingMinutes = (time() - strtotime($ap_date_time)) / 60;
            $rowStyle = ($waitingMinutes > 30) ? 'style="background:#ffe6e6;"' : '';

            $output .= '
                <tr ' . $rowStyle . '>
                    <td>' . $sn . '</td>
                    <td>' . $row['hospital_no'] . '</td>
                    <td>' . $row['patient_name'] . '</td>
                    <td>' . $row['services_name'] . '</td>
                    <td>' . $timeAgo . '</td>
                    <td class="text-center">
                        <a href="patient.php?hosp_no=' . $row['hospital_no'] . '&app=' . $row['appt_no'] . '"
                           class="btn btn-' . $Color . ' btn-sm" ' . $disabled . '>
                           ' . $title . '
                        </a>
                    </td>
                </tr>
            ';
        }
    }

    // =========================
    // EMPTY STATE
    // =========================
    if ($sn == 0) {
        $output = '
            <tr>
                <td colspan="6" class="text-center text-muted">
                    No patients in queue
                </td>
            </tr>
        ';
    }

    echo $output;
} catch (PDOException $e) {

    echo '
        <tr>
            <td colspan="6" class="text-danger text-center">
                Error: ' . $e->getMessage() . '
            </td>
        </tr>
    ';
}
