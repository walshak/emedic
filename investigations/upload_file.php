<?php
session_start();
include("../Connections/Conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && isset($_POST['labrequest_no'])) {
        $file = $_FILES['file'];
        $labRequestNo = $_POST['labrequest_no'];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        // Define the upload directory
        $uploadDir = 'uploads/'; // Ensure this directory exists and is writable

        // Check if the directory is empty
        //if (count(scandir($uploadDir)) <= 2) { // Only '.' and '..' are present
        // Define the file name
        $fileName = $labRequestNo;
        $uploadFilePath = $uploadDir . $fileName . '.' . $fileExtension;


        // Move the uploaded file to the desired directory
        if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {

            $stmt = $db->prepare("UPDATE lab_manage SET attachment = :attachment, entered_by = :entered_by WHERE labrequest_no = :labrequest_no");
            $stmt->bindValue(':attachment', $fileExtension, PDO::PARAM_STR);
            $stmt->bindValue(':entered_by', $_SESSION['fullname'], PDO::PARAM_STR);
            $stmt->bindValue(':labrequest_no', $labRequestNo, PDO::PARAM_STR);
            $stmt->execute();

            // Check if the update was successful
            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "success", "message" => "File uploaded successfully and update successful."]);
            } else {
                echo json_encode(["status" => "warning", "message" => "File uploaded but no rows were updated. The labrequest_no may not exist."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error uploading file."]);
        }
        //  } else {
        //     echo json_encode(["status" => "error", "message" => "Upload directory is not empty."]);
        //}
    } else {
        echo json_encode(["status" => "error", "message" => "No file or lab request number provided."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
