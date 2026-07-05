<?php
$dischgr_date=$_POST['dischgr_date'];

?>


<div class="row">


<div class="col-lg-12">
<div class="ibox float-e-margins">
<div class="ibox-title"><h5>Discharge Patients Query </h5></div>

<div class="ibox-content">

<form action="index.php?pend" method="POST">

<div class="row">
<div class="col-sm-4 b-r">
<p>Check past discharged patients using dates</p>

<div class="form_sep" id="data_1">
<label for="reg_input_no" class="req">Discharge Date</label>
<div class="input-group date">
<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
<input type="text" class="form-control" name="dischgr_date" required >
</div>
</div>

</div>

<div class="col-sm-4 b-r">
<label for="reg_input_no" class="">.</label><br>

<button class="btn btn-success btn btn-sm" type="submit" name="apply_discharge" id="apply_discharge" >Apply</button>


</div>

<div class="col-sm-4">
<label for="reg_input_no" class="">.</label><br>
<a href="index.php?pend" class="btn btn-white btn btn-sm" ><i class="fa fa-refresh"></i>&nbsp;Refresh</a>


</div>

</div>

    </form>
    
    
</div>

</div>
</div> 




<div class="col-lg-12">
<div class="ibox float-e-margins">
<div class="ibox-title"><h5>Pending Activities</h5></div>
 
<div class="ibox-content">

