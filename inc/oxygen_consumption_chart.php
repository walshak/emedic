<?php

if(isset($_POST['adm_hospital'])){
	session_start();			 
	require_once('../Connections/Conn.php'); 

	$hospital_no=$_POST['adm_hospital'];		

    $app_no = $_POST['app_no'];
	$date_entry = $_POST['date_entry'];
	$time_range = $_POST['time_range'];
	$indication = $_POST['indication'];
	$flow_rate = $_POST['flow_rate'];
	$remarks = $_POST['remarks'];
	$prepared_by = $_SESSION['fullname'];////['nurses_name'];

	$sql = $db->prepare("INSERT INTO oxygen_consumption_chart (app_no, hospital_no, date_entry,time_range, indication,flow_rate, remarks, prepared_by) 
	VALUES (:app_no, :hospital_no, :date_entry,:time_range, :indication,:flow_rate,:remarks,:prepared_by)");
				$sql->bindParam(':app_no',$app_no, PDO::PARAM_STR);
				$sql->bindParam(':hospital_no',$hospital_no, PDO::PARAM_STR);
				$sql->bindParam(':date_entry',$date_entry, PDO::PARAM_STR);
				$sql->bindParam(':time_range',$time_range, PDO::PARAM_STR);
				$sql->bindParam(':indication',$indication, PDO::PARAM_STR);
				$sql->bindParam(':flow_rate',$flow_rate, PDO::PARAM_STR);
				$sql->bindParam(':remarks',$remarks, PDO::PARAM_STR);
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
	
$stmtt =$db->prepare("SELECT * 
FROM oxygen_consumption_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 30");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4>OXYGEN CONSUMPTION CHART</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-toggle="true">Date</th>
						<th data-toggle="true">Time Range</th>
						<th data-toggle="true">Indication</th>
						<th data-toggle="true">Flow Rate</th>
						<th data-toggle="true">Remarks</th>
						<th data-toggle="true">Nurses Name</th>
						<th data-toggle="true"></th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo date('d M, Y', strtotime($row['date_entry'])); ?></td>
					
					<td><?php echo $row['time_range']; ?></td>
						<td><?php echo $row['indication']; ?></td>
					<td><?php echo $row['flow_rate']; ?></td>
					<td><?php echo $row['remarks']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					<td>
							<?php if ($_SESSION['fullname']==$row['prepared_by']){ ?> 
						<a href="patient.php?hosp_no=<?= $hospital_no; ?>&oxygen=<?php echo $row['sn']; ?>" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
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
$stmtt =$db->prepare("SELECT * FROM oxygen_consumption_chart WHERE hospital_no=:hospital_no and app_no!=:app_no order by sn desc limit 30");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	
	<h4 style="color: darkred; "><u>Previous</u> Oxygen Consumption Chart</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-hide="phone,tablet">Appt. No.</th>
						<th data-toggle="true">Date</th>
						<th data-toggle="true">Time Range</th>
						<th data-toggle="true">Indication</th>
						<th data-toggle="true"> Flow Rate</th>
						<th data-toggle="true">Remarks</th>
						<th data-toggle="true">Nurses Name</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $row['app_no']; ?></td>
					<td><?php echo date('d M, Y', strtotime($row['date_entry'])); ?></td>
					<td><?php echo $row['time_range']; ?></td>
					<td><?php echo $row['indication']; ?></td>
					<td><?php echo $row['flow_rate']; ?></td>
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
<h2 align="center">OXYGEN CONSUMPTION CHART</h2><hr>



		<div class="form_sep">
			<label for="reg_select" class="req">Date:</label>
				<div class="input-group date">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="date" name="date_entry" id="date_entry" value="<?= date('Y-m-d'); ?>" class="form-control" required>
				</div>
		</div>		
	
		<div class="form_sep">
			<label for="reg_select" class="req">Time Range:</label>
			<input type="text" name="time" id="time_range" class="form-control" maxlength="20" required>
		</div>
	
        <div class="form_sep">
            <label for="reg_textarea_message" class="req">Indication:</label>
			<textarea name="indication" id="indication" cols="30" rows="2" class="form-control"  required></textarea>
        </div>   
		
		<div class="form_sep">
            <label for="" class="req">Flow Rate:</label>
			<input type="text" name="flow_rate" id="flow_rate" class="form-control" maxlength="20">
        </div>
        
        <div class="form_sep">
            <label for="reg_textarea_message" class="">Remarks:</label>
			<textarea name="remarks" id="remarks" cols="30" rows="2" class="form-control" ></textarea>
        </div>
		


		<div class="form_sep">
			<input type="hidden" id="app_no" value="<?php echo $app_no;?>">
			<input type="hidden" id="hospital_no" value="<?php echo $hospital_no;?>">
	
			<button class="btn btn-primary"  id="save-mgt-button_oxy" >Save</button>

		</div>

		
	</div>
	</div>
	</div>
	</div>


<script>

$(document).ready(function() {	
		
	$(document).on('click', '#save-mgt-button_oxy', function(){	
				
		var app_no = $("#app_no").val();
		var hospital_no = $("#hospital_no").val();              
		var date_entry = $("#date_entry").val();
		var time_range = $("#time_range").val();
		var indication = $("#indication").val();
		var flow_rate = $("#flow_rate").val();
		var remarks = $("#remarks").val();
	//	var nurses_name = $("#nurses_name").val();	
		
	if(indication!='' && date_entry!='' && flow_rate!='' && time_range!=''){
		
		 $.ajax({
			 type: "POST",
			 url: "../inc/oxygen_consumption_chart.php",
			 data:{app_no:app_no, adm_hospital:hospital_no, date_entry:date_entry,time_range:time_range,indication:indication,flow_rate:flow_rate, remarks:remarks},
			 cache: false,
			 success: function(result) {
				toastr.success(result, 'Attention', {timeOut: 5000});
				window.location.href = "patient.php?hosp_no=" + hospital_no + '&oxygen';
			 }
		 });
	}else{
		toastr.error('Invalid Data Entries', 'Error', {timeOut: 5000});
		
	}
	
	});	  

});		 
		

</script>
