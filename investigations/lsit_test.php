<?php


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
		 WHERE patient='$hosp_no' $appt_no_ $setdates order by sn DESC");///,  
		// WHERE $lab_mgt_where (data_capture_status='specimen' or data_capture_status='queue') and patient=%s $appt_no_ $setdates order by sn DESC",  

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