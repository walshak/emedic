<?php

if (!empty($_REQUEST['item'])) {

	$lg_ref_no = time() . $emr;
	$list = $_POST["item"];
	$batch_name_array = explode(",", $list);
	$result_count = count($batch_name_array);
	$sv = 0;

	try {
		// Start transaction
		$db->beginTransaction();

		if ($v_discount > 0) {
			include('ClearVoucher.php');
		}


		for ($i = 0; $i < $result_count; $i++) {
			$inv_id = $batch_name_array[$i];
			$break = explode("__", $inv_id);

			// Extract values from $break
			$sale_sn = $break[0];
			$sn = $break[0];
			$item_services = $break[2];
			$pay = $break[1];
			$discount = !empty($break[4]) ? $break[4] : 0;
			$charge   = !empty($break[5]) ? $break[5] : 0;
			$dura = $break[6];
			$post_type = $break[7];
			$count_bal = $break[8];
			$dsc_chr_set = $break[9];
			$cat_type = $break[10];
			$cr = $break[11];
			$service_group = $break[12];
			$departmentName = $break[13];
			$service_type = $break[14];
			$sn_service = $break[15];

			$credit_gl_account = null;

			// Step 1: Try to get credit account based on department name
			if (!empty($departmentName)) {
				$stmt = $db->prepare("
							SELECT account_code 
							FROM chart_accounts 
							WHERE account_name = :account_name
							LIMIT 1	");
				$stmt->execute([':account_name' => $departmentName]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);

				if ($row) {
					$credit_gl_account = $row['account_code'];
				}
			}

			// Step 2: Fallback using service group/category logic if not found
			if ($credit_gl_account === null) {
				$credit_gl_account = getCreditGLAccount($db, $service_group, $cat_type);
			}


			$general_credit_limit =	$_SESSION['credit_limit_status'];
			$items = call_current_balance($db, $emr, $general_credit_limit);
			$MYwallet_amount   = $items["current_balance"];

			// Check if payment is greater than zero
			if ($pay > 0 && $MYwallet_amount >= $pay) {

				$stmt_checkExist = $db->query("SELECT drug_sn FROM patient_ap_services WHERE sn='$sale_sn' and paystatus=0 and pay>0");
				if ($stmt_checkExist->rowCount() > 0) {
					$row__ = $stmt_checkExist->fetch(PDO::FETCH_ASSOC);
					$drug_sn = $row__['drug_sn'];

					if ($credit_gl_account != '') {
						// Handle charge and discount logic
						if ($dsc_chr_set == 1 && $charge > 0) {
							$discount_pay = $pay - $charge;
							$cr_amt = $charge;
							$bank_name = '';
							$CREDIT_ENTRY_2 = billing(
								$db,
								$emr,
								$emr,
								$insurance_no,
								$ref_value,
								$sale_sn,
								$item_services,
								'CREDIT',
								0,
								$cr_amt,
								$bal,
								$value_date,
								$auth_code,
								$bank_name,
								$credit_gl_account_charge,
								$lg_ref_no,
								0
							);

							if ($CREDIT_ENTRY_2 !== 'success') {
								throw new Exception('Error CREDIT ENTRY ');
							}
						} elseif ($discount == 0) {
							$CREDIT_ENTRY_2 = 'success';
							$discount_pay = $pay;
						}

						if ($dsc_chr_set == 1 && $discount > 0) {

							$discount_pay = $pay + $discount;
							$dr_amt = $discount;

							$DEBIT_ENTRY_2 = billing(
								$db,
								$emr,
								$emr,
								$discount_insurance_no,
								$ref_value,
								$sale_sn,
								$item_services,
								'DEBIT',
								$dr_amt,
								0,
								$bal,
								$value_date,
								$auth_code,
								$bank_name,
								$debit_gl_account_discount,
								$lg_ref_no,
								0
							);
							if ($DEBIT_ENTRY_2 !== 'success') {
								throw new Exception('Error DEBIT DISCOUNT ERROR ');
							}
						} elseif ($charge == 0) {
							$DEBIT_ENTRY_2 = 'success';
							$discount_pay = $pay;
						}

						// Main credit entry
						$empty = '';
						$cr_amt = $discount_pay;
						$CREDIT_ENTRY = billing(
							$db,
							$emr,
							$emr,
							$insurance_no,
							$ref_value,
							$sale_sn,
							$item_services,
							'CREDIT',
							0,
							$cr_amt,
							$bal,
							$value_date,
							$empty,
							$bank_name,
							$credit_gl_account,
							$lg_ref_no,
							0
						);

						// Main debit entry
						$dr_amt = $pay;
						$DEBIT_ENTRY = billing(
							$db,
							$emr,
							$emr,
							$insurance_no,
							$ref_value,
							$sale_sn,
							$item_services,
							'DEBIT',
							$dr_amt,
							0,
							$bal,
							$value_date,
							$auth_code,
							$bank_name,
							'2121',
							$lg_ref_no,
							0
						);

						if ($DEBIT_ENTRY !== 'success') {
							throw new Exception('Error DEBIT WALLET ');
						}
						// Check if all entries were successful
						$setdate = $value_date . ' ' . date('H:i');
						$wallet_debt_bill_to_acct = 'WALET'; // Means PAY FROM WALLET
						$zero = '0';
						$one = '1';
						$pay_mode = 'cash';
						// Update patient_ap_services
						$updateSQL = "UPDATE patient_ap_services 
                                SET paystatus = :paystatus,
                                    pay_mode = :pay_mode,
                                    cr = :cr,
                                    transact_date = :transact_date,
                                    invoice_by = :invoice_by,
                                    pay = :pay,
                                    discount = :discount,
                                    add_charge = :add_charge,
                                    payment_remarks = :payment_remarks,
                                    ledger_TX = :ledger_TX,
                                    wallet_debt_bill_to_acct = :wallet_debt_bill_to_acct,
                                    who_process_paystatus = :who_process_paystatus
                                WHERE hospital_no = :hospital_no
                                  AND sn = :sn
                                  AND paystatus =0";

						// Prepare the statement
						$stmt = $db->prepare($updateSQL);

						// Bind parameters
						$stmt->bindValue(':paystatus', $one, PDO::PARAM_INT);
						$stmt->bindValue(':pay_mode', $pay_mode, PDO::PARAM_INT);
						$stmt->bindValue(':cr', $zero, PDO::PARAM_INT);
						$stmt->bindValue(':transact_date', $setdate, PDO::PARAM_STR);
						$stmt->bindParam(':invoice_by', $_SESSION['fullname'], PDO::PARAM_STR);
						$stmt->bindValue(':pay', $pay, PDO::PARAM_STR);
						$stmt->bindValue(':discount', $discount, PDO::PARAM_STR);
						$stmt->bindValue(':add_charge', $charge, PDO::PARAM_STR);
						$stmt->bindValue(':payment_remarks', $payment_remarks, PDO::PARAM_STR);
						$stmt->bindValue(':ledger_TX', $lg_ref_no, PDO::PARAM_STR);
						$stmt->bindValue(':wallet_debt_bill_to_acct', $wallet_debt_bill_to_acct, PDO::PARAM_STR);
						$stmt->bindValue(':who_process_paystatus', $_SESSION['EmployeeCode'], PDO::PARAM_STR);
						$stmt->bindValue(':hospital_no', $emr, PDO::PARAM_STR);
						$stmt->bindValue(':sn', $sale_sn, PDO::PARAM_STR);

						// Execute the update statement
						$result = $stmt->execute();

						if ($result) {
							$insertSQL2 = "INSERT INTO saleprint(hos_no, sale_no) VALUES (:hos_no, :sale_no)";
							$stmt2 = $db->prepare($insertSQL2);
							$stmt2->bindValue(':hos_no', $emr, PDO::PARAM_STR);
							$stmt2->bindValue(':sale_no', $sale_sn, PDO::PARAM_STR);
							$stmt2->execute();

							if ($dsc_chr_set == 1) {
								if ($dura == 'Once') {
									$status = 'Finish';
									$status2 = '1';
									$count_bal = '';
								} elseif ($dura == 'Limited') {
									$count_bal = $count_bal - 1;
									if ($count_bal <= 0) {
										$status = 'Finish';
										$status2 = '1';
									} else {
										$status = 'On-going';
										$status2 = '0';
									}
								} elseif ($dura == 'Always') {
									$status = 'On-going';
									$status2 = '0';
									$count_bal = '';
								}

								if ($service_type == 'SPECIFY') {
									$updateSQL = "UPDATE patient_discount_services 
										SET status = ?, count_bal = ? 
										WHERE sn = ?";
									$stmt = $db->prepare($updateSQL);
									$stmt->execute(array($status, $count_bal, $sn_service));
								} else {
									$updateSQL = "UPDATE patient_discount 
										SET status = ?, count_bal = ? 
										WHERE individual_group_no = ?";
									$stmt = $db->prepare($updateSQL);
									$stmt->execute(array($status, $count_bal, $emr));
								}
							}

							if ($service_group == 'Laboratory' or $service_group == 'Radiology') {
								$updateSQL3 = "UPDATE lab_manage SET result_on_credit = 0,date_time_pay = :date_time_pay WHERE patient = :hospital_no                                AND labrequest_no = :labrequest_no";
								$stmt = $db->prepare($updateSQL3);
								$stmt->bindParam(':date_time_pay', $setdate, PDO::PARAM_STR);
								$stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
								$stmt->bindParam(':labrequest_no', $drug_sn, PDO::PARAM_STR);
								$stmt->execute();
							}
						} else {
							// Rollback transaction if update fails
							throw new Exception('Update failed for patient_ap_services');
						}
					} else {
						throw new Exception('Account or Ledger Error!');
					}
				} else {
					throw new Exception('Already Paid!');
				}
			} else {
				throw new Exception('Unable to Continue Transactions. Insufficient Amount!');
			}
		}

		// Commit the transaction if all operations are successful
		$db->commit();

		$response_main['message'] = 'Item(s) Has been Posted Successfully!';
		$response_main['status'] = '0';
		echo json_encode($response_main);
		exit;
	} catch (Exception $e) {
		// Rollback the transaction on error
		$db->rollBack();

		// Log the error message for debugging
		error_log($e->getMessage());

		// Return error response
		$response_main['message'] = 'Transaction Error! Something Went Wrong: ' . $e->getMessage();
		$response_main['status'] = '1';
		echo json_encode($response_main);
		exit;
	}
}
