<?php

if (!empty($_REQUEST['item'])) {
	$emr = $_POST['wallet_payeee'];
	$patient_beneficiary = $_POST['emr'];
	$empty = null;

	$general_credit_limit =	$_SESSION['credit_limit_status'];
	$items = call_current_balance($db, $emr, $general_credit_limit);

	$MYwallet_amount   = $items["current_balance"];
	$insurance_no      = $items['insurance_no'];
	$wallet_account = '2121';
	try {

		// Start a transaction
		$db->beginTransaction();

		$lg_ref_no = time() . $emr;
		$list = $_POST["item"];
		$batch_name_array = explode(",", $list);
		$result_count = count($batch_name_array);


		$sum = 0; // Initialize total

		for ($i = 0; $i < $result_count; $i++) {
			$inv_id = $batch_name_array[$i];
			$break = explode("__", $inv_id);

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

			if ($pay > 0 && $pay <= $MYwallet_amount) {

				$stmt_ckhkStatus = $db->query("SELECT paystatus, hospital_no,drug_sn FROM patient_ap_services WHERE sn='$sale_sn'");
				if ($stmt_ckhkStatus->rowCount() > 0) {
					$row_checking = $stmt_ckhkStatus->fetch(PDO::FETCH_ASSOC);
					$paystatus = $row_checking['paystatus'];
					$hosiptal_no_benefactor = $row_checking['hospital_no'];
					$drug_sn = $row_checking['drug_sn'];

					$item_services .= ' //Paid for EMR: ' . $hosiptal_no_benefactor;

					if ($paystatus == 0) {

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

						if ($credit_gl_account && $wallet_account) {
							// Perform the CREDIT entry
							$CREDIT_ENTRY = billing($db, $emr, $emr, $insurance_no, $ref_value, $sale_sn, $item_services, 'CREDIT', 0, $pay, $bal, $value_date, $empty, $bank_name, $credit_gl_account, $lg_ref_no, 0);

							if ($CREDIT_ENTRY !== 'success') {
								throw new Exception('Error CREDIT SALES ');
							}
							// Perform the DEBIT entry
							$DEBIT_ENTRY = billing($db, $emr, $emr, $insurance_no, $ref_value, $sale_sn, $item_services, 'DEBIT', $pay, 0, $bal, $value_date, $auth_code, $bank_name, $wallet_account, $lg_ref_no, 0);

							if ($DEBIT_ENTRY !== 'success') {
								throw new Exception('Error DEBIT SALES ');
							}
							$setdate = $value_date . ' ' . date('H:i');
							$wallet_debt_bill_to_acct = 'PWALET'; // Pay from another wallet
							$one = '1';
							$zero = '0';

							// Update patient_ap_services
							$updateSQL = "UPDATE patient_ap_services 
                                    SET paystatus = :paystatus,
                                        cr = :cr,
                                        transact_date = :transact_date,
                                        pay = :pay,
                                        discount = :discount,
                                        add_charge = :add_charge,
                                        payment_remarks = :payment_remarks,
                                        ledger_TX = :ledger_TX,
                                        wallet_payee = :wallet_payee,
                                        wallet_debt_bill_to_acct = :wallet_debt_bill_to_acct,
                                        who_process_paystatus = :who_process_paystatus
                                    WHERE hospital_no = :hospital_no
                                      AND sn = :sn
                                      AND paystatus = 0";

							$stmt = $db->prepare($updateSQL);
							$stmt->bindValue(':paystatus', $one, PDO::PARAM_INT); // Assuming paystatus is an integer
							$stmt->bindValue(':cr', $zero, PDO::PARAM_INT); // Assuming cr is an integer
							$stmt->bindValue(':transact_date', $setdate, PDO::PARAM_STR); // Assuming setdate is a string
							$stmt->bindValue(':pay', $pay, PDO::PARAM_STR); // Assuming pay is a string
							$stmt->bindValue(':discount', $discount, PDO::PARAM_STR); // Assuming discount is a string
							$stmt->bindValue(':add_charge', $charge, PDO::PARAM_STR); // Assuming charge is a string
							$stmt->bindValue(':payment_remarks', $payment_remarks, PDO::PARAM_STR); // Assuming payment_remarks is a string
							$stmt->bindValue(':ledger_TX', $lg_ref_no, PDO::PARAM_STR); // Assuming lg_ref_no is a string
							$stmt->bindValue(':wallet_payee', $emr, PDO::PARAM_STR); // Assuming emr is a string
							$stmt->bindValue(':wallet_debt_bill_to_acct', $wallet_debt_bill_to_acct, PDO::PARAM_STR); // Assuming wallet_debt_bill_to_acct is a string
							$stmt->bindValue(':who_process_paystatus', $_SESSION['EmployeeCode'], PDO::PARAM_STR); // Assuming $_SESSION['EmployeeCode'] is a string
							$stmt->bindValue(':hospital_no', $patient_beneficiary, PDO::PARAM_STR); // Assuming patient_beneficiary is a string
							$stmt->bindValue(':sn', $sn, PDO::PARAM_STR); // Assuming sn is a string

							// Execute the update
							$stmt->execute();

							if ($stmt->rowCount() > 0) {
								// Insert into saleprint table
								$insertSQL = "INSERT INTO saleprint (hos_no, sale_no) VALUES (:hos_no, :sale_no)";
								$stmt2 = $db->prepare($insertSQL);
								$stmt2->bindValue(':hos_no', $patient_beneficiary, PDO::PARAM_STR); // Assuming patient_beneficiary is a string
								$stmt2->bindValue(':sale_no', $sale_sn, PDO::PARAM_STR); // Assuming sale_sn is a string
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
										$updateSQL = "UPDATE patient_discount_services SET status='$status', count_bal='$count_bal' WHERE sn='$sn_service'";
										$db->exec($updateSQL);
									} else {
										$updateSQL = "UPDATE patient_discount SET status='$status', count_bal='$count_bal' WHERE individual_group_no='$emr'";
										$db->exec($updateSQL);
									}
								}


								if ($service_group == 'Laboratory' or $service_group == 'Radiology') {
									$updateSQL3 = "UPDATE lab_manage SET result_on_credit = 0,date_time_pay = :date_time_pay WHERE patient = :hospital_no                                AND labrequest_no = :labrequest_no";
									$stmt = $db->prepare($updateSQL3);
									$stmt->bindParam(':date_time_pay', $setdate, PDO::PARAM_STR);
									$stmt->bindParam(':hospital_no', $patient_beneficiary, PDO::PARAM_STR);
									$stmt->bindParam(':labrequest_no', $drug_sn, PDO::PARAM_STR);
									$stmt->execute();
								}
							} else {

								throw new Exception('Failed to update patient_ap_services.');
							}
						} else {
							throw new Exception('Credit GL account or wallet account is not set.');
						}
					} else {
						$response_main['message'] = 'Already Paid!';
						$response_main['status'] = '1';
						echo json_encode($response_main);
						exit;
					}
				} else {
					throw new Exception('No valid payment status found for the sale number.');
				}
			} else {
				///throw new Exception('Payment exceeds available wallet amount.');
				throw new Exception('Payment exceeds available wallet amount: ' . $MYwallet_amount . ' Total Sum: ' . $sum);
			}
		}

		// Commit the transaction if everything is successful
		$db->commit();

		$response_main['message'] = 'Item(s) Have Been Posted Successfully!';
		$response_main['status'] = '0';
		echo json_encode($response_main);
		exit;
	} catch (Exception $e) {
		// Rollback the transaction if an error occurred
		$db->rollBack();

		// Log the error message for debugging
		error_log($e->getMessage());

		$response_main['message'] = 'Transaction Error! ' . $e->getMessage();
		$response_main['status'] = '1';
		echo json_encode($response_main);
		exit;
	}
} else {
	$response_main['message'] = 'Item Does Not Exist!';
	$response_main['status'] = '1';
	echo json_encode($response_main);
	exit;
}
