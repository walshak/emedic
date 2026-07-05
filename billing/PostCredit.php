<?php
if (!empty($_REQUEST['item'])) {

	$zero = 0;
	$one = 1;
	$two = 2;
	$pay_mode = 'cash';
	// Start a transaction
	date_default_timezone_set('Africa/Lagos');
	$setdate = $value_date . ' ' . date('H:i');

	try {
		$db->beginTransaction();

		$sql = "SELECT account_code FROM chart_accounts WHERE account_code IN (1502, 2121)";
		$stmt = $db->query($sql);
		$accounts = $stmt->fetchAll(PDO::FETCH_COLUMN);

		$debit_gl_account_recievable = in_array(1502, $accounts) ? 1502 : '';
		$debit_gl_account = in_array(2121, $accounts) ? 2121 : '';

		$list = $_POST["item"];
		$batch_name_array = explode(",", $list);
		$result_count = count($batch_name_array);
		$lg_ref_no = time() . $emr;

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

			$stmt = $db->prepare("
					SELECT drug_sn 
					FROM patient_ap_services 
					WHERE paystatus = 0 
					AND (cr = 0 OR cr = 1) 
					AND sn = :sn
					LIMIT 1");
			$stmt->execute([':sn' => $sn]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($row) {
				$drug_sn = $row['drug_sn'];

				if ($pay > 0) {
					$wallet_debt_bill_to_acct = 'CREDIT';
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

					if ($credit_gl_account && $debit_gl_account && $debit_gl_account_recievable) {

						$general_credit_limit =	$_SESSION['credit_limit_status'];
						$items = call_current_balance($db, $emr, $general_credit_limit);
						$current_balance   = $items["current_balance"];
						$insurance_type    = $items['insurance_type'];

						///throw new Exception('Error CREDIT SALES ' . $current_balance);

						if ($current_balance > 0 && $pay >= $current_balance) {
							// Calculate remaining unpaid portion after using wallet
							$auth_amt = $pay - $current_balance;

							// === CASE 1: Wallet fully covers the bill ===
							if ($auth_amt == 0) {

								///// CHECK FOR DISCOUNT ===================================

								if ($dsc_chr_set == 1 && $discount > 0) {
									$discount_pay = $pay + $discount;
									$DEBIT_ENTRY_2 = billing(
										$db,
										$emr,
										$emr,
										$insurance_no,
										$ref_value,
										$sale_sn,
										$item_services,
										'DEBIT',
										$discount,
										0,
										0,
										$value_date,
										$auth_code,
										null,
										$debit_gl_account_discount,
										$lg_ref_no,
										0
									);

									if ($DEBIT_ENTRY_2 !== 'success') {
										throw new Exception('Error saving bill: ');
									}
								} else {
									$discount_pay = $pay;
								}

								// CREDIT Sales
								$CREDIT_ENTRY = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									'C/Bal Used: ' . $item_services,
									'CREDIT',
									0,
									$discount_pay,
									$zero,
									$value_date,
									'',
									$bank_name,
									$credit_gl_account,
									$lg_ref_no,
									0
								);
								if ($CREDIT_ENTRY !== 'success') {
									throw new Exception('Error CREDIT SALES ');
								}

								// DEBIT Wallet (2121)
								$DEBIT_ENTRY = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									'C/Bal Used ' . $item_services,
									'DEBIT',
									$current_balance,
									0,
									$bal,
									$value_date,
									$auth_code,
									$bank_name,
									$debit_gl_account,
									$lg_ref_no,
									0
								);
								if ($DEBIT_ENTRY !== 'success') {
									throw new Exception('Error DEBIT WALLET ');
								}
							}

							// === CASE 2: Wallet partially covers, remaining via A/R ===
							elseif ($auth_amt > 0) {

								// CREDIT Sales for Wallet Portion
								$CREDIT_ENTRY = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									'C/Bal Used: ' . $item_services,
									'CREDIT',
									0,
									$current_balance,
									$zero,
									$value_date,
									'',
									$bank_name,
									$credit_gl_account,
									$lg_ref_no,
									0
								);
								if ($CREDIT_ENTRY !== 'success') {
									throw new Exception('Error CREDIT SALES ');
								}

								// DEBIT Wallet (2121)
								$DEBIT_ENTRY = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									'C/Bal Used ' . $item_services,
									'DEBIT',
									$current_balance,
									0,
									$bal,
									$value_date,
									$auth_code,
									$bank_name,
									$debit_gl_account,
									$lg_ref_no,
									0
								);
								if ($DEBIT_ENTRY !== 'success') {
									throw new Exception('Error DEBIT WALLET ');
								}
								// CREDIT Sales for A/R Portion
								$CREDIT_ENTRY_AR = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									'A/R Used: ' . $item_services,
									'CREDIT',
									0,
									$auth_amt,
									$zero,
									$value_date,
									'',
									$bank_name,
									$credit_gl_account,
									$lg_ref_no,
									0
								);
								if ($CREDIT_ENTRY_AR !== 'success') {
									throw new Exception('Error CREDIT AR SALES ');
								}
								// DEBIT A/R Account (1502)
								$DEBIT_ENTRY_AR = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									'A/R Used: ' . $item_services,
									'DEBIT',
									$auth_amt,
									0,
									$bal,
									$value_date,
									$auth_code,
									$bank_name,
									$debit_gl_account_recievable,
									$lg_ref_no,
									0
								);
								if ($DEBIT_ENTRY_AR !== 'success') {
									throw new Exception('Error DEBIT SALES ');
								}

								// DEBIT Wallet (2121) WITH CODE 1 PATIENT STT
								$DEBIT_ENTRY_WALLET_1 = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									'A/R Used: ' . $item_services,
									'DEBIT',
									$auth_amt,
									0,
									$bal,
									$value_date,
									$auth_code,
									$bank_name,
									$debit_gl_account,
									$lg_ref_no,
									1
								);
								if ($DEBIT_ENTRY_WALLET_1 !== 'success') {
									throw new Exception('Error DEBIT WALLET ');
								}
							}
						} elseif ($current_balance > 0 && $pay < $current_balance) {

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
						}
						// === CASE 3: No wallet or negative balance ===
						else {

							if ($dsc_chr_set == 1 && $discount > 0) {
								$discount_pay = $pay + $discount;
								$DEBIT_ENTRY_2 = billing(
									$db,
									$emr,
									$emr,
									$insurance_no,
									$ref_value,
									$sale_sn,
									$item_services,
									'DEBIT',
									$discount,
									0,
									0,
									$value_date,
									$auth_code,
									null,
									$debit_gl_account_discount,
									$lg_ref_no,
									0
								);

								if ($DEBIT_ENTRY_2 !== 'success') {
									throw new Exception('Error saving bill: ');
								}
							} else {
								$discount_pay = $pay;
							}



							// CREDIT Sales
							$CREDIT_ENTRY = billing(
								$db,
								$emr,
								$emr,
								$insurance_no,
								$ref_value,
								$sale_sn,
								'A/R Used: ' . $item_services,
								'CREDIT',
								0,
								$discount_pay,
								$bal,
								$value_date,
								'',
								$bank_name,
								$credit_gl_account,
								$lg_ref_no,
								0
							);
							if ($CREDIT_ENTRY !== 'success') {
								throw new Exception('Error CREDIT SALES ');
							}

							// DEBIT A/R (1502)
							$DEBIT_ENTRY_AR = billing(
								$db,
								$emr,
								$emr,
								$insurance_no,
								$ref_value,
								$sale_sn,
								'A/R Used: ' . $item_services,
								'DEBIT',
								$pay,
								0,
								$bal,
								$value_date,
								$auth_code,
								$bank_name,
								$debit_gl_account_recievable,
								$lg_ref_no,
								0
							);
							if ($DEBIT_ENTRY_AR !== 'success') {
								throw new Exception('Error DEBIT SALES ');
							}

							// DEBIT Wallet (2121) WITH CODE 1 PATIENT STT
							$DEBIT_ENTRY_WALLET_1 = billing(
								$db,
								$emr,
								$emr,
								$insurance_no,
								$ref_value,
								$sale_sn,
								'A/R Used: ' . $item_services,
								'DEBIT',
								$pay,
								0,
								$bal,
								$value_date,
								$auth_code,
								$bank_name,
								$debit_gl_account,
								$lg_ref_no,
								1
							);
							if ($DEBIT_ENTRY_WALLET_1 !== 'success') {
								throw new Exception('Error DEBIT WALLET ');
							}
						}
						$updateSQL = "UPDATE patient_ap_services 
                                          SET cr = :cr,
										  transact_date = :transact_date,
                                              paystatus = :paystatus,
                                              pay_mode = :pay_mode,
											   pay = :pay,
											  discount = :discount,
                                              ledger_TX = :ledger_TX,
                                              wallet_debt_bill_to_acct = :wallet_debt_bill_to_acct,
                                              who_process_paystatus = :who_process_paystatus
                                          WHERE hospital_no = :hospital_no AND sn = :sn";

						$stmt = $db->prepare($updateSQL);
						$stmt->bindValue(':cr', $zero, PDO::PARAM_INT);
						$stmt->bindParam(':transact_date', $setdate, PDO::PARAM_STR);
						$stmt->bindValue(':paystatus', $one, PDO::PARAM_INT);
						$stmt->bindValue(':pay', $pay, PDO::PARAM_INT);
						$stmt->bindValue(':pay_mode', $pay_mode, PDO::PARAM_INT);
						$stmt->bindValue(':discount', $discount, PDO::PARAM_INT);
						$stmt->bindValue(':ledger_TX', $lg_ref_no, PDO::PARAM_STR);
						$stmt->bindValue(':wallet_debt_bill_to_acct', $wallet_debt_bill_to_acct, PDO::PARAM_STR);
						$stmt->bindValue(':who_process_paystatus', $_SESSION['EmployeeCode'], PDO::PARAM_STR);
						$stmt->bindValue(':hospital_no', $emr, PDO::PARAM_STR);
						$stmt->bindValue(':sn', $sn, PDO::PARAM_STR);
						$stmt->execute();

						if ($stmt->rowCount() > 0) {
							// Insert into saleprint
							$insertSQL = "INSERT INTO saleprint (hos_no, sale_no) VALUES (:hos_no, :sale_no)";
							$stmt2 = $db->prepare($insertSQL);
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
							throw new Exception('Failed to update patient_ap_services');
						}
					} else {
						throw new Exception('Something Went Wrong With Transaction Ledger. Ensure you create this Ledger: PATIENT BILLS RECEIVABLE or Accounts Receivables: 1114');
					}
				}
			}
		}

		// Commit the transaction if everything is successful
		$db->commit();
		$response_main['message'] = 'Item(s) Have Been Posted Successfully!';
		$response_main['status'] = '10';
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
}
