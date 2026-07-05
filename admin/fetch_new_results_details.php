<?php
session_start();
include("../Connections/Conn.php");

try {

    $query = "
        SELECT patient_name, test_name, lab_sci_name, result_date 
        FROM lab_manage 
        WHERE data_capture_status = 'approve' 
        AND result_date >= NOW() - INTERVAL 6 HOUR
        ORDER BY result_date DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $output = '';
    $n = 1;

    if ($stmt->rowCount() > 0) {

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // Safe output
            $patient = htmlspecialchars($row['patient_name']);
            $test = htmlspecialchars($row['test_name']);
            $lab_sci_name = htmlspecialchars($row['lab_sci_name']);

            // Safe date formatting
            $date = !empty($row['result_date'])
                ? date('d-m-Y h:i:s A', strtotime($row['result_date']))
                : '-';

            $output .= '
                <tr>
                    <td>' . $n++ . '</td>
                    <td>' . $patient . '</td>
                    <td>' . $test . '</td>
                    <td>' . $lab_sci_name . '</td>
                    <td>' . $date . '</td>
                </tr>
            ';
        }
    } else {

        $output .= '
            <tr>
                <td colspan="5" class="text-center text-danger">
                    No new test results found
                </td>
            </tr>
        ';
    }

    echo $output;
} catch (PDOException $e) {

    echo '
        <tr>
            <td colspan="5" class="text-danger">
                Error: ' . $e->getMessage() . '
            </td>
        </tr>
    ';
}
