<?php include("../Connections/Conn.php"); ?>

<?php

if (isset($_POST["service_item_income_id"])) {
  $stmt = $db->prepare('DELETE FROM hred_income_services WHERE sn = :sn');
  $stmt->bindParam(':sn', $_POST["service_item_income_id"]);
  $stmt->execute();
}
