<?php
///session_start();
include("../Connections/Conn.php");

if (isset($_POST['hos_no'])) {
    $hos_no = $_POST['hos_no'];

    try {
        // Prepare the SQL statement to retrieve the record
        $sql = "SELECT * FROM labour_summary WHERE hos_no = :hos_no";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':hos_no', $hos_no);
        $stmt->execute();

        // Fetch the record
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            // Return the record as JSON
            echo json_encode($record);
        } else {
            echo json_encode(null); // No record found
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
