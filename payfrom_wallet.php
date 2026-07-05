<?php
session_start();
include("Connections/Conn.php");
include("inc/credit_current_balance.php");

$response_main = array(
	'message' => '',
	'url' => '',
	'status' => ''
);


if (isset($_POST['sale_sn'])) {
	$sale_sn = $_POST['sale_sn'];

	// Fetch patient service record by sale_sn
	$stmtxx = $db->prepare("SELECT item_services, pay, serv_group, cat_type, paystatus, hospital_no, drug_sn 
FROM patient_ap_services 
WHERE sn = :sale_sn");
	$stmtxx->execute([':sale_sn' => $sale_sn]);

	if ($row_xs = $stmtxx->fetch(PDO::FETCH_ASSOC)) {
		$item_services  = $row_xs['item_services'];
		$pay            = $row_xs['pay'];
		$service_group  = $row_xs['serv_group'];
		$cat_type       = $row_xs['cat_type'];
		$paystatus      = $row_xs['paystatus'];
		$emr            = $row_xs['hospital_no'];

		// Check for duplicate request in lab_manage (only for Radiology or Laboratory)
		if ($service_group === 'Radiology' || $service_group === 'Laboratory') {
			$labrequest_no = $row_xs['drug_sn'];

			$stmt2 = $db->prepare("SELECT sn FROM lab_manage WHERE labrequest_no = :labrequest_no");
			$stmt2->execute([':labrequest_no' => $labrequest_no]);

			if ($stmt2->rowCount() == 0) {
				$response_main = [
					'message' => 'Error occurred while searching for duplicate record of investigation request!',
					'status'  => '1'
				];
				echo json_encode($response_main);
				exit;
			}
		}


		$lg_ref_no = time() . $emr;
		$general_credit_limit =	$_SESSION['credit_limit_status'];
		$items = call_current_balance($db, $emr, $general_credit_limit);

		$current_balance =  $items["current_balance"];
		$patient_name      = $items['patient_name'];
		$discount_set      = $items['discount_set'];
		$insurance_type    = $items['insurance_type'];
		$insurance_no      = $items['insurance_no'];
		$save_insurance_no = $items['save_insurance_no'];
		$wallet_amount     = $items['wallet_amount'];
		$bal_credit_limit  = $items['bal_credit_limit'];
		$credit_limit  = $items['bal_credit_limit'];
		$wallet_account  = $items['wallet_account'];

		$TOTAL_CREDITS = 0;
		$TOTAL_DEBITS = 0;

		// Build condition based on insurance type
		$whereField = ($insurance_type == 'Family') ? "insurance_no" : "hospital_no";
		$whereValue = ($insurance_type == 'Family') ? $insurance_no : $emr;

		// Prepare and execute the query
		$query = "SELECT 
					  SUM(dr_amt) AS TOTAL_DEBITS, 
					  SUM(cr_amt) AS TOTAL_CREDITS 
				  FROM chart_ledger 
				  WHERE {$whereField} = :value AND account_no = :account_no";

		$stmt = $db->prepare($query);
		$stmt->execute([
			':value' => $whereValue,
			':account_no' => $wallet_account
		]);

		// Fetch and assign results
		if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$TOTAL_CREDITS = $row['TOTAL_CREDITS'];
			$TOTAL_DEBITS = $row['TOTAL_DEBITS'];
		}

		$MYwallet_amount = $TOTAL_CREDITS - $TOTAL_DEBITS;


		$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_name='$cat_type'");
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$credit_gl_account = $row['account_code'];
		} else {

			/// CHECK FOR SERVICE GROUP /////
			$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_name='$service_group'");
			if ($stmt->rowCount() > 0) {
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				$credit_gl_account = $row['account_code'];
			} else {
				$credit_gl_account = '';
			}
		}


		if ($paystatus == 0) {
			if ($credit_gl_account != '' and $wallet_account != '') {
				if ($pay > 0 && $pay <= $MYwallet_amount) {
					try {
						// Begin transaction
						$db->beginTransaction();

						$ref_value = '';
						$bal = 0;
						$empty = '';
						$cr_amt = $pay;
						$dr_amt = 0;
						$transc_type = 'CREDIT';
						$CREDIT_ENTRY = billing(
							$db,
							$emr,
							$emr,
							$insurance_no,
							$ref_value,
							$sale_sn,
							$item_services,
							$transc_type,
							$dr_amt,
							$cr_amt,
							$bal,
							$setdate,
							$empty,
							$bank_name,
							$credit_gl_account,
							$lg_ref_no,
							0
						);

						$dr_amt = $pay;
						$cr_amt = 0;
						$transc_type = 'DEBIT';
						$DEBIT_ENTRY = billing(
							$db,
							$emr,
							$emr,
							$insurance_no,
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
							$wallet_account,
							$lg_ref_no,
							0
						);

						if ($CREDIT_ENTRY == 'success' && $DEBIT_ENTRY == 'success') {
							$setdate = date('Y-m-d H:i');
							$invoice_by = str_replace("'", "", $_SESSION['fullname']);
							$wallet_debt_bill_to_acct = 'WALET'; // PAY FROM WALLET

							$who_process_paystatus = $_SESSION['EmployeeCode'];

							$updateSQL = sprintf("UPDATE patient_ap_services 
							SET paystatus = 1,
								cr = 0, 
								transact_date = '$setdate',
								invoice_by = '$invoice_by',
								pay = '$pay',
								ledger_TX = '$lg_ref_no',
								wallet_debt_bill_to_acct = '$wallet_debt_bill_to_acct',
								who_process_paystatus = '$who_process_paystatus' 
							WHERE hospital_no = '$emr' and sn = '$sale_sn' and paystatus = 0");
							$db->exec($updateSQL);

							if ($updateSQL) {

								if ($service_group == 'Laboratory' or $service_group == 'Radiology') {
									$updateSQL3 = "UPDATE lab_manage SET result_on_credit = 0,date_time_pay = :date_time_pay WHERE labrequest_no = :labrequest_no";
									$stmt = $db->prepare($updateSQL3);
									$stmt->bindParam(':date_time_pay', $setdate, PDO::PARAM_STR);
									$stmt->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
									$stmt->execute();
								}



								// Commit transaction
								$db->commit();

								$response_main['message'] = 'Successful';
								$response_main['status'] = '0';
								echo json_encode($response_main);
								exit;
							} else {
								throw new Exception("Failed to update patient_ap_services");
							}
						} else {
							throw new Exception("CREDIT or DEBIT entry failed");
						}
					} catch (Exception $e) {
						// Rollback transaction
						$db->rollBack();

						// Roll back any changes
						$update = "DELETE FROM chart_ledger WHERE lg_ref_no='$lg_ref_no'";
						$db->exec($update);

						$updateSQLv = "UPDATE patient_ap_services 
					SET paystatus='0', transact_date='', invoice_by='', discount='', add_charge='', payment_remarks='', who_process_paystatus='', wallet_debt_bill_to_acct='' WHERE ledger_TX='$lg_ref_no'";
						$db->exec($updateSQLv);

						$response_main['message'] = 'Transaction Error! Something Went Wrong!';
						$response_main['status'] = '1';
						echo json_encode($response_main);
						exit;
					}
				} else {
					$response_main['message'] = 'Insufficient Fund!';
					$response_main['status'] = '1';
					echo json_encode($response_main);
					exit;
				}
			} else {
				$response_main['message'] = 'Account or Ledger Error!';
				$response_main['status'] = '1';
				echo  json_encode($response_main);
				exit;
			}
		} else {
			$response_main['message'] = 'Payment Already Done';
			$response_main['status'] = '1';
			echo  json_encode($response_main);
			exit;
		}
	} else {

		$response_main['message'] = 'Unable to Continue Transaction';
		$response_main['status'] = '1';
		echo  json_encode($response_main);
		exit;
	}
}



