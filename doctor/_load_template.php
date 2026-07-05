<?php

if(isset($_POST['template_id'])){
   include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');

$template_id = $_POST['template_id'];

    $stmt = $db->prepare("SELECT template FROM  services_templates  WHERE id = ? ");
    $stmt->execute(array($template_id));
    if($stmt->rowCount() > 0){
        $template_arr = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $template_arr['template'];
    } 
}


?>