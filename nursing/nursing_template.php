<?php

require_once('../Connections/Conn.php'); 


if(isset($_POST['save_custom_template'])){
	$hospital=$_POST['hospital'];
	
$stmt_22 =$db->prepare("SELECT * FROM template_doctors WHERE template_name=:template_name");
				$stmt_22->bindParam(':template_name', $_POST['create_template'], PDO::PARAM_STR);
			$stmt_22->execute();
						
if($stmt_22->rowCount()==0){						
	$sql = $db->prepare("INSERT INTO template_doctors (template_name,template) 
	VALUES (:template_name,:template)");
				$sql->bindParam(':template_name',$_POST['template_title'], PDO::PARAM_STR);
				$sql->bindParam(':template',$_POST['create_template'], PDO::PARAM_STR);
				$sql->execute();
	}else{
					$updateSQL = "UPDATE template_doctors SET template=:template WHERE template_name=:template_title";
			$sql = $db->prepare($updateSQL);
			$sql->bindParam(':template', $_POST['create_template'], PDO::PARAM_STR);
			$sql->bindParam(':template_title', $_POST['template_title'], PDO::PARAM_STR);
			$sql->execute();
		}
}

header("location:patient.php?hosp_no=$hospital&progress");

?>