
<?php

if ($_SESSION['section']=='Laboratory' or $_SESSION['section']=='Radiology'){
		$span=9;
}else{
	$span=3;
}
	if ($_SESSION['section']=='Laboratory'){
		$section="section='Laboratory' and ";
	}elseif($_SESSION['section']=='Laboratory'){
$section="section='Laboratory' and ";
	}else{
		$section='';	
	}


$setdate=date("Y-m-d");
$yr=date("Y");
$mth=date("m");
$queue=$db->query("SELECT l.labrequest_no 
	FROM lab_manage l 
	INNER JOIN patient_ap_services p 
	ON p.drug_sn=l.labrequest_no WHERE $section data_capture_status='queue' and p.paystatus=1");						
	 ?>
     
<div class="row">
	
           
  <?php if ($_SESSION['speciality']=='Receptionist' or $_SESSION['speciality']=='Administrator'){ ?>
  
  
 <div class="col-lg-6">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right"></span>
                                <h5>Transactions</h5>
                            </div>
                            <div class="ibox-content">
                                                       
<a class="btn btn-app add_new_sale" data-toggle="modal" data-target="#myModal5" ><strong style="color: blue;">NEW PATIENT</strong></a>
             								
                              <a href="xsale.php" class="btn btn-app"><strong style="color:darkgreen;">ADD REQUEST</strong></a>
                              <a href="xsale.php" class="btn btn-app"><strong style="color:brown;">PRINT</strong></a>
									
            		<?php if($_SESSION['bill']=='1'){?>}
                     <a href="../billing/index.php?invest" class="btn btn-app"><strong style="color:#F30">Biller</strong></a>                               
						<?php } ?>															   
                                    
                            </div>
                            
                        </div>
                    </div>
                    
  
  <?php } ?>	
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right">Since Inception</span>
                                <h5>Investigation(s)</h5>
                            </div>
                            <div class="ibox-content">
                                <h1 class="no-margins"><?php echo $queue->rowCount(); ?></h1>
                                <div class="stat-percent font-bold text-success"><i class="fa fa-bolt"></i></div>
                                <small>Total Request</small>
                            </div>
                        </div>
                    </div>
<?php
//// approve
			
$approve=$db->query("SELECT sn FROM lab_manage WHERE $section data_capture_status='approve' and date(result_date)='$setdate'");						
	$result=$db->query("SELECT sn FROM lab_manage WHERE $section (data_capture_status='specimen' or data_capture_status='capture' or data_capture_status='result')");
	
	if ($_SESSION['section']=='Laboratory' or $_SESSION['section']=='Radiology'){
			$fullname=$_SESSION['fullname'];
		$section=$_SESSION['section'];
				$section="section='$section' and ";
				
	$myappr=$db->query("SELECT sn FROM lab_manage WHERE $section data_capture_status='approve' and MONTH(result_date)='$mth' and approved_by='$fullname'");
		$myappr=$myappr->rowCount();
		
	$myInvsti=$db->query("SELECT sn FROM lab_result WHERE MONTH(result_date)='$mth' and lab_sci_name='$fullname'");
		$myInvsti=$myInvsti->rowCount();						
		
	}	
	
							
	 ?>                    
                    <div class="col-lg-<?php echo $span; ?>">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-info pull-right"></span>
                                <h5>Results/Approved Status</h5>
                            </div>
                            <div class="ibox-content">
                            <table width="100%"><tr><td align="center">
                                <h1 class="no-margins"><?php echo $result->rowCount(); ?></h1>
                                <small>Awaits Approval</small>
										</td>
                                        
                                     <td align="center">
                                <h1 class="no-margins"><?php echo $approve->rowCount(); ?></h1>
                                <small>Approved Today</small>
									</td>
                                  
