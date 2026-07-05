<?php 

require_once('../Connections/Conn.php'); 
$hospital_no=$_POST['hospital_no'];			
$app_no=$_POST['appointment_number'];			
	/// show_chart_view	

if($_POST['show_chart_view'] == 'Fluid'){
	
$stmtt =$db->prepare("SELECT distinct(start_code) FROM fluidchart 
WHERE app_no=:app_no and hospital_no=:hospital_no ORDER BY id LIMIT 30");
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){
	$output ='';
	$input  ='';
	$total_in=0;
	$total_out=0;
	
	while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){
		
			$start_code = $row['start_code'];
		
			$stmttx =$db->prepare("SELECT * FROM fluidchart 
			WHERE app_no=:app_no and hospital_no=:hospital_no and start_code=:start_code order by id");
			$stmttx->bindParam(':app_no', $app_no, PDO::PARAM_STR);
			$stmttx->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmttx->bindParam(':start_code', $start_code, PDO::PARAM_STR);
			$stmttx->execute();
		
while($rowx = $stmttx->fetch(PDO::FETCH_ASSOC)){
	$tme = date('h:i a', strtotime($rowx['entry_time']));	
		$dd = date('d-M', strtotime($rowx['entry_date']));	

		$id=$rowx['id'];
		$hospital_no=$rowx['hospital_no'];
		$entry_date=$rowx['entry_date'];
		$start_close_status=$rowx['start_close_status'];
	$remarks=$rowx['remarks'];
	
	
	if ($_SESSION['fullname']==$rowx['nurse'] and $entry_date == date('Y-m-d')){ 
			$nurse="<a href=patient.php?hosp_no=$hospital_no&in_out=$id>Delete</a>";
		}
	
	if($rowx['chart'] =='in'){
		$total_in = $total_in + $rowx['amount'];
		$input .='<tr><td>' . $dd . ':' .$tme . '</td><td>' . $rowx['fluid_type'].'/'.$rowx['remark']  . '</td><td>' .
			$rowx['amount'] . '</td><td>' . $rowx['nurse'] . '</td><td>' . $nurse . '</td></tr>'; 
	}
					
	if($rowx['chart'] =='out'){
		$total_out = $total_out + $rowx['amount'];
		$output .='<tr><td>' . $dd .':' . $tme . '</td><td>' . $rowx['fluid_type'].'/'.$rowx['remark']  . '</td><td>' . 
			$rowx['amount'] . '</td><td>' . $rowx['nurse'] . '</td><td>' . $nurse . '</td></tr>'; 
	}					
					///<tr>
}

?>




	

	
<h3>Start Code: <?= $start_code; ?></h3>
	
<div class="row">
<div class="col-lg-6"> 
	
		<h4>INTAKE FLUIDS</h4>
				<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-hide="phone,tablet">Date/Time</th>
						<th data-toggle="true">Input Type</th>
						<th data-toggle="true">Amount(ml)</th>
						<th data-toggle="true">Entered by</th>
						<th > .</th>
					</tr>
				</thead>
				<tbody>
				<?php echo $input; ?>
				</tbody>
				</table>
	<h4>TOTAL INTAKE: <?= $total_in .' ml'; ?></h4>
	
	</div>	
<div class="col-lg-6"> 
		<h4>OUTPUT FLUIDS</h4>
				<table id="resp_table" class="table toggle-square" data-filter="#table_search" data-page-size="40">
				<thead>
					<tr>
						<th data-hide="phone,tablet">Date/Time</th>
						<th data-toggle="true">Input Type</th>
						<th data-toggle="true">Amount(ml)</th>
						<th data-toggle="true">Entered by</th>
						<th > .</th>
					</tr>
				</thead>
				<tbody>
				<?php echo $output; ?>
				</tbody>
				</table>
	
	<h4>TOTAL OUTPUT: <?= $total_out .' ml'; ?></h4>
	
	<?php if($start_close_status==1){?>
	
	<h4><u>REMARKS:</u> <?= $remarks; ?></h4>
	<h3 style="color: darkorange"><u>BALANCE:</u> <?= $total_in- $total_out .' ml'; ?> / <?= date('d M y', strtotime($entry_date)); ?></h3>
	<?php } ?>
	
	</div>	
	</div>

	
	<?php 
		$output ='';
		$input  ='';
			$total_in=0;
	$total_out=0;
	}
}
?>


	<?php }elseif($_POST['show_chart_view'] == 'PATIENT OBSERVATION CHART'){ 

	$service='PATIENT OBSERVATION CHART';
				$stmt2 =$db->prepare("SELECT * FROM notes_services 
						WHERE hospital_no=:hospital_no and app_no=:app_no and service=:service order by id desc limit 2");
					$stmt2->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
					$stmt2->bindParam(':app_no', $ppointment_number, PDO::PARAM_STR);
					$stmt2->bindParam(':service', $service, PDO::PARAM_STR);
					$stmt2->execute();	

				if($stmt2->rowCount()>0){
		

									?>

						<table class="table active hover" border="2" width="100%">
							
						<?php while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>		
							<tr>
								<td>
									
									<?= $row["notes"];?>
									
								</td>
							</tr>
						<?php } ?>
							
						</table>
<?php } ?>
	



	<?php }elseif($_POST['show_chart_view'] == 'Oxygen'){ 
	
	$stmtt =$db->prepare("SELECT * 
FROM oxygen_consumption_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 100");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ $n=1; ?>
	
	<h4>OXYGEN CONSUMPTION CHART</h4>
		<table class="table table-striped" id="" style="font-size:15px;"  width="100%">			
				<thead>
					<tr>
						<th>#</th>
						<th>Date</th>
						<th>Indication</th>
						<th>Flow Rate</th>
						<th>Remarks</th>
						<th>Nurses Name</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $n++; ?></td>
					<td><?php echo date('d M, Y H:i:s a', strtotime($row['date_entry'])); ?></td>
					<td><?php echo $row['indication']; ?></td>
					<td><?php echo $row['flow_rate']; ?></td>
					<td><?php echo $row['remarks']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php 	}?>

				</tbody>
			</table>

	<?php }else{?>
					<div class="alert alert-danger" >
					   <h3>NO RECORDS</h3>
					</div>
	<?php } ?>




	<?php }elseif($_POST['show_chart_view'] == 'Blood_sugar_old'){ 


	$service='BLOOD SUGAR CHART';
				$stmt2 =$db->prepare("SELECT * FROM notes_services 
						WHERE hospital_no=:hospital_no and app_no=:app_no and service=:service order by id desc limit 2");
					$stmt2->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
					$stmt2->bindParam(':app_no', $ppointment_number, PDO::PARAM_STR);
					$stmt2->bindParam(':service', $service, PDO::PARAM_STR);
					$stmt2->execute();	

				if($stmt2->rowCount()>0){
		

									?>

						<table class="table active hover" border="2" width="100%">
							
						<?php while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>		
							<tr>
								<td>
									
									<?= $row["notes"];?>
									
								</td>
							</tr>
						<?php } ?>
							
						</table>
<?php } ?>
	

	<?php }elseif($_POST['show_chart_view'] == 'Blood_sugar'){ 
	
	
$stmtt =$db->prepare("SELECT * FROM blood_sugar_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 100");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ $n=1; ?>
	
	<h4>BLOOD SUGAR CHART</h4>

		<table class="table table-striped" id="" style="font-size:15px;"  width="100%">			
				<thead>
					<tr>
						<th>#</th>
						<th>DATE</th>
						<th>TIME</th>
						<th>FBS</th>
						<th>RBS</th>
						<th>INTERVENTION</th>
						<th>NURSES NAME</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $n++; ?></td>
					<td><?php echo date('d M, Y', strtotime($row['date_entry'])); ?></td>
					<td><?php echo date('H:i:s a', strtotime($row['time_entry'])); ?></td>
					<td><?php echo $row['fbs']; ?></td>
					<td><?php echo $row['rbs']; ?></td>
					<td><?php echo $row['intervention']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php 	}?>

				</tbody>
			</table>
	<?php }else{?>
					<div class="alert alert-danger" >
					   <h3>NO RECORDS</h3>
					</div>
	<?php } ?>


