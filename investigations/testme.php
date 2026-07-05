<?php include("../Connections/Conn.php"); ?>

<?php

if (isset($_POST["edit_lab_id"])) {
  $edit_lab_id = $_POST["edit_lab_id"];

  // Prepare and execute the SELECT query using PDO
  $stmt = $db->prepare("SELECT * FROM lab_scan WHERE sn = :edit_lab_id");
  $stmt->bindParam(':edit_lab_id', $edit_lab_id, PDO::PARAM_STR);
  $stmt->execute();

  // Fetch the row as an associative array
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  // Encode the fetched row as JSON and echo it
  if ($row) {
    echo json_encode($row);
  } else {
    echo json_encode(array()); // Return empty array if no row found
  }
}
