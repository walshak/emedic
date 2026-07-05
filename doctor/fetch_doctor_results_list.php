<?php
session_start();
include("../Connections/Conn.php");

try {

    $doctor_id = $_SESSION['id'];

    $query = "
        SELECT patient_name, test_name, result_date, patient
        FROM lab_manage
        WHERE data_capture_status = 'approve'
        AND created_by = :doctor_id
        AND result_date >= NOW() - INTERVAL 20 MINUTE
        ORDER BY result_date DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->execute();

    $output = '';
    $n = 1;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {


        $date = !empty($row['result_date'])
            ? date('d-m-Y h:i:s A', strtotime($row['result_date']))
            : '-';


        $output .= '
        <tr>
            <td>' . $n++ . '</td>
            <td>' . $row['patient_name'] . '</td>
            <td>' . $row['test_name'] . '</td>
            <td>' . $date . '</td>
            <td>
                <a href="patient.php?hosp_no=' . $row['patient'] . '" class="btn btn-sm btn-primary">
                    View
                </a>
            </td>
        </tr>';
    }

    echo $output;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
