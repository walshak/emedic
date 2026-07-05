<?php
session_start();
require_once('../Connections/Conn.php');


if (isset($_POST["acknowled_procedure"])) {

    $consultant_id = $_SESSION['id'];
    $staff_id = $consultant_id;
    $what_to = "procedure";
    $current_date = date('Y-m-d');


    try {
        // Check if the staff_id exists with the given what_to
        $sql = "SELECT * FROM acknowledge WHERE staff_id = :staff_id AND what_to = :what_to";
        $stmt = $db->prepare($sql);
        $stmt->execute([':staff_id' => $staff_id, ':what_to' => $what_to]);

        if ($stmt->rowCount() > 0) {
            // Staff ID and what_to exist, check the date
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $existing_date = $row['date_done'];

            if ($existing_date != $current_date) {
                // Update the date if it's not current
                $update_sql = "UPDATE acknowledge SET date_done = :date_done WHERE staff_id = :staff_id AND what_to = :what_to";
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->execute([':date_done' => $current_date, ':staff_id' => $staff_id, ':what_to' => $what_to]);
                /// echo "Record updated successfully.";
            } else {
                /// echo "Record already exists with the current date.";
            }
        } else {
            // Staff ID and what_to do not exist, insert new record
            $insert_sql = "INSERT INTO acknowledge (staff_id, what_to, date_done) VALUES (:staff_id, :what_to, :date_done)";
            $insert_stmt = $db->prepare($insert_sql);
            $insert_stmt->execute([':staff_id' => $staff_id, ':what_to' => $what_to, ':date_done' => $current_date]);
            ///echo "New record created successfully.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}
