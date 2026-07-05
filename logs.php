<?php
	
date_default_timezone_set('Africa/Lagos');
$setdatetime=date("Y-m-d H:i:s");
						
		 $chk= $db->query("SELECT * FROM patient_staff_logs WHERE descriptions='$desc' and date_and_time='$setdatetime'");
		 			if($chk->rowCount()==0 and $desc!=''){
						
$sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn,descriptions,staff_name,patient_id,patient_name,action,date_and_time) 
VALUES (:item_sn,:descriptions,:staff_name,:patient_id,:patient_name,:action,:date_and_time)");
				$sql->bindParam(':item_sn', $item_sn, PDO::PARAM_STR);
				$sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
				$sql->bindParam(':staff_name', $staff, PDO::PARAM_STR);
				$sql->bindParam(':patient_id', $pid, PDO::PARAM_STR);
				$sql->bindParam(':patient_name', $pname, PDO::PARAM_STR);
				$sql->bindParam(':action', $action, PDO::PARAM_STR);
				$sql->bindParam(':date_and_time', $setdatetime, PDO::PARAM_STR);
				$sql->execute();
						
						
		}
?>