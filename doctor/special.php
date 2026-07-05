<?php session_start();
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');



$stmt = $db->prepare("SELECT  * FROM prices_table");
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $services = json_decode(json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));

    foreach ($services as $key => $service_) {
        $service_id = $service_->sn;
        $template_name = $service_->item_service . ' Template';
        $dept = $service_->dept;
        $created_by = $_SESSION["id"];

        $stmt = $db->prepare("SELECT  * FROM services_templates WHERE service_id = ?");
        $stmt->execute(array($service_id));
        if ($stmt->rowCount() == 0) {
            $stmt = $db->prepare("INSERT INTO services_templates (template_name, service_id, dept) VALUES (?, ?, ?)");
            $stmt->execute(array($template_name, $service_id, $dept));
        }
    }
}
