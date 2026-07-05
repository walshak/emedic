<?php
include("../Connections/Conn.php");
session_start();



$response_mainx = array(
	'status' => '0',
	'message' => ''
);

if (isset($_POST["save_purchase_payment"])) {

	$amt_pay = $_POST["amt_pay"];

	///$bal_amt=$_POST["bal_amt"];
	$stock = $_POST["stock"];
	$order_by = $_POST["order_by"];
	$order_date = $_POST["order_date"];
	$supplier_id = $_POST["supplier_id"];
	$batch_no = $_POST["batch_no"];
	$item_services = $_POST["remarks"];


	$credit_gl_account = $_POST["account_credit"];

	$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_name='$credit_gl_account'");
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$credit_gl_account = $row['account_code'];
	} else {
		$credit_gl_account = '';
	}

	if ($credit_gl_account == '') {
		$response_mainx['message'] = 'ERROR: Unable to Post. Account Payable Does Not Exist!';
		$response_mainx['status'] = '0';
		echo  json_encode($response_mainx);
		exit;
	}



	$debit_gl_account = $_POST["account_debit"];

	$setdate = date("Y-m-d H:00:00");
	$bal = 0;
	$empty = '';
	$lg_ref_no = time();


	$cr_amt = $amt_pay;
	$dr_amt = 0;
	$transc_type = 'CREDIT';
	$CREDIT_ENTRY = billing($db, $batch_no, $item_services, $transc_type, $dr_amt, $cr_amt, $credit_gl_account, $lg_ref_no, $supplier_id);
	$dr_amt = $amt_pay;
	$cr_amt = 0;
	$transc_type = 'DEBIT';
	$DEBIT_ENTRY = billing($db, $batch_no, $item_services, $transc_type, $dr_amt, $cr_amt, $debit_gl_account, $lg_ref_no, $supplier_id);



	if ($CREDIT_ENTRY == 'success' and $DEBIT_ENTRY == 'success') {

		$updateSQL = "UPDATE stock_table_procurment SET pay_status='1' WHERE batch_no='$batch_no'";
		$db->exec($updateSQL);

		$response_mainx['message'] = 'Data Posted Successfully';
		$response_mainx['status'] = '1';
	} else {

		$update = "DELETE FROM chart_ledger WHERE sale_sn='$batch_no' and lg_ref_no='$lg_ref_no'";
		$db->exec($update);

		$response_mainx['message'] = 'ERROR: Unable to Post';
		$response_mainx['status'] = '0';
	}

	echo  json_encode($response_mainx);
}




function billing($db, $batch_no, $item_services, $transc_type, $dr_amt, $cr_amt, $account_no, $lg_ref_no, $supplier_id)
{
	$setdate = date('Y-m-d H:i');
	$setdate2 = date('Y-m-d');

	$prepared_by = $_SESSION['fullname'];
	$stmt = $db->query("SELECT app_no FROM chart_ledger 
	where sale_sn='$batch_no' and transc_type='$transc_type' and date_entry='$setdate' 
	and dr_amt='$dr_amt' and cr_amt='$cr_amt' and account_no='$account_no' 
	and prepared_by='$prepared_by'");
	if ($stmt->rowCount() == 0) {


		$fical_year = $db->query("SELECT id FROM chart_fiscal_year WHERE closed = '0' LIMIT 1");
		$fical_year = $fical_year->fetch()['id'];

		$empty = '';
		$zero = 0;

		$stmt = $db->prepare("INSERT INTO chart_ledger(app_no, hospital_no, insurance_no, ref_value, sale_sn, item_services, bank_name, 
	  transc_type, dr_amt, cr_amt, bal, prepared_by, date_entry, date_entry2, auth_code, account_no, lg_ref_no, fiscal_year) 
	  VALUES (:app_no, :hospital_no, :insurance_no, :ref_value, :sale_sn, :item_services, :bank_name, 
	  :transc_type, :dr_amt, :cr_amt, :bal, :prepared_by, :date_entry, :date_entry2, :auth_code, :account_no, :lg_ref_no, :fiscal_year)");

		$stmt->bindParam(':app_no', $empty);
		$stmt->bindParam(':hospital_no', $empty);
		$stmt->bindParam(':insurance_no', $supplier_id);
		$stmt->bindParam(':ref_value', $empty);
		$stmt->bindParam(':sale_sn', $batch_no);
		$stmt->bindParam(':item_services', $item_services);
		$stmt->bindParam(':bank_name', $empty);
		$stmt->bindParam(':transc_type', $transc_type);
		$stmt->bindParam(':dr_amt', $dr_amt);
		$stmt->bindParam(':cr_amt', $cr_amt);
		$stmt->bindParam(':bal', $zero);
		$stmt->bindParam(':prepared_by', $_SESSION['fullname']);
		$stmt->bindParam(':date_entry', $setdate);
		$stmt->bindParam(':date_entry2', $setdate2);
		$stmt->bindParam(':auth_code', $empty);
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
