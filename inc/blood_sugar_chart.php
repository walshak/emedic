<?php

if(isset($_POST['adm_hospital'])){
	session_start();			 
	require_once('../Connections/Conn.php'); 

	$hospital_no=$_POST['adm_hospital'];		

    $app_no = $_POST['app_no'];
	$date_entry = $_POST['date_entry'];
	$time_entry = $_POST['time_entry'];
	$fbs = $_POST['fbs'];
	$rbs = $_POST['rbs'];
	$intervention = $_POST['intervention'];	
	$prepared_by = $_SESSION['fullname'];///['nurses_name'];

	$sql = $db->prepare("INSERT INTO blood_sugar_chart (app_no, hospital_no, date_entry,time_entry, fbs,rbs, intervention, prepared_by) 
	VALUES (:app_no, :hospital_no, :date_entry,:time_entry, :fbs,:rbs,:intervention,:prepared_by)");
				$sql->bindParam(':app_no',$app_no, PDO::PARAM_STR);
				$sql->bindParam(':hospital_no',$hospital_no, PDO::PARAM_STR);
				$sql->bindParam(':date_entry',$date_entry, PDO::PARAM_STR);
				$sql->bindParam(':time_entry',$time_entry, PDO::PARAM_STR);
				$sql->bindParam(':fbs',$fbs, PDO::PARAM_STR);
				$sql->bindParam(':rbs',$rbs, PDO::PARAM_STR);
				$sql->bindParam(':intervention',$intervention, PDO::PARAM_STR);
				$sql->bindParam(':prepared_by',$prepared_by, PDO::PARAM_STR);
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
	
	
$stmtt =$db->prepare("SELECT * FROM blood_sugar_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 30");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4>BLOOD SUGAR CHART</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-toggle="true">DATE</th>
						<th data-toggle="true">TIME</th>
						<th data-toggle="true">FBS</th>
						<th data-toggle="true">RBS</th>
						<th data-toggle="true">INTERVENTION</th>
						<th data-toggle="true">NURSES NAME</th>
						<th data-toggle="true"></th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo date('d M, Y', strtotime($row['date_entry'])); ?></td>
					<td><?php echo date('H:i a', strtotime($row['time_entry'])); ?></td>
					<td><?php echo $row['fbs']; ?></td>
					<td><?php echo $row['rbs']; ?></td>
					<td><?php echo $row['intervention']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
						
			<td>
							<?php if ($_SESSION['fullname']==$row['prepared_by']){ ?> 
						<a href="patient.php?hosp_no=<?= $hospital_no; ?>&blood_sugar_chart=<?php echo $row['sn']; ?>" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
						<?php } ?>
					</td>
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
$stmtt =$db->prepare("SELECT * FROM blood_sugar_chart WHERE hospital_no=:hospital_no and app_no!=:app_no order by sn desc limit 100");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4 style="color: darkred; "><u>Previous</u> Blood Sugar Chart</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-toggle="true">Date</th>
						<th data-toggle="true">Time</th>
						<th data-toggle="true">FBS</th>
						<th data-toggle="true">RBS</th>
						<th data-toggle="true">Intervention</th>						
						<th data-toggle="true">Nurses Name</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo date('d M, Y', strtotime($row['date_entry'])); ?></td>
					<td><?php echo date('H:i a', strtotime($row['time_entry'])); ?></td>
					<td><?php echo $row['fbs']; ?></td>
					<td><?php echo $row['rbs']; ?></td>
					<td><?php echo $row['intervention']; ?></td>
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
<h2 align="center">BLOOD SUGAR CHART</h2><hr>



		<div class="form_sep">
			<label for="reg_select" class="req">Date:</label>
				<div class="input-group date">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="date" name="date_entry" id="date_entry" value="<?= date('Y-m-d'); ?>" class="form-control" required>
				</div>
		</div>		
		
		<div class="form_sep">
			<label for="reg_select" class="req">Time:</label>
				<div class="input-group date">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="time" name="time_entry" id="time_entry" value="<?= date('H:i'); ?>" class="form-control" required>
				</div>
		</div>
	
        <div class="form_sep">
            <label for="reg_textarea_message" class="">FBS:</label>
			            <input type="text" id="fbs_" name="fbs" value="mmol" class="form-control">
        </div>         
		
		<div class="form_sep">
            <label for="reg_textarea_message" class="">RBS:</label>
			            <input type="text" id="rbs_" name="rbs" value="mmol" class="form-control">
        </div>   
		
		<div class="form_sep">
            <label for="reg_textarea_message" class="">Intervention:</label>
			<textarea name="intervention" id="intervention" cols="30" rows="2" class="form-control" ></textarea>
        </div>

		<div class="form_sep">
			<input type="hidden" id="app_no" value="<?php echo $app_no;?>">
			<input type="hidden" id="hospital_no" value="<?php echo $hospital_no;?>">
	
			<button class="btn btn-primary"  id="blood_sugar_btn" >Save</button>

		</div>

		
	</div>
	</div>
	</div>
	</div>


<script>

$(document).ready(function() {	
		
	$(document).on('click', '#blood_sugar_btn', function(){  
		
		var app_no = $("#app_no").val();
		var hospital_no = $("#hospital_no").val();              
		var date_entry = $("#date_entry").val();
		var time_entry = $("#time_entry").val();
		var fbs = $("#fbs_").val();
		var rbs = $("#rbs_").val();
		var intervention = $("#intervention").val();		
		

		
	if(fbs!='' || rbs !=''){
	///	alert();
		
					 $.ajax({
						 type: "POST",
						 url: "../inc/blood_sugar_chart.php",
						 data:{app_no:app_no, adm_hospital:hospital_no, date_entry:date_entry,time_entry:time_entry, fbs:fbs,rbs:fbs, intervention:intervention},
						 cache: false,
						 success: function(result) {
							 
							 alert(result);
							 
							toastr.success(result, 'Attention', {timeOut: 5000});
							window.location.href = "patient.php?hosp_no=" + hospital_no + '&blood_sugar';
						 }
					 });
	}else{
		toastr.error('Invalid Data Entries', 'Error', {timeOut: 5000});
		
	}
	
	});	  

});		 
		

</script>