<?php }elseif($_POST['show_chart_view'] == 'ward_to_icu_chart'){ 
	
$stmtt =$db->prepare("SELECT * FROM ward_to_icu_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 100");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();
if($stmtt->rowCount()>0){$n=1; ?>

	<h4>WARD TO ICU/TREATMENT TRANSFER CHART</h4>
		<table class="table table-striped" id="" style="font-size:15px;"  width="100%">			
				<thead>
					<tr>
						<th>#</th>
						<th>Name of Drug</th>
						<th>Strenght/Amount</th>
						<th>Intervals</th>
						<th>Duration</th>
                        <th>Date Commenced</th>
                        <th>Entered By</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $n++; ?></td>
					<td><?php echo $row['name_of_drug']; ?></td>
					<td><?php echo $row['strenght_amount']; ?></td>
					<td><?php echo $row['intervals']; ?></td>
					<td><?php echo $row['duration']; ?></td>
					<td><?php echo date('d M, Y H:i:s a', strtotime($row['date_entry'])); ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php $n++;	}?>

				</tbody>
			</table>
	<?php }else{?>
					<div class="alert alert-danger" >
					   <h3>NO RECORDS</h3>
					</div>
	<?php } ?>	
	
	
<?php }elseif($_POST['show_chart_view'] == 'seizure'){ 
		
$stmtt =$db->prepare("SELECT * FROM seizure_chart  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 100");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ $n=1; ?>
	
	<h4>Seizure Chart</h4>
		<table class="table table-striped" id="" style="font-size:15px;"  width="100%">			
				<thead>
					<tr>
						<th>#</th>
						<th>Date</th>
						<th>Duration</th>
						<th>Intervention</th>
						<th>Nurses Name</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $n++; ?></td>
					<td><?php echo date('d M, Y H:i:s a', strtotime($row['date_entry'])); ?></td>
					<td><?php echo $row['duration']; ?></td>
					<td><?php echo $row['intervention']; ?></td>					
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php 	}?>

				</tbody>
			</table>
	<?php }else{?>
					<div class="alert alert-danger" >
					   <h3>NO RECORDS</h3>
					</div>
	<?php } ?>	
	
	
