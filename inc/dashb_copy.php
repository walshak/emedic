
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
$queue=$db->query("SELECT labrequest_no FROM lab_manage WHERE $section data_capture_status='queue'");						
	 ?>
     
<div class="row">
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
				
	//$myappr=$db->query("SELECT sn FROM lab_manage WHERE $section data_capture_status='approve' and MONTH(result_date)='$mth' and approved_by='$fullname'");
	//	$myappr=$myappr->rowCount();
		//echo 'dddd';
	//$myInvsti=$db->query("SELECT sn FROM lab_result WHERE MONTH(result_date)='$mth' and lab_sci_name='$fullname'");
		//$myInvsti=$myInvsti->rowCount();						
		
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
                    
                    
                    
<?php 
				
if ($_SESSION['speciality']=='Administrator'){

$stmtExp=$db->query("SELECT expire_date FROM lab_stocks where expire_date<'$setdate'");
$stmtOrder=$db->query("SELECT reorder_level FROM lab_stocks where qty<=reorder_level");
?>                   
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-primary pull-right">Today</span>
                                <h5>Consumable Alerts</h5>
                            </div>
                            <div class="ibox-content">
                                <h2 class="no-margins">
												<?php if($stmtExp->rowCount()>0){?>
                                		<span style="color:#F00">Expired</span><?php }else{?>Expired<?php }?>
                                        				 / <?php echo $stmtExp->rowCount(); ?>
                                                         <br>
                                         
													   <?php if($stmtOrder->rowCount()>0){?>
                                		<span style="color:#F00">Re/Order/Level</span><?php }else{?>Re/Order/Level<?php }?>
                                      			 /<?php echo $stmtOrder->rowCount(); ?>
                                 </h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-danger pull-right">Since Inception</span>
                                <h5>Credit Requests</h5>
                            </div>

<?php
//// approve
				$TotalPay=0;
    $stmt_cr=$db->query("SELECT pay FROM patient_ap_services WHERE (serv_group='Laboratory' or serv_group='Radiology') and cr='1' and paystatus='0'");						
	 if($stmt_cr->rowCount()>0){
		 $c=$stmt_cr->rowCount();
		 while($roww=$stmt_cr->fetch(PDO::FETCH_ASSOC)){
			 $TotalPay=$TotalPay+$roww['pay'];
			 }
	}
	 ?> 
                            <div class="ibox-content">
                                <h1 class="no-margins"><?php echo $c; ?> | <?php if($c==0){echo "Zero Naira";}
								else{echo number_format($TotalPay, 0, '.', ',');} ?></h1>
                                <?php if($TotalPay>0){?>
                                <div class="stat-percent font-bold text-danger"><i class="fa fa-level-up"></i></div>
                                <?php }else{ ?>
                                <div class="stat-percent font-bold text-success"><i class="fa fa-level-down"></i></div>
                                <?php } ?>
                                <small>Counts & Amount Accrued =N= </small>
                            </div>
                        </div>
            </div>
            
   <?php } ?>         
           
  <?php if ($_SESSION['speciality']=='Receptionist'){ ?>
  
  
 <div class="col-lg-6">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <span class="label label-success pull-right"></span>
                                <h5>Transactions</h5>
                            </div>
                            <div class="ibox-content">
                                                       <table width="100%"><tr><td>
<a class="btn btn-app add_new_sale" data-toggle="modal" data-target="#myModal5" ><strong>New Patient</strong></a>
             										</td>
                                            <td align="">
                              <a href="xsale.php" class="btn btn-app"><strong>New Request</strong></a>
									</td>
                                                                           <td align="">
                               <a href="mgt.php" class="btn btn-app"><strong>Investigations</strong></a>
									</td>
                                    <td align="">
                     <a href="../billing/index.php?invest" class="btn btn-app"><strong style="color:#F30">Biller</strong></a>                               
									</td>
                                    </tr></table>   
                            </div>
                            
                        </div>
                    </div>
                    
  
  <?php } ?>
                   
            
        </div>
        
        
        <div class="row">
                    <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Messages</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                    <a class="close-link">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            </div>
                            <?php  
							$usern=$_SESSION['username'];  
	 $stmt_inbox=$db->query("SELECT * FROM mails WHERE on_contact_username='$usern' and mail_status='send' and read_status='0' order by sn desc limit 10");						
	 $newemail=$stmt_inbox->rowCount();
	 ?>		
     				<?php if($newemail>0){?>
                            <div class="ibox-content ibox-heading">
                                <h3><i class="fa fa-envelope-o"></i> New messages</h3>
                 <small><i class="fa fa-tim"></i> You have <?php echo $newemail; ?> new messages <?php echo $draft; ?>.</small>
                            </div>
                            
                            <div class="ibox-content">
                                <div class="feed-activity-list">
					<?php while($row_i=$stmt_inbox->fetch(PDO::FETCH_ASSOC)){?>
                                    <div class="feed-element">
                                        <div>
            <small class="pull-right text-navy"><?php include_once("dd.php"); echo dateDiff($row_i['mail_date']) .' ago';?></small>
                                            <strong><?php echo $row_i['from_name'] ?></strong>
                       <div><?php if($row_i['subject']==''){echo 'No Subject';}else{echo substr($row_i['subject'], 0, 50);} ?></div>
                                            <small class="text-muted"> <?php echo getTheDay($row_i['mail_date']); ?></small>
                                        </div>
                                    </div>
					<?php } ?>                                    


                                </div>
                            </div>
                            <?php }else{ ?>
                            
                            <?php }?>
                        </div>

 

