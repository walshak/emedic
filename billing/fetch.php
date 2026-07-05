<?php include("../Connections/Conn.php");?>

<?php


if (isset($_POST["edit_id"])) { 
  try {
      $edit_id = $_POST["edit_id"];
      
      // Prepare the SQL statement with a placeholder for the 'sn' parameter
      $stmt = $db->prepare("SELECT * FROM patient_discount WHERE sn = :sn");

      // Bind the parameter to the actual value
      $stmt->bindParam(':sn', $edit_id, PDO::PARAM_STR);

      // Execute the prepared statement
      $stmt->execute();

      // Fetch the result as an associative array
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      // Encode the result in JSON format and output it
      echo json_encode($row);
  } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
  }
}

 
?>


