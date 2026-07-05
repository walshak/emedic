<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Form</title>
</head>
<body>
    <form action="php_upload_file.php" method="post" enctype="multipart/form-data">
        <label for="file">Choose a file:</label>
        <input type="file" name="file" id="file">
        <br>
        <input type="submit" value="Upload File">
    </form>
</body>
</html>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the file was uploaded without errors
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $targetDirectory = "uploads/enrollee";
        $targetFile = $targetDirectory . basename($_FILES["file"]["name"]);

        // Check if the file already exists
        if (file_exists($targetFile)) {
            echo "File already exists.";
        } else {
            // Move the uploaded file to the desired directory
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
                echo "File uploaded successfully: " . htmlspecialchars(basename($_FILES["file"]["name"]));
            } else {
                echo "Error uploading file.";
            }
        }
    } else {
        echo "Error: " . $_FILES["file"]["error"];
    }
}
?>