if (isset($_POST['pay_wall'])) {

	$pay_wall = $_POST['pay_wall'];
	$pp = explode('___', $pay_wall);

	$emr = $pp[0];
	$sale_sn = $pp[1];

	$stmtxx = $db->query("SELECT item_services,pay,paystatus from patient_ap_services WHERE sn='$sale_sn'");
	if ($stmtxx->rowCount() > 0) {
		$row_xs = $stmtxx->fetch(PDO::FETCH_ASSOC);
		$item_services = $row_xs['item_services'];
		$pay = $row_xs['pay'];
		$paystatus = $row_xs['paystatus'];
	}
	$general_credit_limit =	$_SESSION['credit_limit_status'];
	$items = call_current_balance($db, $emr, $general_credit_limit);

	$current_balance   = $items["current_balance"];
	$patient_name      = $items['patient_name'];
	$nhis_no           = $items['nhis_no'];
	$names             = $items['names'];
	$surname           = $items['surname'];
	$discount_set      = $items['discount_set'];
	$insurance_type    = $items['insurance_type'];
	$insurance_no      = $items['insurance_no'];
	$wallet_amount     = $items['wallet_amount'];
	$credit_limit  	   = $items['bal_credit_limit'];
	$add_minus         = $items['add_minus'];
	$wallet_account    = $items['wallet_account'];
	$interest          = $items['interest'];


?>
	<table width="100%">
		<tr>
			<td>
				<h3><u>ITEM</u></h3>
			</td>
			<td>
				<h3><u>PRICE</u></h3>
			</td>
		</tr>
		<tr>
			<td>
				<h3><?= $item_services; ?></h3>
			</td>
			<td>
				<h3><?= number_format($pay); ?></h3>
			</td>
		</tr>
	</table>

	<hr>

	<?php if ($paystatus == 1) { ?>
		<h3 style="color: blue;">ITEM HAS BEEN PAID ALREADY!</h3>
	<?php } elseif ($wallet_amount >= $pay and $_SESSION['payfrom_status'] == 1) { ?>
		<button class="btn btn-warning btn-sm dropdown-toggle" id="pay_now" onClick="payNow('<?php echo $sale_sn; ?>','','')">Pay</button>
	<?php } else { ?>
		<h3 style="color: red;">INSUFFICIENT BALANCE!</h3>
	<?php } ?>


<?php } ?>


