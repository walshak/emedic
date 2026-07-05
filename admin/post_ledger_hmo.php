<?php include("../Connections/Conn.php"); ?>

<?php
session_start();

$response_main = array(
	'message' => '',
	'status' => ''
);



if (isset($_POST['insurance_no'])) {

	$insurance_no = $_POST['insurance_no'];
	$firstDay = $_POST['firstDay'];
	$lastDay = $_POST['lastDay'];

	$stt_enl2 = $db->prepare("SELECT 
ap.sn, 
ap.cat_type, 
ap.hospital_no, 
ap.serv_group, 
ap.app_no, 
ap.item_services, 
ap.claim_amt

FROM enrollee AS enl 
INNER JOIN patient_ap_services AS ap ON ap.hospital_no=enl.hospital_no
WHERE paystatus='1' and claim_amt>0 and ledger_TX is null and enl.hmo_no= '$insurance_no' and date(date_entry) between '$firstDay' and '$lastDay'");
	$stt_enl2->execute();

	while ($rowx = $stt_enl2->fetch(PDO::FETCH_ASSOC)) {

		$cat_type = $rowx['cat_type'];
		$service_group = $rowx['serv_group'];
		$sale_sn = $rowx['sn'];
		$hospital_no = $rowx['hospital_no'];
		$item_services = $rowx['item_services'];
		$claim_amt = $rowx['claim_amt'];
		$ref_value = '';
		$bal = 0;
		$setdate = date('Y-m-d H:i');
		$auth_code = '';
		$bank_name = '';
		$lg_ref_no = time() . $sale_sn;

		//// ACCOUNT RECIEVABLES //////
		$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_code=1502");
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$debit_gl_account = $row['account_code'];
		} else {
			$debit_gl_account = '';
		}


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



		if ($credit_gl_account != '' and $debit_gl_account != '') {

			$empty = '';
			$cr_amt = $claim_amt;
			$dr_amt = 0;
			$transc_type = 'CREDIT';
			$CREDIT_ENTRY = billing(
				$db,
				$hospital_no,
				$hospital_no,
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
				$lg_ref_no
			);

			$dr_amt = $claim_amt;
			$cr_amt = 0;
			$transc_type = 'DEBIT';
			$DEBIT_ENTRY = billing(
				$db,
				$hospital_no,
				$hospital_no,
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
				$debit_gl_account,
				$lg_ref_no
			);



			if ($CREDIT_ENTRY == 'success' and $DEBIT_ENTRY == 'success') {

				$updateSQL = "UPDATE patient_ap_services SET 
		ledger_TX=:ledger_TX, 
		wallet_debt_bill_to_acct=:wallet_debt_bill_to_acct, 
		who_process_paystatus=:who_process_paystatus 
		WHERE hospital_no=:hospital_no and sn=:sn and ledger_TX is null";

				$stmt = $db->prepare($updateSQL);
				$stmt->bindParam(':ledger_TX', $lg_ref_no);
				$stmt->bindParam(':wallet_debt_bill_to_acct', $wallet_debt_bill_to_acct);
				$stmt->bindParam(':who_process_paystatus', $_SESSION['EmployeeCode']);
				$stmt->bindParam(':hospital_no', $hospital_no);
				$stmt->bindParam(':sn', $sale_sn);
				$stmt->execute();

				if ($stmt->rowCount() > 0) {
					// update successful

				} else {
					/// roll back

					$update = "DELETE FROM chart_ledger WHERE lg_ref_no='$lg_ref_no'";
					$db->exec($update);

					$response_main['message'] = 'Transaction Error! Something Went Wrong!';
					$response_main['status'] = '1';
					echo  json_encode($response_main);
					exit;
				}

				///	3070965059


			} else {

				//// roll back 

				$update = "DELETE FROM chart_ledger WHERE lg_ref_no='$lg_ref_no'";
				$db->exec($update);

				$response_main['message'] = 'Transaction Error! Something Went Wrong!';
				$response_main['status'] = '1';
				echo  json_encode($response_main);
				exit;
			}
		}
	}

	$response_main['message'] = 'Successfully Posted';
	$response_main['status'] = '0';
	echo  json_encode($response_main);
	exit;
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
	$lg_ref_no
) {
	$setdate = date('Y-m-d H:i');
	$setdate2 = date('Y-m-d');

	$prepared_by = $_SESSION['fullname'];
	$stmt = $db->query("SELECT app_no FROM chart_ledger 
	where hospital_no='$hosp_no' 
	and sale_sn='$sale_sn' and date_entry='$setdate' 
	and dr_amt='$dr_amt' and cr_amt='$cr_amt' and account_no='$account_no' 
	and prepared_by='$prepared_by'");
	if ($stmt->rowCount() == 0) {


		$fical_year = $db->query("SELECT id FROM chart_fiscal_year WHERE closed = '0' LIMIT 1");
		$fical_year = $fical_year->fetch()['id'];
		$stmt = $db->prepare("
				INSERT INTO chart_ledger (
					app_no,
					hospital_no,
					insurance_no,
					ref_value,
					sale_sn,
					item_services,
					bank_name,
					transc_type,
					dr_amt,
					cr_amt,
					bal,
					prepared_by,
					date_entry,
					date_entry2,
					auth_code,
					account_no,
					lg_ref_no,
					fiscal_year
				) VALUES (
					:app_no,
					:hospital_no,
					:insurance_no,
					:ref_value,
					:sale_sn,
					:item_services,
					:bank_name,
					:transc_type,
					:dr_amt,
					:cr_amt,
					:bal,
					:prepared_by,
					:date_entry,
					:date_entry2,
					:auth_code,
					:account_no,
					:lg_ref_no,
					:fiscal_year
				)
			");

		$stmt->bindParam(':app_no', $app_no);
		$stmt->bindParam(':hospital_no', $hosp_no);
		$stmt->bindParam(':insurance_no', $hmo_no);
		$stmt->bindParam(':ref_value', $ref_value);
		$stmt->bindParam(':sale_sn', $sale_sn);
		$stmt->bindParam(':item_services', $item_services);
		$stmt->bindParam(':bank_name', $bank_name);
		$stmt->bindParam(':transc_type', $transc_type);
		$stmt->bindParam(':dr_amt', $dr_amt);
		$stmt->bindParam(':cr_amt', $cr_amt);
		$stmt->bindParam(':bal', $bal);
		$stmt->bindParam(':prepared_by', $_SESSION['fullname']);
		$stmt->bindParam(':date_entry', $setdate);
		$stmt->bindParam(':date_entry2', $setdate2);
		$stmt->bindParam(':auth_code', $auth_code);
		$stmt->bindParam(':account_no', $account_no);
		$stmt->bindParam(':lg_ref_no', $lg_ref_no);
		$stmt->bindParam(':fiscal_year', $fical_year);

		$stmt->execute();

		if ($stmt->rowCount() > 0) {
			return 'success';
		} else {
			return 'error';
		}
	}
}
