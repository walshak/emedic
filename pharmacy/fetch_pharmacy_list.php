<?php
session_start();
include("../Connections/Conn.php");

try {

    $query = "
        SELECT 
            hospital_no,
            MAX(date_entry) AS latest_time,
            prepared_by,
            COUNT(*) AS total_drugs
        FROM patient_ap_services
        WHERE serv_group = 'Pharmacy'
        AND invoice_status = 0
        AND date_entry >= NOW() - INTERVAL 30 MINUTE
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
            <td>' . $row['total_drugs'] . '</td>
            <td>
                <a href="index.php?presc&hos_no=' . $row['hospital_no'] . '" 
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
