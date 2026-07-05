<?php
session_start();
include("../Connections/Conn.php");

try {

    $section = isset($_SESSION['section']) ? $_SESSION['section'] : '';

    $query = "
        SELECT 
            lm.patient AS hospital_no,
            lm.test_name,
            lm.section,
            lm.request_date,
            lm.request_by,
            admission.hospital_no AS admitted_patient

        FROM lab_manage lm

        LEFT JOIN admission 
            ON lm.patient = admission.hospital_no 
            AND admission.adm_status = 3

        WHERE lm.section = ?
        AND lm.data_capture_status = 'queue'
        AND (
            (admission.hospital_no IS NULL 
                AND lm.request_date >= NOW() - INTERVAL 20 MINUTE)
            OR
            (admission.hospital_no IS NOT NULL 
                AND lm.request_date >= NOW() - INTERVAL 1 DAY)
        )

        ORDER BY lm.request_date DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute(array($section));

    $output = '';
    $sn = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $sn++;

        $hospital_no = isset($row['hospital_no']) ? $row['hospital_no'] : '';
        $test_name   = isset($row['test_name']) ? $row['test_name'] : '';
        $section     = isset($row['section']) ? $row['section'] : '';
        $request_by  = isset($row['request_by']) ? $row['request_by'] : '';
        $request_date = isset($row['request_date']) ? $row['request_date'] : '';
        $isAdmitted  = isset($row['admitted_patient']) ? $row['admitted_patient'] : null;

        // Detect type
        if ($isAdmitted) {
            $badge = '<span class="badge badge-danger">🏥 Inpatient</span>';
        } else {
            $badge = '<span class="badge badge-info">🚶 Outpatient</span>';
        }

        // Format time
        $formatted_time = date('d M Y h:i A', strtotime($request_date));

        // Highlight recent
        $minutesAgo = (time() - strtotime($request_date)) / 60;
        $rowStyle = ($minutesAgo <= 5) ? 'style="background:#e6f7ff;"' : '';

        $output .= '
        <tr ' . $rowStyle . '>
            <td>' . $sn . '</td>
            <td>' . $hospital_no . '</td>
            <td>' . $test_name . '</td>
            <td>' . $badge . '</td>
            <td>' . $request_by . '</td>
            <td>' . $formatted_time . '</td>
            <td>
                <a href="mgt.php?hosp_no=' . $hospital_no . '" 
                   class="btn btn-success btn-sm">
                   Open
                </a>
            </td>
        </tr>';
    }

    if ($sn == 0) {
        $output = '
        <tr>
            <td colspan="8" class="text-center text-muted">
                No new lab/radiology requests
            </td>
        </tr>';
    }

    echo $output;
} catch (PDOException $e) {
    echo '
    <tr>
        <td colspan="8" class="text-danger text-center">
            Error: ' . $e->getMessage() . '
        </td>
    </tr>';
}