<div class="row">
<div class="col-sm-12">

                    <?php 
					
										$c=1;
		$stmt=$db->query("SELECT * FROM discharge_fellowup order by sn ");
			if($stmt->rowCount()>0){ ?>
            
            <h4 style="color:#F00">Discharge Requests</h4>

              <table class="table table-striped table-bordered">                 
                <thead>
                <tr>
                    <th data-toggle="true">#</th>
                    <th data-toggle="true">Hospital No</th>
                    <th data-toggle="true">Name</th>
                    <th data-toggle="true">Sent by</th>
                    <th data-toggle="true">Time</th>
                    <th data-toggle="true">Status</th>
                </tr>
                </thead>
                <tbody>
         <?php
		 	$n=1;
         	while($roww=$stmt->fetch(PDO::FETCH_ASSOC)){
				$Current_date=date('Y-m-d H:i:s'); 
				
	$date1 = new DateTime($Current_date);
	$date2 = new DateTime($roww['date_ap'] .' '. $roww['ap_time']);
	$diff = $date2->diff($date1);	
			$hr= $diff->format('%h');
		$day=$diff->format('%a');
		
				   ?>
                    <tr>
                    <td><?php echo $n;?></td>  
                    
                    <td><?php echo $roww['hospital_no']; ?></td>
                    <td><?php echo $roww['patient_name']; ?></td>
                    <td><?php echo $roww['referal_doc']; ?></td>
                    <td><?php echo date('d,M y', strtotime($roww['date_ap'])) .' '. date('H:i:s a', strtotime($roww['ap_time'])) . '/<br>' .$day.'day' .':'.$hr.'hr ago';?></td>
                    <td><?php echo $roww['status']; ?></td>
                    </tr>
                    <?php $n++; }?>
                    </tbody>
                    </table>
                    <hr>
<?php } ?>



                    <?php 
	$c=1;
		if($dischgr_date==''){
			$stmt=$db->query("SELECT * FROM admission where adm_status='3' order by sn");
			$adm_status='3';
			$title= "As At Today";
						}else{
				$stmt=$db->query("SELECT * FROM admission where adm_status='4' order by sn");
				$adm_status='4';
				$title="As At " .date("d M Y",strtotime($dischgr_date));
		}
			if($stmt->rowCount()>0){ ?>
            
            <h4 style="color: #F60">Admitted Patient Routing Checkups // <?php echo $title; ?></h4>

              <table class="table table-striped table-bordered">                 
                <thead>
                <tr>
                    <th data-toggle="true">#</th>
                    <th data-toggle="true">Details</th>
                    <th data-toggle="true">Consumables</th>
                    <th data-toggle="true">Other Checkup</th>
                </tr>
                </thead>
                <tbody>
         <?php
		 	$nn=1;
         	while($roww=$stmt->fetch(PDO::FETCH_ASSOC)){
				if($adm_status=='3'){
					$Current_date=date('Y-m-d H:i:s'); 
			$Current_date2=date('Y-m-d');
				}else{
						$Current_date=$roww['date_discharge'];
				$Current_date2=date("Y-m-d",strtotime($roww['date_discharge']));
				}
	$date1 = new DateTime($Current_date);
	$date2 = new DateTime($roww['date_admit']);
	$diff = $date2->diff($date1);	
			$hr= $diff->format('%h');
		$day=$diff->format('%a');
		
				   ?>
                    <tr>
                    <td><?php echo $nn;?></td>  
                    <td><?php echo '<strong>Hospital #</strong>: '. $roww['hospital_no'].'<hr><strong>Duration:</strong><br>'. date('d,M y H:i:s a', strtotime($roww['date_admit'])) . '/<br>' .$day.'day' .':'.$hr.'hr ago';?></td>
                    <td align="right"><?php 
					// 
							$hospital_no=$roww['hospital_no'];
							$effect_date=$roww['date_admit'];
							$con='';
					while ($effect_date<=$Current_date2){
						////////// effectv dates
						echo '<table width="100%" class="table-bordered ">';
						
						//admin_users_logs
						//$stmt2=$db->query("SELECT * FROM patient_ap_services where cat_type='Nursing Consumable' and hospital_no='$hospital_no' and date(date_entry)='$effect_date' order by sn ");

						$stmt2=$db->query("SELECT * FROM patient_ap_services where cat_type='Nursing Consumable' and hospital_no='$hospital_no' and date(date_entry)='$effect_date' order by sn ");
								if($stmt2->rowCount()>0){
									while($rwx=$stmt2->fetch(PDO::FETCH_ASSOC)){
										$con.=$rwx['item_services'] .',';	
									}
									echo '<tr><td align="left"><strong>' . $stmt2->rowCount() . ' Consumables Posted on:</strong><br>'.$con .'</td><td align="right" width="25%"> '. date("d,M y", strtotime($effect_date)).'</td><tr>';
										}else{
								echo '<tr><td align="left"><strong style="color:#F00">No Consumables Posted on: </strong></td><td align="right" width="25%">' . date("d,M y", strtotime($effect_date)) .'</td><tr>';
							}
						$effect_date = date("Y-m-d",strtotime($effect_date." +1 day"));
						///////////// end of effectv dates
					}
					echo '</table>';
					
							 ?></td>
                    <td>
					<?php 
					
						///DRUG ADMINISTRATION //////////////////////////////==============================
							$date_admit2=date("Y-m-d",strtotime($roww['date_admit']));
						$stmt_adm=$db->query("SELECT app_no,sn,item_services FROM patient_ap_services where serv_group='Pharmacy' and hospital_no='$hospital_no' and drug_status='1' and date(date_entry) between '$date_admit2' and '$Current_date2' order by sn ");
								if($stmt_adm->rowCount()>0){ ?>
                                <h4>Medications Chart Status</h4>
                                <?php echo '<table width="100%" class="table-bordered"><tr><td width="5%"><strong>#</strong></td><td><strong>Medication</strong></td><td><strong>Adm</strong><br>/<strong>Status</strong></td><td width="15%"><strong>Adm</strong><br>/<strong>Times</strong></td></tr>';
									$n=1;
									while($rwxa=$stmt_adm->fetch(PDO::FETCH_ASSOC)){
												 $sn=$rwxa['sn']; 
													$app_no=$rwxa['app_no']; 
												$item_services=$rwxa['item_services'];
	/////
				
				$stmt3=$db->query("SELECT * FROM drug_charts_inven where app_no='$app_no' and drug_sn='$sn'");
								if($stmt3->rowCount()>0){
									echo '<tr><td>'.$n.'-</td><td>'.$item_services.'</td><td><strong>YES</strong></td><td>'.$stmt3->rowCount().'</td></tr>';
										}else{
									echo '<tr><td>'.$n.'-</td><td>'.$item_services.'</td><td><strong style="color:#F00">NO</strong></td><td>'.$stmt3->rowCount().'</td></tr>';
								}
								$n++;
////--------------------------------------------------------------------------											
						}
					echo '</table>';
			
									}else{
								echo '<h4>No Medications chart available</h4>';
							}
	/////// END OF DRUG ADMINISTRATION =========================================================
	
	////////  PROGRESSS NOTE AND DOCTOR ROUNDS
	
	?>
    <hr>
     <h4>Doctors Rounds / Progress Notes </h4>
    <?php
					$n=1;
					echo '<table width="100%" class="table-bordered"><tr><td><strong>#</strong></td><td><strong>Doctors<br>/Round</strong></td><td><strong>Nurses</strong><br>/<strong>Notes</strong></td><td width="25%"><strong>Dates</strong></td></tr>';
						$effect_date=$roww['date_admit'];
			
				if($adm_status=='3'){
			$Current_date2=date('Y-m-d');
				}else{
		$Current_date=$roww['date_discharge'];
				}
										
					while ($effect_date<=$Current_date2){
					////////// effectv dates
						///// check doctrors roundsss
						$stmt2=$db->query("SELECT hospital_no FROM c_d_remarks where hospital_no='$hospital_no' and date(date_entry)='$effect_date'");
								if($stmt2->rowCount()>0){
									$DR_round='<strong style="color:#00F; background-color:#FF9">YES</strong>';
											}else{
									$stmt2=$db->query("SELECT hospital_no FROM notes where tag='DR' and  hospital_no='$hospital_no' and date(date_entry)='$effect_date'");
										if($stmt2->rowCount()>0){
												$DR_round='<strong style="color:#00F; background-color:#FF9">YES</strong>';
											}else{
											$DR_round='<strong style="color:#F00">NO</strong>';
										}
									}
						///// check nursesing round
			
						$stmt2=$db->query("SELECT hospital_no FROM notes where tag='NS' and hospital_no='$hospital_no' and date(date_entry)='$effect_date'");
							if($stmt2->rowCount()>0){
									$NS_round='<strong style="color:#00F; background-color:#FF9">YES</strong>';
								}else{
								$NS_round='<strong style="color:#F00">NO</strong>';
							}
			
						echo '<tr><td>'.$n.'-</td><td>'.$DR_round.'</td><td>'.$NS_round.'</td><td>'.date("d,M y", strtotime($effect_date)).'</td></tr>';
						$n++;
						$effect_date = date("Y-m-d",strtotime($effect_date." +1 day"));
						///////////// end of effectv dates
					}
					echo '</table>';
					
					
										
					 ?></td>
                    </tr>
                    <?php $nn++; }?>
                    </tbody>
                    </table>
                    <hr>
<?php }else{ ?>

<div class="alert alert-danger">No record found ... </div>
<?php } ?>

</div>
</div>


</div>

</div>
</div>
 
 
</div>