<?php


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
	$setdate = date('Y-m-d H:i');
	$setdate2 = date('Y-m-d');
	$prepared_by = $_SESSION['fullname'];

	$stmt = $db->prepare("SELECT app_no FROM chart_ledger 
                          WHERE app_no = :app_no AND hospital_no = :hospital_no AND ref_value = :ref_value 
                          AND sale_sn = :sale_sn AND transc_type = :transc_type AND date_entry = :setdate 
                          AND dr_amt = :dr_amt AND cr_amt = :cr_amt AND account_no = :account_no 
                          AND prepared_by = :prepared_by");
	$stmt->execute([
		':app_no' => $app_no,
		':hospital_no' => $hosp_no,
		':ref_value' => $ref_value,
		':sale_sn' => $sale_sn,
		':transc_type' => $transc_type,
		':setdate' => $setdate,
		':dr_amt' => $dr_amt,
		':cr_amt' => $cr_amt,
		':account_no' => $account_no,
		':prepared_by' => $prepared_by
	]);

	if ($stmt->rowCount() == 0) {
		$fical_year_stmt = $db->query("SELECT id FROM chart_fiscal_year WHERE closed = '0' LIMIT 1");
		$fical_year = $fical_year_stmt->fetch()['id'];

		$insertSQL = "INSERT INTO chart_ledger(app_no, hospital_no, insurance_no, ref_value, sale_sn, item_services, bank_name,
                                               transc_type, dr_amt, cr_amt, bal, prepared_by, date_entry, date_entry2, auth_code, account_no, lg_ref_no, fiscal_year, patient_stt_status) 
                      VALUES (:app_no, :hospital_no, :insurance_no, :ref_value, :sale_sn, :item_services, :bank_name,
                              :transc_type, :dr_amt, :cr_amt, :bal, :prepared_by, :date_entry, :date_entry2, :auth_code, :account_no, :lg_ref_no, :fiscal_year, :patient_stt_status)";

		$stmt = $db->prepare($insertSQL);
		$stmt->execute([
			':app_no' => $app_no,
			':hospital_no' => $hosp_no,
			':insurance_no' => $hmo_no,
			':ref_value' => $ref_value,
			':sale_sn' => $sale_sn,
			':item_services' => $item_services,
			':bank_name' => $bank_name,
			':transc_type' => $transc_type,
			':dr_amt' => $dr_amt,
			':cr_amt' => $cr_amt,
			':bal' => $bal,
			':prepared_by' => $prepared_by,
			':date_entry' => $setdate,
			':date_entry2' => $setdate2,
			':auth_code' => $auth_code,
			':account_no' => $account_no,
			':lg_ref_no' => $lg_ref_no,
			':fiscal_year' => $fical_year,
			':patient_stt_status' => $patient_stt_status
		]);

		if ($stmt->rowCount() > 0) {
			return 'success';
		} else {
			return 'error';
		}
	} else {
		return 'exists'; // or some other appropriate status indicating the entry already exists
	}
}



?>