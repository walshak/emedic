<?php

if (isset($_POST['inv'])) {
    // Check if 'inv' is set and is an array

    $ledger_TX = 1;
    $sql = $db->prepare("INSERT INTO patient_ap_bill_group (ledger_id) VALUES (:ledger_id)");
    $sql->bindParam(':ledger_id', $ledger_TX, PDO::PARAM_STR);

    // Execute the statement and check if it was successful
    if ($sql->execute()) {
        // Get the last inserted ID
        $last_id = $db->lastInsertId();

        // Check if last_id is greater than zero
        if ($last_id > 0) {
            $last_id = str_pad($last_id, 3, "0", STR_PAD_LEFT);


            if ($last_id != '') {
                if (isset($_POST['inv']) && is_array($_POST['inv'])) {
                    $selectedRequests = $_POST['inv'];

                    // Loop through each selected request
                    foreach ($selectedRequests as $request) {

                        $updateSQL = "UPDATE lab_manage SET bill = :bill WHERE labrequest_no = :labrequest_no AND bill is null";
                        $stmt_update = $db->prepare($updateSQL);

                        // Bind parameters
                        $stmt_update->bindParam(':bill', $last_id, PDO::PARAM_STR);
                        $stmt_update->bindParam(':labrequest_no', $request, PDO::PARAM_STR);
                        $stmt_update->execute();
                    }

                    // Optionally, you can return a summary response
                    echo 'Total requests processed: ' . count($selectedRequests);
                } else {
                    echo 'No Item checkboxes selected.';
                }
            } else {
                echo 'Empty Lab Number';
            }
        } else {
            echo "Insertion failed, no ID returned.";
        }
    } else {
        // Handle the error if the execution failed
        echo "Insertion failed: " . implode(", ", $sql->errorInfo());
    }
} else {
    echo 'Invalid request method.';
}
