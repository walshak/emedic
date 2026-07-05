<?php include("../Connections/Conn.php"); ?>

<?php

$response_main = array(
    'status' => '0',
    'name' => '',
    'wallet' => '0',
    'message' => ''
);

if (isset($_POST["cancl_sn"])) {
    $sn = $_POST["cancl_sn"];

    try {
        $one = 0;
        $null = null;
        $stmt = $db->prepare('UPDATE patient_ap_services 
            SET invoice_status = :invoice_status, 
                paystatus = :paystatus, 
                invoice_by = :invoice_by, 
                invoice_date = :invoice_date,
                transact_date = :transact_date
            WHERE sn = :sn AND process_claim = 0 AND drug_status = 0 AND paystatus = 1');

        $stmt->bindParam(':invoice_status', $one);
        $stmt->bindParam(':paystatus', $one);
        $stmt->bindParam(':invoice_by', $null);
        $stmt->bindParam(':invoice_date', $null);
        $stmt->bindParam(':transact_date', $null);
        $stmt->bindParam(':sn', $sn);

        // Execute the statement
        if ($stmt->execute()) {
            // Check the number of affected rows
            if ($stmt->rowCount() > 0) {
                $response_main['status'] = 1; // Indicate success
                $response_main['message'] = 'Canceled Successfully.';
            } else {
                $response_main['status'] = 0; // Indicate no rows updated
                $response_main['message'] = 'No records were updated. The conditions may not have been met.';
            }
        } else {
            $response_main['status'] = 0; // Indicate failure
            $response_main['message'] = 'Update failed. Please try again.';
        }

        // Return the response as JSON
        echo json_encode($response_main);
        exit;
    } catch (PDOException $e) {
        $response_main['status'] = 0; // Indicate error
        $response_main['message'] = 'Error: ' . $e->getMessage();
        echo json_encode($response_main);
        exit;
    }
}
?>