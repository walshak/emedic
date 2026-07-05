<?php
session_start();

if (isset($_POST['sn_reply'])) {

    include_once('../Connections/Conn.php');
    $sn_reply = $_POST['sn_reply'];
    $reply_text = $_POST['reply_text'];

    $searchSQL = "SELECT * FROM pharm_doctor_notes WHERE sn = :sn";
    $searchStmt = $db->prepare($searchSQL);
    $searchStmt->bindParam(':sn', $sn_reply, PDO::PARAM_STR);
    $searchStmt->execute();
    $existingRecord = $searchStmt->fetch();

    if ($existingRecord) {
        $noteee = '<br><i>' . $_SESSION['fullname'] . ' : </i>' . "<span style='color:blue;'>" . $reply_text . '</span>';
        // Record exists, concatenate new drug_note and update
        $updateSQL = "UPDATE pharm_doctor_notes SET notes = CONCAT(notes, :new_notes), view_status=1, is_delete=1 WHERE sn = :sn";
        $updateStmt = $db->prepare($updateSQL);
        $updateStmt->bindParam(':new_notes', $noteee, PDO::PARAM_STR); // Concatenate with a newline character
        $updateStmt->bindParam(':sn', $sn_reply, PDO::PARAM_STR);
        $updateStmt->execute();

        // Check if the update was successful
        if ($updateStmt->rowCount() > 0) {
            echo "Chat Sent.\n";
        } else {
            echo "No record was updated.\n";
        }
    }

    exit;
}