<?php }elseif($_POST['show_chart_view'] == 'adm_note'){ 
	
	
$stmtt =$db->prepare("SELECT * FROM patient_admission_note  WHERE hospital_no=:hospital_no AND app_no=:app_no order by sn desc limit 100");
$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
$stmtt->execute();	
if($stmtt->rowCount()>0){ $n=1; ?>
	
	<h4>PATIENT ADMISSION NOTE/PROCEDURE CHART</h4>
		<table class="table table-striped" id="" style="font-size:15px;"  width="100%">			

				<thead>
					<tr>
						<th>#</th>
						<th>Date</th>
						<th>Prescription</th>
						<th>Ordered By</th>
						<th>Remarks</th>
						<th>Nurses Name</th>
					</tr>
				</thead>
				<tbody>
				<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
					<tr>
					<td><?php echo $n++; ?></td>
					<td><?php echo date('d M, Y H:i:s a', strtotime($row['date_entry'])); ?></td>
					<td><?php echo $row['prescription']; ?></td>
					<td><?php echo $row['ordered_by']; ?></td>
					<td><?php echo $row['remarks']; ?></td>
					<td><?php echo $row['prepared_by']; ?></td>
					</tr>
				<?php 	}?>

				</tbody>
			</table>
	<?php }else{?>
					<div class="alert alert-danger" >
					   <h3>NO RECORDS</h3>
					</div>
	<?php } ?>	
	
	
<?php }elseif($_POST['show_chart_view'] == 'Feeding'){ 

			$stmtt =$db->prepare("SELECT * FROM feedings WHERE app_no=:app_no and hospital_no=:hospital_no order by sn desc limit 100");
			$stmtt->bindParam(':app_no', $app_no, PDO::PARAM_STR);
			$stmtt->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
			$stmtt->execute();	
			if($stmtt->rowCount()>0){ $n=1; ?>

				<h4>Feeding Report</h4>
		<table class="table table-striped" id="" style="font-size:15px;"  width="100%">			
							<thead>
								<tr>
									<th>#</th>
									<th>Date</th>
									<th>Nature of Feeding</th>
									<th>Quantity of Feeding</th>
									<th>Comments</th>
									<th>Responsible</th>
								</tr>
							</thead>
							<tbody>
							<?php  while($row = $stmtt->fetch(PDO::FETCH_ASSOC)){ ?>
								<tr>
								<td><?php echo $n++; ?></td>
								<td><?php echo $row['time_feeding']; ?></td>
								<td><?php echo $row['nature_feed']; ?></td>
								<td><?php echo $row['quantity_feed']; ?></td>
								<td><?php echo $row['feed_comment']; ?></td>
								<td><?php echo $row['prepared_by']; ?></td>
								</tr>
							<?php 	}?>

							</tbody>
						</table>
				<?php }else{?>
								<div class="alert alert-danger" >
								   <h3>NO FEEDING RECORDS</h3>
								</div>
				<?php } ?>

	<?php } ?>
