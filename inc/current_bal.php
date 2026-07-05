<?php
$noExist = '0';
$stmt_en = $db->query("SELECT i.insurance_name,i.interest,i.add_minus,i.insurance_type,i.payment_mode,e.hmo_no,e.surname,e.fname,e.gender,e.addr,e.discount_set FROM enrollee as e INNER JOIN insurance_tbl as i ON e.hmo_no=i.insurance_no WHERE hospital_no='$emr' and status='active'");
if ($stmt_en->rowCount() > 0) {

	$row = $stmt_en->fetch(PDO::FETCH_ASSOC);
	$insurance_name = $row['insurance_name'];
	//$insurance=$row['insurance_type'];
	$interest = $row['interest'];
	$add_minus = $row['add_minus'];
	//$insurance_type=$row['insurance_type'];
	$payment_mode = $row['payment_mode'];
	$insurance_no = $row['hmo_no'];
	$patient_name = $row['surname'] . ', ' . $row['fname'];
	$names = $row['surname'] . ', ' . $row['fname'];
	$gender = $row['gender'];
	$address = $row['addr'];
	$discount_set = $row['discount_set'];
	$patient_type = $insurance_type;
	$referral_name = ' ';

	if ($insurance == 'NHIS') {
		$insurance_status = 1;
	}


	$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_code=2121");
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$wallet_account = $row['account_code'];
		$error_wallet = 0;
	} else {
		$error_wallet == 1;
	}

	///================== DEPOSIT/OWNER BALANCE ==================================
	$TOTAL_CREDITS = 0;
	$TOTAL_DEBITS = 0;

	if ($insurance_type == 'Family') {
		$stmt = $db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE insurance_no='$insurance_no' and account_no='$wallet_account'");
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$TOTAL_CREDITS = $row['TOTAL_CREDITS'];
			$TOTAL_DEBITS = $row['TOTAL_DEBITS'];
		}
	} else {
		$stmt = $db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE hospital_no='$emr' and account_no='$wallet_account'");
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$TOTAL_CREDITS = $row['TOTAL_CREDITS'];
			$TOTAL_DEBITS = $row['TOTAL_DEBITS'];
		}
	}

	$current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
	$wallet_amount = $current_balance;
	///===================== WALLET BALANCE ===========================================================================		



} else {

	/// external 
	$patient_type = 'Referral';
	$stmt = $db->query("SELECT * FROM pharm_ext WHERE transc_code='$emr'");
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$patient_name = $row['cust_name'];
		$gender = $row['gender'];
		$address = $row['address'];
		$referral_name = $row['referral'];
		$discount_set = $row['discount_set'];

		$current_balance = 0;
	} else {
		header("location:index.php?Nex");
	}
}
