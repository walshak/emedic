<?php

$labrequest_no=$_GET['clear'];	
	
			$stmt =$db->prepare("SELECT test_id FROM lab_manage WHERE labrequest_no=:labrequest_no and lab_combos=0");
			$stmt->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
			$stmt->execute();
				if($stmt->rowCount()==0){
		
				$stmt =$db->prepare("SELECT cat_type,item_services,prepared_by,created_by,hospital_no,app_no,cat_type,date_entry FROM patient_ap_services WHERE drug_sn=:labrequest_no");
				$stmt->bindValue(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
					$stmt->execute();
					if($stmt->rowCount()>0){
						$rwx=$stmt->fetch(PDO::FETCH_ASSOC);		
						$item_services=$rwx['item_services'];
						$prepared_by=$rwx['prepared_by'];
						$created_by=$rwx['created_by'];
						$hos_no=$rwx['hospital_no'];
						$app_no=$rwx['app_no'];
						$cat_type=$rwx['cat_type'];
						$date_entry=date( "Y-m-d", strtotime($rwx["date_entry"] ));
						
						
								/// get lab no   =====================
								$stmtxx =$db->prepare("SELECT sn,category,dept FROM lab_scan WHERE test=:item_services");
				$stmtxx->bindValue(':item_services', $item_services, PDO::PARAM_STR);
					$stmtxx->execute();
					if($stmtxx->rowCount()>0){
						$rwxx=$stmtxx->fetch(PDO::FETCH_ASSOC);		
						$test_id=$rwxx['sn'];
						$section=$rwxx['category'];
						$lab_cat=$rwxx['dept'];
					}
								///=====================================================
								
					////// get patient details
						
			$stmtxx =$db->prepare("SELECT patient_name,group_id FROM lab_manage 
			WHERE patient=:patient and date(request_date)=:request_date");
				$stmtxx->bindValue(':patient', $hos_no, PDO::PARAM_STR);
				$stmtxx->bindValue(':request_date', $date_entry, PDO::PARAM_STR);
					$stmtxx->execute();
					if($stmtxx->rowCount()>0){
						$rwxx=$stmtxx->fetch(PDO::FETCH_ASSOC);		
						$patient_name=$rwxx['patient_name'];
						$group_id=$rwxx['group_id'];
					}else{
				$stmtxx =$db->prepare("SELECT surname,fname,oname FROM enrollee 
			WHERE hospital_no=:hospital_no");
				$stmtxx->bindValue(':hospital_no', $hos_no, PDO::PARAM_STR);
					$stmtxx->execute();
					if($stmtxx->rowCount()>0){
						$rwxx=$stmtxx->fetch(PDO::FETCH_ASSOC);
						$patient_name=$rwxx['surname'] . ' ' . $rwxx['fname']. ' ' . $rwxx['oname'];
						$group_id=$hos_no . date('d');
					}	
						
				}
				///====================================================================
						
			
					//// INSERT NOW ----------------------
						$business_service_center ='IN';
					$sql = $db->prepare("INSERT INTO lab_manage (app_no,labrequest_no,patient,patient_name,test_id,test_name,lab_cat,section,group_id,business_service_center,request_date,	request_by,created_by) VALUES (:app_no,:labrequest_no,:patient,:patient_name,:test_id,:test_name,:lab_cat,:section,:group_id,:business_service_center,:request_date,:request_by,:created_by)");

									$sql->bindParam(':app_no', $app_no, PDO::PARAM_STR);
									$sql->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
									$sql->bindParam(':patient', $hos_no, PDO::PARAM_STR);
									$sql->bindParam(':patient_name', $patient_name, PDO::PARAM_STR);
									$sql->bindParam(':test_id', $test_id, PDO::PARAM_STR);
									$sql->bindParam(':test_name', $item_services, PDO::PARAM_STR);
									$sql->bindParam(':lab_cat', $lab_cat, PDO::PARAM_STR);
									$sql->bindParam(':section', $section, PDO::PARAM_STR);
									$sql->bindParam(':group_id', $group_id, PDO::PARAM_STR);
									$sql->bindParam(':business_service_center', $business_service_center, PDO::PARAM_STR);
									$sql->bindParam(':request_date', $date_entry, PDO::PARAM_STR);
									$sql->bindParam(':request_by', $prepared_by, PDO::PARAM_STR);
									$sql->bindParam(':created_by', $created_by, PDO::PARAM_STR);
									$sql->execute();

				}
		}


?>