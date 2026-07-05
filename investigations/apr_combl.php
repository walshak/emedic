<?php

	  
			/// GET INFORMATION / DETAILS			
						$stmt = $db->prepare("SELECT l.test_id, l.business_service_center, l.patient, l.patient_name, ls.category, ls.dept 
											  FROM lab_manage AS l 
											  INNER JOIN lab_scan AS ls ON l.test_id = ls.sn 
											  WHERE labrequest_no = :labrequest_no");
						
						$stmt->bindParam(':labrequest_no', $RQ_No, PDO::PARAM_STR);
						$stmt->execute();
						$row_lab = $stmt->fetch(PDO::FETCH_ASSOC);
						
						if ($row_lab) {
							$section = $row_lab["category"];
							$dept = $row_lab["dept"];
							$test_id = $row_lab["test_id"];
							$patient_id = $row_lab["patient"];
							$patient_name = $row_lab["patient_name"];
							$buz = $row_lab["business_service_center"];
						} 

							
				$stmtCnbl = $db->prepare("SELECT * FROM lab_test_consumble WHERE lab_test_no = :lab_test_no");
				$stmtCnbl->bindParam(':lab_test_no', $test_id, PDO::PARAM_STR);
				$stmtCnbl->execute();
				
				if ($stmtCnbl->rowCount() > 0) {
					while ($row_cons = $stmtCnbl->fetch(PDO::FETCH_ASSOC)) {
						$mgt_stock_id = $row_cons['consumable_no'];
						$qty_test = $row_cons['qty_test'];
						$posting_method = $row_cons['posting_method'];

						if ($posting_method==0){
							$stmt_check = $db->prepare("SELECT sale_sn FROM lab_stocks_manual_rq WHERE stock_sn = :stock_sn AND sale_sn = :sale_sn");
							$stmt_check->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
							$stmt_check->bindParam(':sale_sn', $RQ_No, PDO::PARAM_STR);
							$stmt_check->execute();
							
							if ($stmt_check->rowCount() == 0) {
								$inven_desc = 'Used: ' . $patient_id . '/' . $patient_name;
								$cust_patient_id = $patient_id;
								$sale_sn = $RQ_No;
								$captured_date = date("Y-m-d H:i:s");
								$enter_by = $_SESSION['fullname'];
							
								$stmt = $db->prepare("INSERT INTO lab_stocks_manual_rq (stock_sn, dept, section, sale_sn, inven_desc, cust_patient_id, cust_patient_type, enter_by, captured_date) 
													  VALUES (:stock_sn, :dept, :section, :sale_sn, :inven_desc, :cust_patient_id, :cust_patient_type, :enter_by, :captured_date)");
								$stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
								$stmt->bindParam(':dept', $dept, PDO::PARAM_STR);
								$stmt->bindParam(':section', $section, PDO::PARAM_STR);
								$stmt->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
								$stmt->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
								$stmt->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
								$stmt->bindParam(':cust_patient_type', $buz, PDO::PARAM_STR);
								$stmt->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
								$stmt->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
								$stmt->execute();
							}							
									
						}else{

							$stmt_check = $db->prepare("SELECT sale_sn FROM lab_stocks_inven_rq WHERE stock_sn = :stock_sn AND sale_sn = :sale_sn");
							$stmt_check->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
							$stmt_check->bindParam(':sale_sn', $RQ_No, PDO::PARAM_STR);
							$stmt_check->execute();
							
							if ($stmt_check->rowCount() == 0) {
								$stmt = $db->prepare("SELECT bal FROM lab_stocks_inven_rq WHERE stock_sn = :stock_sn AND section = :section AND dept = :dept ORDER BY sn DESC LIMIT 1");
								$stmt->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
								$stmt->bindParam(':section', $section, PDO::PARAM_STR);
								$stmt->bindParam(':dept', $dept, PDO::PARAM_STR);
								$stmt->execute();
							
								if ($stmt->rowCount() > 0) {
									$row_rstSelect = $stmt->fetch(PDO::FETCH_ASSOC);
									$c_qty = $row_rstSelect['bal'] - $qty_test;
							
									$qtyIN = 0;
									$qtyOUT = $qty_test;
									$inven_desc = 'Deduct: ' . $patient_id . '/' . $patient_name;
									$cust_patient_id = $patient_id;
									$sale_sn = $RQ_No;
									$captured_date = date("Y-m-d H:i:s");
									$enter_by = $_SESSION['fullname'];
									$return_status = 1;
									$batch = '';
							
									$stmt_insert = $db->prepare("INSERT INTO lab_stocks_inven_rq (stock_sn, dept, section, sale_sn, inven_desc, batch, qtyIN, qtyOUT, bal, cust_patient_id, cust_patient_type, return_status, enter_by, captured_date) 
																 VALUES (:stock_sn, :dept, :section, :sale_sn, :inven_desc, :batch, :qtyIN, :qtyOUT, :bal, :cust_patient_id, :cust_patient_type, :return_status, :enter_by, :captured_date)");
									$stmt_insert->bindParam(':stock_sn', $mgt_stock_id, PDO::PARAM_STR);
									$stmt_insert->bindParam(':dept', $dept, PDO::PARAM_STR);
									$stmt_insert->bindParam(':section', $section, PDO::PARAM_STR);
									$stmt_insert->bindParam(':sale_sn', $sale_sn, PDO::PARAM_STR);
									$stmt_insert->bindParam(':inven_desc', $inven_desc, PDO::PARAM_STR);
									$stmt_insert->bindParam(':batch', $batch, PDO::PARAM_STR);
									$stmt_insert->bindParam(':qtyIN', $qtyIN, PDO::PARAM_INT);
									$stmt_insert->bindParam(':qtyOUT', $qtyOUT, PDO::PARAM_INT);
									$stmt_insert->bindParam(':bal', $c_qty, PDO::PARAM_INT);
									$stmt_insert->bindParam(':cust_patient_id', $cust_patient_id, PDO::PARAM_STR);
									$stmt_insert->bindParam(':cust_patient_type', $buz, PDO::PARAM_STR);
									$stmt_insert->bindParam(':return_status', $return_status, PDO::PARAM_INT);
									$stmt_insert->bindParam(':enter_by', $enter_by, PDO::PARAM_STR);
									$stmt_insert->bindParam(':captured_date', $captured_date, PDO::PARAM_STR);
									$stmt_insert->execute();
								}
							}
													
					////   AUTO DEDUCTION
						}
		/// HAS BEEN DONE BEFORE////// CONTI
	}

/// END OF LOOPING
}


?>