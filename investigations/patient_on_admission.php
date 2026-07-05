<?php
session_start();

include("../Connections/Conn.php");

if(isset($_POST["patient_on_adm"])){
			
 $stmtssr=$db->query("SELECT room_bed,doc_incharge,floor, date_admit,app_no,hospital_no,dept_id FROM admission where adm_status='3'");
	if($stmtssr->rowCount()>0){?>
    <hr>                                    

	<div class="alert alert-info">
	<?php  echo '<strong>' .$stmtssr->rowCount() . ' - ' . 'Patient(s) On Admission' .'</strong>' ; ?>
	</div>                  

<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 13px; " >                 
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>PATIENTS DETAILS</th>
													<th width="20%">PENDING LIST</th>
													<th width="20%">COMPLETED LIST</th>
                                                    <th>ACCOUNT</th>
                                                    <th>DEPT</th>
													<th>INSURANCE</th>
                                                    <th>BED / ADMITTED BY</th>
                                                  </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
													//	$data = $stmtss->fetchAll(PDO::FETCH_ASSOC);
										while($rowwxx=$stmtssr->fetch(PDO::FETCH_ASSOC)) {   
											///while ($roww=$stmt_sub->fetch(PDO::FETCH_ASSOC)){
$hosp_no=$rowwxx['hospital_no'];											
$app_no=$rowwxx['app_no'];											
$dept_id=$rowwxx['dept_id'];	
											
$stmt_appt=$db->query("SELECT insurance_type,
								dept_name,
								patient_name
								FROM apptm where appt_no='$app_no'");
											$rwx_=$stmt_appt->fetch(PDO::FETCH_ASSOC);										
													?>
                                                       <tr>  
													<td><?php echo $n;?></td>
													<?php $patientt=$rowwxx['hospital_no']; ?>
													<td><?php echo $patientt .  '<br>' . $rwx_['patient_name']; 
											
														$patient_name = $rwx_['patient_name'];
														
																			$lab_mgt_where = '';
								$setdates ='';
								$data_capture_status='';
											if($app_no==''){$app_no=' ';}
													$appt_no_=" and app_no='$app_no'";
											
								$query=list_test($db,$app_no,$hosp_no,$lab_mgt_where,$setdates,$data_capture_status,$appt_no_);
											$requesting_physician=$query[1];
											$request_by=$query[2];
											$list_tests_sn=$query[3];
						if($requesting_physician!=''){
							  $RQ=$requesting_physician;
									  }else{
								  $RQ=$request_by;
							} 
														
							  	 $image_list=$query[6]; //// image
										 $lab_list=$query[7]; //// lab
																		  
									 $f=$query[5];
									 $g=$query[4];
											
										
													?>
														
														
  <br>													
  <input type="button" name="" value="View Investigation" data-target="#modal" id="<?php 
				echo $app_no .'____' . $hosp_no .'____' .$list_tests_sn.'____' .$list_tests.'____' .$patient_name.'____new____' .$type_patient; ?>" 
		 class="btn btn-success btn-sm enter_results" <?php if($f=='' and $g==''){?> disabled <?php } ?>
				 <?php if($_SESSION['speciality']=='Administrator' or $_SESSION['speciality']=='Receptionist'){ ?> disabled <?php } ?> />
														
														   </td>
													<td>
		<?php					
			
										if($f!=''){	
											echo $f=$query[5];
											
		
	if($RQ!=''){
		$part=explode(" ",$RQ); 
		echo '<br><strong>Requested by: </strong>'.$part[0] . ' ' . substr($part[1], 0, 1) . '. ' ;
		}											
		
		
										}else{
									echo 'No Investigation(s)';
								}

										$list_tests=substr_replace($list_tests, "", -1); 
										
														?>

														
						
														
										</td>	   
													<td><?php echo $query[4]; ?></td>	   
													<td>
												<?php
 $stmtss=$db->query("SELECT sum(pay) as total_pay FROM patient_ap_services 
 	WHERE cat_type='Laboratory' and paystatus='0' and cr='1' and hospital_no='$hosp_no'");
	if($stmtss->rowCount()>0){
		$rowx=$stmtss->fetch(PDO::FETCH_ASSOC)	;																	  
		$total_pay=$rowx['total_pay'];
	}else{	
		$total_pay=0;
	}	
		
											
$stmt=$db->query("SELECT 
sum(dr_amt) as TOTAL_DEBITS, 
sum(cr_amt) as TOTAL_CREDITS
FROM chart_ledger WHERE hospital_no='$hosp_no' and account_no='2121'");
if($stmt->rowCount()>0){
$row=$stmt->fetch(PDO::FETCH_ASSOC);
$TOTAL_CREDITS=$row['TOTAL_CREDITS'];
$TOTAL_DEBITS=$row['TOTAL_DEBITS'];
	$deposit = $TOTAL_CREDITS - $TOTAL_DEBITS;	
	echo '<strong>CURRENT DEPOSIT: '. number_format($current_balance,2).'</strong>';
}else{
	echo '<strong>CURRENT DEPOSIT: Zero Naira</strong>';
	$deposit = 0;
}
?>
														
	<hr><strong style='color:<?php if($total_pay > 0) { ?>red<?php } ?>'>BILLED/CREDITS: </strong><br>
											
	<?php
		echo '=N=' . number_format($total_pay);
	?>
														   
													</td>	   
<?php
			
												$insurance=$rwx_['insurance_type'];
												///$department=$rwx_['department'];
											
$stmt_dep=$db->query("SELECT department FROM department WHERE sn='$dept_id'");
$rwDep=$stmt_dep->fetch(PDO::FETCH_ASSOC);
		$department=$rwDep['department'];
				///if($deposit==0){											
											
												$room_bed=$rowwxx['room_bed'];
												$floor=$rowwxx['floor'];
												$date_admit=$rowwxx['date_admit'];
											
												
								
			
?>
	
	
	

														   <td><?php echo $department; ?></td>
														   <td><?php echo $insurance; ?></td>
														   <td><?php echo $floor .':' . $room_bed .'<br>' . '<strong>Doctor: </strong>' . $rowwxx['doc_incharge']; ?>
															<br>  
															<br>  
	<?php	
		$setdate=date('Y-m-d H:i:s');											
		$date1 = new DateTime($setdate);
		$date2 = new DateTime($date_admit);
		$diff = $date2->diff($date1);
		$day=$diff->format('%a');
		$hr= $diff->format('%h');

		echo ''. $diff->format('<strong style="font-size:15px">%a</strong> Day(s)<strong style="font-size:15px"> %h</strong> hr(s) / <strong>On-Admission</strong>') . '<br><strong style="font-size:14px; color:#C60">
		Date Admitted:</strong>
		<strong style="font-size:14px;">'. date('d M,Y h:i:s a',strtotime($date_admit)) .'</strong>'; ?>														  
														   </td><td></td>

                                                        </tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                        
                                                </tbody>
                                                </table>	
		<?php }else{ ?>
		<br>
		<div class="alert alert-danger">No Record(s) Available!</div>
			<?php } 
	

}

function list_test($db,$app_no,$hosp_no,$lab_mgt_where,$setdates,$data_capture_status,$appt_no_){
	
	$list_tests='';				
	$list_tests_pending='';				
	$list_tests_done='';				
	$list_tests_sn='';				
	$list_test_done_image='';				
	$list_test_done_lab='';				
         $stmtx=$db->query("SELECT sn,test_name,request_by,requesting_physician,
		 business_service_center,test_id,section,request_date,labrequest_no,request_by,
		 request_note,requesting_physician,lab_combos,preferred_specimen,collected_specimen, 
		 result_date,entered_by,data_capture_status FROM lab_manage 
		 WHERE patient='$hosp_no' $appt_no_ $setdates order by sn DESC");
	
			while($roww_ = $stmtx->fetch(PDO::FETCH_ASSOC)){
	
						//foreach ($data_ as $key_ => $roww_) {	
			
			$Current_date=date("Y-m-d");			
			$date1 = new DateTime($Current_date);
			$date2 = new DateTime($roww_['result_date']);
			$diff = $date2->diff($date1);	
			$day=$diff->format('%a');	
							
							
							
	$detail=$roww_['sn'].'----'.$roww_['test_name'].'----'.$roww_['business_service_center'].'----'.
		$roww_['test_id'].'----'.$roww_['section'].'----'.$roww_['request_date'].'----'.$roww_['labrequest_no'].'----'.
		$roww_['request_by'].'----'.$roww_['requesting_physician'].'----'.$roww_['lab_combos'].'----'.$roww_['preferred_specimen'].'----'.
		$roww_['result_date'].'----'.$roww_['entered_by'].'----'.$roww_['data_capture_status'].'----'.$day.'----'.$roww_['request_note'].'----'.
		$roww_['collected_specimen'];
							
	///or section='Laboratory') and ";						
				///if($_SESSION['speciality']=='Administrator' or $_SESSION['speciality']=='Receptionist'){	
										
										
										//list_test_done_image
										//	
											
			if($_SESSION['section']=='Laboratory'){
				
				if($roww_['section']=='Radiology'){
					$list_test_done_image.=$roww_['sn'].',';
				}
				
				if($roww_['section']=='Laboratory'){
					
					if($roww_['data_capture_status'] =='queue' or $roww_['data_capture_status'] =='specimen' or $roww_['data_capture_status'] =='capture'){
								$list_tests_pending.=$roww_['test_name'].',';
							}elseif($roww_['data_capture_status'] =='result' or $roww_['data_capture_status'] =='approve'){
								$list_tests_done.=$roww_['test_name'].',';
						}
					$list_tests_sn.=$detail.',';
				}
				
			}elseif($_SESSION['section']=='Radiology'){
				
				if($roww_['section']=='Laboratory'){
					$list_test_done_lab.=$roww_['sn'].',';
				}
				
				if($roww_['section']=='Radiology'){

						if($roww_['data_capture_status'] =='queue' or $roww_['data_capture_status'] =='specimen' or $roww_['data_capture_status'] =='capture'){
									$list_tests_pending.=$roww_['test_name'].',';
								}elseif($roww_['data_capture_status'] =='result' or $roww_['data_capture_status'] =='approve'){
									$list_tests_done.=$roww_['test_name'].',';
							}
					$list_tests_sn.=$detail.',';
				}
			}
										
										

										
													
							$requesting_physician=$roww_['requesting_physician'];
							$request_by=$roww_['request_by'];
			
				}	
	return array($list_tests,$requesting_physician,$request_by,$list_tests_sn,$list_tests_done,
				 $list_tests_pending,$list_test_done_image,$list_test_done_lab);
	
}

?>