<?php if ($_SESSION['section']=='Laboratory' or $_SESSION['section']=='Radiology'){ ?>  
                                
                                <td align="center">
                                <h1 class="no-margins"><?php echo $myappr; ?></h1>
                                <small>My Approval(s) <?php echo date("M, y"); ?></small>
                                </td>
                                
                                
                                <td align="center">
                                <h1 class="no-margins"><?php echo $myInvsti; ?></h1>
                                <small>My Investigations (Done) <?php echo date("M, y"); ?></small>
                                </td>
<?php } ?>                                
                                    
                                    </tr>
                                    </table>                                        

                            </div>
                        </div>
                    </div>
                    
                    
                    
  

                   
            
        </div>
        
        
        <div class="row">
                    
                    
                  
                    
                    
                    
<?php
							if($_SESSION['section']==""){
						 $lab_mgt_where="(section='Radiology' or section='Laboratory') ";
								}else{
									 $category=$_SESSION['section'];	
									$lab_mgt_where="section='$category'";
									}
?>                    
			
			

                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title">
                                        <h5>Investigation enquiry</h5>
                                        <div class="ibox-tools">
             <a href="index.php" class="btn btn-white btn-xs"><i class="fa fa-refresh"></i>&nbsp;&nbsp;Refresh to Current List</a>

                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                            <a class="close-link">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content">

                                        <div class="row">
                                            <div class="col-lg-12">
		<form action="index.php" method="post">		
		<table>	
			<tr>
				<td>
		 <label for="reg_input_no" class="">Search By Patient Name or Hospital No.</label>
                    <select name="patient"  class="chosen-select" style="width:350px;" >
                    <option selected="selected" value="">Search and Select Patient</option>
                    <?php 