<?php
if($_SESSION['speciality']=="Administrator"){
 $sub=quickinfo($usern);
 
}?> 

<?php $sub=what_to_do($usern);?>


                    </div>
                    
                  
                    
                    
                    
<?php
							if($_SESSION['section']==""){
						 $lab_mgt_where="(section='Radiology' or section='Laboratory') ";
								}else{
									 $category=$_SESSION['section'];	
									$lab_mgt_where="section='$category'";
									}
?>                    

                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title">
                                        <h5>Investigation Requests</h5>
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
                      <?php
				$setdate=date("Y-m-d");	                    
      $stmt=$db->query("SELECT data_capture_status,request_date,labrequest_no,test_name,patient_name,patient,request_by,sms_status FROM  lab_manage WHERE date(request_date)='$setdate' and $lab_mgt_where order by sn DESC limit 35");						   						
				if($stmt->rowCount()>0){?>
                                       <table class="table table-hover no-margins">                 
                                                <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Time</th>
                                                    
                                                    <th>Investigation</th>
                                                    <th>Patient #</th>
                                                    <th>Name</th>
                                                    <th>Requester</th>
                                                    <th>SMS</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                        while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) { 
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
<td><i class="fa fa-clock-o"></i>&nbsp; <?php echo date('H:i a', strtotime($roww['request_date']));?></td>
                                    
                                                        <td><?php echo $roww['test_name']; ?></td>
                                                        <td><?php echo $roww['patient']; ?></td>
                                                        <td><?php
															$part=explode(" ",$roww['patient_name']); echo $part[0] . ' ';
			if(trim($part[1])!=''){echo substr($part[1], 0, 1) . '. ' ;} ?></td>
                                                        <td><?php
														if($roww['request_by']!=''){
		$part=explode(" ",$roww['request_by']); echo $part[0] . ' ' . substr($part[1], 0, 1) . '. ' ; }?>													
														</td>
                                                        <td><?php if($roww['sms_status']=='0'){?><i class="fa fa-times"></i> <?php }else{?><i class="fa fa-send"></i> <?php }?> </td>
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



<?php function what_to_do($usern){
include("../Connections/Conn.php")	
?>
                         
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title">
                                        <h5>My todo list</h5>
                                        <div class="ibox-tools">
      <a href="profile.php?profile=<?php echo $usern; ?>" class="btn btn-white btn-xs wat_to_do" ><strong>Add New</strong></a>                                  &nbsp;&nbsp;
     
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                            <a class="close-link">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content">
                                        <ul class="todo-list m-t small-list">
                      <?php
					  if($_GET['sn']){
						 $sn=$_GET['sn'];
						 $update="UPDATE admin_users_what_todo SET read_status=1 WHERE sn='$sn'"; 
						 $db->exec($update);
						  }
					  
      $stmt=$db->query("SELECT * FROM admin_users_what_todo WHERE username='$usern' order by sn DESC limit 10");						 				if($stmt->rowCount()>0){
		  			while($row_to=$stmt->fetch(PDO::FETCH_ASSOC)){ ?>
                      <li>
 <a href="#" class="check-link"><?php if($row_to['read_status']=='1'){?><i class="fa fa-check-square"><?php }else{?><i class="fa fa-square-o"><?php } ?></i> </a>
 
 <?php if($row_to['read_status']=='0'){?> 
 <a href="index.php?sn=<?php echo $row_to['sn']?>">[ Tick ]</a><?php }?>
    <span class="m-l-xs<?php if($row_to['read_status']=='1'){?> todo-completed <?php }?> "><?php echo $row_to['note']; ?></span>
                        <!--<small class="label label-primary"><i class="fa fa-clock-o"></i></small>-->
                        
                    </li>    
				<?php } 
				
	  }?>

                                        </ul>
                                    </div>
                                </div>
      
<?php } ?>



<?php function quickinfo($usern){
include("../Connections/Conn.php")	
?>
                         
                                <div class="ibox float-e-margins">
                                    <div class="ibox-title">
                                        <h5>Notices Dashboard</h5>
                                        <div class="ibox-tools">
                                        
                                            <a class="collapse-link">
                                                <i class="fa fa-chevron-up"></i>
                                            </a>
                                            <a class="close-link">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="ibox-content">
                                        <ul class="todo-list m-t small-list">
                      <?php
					  if($_GET['sn']){
						 $sn=$_GET['sn'];
						 $update="UPDATE admin_users_what_todo SET read_status=1 WHERE sn='$sn'"; 
						 $db->exec($update);
						  }
					  
      $stmt=$db->query("SELECT * FROM admin_users_what_todo WHERE username='$usern' order by sn DESC limit 10");						 				if($stmt->rowCount()>0){
		  			while($row_to=$stmt->fetch(PDO::FETCH_ASSOC)){ ?>
                      <li>
 <a href="#" class="check-link"><?php if($row_to['read_status']=='1'){?><i class="fa fa-check-square"><?php }else{?><i class="fa fa-square-o"><?php } ?></i> </a>
 
 <?php if($row_to['read_status']=='0'){?> 
 <a href="index.php?sn=<?php echo $row_to['sn']?>">[ Tick ]</a><?php }?>
    <span class="m-l-xs<?php if($row_to['read_status']=='1'){?> todo-completed <?php }?> "><?php echo $row_to['note']; ?></span>
                        <!--<small class="label label-primary"><i class="fa fa-clock-o"></i></small>-->
                        
                    </li>    
				<?php } 
				
	  }?>

                                        </ul>
                                    </div>
                                </div>
      
<?php } ?>



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
							while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
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
