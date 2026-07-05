<?php
session_start();
include("../Connections/Conn.php");

try {

    $query = "
        SELECT 
            hospital_no,
            MAX(date_entry) AS latest_time,
            prepared_by,
            COUNT(*) AS total_requests
        FROM patient_ap_services
        WHERE invoice_status = 0
        AND process_claim = 0
        AND paystatus = 0
        AND pay_mode = 'Claim'
        AND date_entry >= NOW() - INTERVAL 2 DAY
        GROUP BY hospital_no
        ORDER BY latest_time DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $output = '';
    $sn = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $sn++;
        $output .= '
        <tr>
            <td>' . $sn . '</td>
            <td>' . $row['hospital_no'] . '</td>
            <td>' . $row['latest_time'] . '</td>
            <td>' . $row['prepared_by'] . '</td>
            <td>' . $row['total_requests'] . '</td>
            <td>
                <a href="index.php?claims=' . $row['hospital_no'] . '" 
                   class="btn btn-primary btn-sm">
                   View
                </a>
            </td>
        </tr>';
    }

    if ($sn == 0) {
        $output = '<tr><td colspan="6" class="text-center">No new prescriptions</td></tr>';
    }

    echo $output;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
