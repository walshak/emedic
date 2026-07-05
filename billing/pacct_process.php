<?php
session_start();
include("../Connections/Conn.php");
include("../inc/credit_current_balance.php");
$general_credit_limit =	$_SESSION['credit_limit_status'];

$response_main = array(
	'message' => '',
	'url' => '',
	'status' => ''
);

function getCreditGLAccount($db, $service_group, $cat_type, $default_account = '3122')
{
	if ($service_group == 'Laboratory') {
		$cat_type = 'Laboratory Test';
	} elseif ($service_group == 'Radiology') {
		$cat_type = 'SCAN/IMAGING';
	}

	// Prepare once
	$stmt = $db->prepare("
        SELECT account_code 
        FROM chart_accounts 
        WHERE account_name = :name 
        LIMIT 1
    ");

	// 1️⃣ FIRST PRIORITY → cat_type
	if (!empty($cat_type)) {
		$stmt->execute(array(':name' => $cat_type));
		$account_code = $stmt->fetchColumn();

		if ($account_code) {
			return $account_code;
		}
	}

	// 2️⃣ SECOND PRIORITY → service_group
	if (!empty($service_group)) {
		$stmt->execute(array(':name' => $service_group));
		$account_code = $stmt->fetchColumn();

		if ($account_code) {
			return $account_code;
		}
	}

	// 3️⃣ FINAL FALLBACK → default
	return $default_account;
}

if (isset($_POST['paynow_final']) && isset($_POST['emr']) && isset($_SESSION['fullname'])) {
	$emr = $_POST['emr'];
	try {
		$stmt = $db->prepare("DELETE FROM saleprint WHERE hos_no = :hos_no");
		$stmt->execute([':hos_no' => $emr]);
	} catch (PDOException $e) {
		$response_main = [
			'status'  => '1',
			'message' => 'Database Error (saleprint delete): ' . $e->getMessage()
		];
		echo json_encode($response_main);
		exit;
	}


	$paymethod = $_POST['paymethod'];
	$ref_no = $_POST['ref_no'];
	$bank_name = $_POST['bank_name'];
	$transaction_code = $_POST['transaction_code'];
	$MYwallet_amount = $_POST['wallet_amount'];
	$payment_remarks = $_POST['payment_remarks'];
	$insurance_type = $_POST['insurance_type'];

	$insurance_no = $_POST['save_insurance_no'];
	$debt_post = $_POST['debt_post'];
	$patient_name = $_POST['patient_name'];
	$v_discount = $_POST['v_discount'];
	$value_date = $_POST['value_date'];
	$auth_code = $_POST['auth_code'];
	$discount_set = $_POST['discount_set'];
	$discount_insurance_no = $_POST['discount_insurance_no'];
	$dsc_chr_type = $_POST['dsc_chr_type'];

	$service_group_desc = '';
	$list_desc = $_POST["item"];
	$Roll_array = explode(",", $list_desc);
	$resultM_count = count($Roll_array);
	for ($i = 0; $i < $resultM_count; $i++) {
		$inv_idd = $Roll_array[$i];
		$break = explode("__", $inv_idd);
		if ($break[12] == 'Medical Services') {
			$my_break = $break[10];
		} else {
			$my_break = $break[12];
		}
		if (strpos($service_group_desc, $my_break) !== false) {
		} else {
			$service_group_desc .= $my_break . ',';
		}
	}
	$service_group_desc = rtrim($service_group_desc, ',');

	if ($paymethod === 'pay_from_patient_wallet' || $paymethod === 'Bill_to' || $paymethod === 'writeoff') {
		try {
			$list = $_POST["item"];
			if (!empty($list)) {
				$batch_name_array = array_map('trim', explode(",", $list));
				$all_items = [];

				foreach ($batch_name_array as $inv_id) {
					$break = explode("__", $inv_id);

					if (isset($break[2], $break[1])) {
						$item_services = trim($break[2]);
						$pay = trim($break[1]);

						if ($item_services !== '' && $pay !== '') {
							$all_items[] = "{$item_services}({$pay})";
						}
					}
				}

				$all_items_here = implode(", ", $all_items);
				$stmt = $db->prepare("INSERT INTO patients_remarks_tbl (hospital_no,service_list,total_amount, remark, staff_name,bill_to_who) 
				VALUES (:hospital_no,:list_desc,:cash, :remark, :staff_name,:bill_to_who)");

				$stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
				$stmt->bindParam(':list_desc', $all_items_here, PDO::PARAM_STR);
				$stmt->bindParam(':cash', $_POST['cash'], PDO::PARAM_STR);
				$stmt->bindParam(':remark', $payment_remarks, PDO::PARAM_STR);
				$stmt->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
				$stmt->bindParam(':bill_to_who', $_POST['auth_staff'], PDO::PARAM_STR);

				if ($stmt->execute()) {
					///echo json_encode(['status' => 0, 'message' => 'Remark successfully added.']);
				} else {
					echo json_encode(['status' => 1, 'message' => 'Failed to add remark.']);
					exit;
				}
			} else {
				echo json_encode(['status' => 1, 'message' => 'Failed to Get All Services.']);
				exit;
			}
		} catch (PDOException $e) {
			echo json_encode(['status' => 1, 'message' => 'Database Error: ' . $e->getMessage()]);
			exit;
		}
	}


	if ($discount_set == 1 && $dsc_chr_type === 'Discount') {
		try {
			$stmt = $db->prepare("
            SELECT account_code 
            FROM chart_accounts 
            WHERE account_name = :account_name 
            LIMIT 1
        ");
			$stmt->execute([':account_name' => 'DISCOUNT TO PATIENTS']);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($row) {
				$debit_gl_account_discount = $row['account_code'];
			} else {
				$response_main = [
					'status' => '1',
					'message' => 'Transaction Error! This Ledger Does Not Exist: DISCOUNT TO PATIENTS'
				];
				echo json_encode($response_main);
				exit;
			}
		} catch (PDOException $e) {
			$response_main = [
				'status' => '1',
				'message' => 'Database Error: ' . $e->getMessage()
			];
			echo json_encode($response_main);
			exit;
		}
	}

	if ($discount_set == 1 && $dsc_chr_type === 'Charge') {
		try {
			$stmt = $db->prepare("
            SELECT account_code 
            FROM chart_accounts 
            WHERE account_name = :account_name 
            LIMIT 1
        ");
			$stmt->execute([':account_name' => 'REFERRAL EXTRA CHARGES']);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($row) {
				$credit_gl_account_charge = $row['account_code'];
			} else {
				$response_main = [
					'status'  => '1',
					'message' => 'Transaction Error! This Ledger Does Not Exist: REFERRAL EXTRA CHARGES'
				];
				echo json_encode($response_main);
				exit;
			}
		} catch (PDOException $e) {
			$response_main = [
				'status'  => '1',
				'message' => 'Database Error: ' . $e->getMessage()
			];
			echo json_encode($response_main);
			exit;
		}
	}


	if ($paymethod == 'post_discount') {
		$bank_name = '';
		include('post_discount_charges.php');
	} elseif (($paymethod == 'POS' or $paymethod == 'CASHPOS') and $bank_name == '') {
		$response_main['message'] = 'Select Bank Name !';
		$response_main['status'] = '1';
		echo  json_encode($response_main);
		exit;
	} elseif (($paymethod == 'Transfer'  or $paymethod == 'CASHTransfer')  and $bank_name == '') {
		$response_main['message'] = 'Select Bank Name !';
		$response_main['status'] = '1';
		echo  json_encode($response_main);
		exit;
	} elseif ($paymethod == 'PostCredit') {
		$bank_name = '';
		include('PostCredit.php');
	} elseif ($paymethod == 'pay_from_patient_wallet') {
		$bank_name = '';
		include('pay_another_wallet.php');
	} elseif ($paymethod == 'Wallet') {
		$bank_name = '';
		include('wallet.php');
	} elseif ($paymethod == 'Bill_to' or $paymethod == 'writeoff') {
		$bank_name = '';
		include('bill_to_account.php');
	} else {
		include('others.php');
	}
}


if (isset($_POST['final_save_money'])) {

	/// SAVE FINAL
	$credit_gl_account = $_POST['credit_gl_account'];
	$wallet_account = $_POST['wallet_account'];
	$hosp_no_giver = $_POST['hosp_no_giver'];
	$hosp_numb = $_POST['hosp_no_giver'];
	$bank_name = $_POST['bank_name'];
	$ref_no = $_POST['ref_no'];
	if ($ref_no != '') {
		$ref = " /Ref: " . $ref_no . ' /BNK: ' . $bank_name;
	}

	// Step 1: Prepare the SQL Query
	$sql = "SELECT * FROM billing_dep_confirm WHERE hospitla_no = :hosp_no_giver ORDER BY sn DESC LIMIT 1";

	// Step 2: Prepare the Statement
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':hosp_no_giver', $hosp_no_giver, PDO::PARAM_STR);

	// Step 3: Execute the Query
	$stmt->execute();
	$row_d = $stmt->fetch(PDO::FETCH_ASSOC);

	// Step 4: Use the Retrieved Data
	if ($row_d) {
		$hosp_no_beneficiary = $row_d['hosp_no'];
		$auth_code = $row_d['auth_code'];
		$auth_amount = $row_d['amount'];
		$transc_type = $row_d['transc_type'];
		$mode_pay = $row_d['mode_pay'];
		$insurance_no = $row_d['insurance_no'];
		$sale_sn = 'bill_' . $row_d['sn'];

		// Optionally, you can proceed with further operations using these variables
	} else {
		// Handle the case where no rows are found (optional)
	}


	if ($transc_type == 'Deposit') {

		try {
			// Start the transaction
			$db->beginTransaction();

			/// deposit code
			$emr = $hosp_no_giver;
			$lg_ref_no = time() . $emr;
			$ref_value = $mode_pay;
			$setdate = date("Y-m-d H:");
			$bal = 0;

			$items = call_current_balance($db, $emr, $general_credit_limit);
			$current_balance   = $items["current_balance"];
			$acct_recievabl_bal   = $items["Patient_Bill_Receivable"];

			// sanitize: remove commas and cast to float
			$current_balance = floatval(str_replace(',', '', $current_balance));
			$acct_recievabl_bal = floatval(str_replace(',', '', $acct_recievabl_bal));

			// compare with tolerance
			$epsilon = 0.005; // adjust tolerance as needed (0.005 tolerates cent-level rounding)
			if ($current_balance < 0 && abs(abs($current_balance) - $acct_recievabl_bal) < $epsilon) {
				// matched: treat as AR DEBT PAID
				if ($acct_recievabl_bal > 0) {
					if ($acct_recievabl_bal >= $auth_amount) {
						$amt_settle = 0;
						$acct_recievabl_bal = $auth_amount;
					} else {
						$amt_settle = $auth_amount - $acct_recievabl_bal;
					}
				}
				$patient_stt_status = 1;
				$item_services = 'AR DEBT PAID: (' . $row_d['descrip'] . ')' . $ref . ' EMR: ' . $emr;
			} else {
				$patient_stt_status = 0;
				$item_services = 'Amount Deposit: (' . $row_d['descrip'] . ')' . $ref . ' EMR: ' . $emr;
			}


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
				$auth_amount,
				0,
				$setdate,
				$auth_code,
				$bank_name,
				$credit_gl_account,  /// WALLET
				$lg_ref_no,
				$patient_stt_status
			);

			if ($CREDIT_ENTRY !== 'success') {
				throw new Exception('Error saving bill:' . $CREDIT_ENTRY);
			}


			$ref_value = $mode_pay;
			$paymethod = $mode_pay;

			$isBankPayment = in_array($paymethod, ['POS', 'Transfer']) && !empty($bank_name);
			$account_name = $isBankPayment ? $bank_name : 'Main Cash';

			$stmt = $db->prepare("SELECT account_code FROM chart_accounts WHERE account_name = :account_name AND account_group = '5' LIMIT 1");
			$stmt->bindParam(':account_name', $account_name);
			$stmt->execute();
			$debit_gl_account = '';
			if ($stmt->rowCount() > 0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$debit_gl_account = $row['account_code'];
			}

			if (!empty($credit_gl_account) && !empty($debit_gl_account) && !empty($insurance_no)) {
				$DEBIT_ENTRY = billing(
					$db,
					$emr,
					$emr,
					$insurance_no,
					$ref_value,
					$sale_sn,
					$item_services,
					'DEBIT',
					$auth_amount,
					0,
					0,
					$setdate,
					$auth_code,
					$bank_name,
					$debit_gl_account, /// BANK /CASH
					$lg_ref_no,
					0
				);

				if ($DEBIT_ENTRY !== 'success') {
					throw new Exception('Error saving bill:' . $DEBIT_ENTRY);
				}

				if ($patient_stt_status == 1) {

					if ($acct_recievabl_bal != 0) {  /// there was somthing beforee
						///========================== ACCOUNT RECIEVABLE ==================================

						$item_services = 'AR Amount Settled' .  '(' . $row_d['descrip'] . ')' . '(' . $emr . ')';
						$credit_gl_account_acct_recievable = 1502;
						$transc_type = "CREDIT";
						$CREDIT_ENTRY = billing(
							$db,
							$emr,
							$emr,
							$insurance_no,
							$ref_value,
							0,
							$item_services,
							'CREDIT',
							0,
							$acct_recievabl_bal,
							0,
							$setdate,
							$auth_code,
							$bank_name,
							$credit_gl_account_acct_recievable,
							$lg_ref_no,
							0
						);

						if ($CREDIT_ENTRY !== 'success') {
							throw new Exception('Error Saving AR ERROR ');
						}
					}
					///========================== ACCOUNT RECIEVABLE ==================================

					if ($amt_settle > 0) {
						$item_services = 'AR CREDIT BAL:' . '(' . $row_d['descrip'] . ')' . $ref . '(' . $emr . ')';
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
							$amt_settle,
							0,
							$setdate,
							$auth_code,
							$bank_name,
							$credit_gl_account,
							$lg_ref_no,
							2
						);

						if ($CREDIT_ENTRY !== 'success') {
							throw new Exception('Error saving bill: AR CREDIT ERROR.');
						}
					}
					///========================== DEPOSIT AMT  ==================================
				}
				// Delete any existing billing confirmations for the patient
				$deleteSql = "DELETE FROM billing_dep_confirm WHERE hospitla_no = :emr";
				$stmtDelete = $db->prepare($deleteSql);
				$stmtDelete->bindParam(':emr', $emr, PDO::PARAM_STR);
				$stmtDelete->execute();

				// Fetch the latest debit transaction for the patient

				$condition = ($current_balance < 0 && $acct_recievabl_bal > 0) ? "AND patient_stt_status = 1" : "";
				$sql = "SELECT sn 
							FROM chart_ledger 
							WHERE hospital_no = :emr 
							AND cr_amt > 0 
							$condition
							ORDER BY sn DESC 
							LIMIT 1";
				$stmt = $db->prepare($sql);
				$stmt->bindParam(':emr', $emr, PDO::PARAM_STR);
				$stmt->execute();

				$sn = ($row = $stmt->fetch(PDO::FETCH_ASSOC)) ? $row['sn'] : null;
				$db->commit();

				$response_main['message'] = 'Amount Deposited Into Patient Account.';
				$response_main['status'] = '2';
				$response_main['url'] = $sn;
				echo json_encode($response_main);
				exit;
			} else {

				$response_main['message'] = 'Invalid Ledger / Account Does Not Exist!';
				$response_main['status'] = '1';
				echo json_encode($response_main);
				exit;
			}
		} catch (Exception $e) {
			// Rollback the transaction if any error occurs
			$db->rollBack();
			$response_main['message'] = 'An Error Has Occurred: ' . $e->getMessage();
			$response_main['status'] = '1'; // Error status

			echo json_encode($response_main);
			exit;
		}
	} elseif ($transc_type == 'Transfer_to_patient' && $insurance_no == '1000') { /// ONLY PRIVATE PATIENT

		try {
			// Begin transaction
			$db->beginTransaction();

			$auth_amount = $_POST['auth_amount'];
			$lg_ref_no = time() . $hosp_no_beneficiary;
			$setdate = date('Y-m-d H:i');

			// DEBIT the giver (patient transferring out funds)
			$item_services = 'TRANSFERRED TO: ' . $hosp_no_beneficiary . '/' . $row_d['descrip'] . ' EMR: ' . $hosp_no_giver;
			$ref_value = 'cash';
			$DEBIT_ENTRY = billing(
				$db,
				$hosp_no_giver,
				$hosp_no_giver,
				'1000',
				$ref_value,
				null,
				$item_services,
				'DEBIT',
				$auth_amount,
				0,
				0,
				$setdate,
				$auth_code,
				null,
				'2121',
				$lg_ref_no,
				0
			);
			if ($DEBIT_ENTRY !== 'success') {
				throw new Exception('Error Saving AR ERROR ');
			}
			// CREDIT the beneficiary
			$emr = $hosp_no_beneficiary;
			$item_services = 'TRANSFERRED FROM: ' . $hosp_no_giver . '/ ' . $row_d['descrip'];
			$ref_value = $mode_pay;
			$CREDIT_ENTRY = billing(
				$db,
				$hosp_no_beneficiary,
				$hosp_no_beneficiary,
				'1000',
				$ref_value,
				0,
				$item_services,
				'CREDIT',
				0,
				$auth_amount,
				0,
				$setdate,
				$auth_code,
				null,
				'2121',
				$lg_ref_no,
				0
			);

			if ($CREDIT_ENTRY !== 'success') {
				throw new Exception('Error Saving AR ERROR ');
			}
			// Optional voucher update
			if ($auth_code != '') {
				$sub_add = voucher_update($db, $emr, $patient_name, $setdate, $auth_code);
			}

			// Commit transaction
			$db->commit();

			$response_main['message'] = 'Transfer Was Successful!';
			$response_main['status'] = '0';
			echo json_encode($response_main);
			exit;
		} catch (PDOException $e) {
			// Roll back if any PDO error occurs
			$db->rollBack();

			$response_main['message'] = 'Error: ' . $e->getMessage();
			$response_main['status'] = '2';
			echo json_encode($response_main);
			exit;
		}
	} elseif ($transc_type == 'Refund') {
		$setdate = date('Y-m-d H:i');

		$emr = $hosp_no_giver;
		$items = call_current_balance($db, $emr, $general_credit_limit);
		$current_balance = $items["current_balance"];

		$item_services = 'REFUND/' . $row_d['descrip'] . ' EMR: ' . $emr;
		$ref_value = 'Refund';
		$lg_ref_no = time() . $emr;

		// Determine account name based on payment mode
		$accountName = ($mode_pay === 'POS' || $mode_pay === 'Transfer') && $bank_name !== ''
			? $bank_name
			: 'Main Cash';

		// Fetch account code
		$stmt = $db->prepare("
			SELECT account_code 
			FROM chart_accounts 
			WHERE account_name = :account_name 
			AND account_group = '5'");
		$stmt->execute([':account_name' => $accountName]);
		$debit_gl_account = $stmt->fetchColumn() ?: '';

		try {
			// ✅ Begin Transaction
			$db->beginTransaction();

			if ($debit_gl_account != '' && $current_balance >= $auth_amount && $insurance_no != '') {

				// DEBIT ENTRY
				$DEBIT_ENTRY = billing(
					$db,
					$emr,
					$emr,
					$insurance_no,
					$ref_value,
					$sale_sn,
					$item_services,
					'DEBIT',
					$auth_amount,
					0,
					0,
					$setdate,
					$auth_code,
					$bank_name,
					'2121',
					$lg_ref_no,
					0
				);

				if ($DEBIT_ENTRY !== 'success') {
					throw new Exception('Error Saving DEBIT_ENTRY');
				}

				// CREDIT ENTRY
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
					$auth_amount,
					0,
					$setdate,
					$auth_code,
					$bank_name,
					$debit_gl_account,
					$lg_ref_no,
					0
				);

				if ($CREDIT_ENTRY !== 'success') {
					throw new Exception('Error Saving CREDIT_ENTRY');
				}

				// Optional voucher update
				if ($auth_code != '') {
					$sub_add = voucher_update($db, $emr, $patient_name, $setdate, $auth_code);
				}

				// Fetch last ledger SN
				$stmt = $db->prepare("
						SELECT sn 
						FROM chart_ledger 
						WHERE hospital_no = :hospital_no 
						AND cr_amt > 0 
						ORDER BY sn DESC 
						LIMIT 1
					");
				$stmt->bindParam(':hospital_no', $emr, PDO::PARAM_STR);
				$stmt->execute();
				$row_d = $stmt->fetch(PDO::FETCH_ASSOC);
				$sn = isset($row_d['sn']) ? $row_d['sn'] : '';

				// ✅ Commit Transaction
				$db->commit();

				$response_main = [
					'message' => 'Amount Refunded.',
					'status'  => '3',
					'url'     => $sn
				];
				echo json_encode($response_main);
				exit;
			} else {
				throw new Exception('Unable to Refund Amount');
			}
		} catch (Exception $e) {
			// ❌ Rollback if any error occurs
			if ($db->inTransaction()) {
				$db->rollBack();
			}

			$response_main = [
				'message' => 'Transaction Failed: ' . $e->getMessage(),
				'status'  => '1'
			];
			echo json_encode($response_main);
			exit;
		}
	}
}


function billing(
	$db,
	$app_no,
	$hosp_no,
	$hmo_no,
	$ref_value,
	$sale_sn,
	$item_services,
	$transc_type,
	$dr_amt,
	$cr_amt,
	$bal,
	$setdate,
	$auth_code,
	$bank_name,
	$account_no,
	$lg_ref_no,
	$patient_stt_status
) {
	// Prepare date variables
	$my_setdate = !empty($setdate) && strtotime($setdate)
		? date('Y-m-d', strtotime($setdate)) . ' ' . date('H:i:00')
		: date('Y-m-d H:i:00');

	$my_setdate2 = !empty($setdate) ? $setdate  : date('Y-m-d');

	try {


		$stmt = $db->query("SELECT id FROM chart_fiscal_year WHERE closed = '0' LIMIT 1");
		$fiscal_year = $stmt->fetchColumn();

		// Check if the record already exists
		$stmt = $db->prepare("SELECT app_no FROM chart_ledger 
                                                              WHERE app_no = :app_no 
                                                              AND hospital_no = :hosp_no 
                                                              AND item_services = :item_services 
                                                              AND ref_value = :ref_value 
                                                              AND sale_sn = :sale_sn 
                                                              AND transc_type = :transc_type 
                                                              AND DATE(date_entry) = :my_setdate 
                                                              AND dr_amt = :dr_amt 
                                                              AND cr_amt = :cr_amt 
                                                              AND account_no = :account_no");
		$stmt->execute([
			':app_no' => $app_no,
			':hosp_no' => $hosp_no,
			':item_services' => $item_services,
			':ref_value' => $ref_value,
			':sale_sn' => $sale_sn,
			':transc_type' => $transc_type,
			':my_setdate' => $my_setdate2,
			':dr_amt' => $dr_amt,
			':cr_amt' => $cr_amt,
			':account_no' => $account_no,
		]);

		if ($stmt->rowCount() == 0) {
			// Insert new record into chart_ledger table
			$insertSQL = "INSERT INTO chart_ledger (app_no, hospital_no, insurance_no, ref_value, sale_sn, item_services, bank_name,
                                                           transc_type, dr_amt, cr_amt, bal, prepared_by, date_entry, date_entry2, auth_code,
                                                           account_no, lg_ref_no, fiscal_year,patient_stt_status) 
                                                          VALUES 
                                                          (:app_no, :hosp_no, :insurance_no, :ref_value, :sale_sn, :item_services, :bank_name,
                           :transc_type, :dr_amt, :cr_amt, :bal, :prepared_by, :date_entry, :date_entry2, :auth_code,
                           :account_no, :lg_ref_no, :fiscal_year, :patient_stt_status)";

			$stmt = $db->prepare($insertSQL);
			$stmt->execute([
				':app_no' => $app_no,
				':hosp_no' => $hosp_no,
				':insurance_no' => $hmo_no,
				':ref_value' => $ref_value,
				':sale_sn' => $sale_sn,
				':item_services' => $item_services,
				':bank_name' => $bank_name,
				':transc_type' => $transc_type,
				':dr_amt' => $dr_amt,
				':cr_amt' => $cr_amt,
				':bal' => $bal,
				':prepared_by' => $_SESSION['fullname'], // Default to 'unknown' if not set
				':date_entry' => $my_setdate,
				':date_entry2' => $my_setdate2,
				':auth_code' => $auth_code,
				':account_no' => $account_no,
				':lg_ref_no' => $lg_ref_no,
				':fiscal_year' => $fiscal_year,
				':patient_stt_status' => $patient_stt_status
			]);

			// Check if the insert was successful
			if ($stmt->rowCount() > 0) {
				return 'success';
			} else {
				return 'error2'; // Insert failed, though this is unlikely with a prepared statement
			}
		} else {
			return 'exists'; // Record already exists
		}
	} catch (Exception $e) {
		// Log the error for debugging purposes
		error_log($e->getMessage());
		return $e->getMessage(); ///'error'; // Return a generic error message
	}
}
