<?php

if(isset($_POST['adm_hospital'])){
	session_start();			 
	require_once('../Connections/Conn.php'); 

	$hospital_no=$_POST['adm_hospital'];		

    $app_no = $_POST['app_no'];
	$date_entry = $_POST['date_entry'] . " " . date('h:i:s a');
	$name_of_drug = $_POST['name_of_drug'];
	$strenght_amount = $_POST['strenght_amount'];
	$intervals = $_POST['intervals'];
	$duration = $_POST['duration'];
	$prepared_by = $_SESSION['fullname'];///['duration'];

	$sql = $db->prepare("INSERT INTO ward_to_icu_chart (app_no, hospital_no, date_entry, name_of_drug, strenght_amount, intervals, duration,prepared_by) 
	VALUES (:app_no, :hospital_no, :date_entry,:name_of_drug,:strenght_amount,:intervals,:duration,:prepared_by)");
				$sql->bindParam(':app_no',$app_no, PDO::PARAM_STR);
				$sql->bindParam(':hospital_no',$hospital_no, PDO::PARAM_STR);
				$sql->bindParam(':date_entry',$date_entry, PDO::PARAM_STR);
				$sql->bindParam(':name_of_drug',$name_of_drug, PDO::PARAM_STR);
				$sql->bindParam(':strenght_amount',$strenght_amount, PDO::PARAM_STR);
				$sql->bindParam(':intervals',$intervals, PDO::PARAM_STR);
				$sql->bindParam(':duration',$duration, PDO::PARAM_STR);
				$sql->bindParam(':duration',$duration, PDO::PARAM_STR);
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
	
$stmtt =$db->prepare("SELECT * FROM ward_to_icu_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 30");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();
if($stmtt->rowCount()>0){ ?>
	<?php $n=1; ?>

	<h4>WARD TO ICU/HDU TREATMENT TRANSFER CHART</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-toggle="true">Sn</th>
						<th data-toggle="true">Name of Drug</th>
						<th data-toggle="true">Strenght/Amount</th>
						<th data-toggle="true">Intervals</th>
						<th data-toggle="true">Duration</th>
                        <th data-toggle="true">Date Commenced</th>
                        <th data-toggle="true">Entered By</th>
                        <th data-toggle="true"></th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
                    <td><?php echo $n; ?></td>
                    <td><?php echo $row['name_of_drug']; ?></td>
                    <td><?php echo $row['strenght_amount']; ?></td>
                    <td><?php echo $row['intervals']; ?></td>
                    <td><?php echo $row['duration']; ?></td>
                    
					<td><?php echo date('d M, Y H:i:s a', strtotime($row['date_entry'])); ?></td>
						<td><?php echo $row['prepared_by']; ?></td>
						
	<td>
							<?php if ($_SESSION['fullname']==$row['prepared_by']){ ?> 
						<a href="patient.php?hosp_no=<?= $hospital_no; ?>&ward_to_icu_chart=<?php echo $row['sn']; ?>" onclick="return confirm('Are you sure you want to DELETE?')">Delete</a>
						<?php } ?>
					</td>
					</tr>
				<?php $n++;	}?>

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
$stmtt =$db->prepare("SELECT * FROM ward_to_icu_chart WHERE hospital_no!=:hospital_no and app_no!=:app_no order by sn desc limit 100");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ ?>
	<?php $n=1; ?>
	<h4 style="color: darkred; "><u>Previous</u>Ward to ICU/HDU Treatment Transfer Chart</h4>
	<div class="panel-heading">
			</div>

	<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
                        <th data-toggle="true">Sn</th>
						<th data-hide="phone,tablet">Appt. No.</th>						
						<th data-toggle="true">Name of Drug</th>
						<th data-toggle="true">Strenght/Amount</th>
						<th data-toggle="true">Intervals</th>
						<th data-toggle="true">Duration</th>
                        <th data-toggle="true">Date Commenced</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
                    <td><?php echo $n; ?></td>
					<td><?php echo $row['app_no']; ?></td>				     
                    <td><?php echo $row['name_of_drug']; ?></td>
                    <td><?php echo $row['strenght_amount']; ?></td>
                    <td><?php echo $row['intervals']; ?></td>
                    <td><?php echo $row['duration']; ?></td>
					<td><?php echo date('d M, Y H:i:s a', strtotime($row['date_entry'])); ?></td>
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
<h2 align="center">WARD TO ICU/HDU TREATMENT TRANSFER CHART</h2><hr>

	
        <div class="form_sep">
            <label for="reg_textarea_message" class="req">Name of Drug:</label>
			<textarea name="name_of_drug" id="name_of_drug" cols="30" rows="2" class="form-control"  required></textarea>
        </div>   
		
		<div class="form_sep">
            <label for="reg_textarea_message" class="req">Strenght/Amount:</label>
			<textarea name="strenght_amount" id="strenght_amount" cols="30" rows="2" class="form-control" required ></textarea>
        </div>
        
        <div class="form_sep">
            <label for="reg_textarea_message" class="req">Intervals:</label>
			<textarea name="intervals" id="intervals" cols="30" rows="2" class="form-control" required ></textarea>
        </div>
		
		<div class="form_sep">
   			<label for="reg_input_no" class="req">Duration:</label>
            <input type="text" id="duration" name="duration" class="form-control" required>
        </div>

        <div class="form_sep">
			<label for="reg_select" class="req">Date Commenced:</label>
				<div class="input-group date">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="date" name="date_entry" id="date_entry" class="form-control" required>
				</div>
		</div>
									
			<!-- 
         	<div class="form_sep">
				<label for="reg_select" class="req">Time:</label>
			   	<input type="time" name="ttime" id="ttime" class="form-control" required>                                
        	</div>    -->

		<div class="form_sep">
			<input type="hidden" id="app_no" value="<?php echo $app_no;?>">
			<input type="hidden" id="hospital_no" value="<?php echo $hospital_no;?>">
	
			<button class="btn btn-primary"  id="icu_wardbutton" >Save</button>

		</div>

		
	</div>
	</div>
	</div>
	</div>


<script>

$(document).ready(function() {	
		
	$(document).on('click', '#icu_wardbutton', function(){  
		
		
		var app_no = $("#app_no").val();
		var hospital_no = $("#hospital_no").val();              
		var date_entry = $("#date_entry").val();
		var name_of_drug = $("#name_of_drug").val();
		var strenght_amount = $("#strenght_amount").val();
		var intervals = $("#intervals").val();
		var duration = $("#duration").val();	
		
		
	if(name_of_drug!='' && date_entry!='' && strenght_amount!='' && intervals!='' && duration!=''){
		
	///	alert();
					 $.ajax({
						 type: "POST",
						 url: "../inc/ward_to_icu_chart.php",
						 data:{app_no:app_no, adm_hospital:hospital_no, date_entry:date_entry, name_of_drug:name_of_drug, strenght_amount:strenght_amount, intervals:intervals, duration:duration},
						 cache: false,
						 success: function(result) {
							toastr.success(result, 'Attention', {timeOut: 5000});
							window.location.href = "patient.php?hosp_no=" + hospital_no + '&ward_icu';
						 }
					 });
	}else{
		toastr.error('Invalid Data Entries', 'Error', {timeOut: 5000});
		
	}
	
	});	  

});		 
		

</script>
