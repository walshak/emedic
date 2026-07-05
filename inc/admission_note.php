<?php

if(isset($_POST['adm_hospital'])){
	session_start();			 
	require_once('../Connections/Conn.php'); 

	$hospital_no=$_POST['adm_hospital'];		

    $app_no = $_POST['app_no'];
	$date_entry = $_POST['date_entry'];
	$prescription = $_POST['prescription'];
	$remarks = $_POST['remarks'];

	$sql = $db->prepare("INSERT INTO patient_admission_note (app_no, hospital_no, date_entry, prescription, ordered_by, remarks, prepared_by) 
	VALUES (:app_no, :hospital_no, :date_entry,:prescription,:ordered_by,:remarks,:prepared_by)");
				$sql->bindParam(':app_no',$app_no, PDO::PARAM_STR);
				$sql->bindParam(':hospital_no',$hospital_no, PDO::PARAM_STR);
				$sql->bindParam(':date_entry',$date_entry, PDO::PARAM_STR);
				$sql->bindParam(':prescription',$prescription, PDO::PARAM_STR);
				$sql->bindParam(':ordered_by',$_POST['ordered_by'], PDO::PARAM_STR);
				$sql->bindParam(':remarks',$remarks, PDO::PARAM_STR);
				$sql->bindParam(':prepared_by',$_SESSION['fullname'], PDO::PARAM_STR);
				$sql->execute();
  if ($sql) {
 	  // $error_status = 2;
 	   echo 'Data Added Successfully!';
  			}else{
	  echo 'Saving Not Successfully!';
	   }
	
exit;
	
}



?>


<div class="row">
<div class="col-lg-8"> 
<div class="ibox ">
<div class="ibox-content">
	
	
<?php 
	
session_start();			 
require_once('../Connections/Conn.php'); 
$hospital_no=$_POST['hospital_no'];		


$appointment_number=$_POST['appointment_number'];			
				
$stmt =$db->prepare("SELECT app_no FROM admission WHERE hospital_no=:hospital_no and adm_status='3' order by sn desc limit 1");
$stmt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmt->execute();
	
if($stmt->rowCount()>0){ 
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$app_no=$row['app_no'];	
}
	if($app_no==''){$app_no=$appointment_number;}
	
	
$stmtt =$db->prepare("SELECT * FROM patient_admission_note  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 50");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4>PATIENT ADMISSION NOTE/PROCEDURE CHART</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-toggle="true">Date</th>
						<th data-toggle="true">Prescription</th>
						<th data-toggle="true">Ordered By</th>
						<th data-toggle="true">Remarks</th>
						<th data-toggle="true">Nurses Name</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo date('d M, Y', strtotime($row['date_entry'])); ?></td>
					<td><?php echo $row['prescription']; ?></td>
					<td><?php echo $row['ordered_by']; ?></td>
					<td><?php echo $row['remarks']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
									<td>
							<?php if ($_SESSION['fullname']==$row['prepared_by']){ ?> 
						<a href="patient.php?hosp_no=<?= $hospital_no; ?>&adm_note=<?php echo $row['sn']; ?>" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
						<?php } ?>
						</td>
					
					 <?php 	
					//  }
					 ?>
					</tr>
				<?php 	}?>

				</tbody>
				<tfoot class="hide-if-no-paging">
					<tr>
						<td colspan="6" class="text-center">
							<ul class="pagination pagination-sm"></ul>
						</td>
					</tr>
				</tfoot>
			</table>
	<?php } ?>
	<hr>
	
	<?php
$stmtt =$db->prepare("SELECT * FROM patient_admission_note WHERE hospital_no=:hospital_no and app_no!=:app_no order by sn desc limit 30");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4 style="color: darkred; "><u>Previous</u> Admission Notes List</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-hide="phone,tablet">Appt. No.</th>
						<th data-toggle="true">Date</th>
						<th data-toggle="true">Prescription</th>
						<th data-toggle="true">Ordered By</th>
						<th data-toggle="true">Remarks</th>
						<th data-toggle="true">Nurses Name</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $row['app_no']; ?></td>
					<td><?php echo date('d M, Y H:i:s a', strtotime($row['date_entry'])); ?></td>
					<td><?php echo $row['prescription']; ?></td>
					<td><?php echo $row['ordered_by']; ?></td>
					<td><?php echo $row['remarks']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php 	}?>

				</tbody>
				<tfoot class="hide-if-no-paging">
					<tr>
						<td colspan="6" class="text-center">
							<ul class="pagination pagination-sm"></ul>
						</td>
					</tr>
				</tfoot>
			</table>
	<?php } ?>
	

</div>	
</div>	
</div>	
	
	
	
	
	
	
<div class="col-lg-4">
<div class="ibox ">
<div class="ibox-content">
<h2 align="center">PATIENT ADMISSION NOTE/PROCEDURE CHART</h2><hr>

		<div class="form_sep">
			<label for="reg_select" class="req">Date:</label>
				<div class="input-group date">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="date" name="date_entry" id="date_entry" class="form-control" value="<?= date('Y-m-d'); ?>" required>
				</div>
		</div>
	
        <div class="form_sep">
            <label for="reg_textarea_message" class="req">Prescription/Procedure:</label>
			<textarea name="prescription" id="prescription" cols="30" rows="2" class="form-control"  required></textarea>
        </div>        
        
        <div class="form_sep">
            <label for="reg_textarea_message" class="req">Remarks:</label>
			<textarea name="remarks" id="remarks" cols="30" rows="4" class="form-control" required ></textarea>
        </div>
		
		<div class="form_sep">
   			<label for="reg_input_no" class="req">Ordered By:</label>
            <input type="text" id="ordered_by" name="ordered_by" class="form-control" required>
        </div>
								

		<div class="form_sep">
			<input type="hidden" id="app_no" value="<?php echo $app_no;?>">
			<input type="hidden" id="hospital_no" value="<?php echo $hospital_no;?>">
	
			<button class="btn btn-primary"  id="save_adm_note_button" >Save</button>

		</div>

		
	</div>
	</div>
	</div>
	</div>


<script>

$(document).ready(function() {	
		
	$(document).on('click', '#save_adm_note_button', function(){  
				
		var app_no = $("#app_no").val();
		var hospital_no = $("#hospital_no").val();              
		var date_entry = $("#date_entry").val();
		var prescription = $("#prescription").val();
		var remarks = $("#remarks").val();
		var ordered_by = $("#ordered_by").val();	
		
	if(prescription!='' && date_entry!='' && remarks!='' && ordered_by!=''){
					 $.ajax({
						 type: "POST",
						 url: "../inc/admission_note.php",
						 data:{app_no:app_no, adm_hospital:hospital_no, date_entry:date_entry, prescription:prescription, remarks:remarks, ordered_by:ordered_by},
						 cache: false,
						 success: function(result) {
							toastr.success(result, 'Attention', {timeOut: 5000});
							window.location.href = "patient.php?hosp_no=" + hospital_no + '&adm_note';
						 }
					 });
	}else{
		toastr.error('Invalid Data Entries', 'Error', {timeOut: 5000});
		
	}
	
	});	  

});		 
		

</script>
