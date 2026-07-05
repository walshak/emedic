<?php include("../Connections/Conn.php");?>

<?php
	
  if (isset($_POST["service_item_no"])) {
    $service_item_no = $_POST["service_item_no"];

    try {
        $updateSQL = "DELETE FROM patient_discount_services WHERE sn = :sn";
        $stmt = $db->prepare($updateSQL);
        
        // Bind the parameter
        $stmt->bindParam(':sn', $service_item_no, PDO::PARAM_STR);
        
        // Execute the statement
        $stmt->execute();

        echo "Delete successful!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

