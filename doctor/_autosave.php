<?php
include("../Connections/Conn.php");

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hospital_no = $_POST['hospital_no'];
    $app_no = $_POST['app_no'];
    $doctor = $_POST['doctor'];
    $content = $_POST['content'];
    $action = $_POST['action'];

    // Check action type
    switch ($action) {
        case 'save':
            // Save the content into the autosave table
            $stmt = $db->prepare("REPLACE INTO autosave (hospital_no, app_no, doctor, content) VALUES (?, ?, ?, ?)");
            $stmt->execute([$hospital_no, $app_no, $doctor, $content]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            break;

        case 'delete':
            // Delete the autosave data when the user manually saves
            $stmt = $db->prepare("DELETE FROM autosave WHERE hospital_no = ? AND app_no = ? AND doctor = ?");
            $stmt->execute([$hospital_no, $app_no, $doctor]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $hospital_no = $_GET['hospital_no'];
    $app_no = $_GET['app_no'];
    $doctor = $_GET['doctor'];
    $action = $_GET['action'];

    if ($action === 'restore') {
        // Retrieve the most recent autosave content
        $stmt = $db->prepare("SELECT content FROM autosave WHERE hospital_no = ? AND app_no = ? AND doctor = ? ORDER BY saved_at DESC LIMIT 1");
        $stmt->execute([$hospital_no, $app_no, $doctor]);

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['content' => $row['content']]);
        } else {
            echo json_encode(['content' => '']);
        }
    }
}
