<?php 

require_once('../Connections/Conn.php');

if(isset($_POST["check_plan"])){
	
	$response = array('status'=>0);
	
	$CurDateHR= date("Y-m-d");
	$hospital_no=$_POST["hospital_no"];
	$stmtx =$db->prepare("SELECT * FROM notes 
			WHERE (notes_type='plan' or notes_type='treatment') 
			and hospital_no='$hospital_no' and date(date_entry)='$CurDateHR' and status='1'");
		$stmtx->execute();
			if($stmtx->rowCount()>0){	
			$response['status']='1';
		}
	echo  json_encode($response);
	
	
}


if(isset($_POST["check_plan2"])){
	
	$response = array('status'=>0);
	
	$CurDateHR= date("Y-m-d");
	$hospital_no=$_POST["hospital_no"];
	$stmtx =$db->prepare("SELECT * FROM notes 
			WHERE (notes_type='plan' or notes_type='treatment') and hospital_no='$hospital_no' and date(date_entry)='$CurDateHR' and status='1'");
		$stmtx->execute();
			if($stmtx->rowCount()>0){ ?>


<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 16px;" >
<thead>
	<tr>
		<th><strong>#</strong></th>
		<th><strong>Doctor Plan / Treatment</strong></th>
	</tr>
</thead>
<tbody>  

<?php 
		$n=1;
		while($rw=$stmtx->fetch(PDO::FETCH_ASSOC)){ ?>
			<tr>
			<td><?php echo $n++;//['reason_adm']; ?></td>
			<td><?php 
			
			echo $rw['notes'].
				'<br><strong><i>Entered by: </i></strong>' . $rw['prepared_by'] .
				'<br>' .
				'<strong>Date:</strong>'.
				date("d M,y H:i:s",strtotime($rw['date_entry'])); ?>
			
			</td>
			</tr>
<?php  } ?>
</tbody>
</table>
			
				
<?php }
	
}





if(isset($_POST["load_template"])){
	
$stmtx =$db->prepare("SELECT template,sn FROM notes WHERE sn=:doc_template");
$stmtx->bindValue(':doc_template', $_POST["load_template"], PDO::PARAM_STR);
$stmtx->execute();
	$stmt_logCOUNT=$stmtx->rowCount();
if($stmt_logCOUNT>0){	
$rowx = $stmtx->fetch(PDO::FETCH_ASSOC);
	echo $sn=$rowx['sn'] ;
}else{
	echo 'Type Consultation Notes';
}
}


?>
