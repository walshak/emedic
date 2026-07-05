<?php

if (!empty($_REQUEST['item'])) {

	$emr = $_POST['emr'];
	$billing_type = ($paymethod === 'Bill_to') ? 'Bill to MD Account' : 'Write Off';
	$wallet_debt_bill_to_acct = ($paymethod === 'Bill_to') ? 'BILL' : 'WRF';

	// Handle optional POST values safely
	$auth_staff = isset($_POST['voucher_created_by']) ? trim($_POST['voucher_created_by']) : '';
	if ($paymethod === 'Bill_to' && isset($_POST['auth_staff'])) {
		$auth_staff = trim($_POST['auth_staff']); // Basic sanitization
	}

	$cash_amount = isset($_POST['cash']) ? floatval($_POST['cash']) : 0.0;

	// Prepare and execute the query safely
	$debit_gl_account = '';
	$sql = "SELECT account_code FROM chart_accounts WHERE account_name = :account_name LIMIT 1";
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':account_name', $billing_type, PDO::PARAM_STR);

	if ($stmt->execute() && $stmt->rowCount() > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$debit_gl_account = $row['account_code'];
	}

	// Prepare to process items
	$list = $_POST["item"];
	$batch_name_array = explode(",", $list);
	$result_count = count($batch_name_array);
	$lg_ref_no = time() . $emr;

	// Start transaction
	$db->beginTransaction();

	try {
		for ($i = 0; $i < $result_count; $i++) {
			$inv_id = $batch_name_array[$i];
			$break = explode("__", $inv_id);
			$sale_sn = $break[0];
			$sn = $break[0];
			$item_services = $break[2];
			$pay = $break[1];
			$discount = $break[4];
			$charge = $break[5];
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

			// Check payment status

			$stmt = $db->prepare("
					SELECT drug_sn 
					FROM patient_ap_services 
					WHERE paystatus = 0 
					AND sn = :sn
					LIMIT 1");
			$stmt->execute([':sn' => $sn]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($row) {
				$drug_sn = $row['drug_sn'];

				if ($pay >= 0) {

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

					if ($credit_gl_account != '' && $debit_gl_account != '' && $auth_staff != '') {
						// Prepare billing entries
						$empty = '';
						$insurance_no = null;
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
							$pay,
							$bal,
							$value_date,
							$empty,
							null,
							$credit_gl_account,
							$lg_ref_no,
							0
						);
						if ($CREDIT_ENTRY !== 'success') {
							throw new Exception('Error CREDIT SALES ');
						}

						$DEBIT_ENTRY = billing(
							$db,
							$emr,
							$emr,
							$insurance_no,
							$ref_value,
							$sale_sn,
							$item_services,
							'DEBIT',
							$pay,
							0,
							0,
							$value_date,
							$auth_code,
							$bank_name,
							$debit_gl_account,
							$lg_ref_no,
							0
						);
						if ($DEBIT_ENTRY !== 'success') {
							throw new Exception('Error DEBIT SALES ');
						}

						$setdate = $value_date . ' ' . date('H:i');
						$invoice_by = str_replace("'", "", $_SESSION['fullname']);
						$paystatus = '1';
						$cr = '2';

						// Update patient_ap_services
						$updateSQL = "UPDATE patient_ap_services 
                                          SET paystatus = :paystatus, 
                                              cr = :cr, 
                                              payment_remarks = :payment_remarks, 
                                              transact_date = :transact_date, 
                                              invoice_by = :invoice_by, 
                                              created_by = :created_by, 
                                              acct_billed_staff = :acct_billed_staff, 
                                              wallet_debt_bill_to_acct = :wallet_debt_bill_to_acct, 
                                              who_process_paystatus = :who_process_paystatus, 
                                              ledger_TX = :ledger_TX
                                          WHERE sn = :sn 
                                          AND paystatus = 0";

						$stmt = $db->prepare($updateSQL);

						// Binding parameters
						$stmt->bindParam(':paystatus', $paystatus);
						$stmt->bindParam(':cr', $cr);
						$stmt->bindParam(':payment_remarks', $payment_remarks);
						$stmt->bindParam(':transact_date', $setdate);
						$stmt->bindParam(':invoice_by', $invoice_by);
						$stmt->bindParam(':created_by', $_SESSION['fullname']);
						$stmt->bindParam(':acct_billed_staff', $auth_staff);
						$stmt->bindParam(':wallet_debt_bill_to_acct', $wallet_debt_bill_to_acct);
						$stmt->bindParam(':who_process_paystatus', $_SESSION['EmployeeCode']);
						$stmt->bindParam(':ledger_TX', $lg_ref_no);
						$stmt->bindParam(':sn', $sale_sn);

						// Execute the statement
						$stmt->execute();

						// Insert into saleprint
						$insertSQL = "INSERT INTO saleprint(hos_no, sale_no) VALUES (:hos_no, :sale_no)";
						$stmtInsert = $db->prepare($insertSQL);
						$stmtInsert->bindParam(':hos_no', $emr);
						$stmtInsert->bindParam(':sale_no', $sale_sn);
						$stmtInsert->execute();

						if ($service_group == 'Laboratory' or $service_group == 'Radiology') {
							$updateSQL3 = "UPDATE lab_manage SET result_on_credit = 0,date_time_pay = :date_time_pay WHERE patient = :hospital_no                                AND labrequest_no = :labrequest_no";
							$stmt = $db->prepare($updateSQL3);
							$stmt->bindParam(':date_time_pay', $setdate, PDO::PARAM_STR);
							$stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
							$stmt->bindParam(':labrequest_no', $drug_sn, PDO::PARAM_STR);
							$stmt->execute();
						}
					} else {
						throw new Exception('Error with transaction ledger. Ensure you create this ledger: ' . $billing_type);
					}
				}
			}
		}

		// Handle write-off if applicable
		if ($paymethod == 'writeoff') {
			$updateSQL = "UPDATE vouchers_inventory 
                          SET hospital_no = :hospital_no, 
                              patient_name = :patient_name, 
                              issued_by = :issued_by, 
                              issued_date = :issued_date, 
                              amount = :amount, 
                              used_status = '1' 
                          WHERE voucher_code = :voucher_code";

			$stmt = $db->prepare($updateSQL);
			$stmt->bindParam(':hospital_no', $emr);
			$stmt->bindParam(':patient_name', $patient_name);
			$stmt->bindParam(':issued_by', $_SESSION['fullname']);
			$stmt->bindParam(':issued_date', $setdate);
			$stmt->bindParam(':amount', $cash_amount);
			$stmt->bindParam(':voucher_code', $auth_code);
			$stmt->execute();
		}

		// Commit transaction if everything is successful
		$db->commit();

		$response_main['message'] = 'Item(s) have been posted successfully!';
		$response_main['status'] = '0';
		echo json_encode($response_main);
		exit;
	} catch (Exception $e) {
		// Rollback transaction on error
		$db->rollBack();
		$response_main['message'] = 'Transaction Error! Something went wrong: ' . $e->getMessage();
		$response_main['status'] = '1';
		echo json_encode($response_main);
		exit;
	}
}