$stmt=$db->query("SELECT DISTINCT patient, patient_name FROM lab_manage WHERE $lab_mgt_where order by sn DESC limit 1000");
				$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						foreach ($data as $key => $row) {	
						//while ($row=$stmt->fetch(PDO::FETCH_ASSOC)){ ?>
                    <option value="<?php echo $row["patient"];?>"><?php echo $row["patient"] .' '. $row["patient_name"]; ?></option>
                    <?php } ?>
                    </select>
					</td>
				<td>
							 <label >.</label><br>&nbsp;
				 <button class="btn btn-primary btn-sm" type="submit" name="apply_requests" >Apply</button>
				</td></tr></table>
			</form>
			<br>
                      <?php
												
					if(isset($_POST['apply_requests'])){
							 $hosp_no=$_POST['patient'];
								$add_on=" and patient='$hosp_no'";
						}else{
							$add_on=" and date(request_date)='$setdate'";					
					}
												
												
												
				$setdate=date("Y-m-d");	                    
      $stmt=$db->query("SELECT data_capture_status,request_date,labrequest_no,test_name,patient_name,patient,request_by,sms_status FROM  lab_manage WHERE $lab_mgt_where $add_on order by sn DESC limit 50");						   						
				if($stmt->rowCount()>0){?>
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 

                                                <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Date RQ.</th>
                                                    
                                                    <th>Investigation</th>
                                                    <th>Patient #</th>
                                                    <th>Name</th>
                                                    <th>Requester</th>
                                                   
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
						$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						foreach ($data as $key => $roww) {	
                                                      //  while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
                                                        ?>
                                                       <tr>  
                                     <td>
									 <?php 
		if($roww['data_capture_status']=='queue' or 
				$roww['data_capture_status']=='specimen' 
					or $roww['data_capture_status']=='result'){
						$status=1;
						}
		if($roww['data_capture_status']=='approve'){
						$status=2;
						}
		if($roww['data_capture_status']=='cancel'){
						$status=3;
						}
		if($roww['data_capture_status']=='reject' or $roww['data_capture_status']=='delete'){
						$status=4;
						}	
												
			?>
						
			<?php if($status==4){ ?><span class="label label-warning">Canceled</span><?php }?>	
            <?php if($status==3){ ?><span class="label label-warning">Reject</span><?php }?>	
            <?php if($status==2){ ?><span class="label label-primary">Completed</span> <?php }?>	
            <?php if($status==1){ ?><small>Pending...</small>  <?php }?>											
</td>
<td><i class="fa fa-clock-o"></i>&nbsp; <?php echo date('d M y', strtotime($roww['request_date']));?></td>
                                    
                                                        <td><?php echo $roww['test_name']; ?></td>
                                                        <td><?php echo $roww['patient']; ?></td>
                                                        <td><?php
															$part=explode(" ",$roww['patient_name']); echo $part[0] . ' ';
			if(trim($part[1])!=''){echo substr($part[1], 0, 1) . '. ' ;} ?></td>
                                                        <td><?php
														if($roww['request_by']!=''){
		$part=explode(" ",$roww['request_by']); echo $part[0] . ' ' . substr($part[1], 0, 1) . '. ' ; }?>													
														</td>
                                               
                                                        </tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                        
                                                </tbody>
                                                </table>
                                             
                                        <?php }else{echo '<br>No Requests Available!';} ?>       
                                       </div>
                                            
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                

<?php

//// chech maintenance status

	 //$todays_date=date("Y-m-d");
     //$stmt_chk=$db->query("SELECT task_name FROM invsti_dailyCheck WHERE date(date_check)='$todays_date' and task_name='maintenance'");						 				
	//if($stmt_chk->rowCount()==0){
			
			    $stmt=$db->query("SELECT * FROM invsti_machine WHERE due_status='0'");
			 			if($stmt->rowCount()>0){
								while($rowx=$stmt->fetch(PDO::FETCH_ASSOC)){
									$s_type=$rowx['service_every_type'];
									$next_due_date=$rowx['next_due_date'];
									$investi_count=$rowx['investigation_count'];
									$s_count=$rowx['service_every_count'];
									$device_no=$rowx['sn'];
									
										if($s_type=='Days' and $next_due_date<date("Y-m-d")){
									$update="UPDATE invsti_machine SET due_status='1' WHERE sn='$device_no'";
									$db->exec($update);											
										}

										if($s_type=='Investigation'){
		//---------------------------------------------------------------------------------------------
						$Cur_date=date("Y-m-d");
					$stmt=$db->query("SELECT * FROM invsti_machine_settings WHERE device_no='$device_no'");
						if($stmt->rowCount()>0){
								$total=0;
							 	 $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
						foreach ($data as $key => $row) {
							
							//while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
								$investigation_name=$row['investigation_name'];
								
								/// get total lab manage table for approve, result, rejected
							$stmt3=$db->query("SELECT sn FROM lab_manage WHERE maintenance_lock='0' and test_name='$investigation_name' and date(request_date)='$Cur_date' and (data_capture_status='result' or data_capture_status='reject' or data_capture_status='approve')");
								if($stmt3->rowCount()>0){
									$total=$total+$stmt3->rowCount();
										while($rowxx=$stmt3->fetch(PDO::FETCH_ASSOC)){
												$sn=$rowxx['sn'];
												$update="UPDATE lab_manage SET maintenance_lock='1' WHERE sn='$sn'";
												$db->exec($update);	
										}
									
								}
							}
							/// end of looopiiiingggggggggggggggggggggggggggg
							// calculate and decide here
							$TOTAL_invsti=$investi_count+$total;
									if($TOTAL_invsti>=$s_count){
				$update="UPDATE invsti_machine SET due_status='1', investigation_count='$TOTAL_invsti' WHERE sn='$device_no'";
									$db->exec($update);											
										}else{
								$update="UPDATE invsti_machine SET investigation_count='$TOTAL_invsti' WHERE sn='$device_no'";
									$db->exec($update);	
										}
						}
		///============================================================================================															
											
										}
									
								}
										
						}
			 		
	
//	$update="UPDATE invsti_dailyCheck SET date_check='$todays_date'";
//	$db->exec($update);

//}
                   


//============================================================


?>

<?php include("investigations/search_modal.php") ?>
