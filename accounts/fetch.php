<?php 
include("../Connections/Conn.php");
session_start();

$response_main = array( 
	'message' => '',
	'code' => '',
	'hmo' => '',
	'hospital_no' => '',
	'amount' => '0'
);

	$fical_year = $db->query("SELECT * FROM chart_fiscal_year WHERE closed = '0' LIMIT 1");
	$row=$fical_year->fetch(PDO::FETCH_ASSOC);
	$begin=$row['begin'];
	$end=$row['end'];


if(isset($_POST["account_get_acctNO"])){
	$account = $_POST["account_get_acctNO"];

	
	
		$fical_year = $db->query("SELECT class_id FROM chart_accounts as c 
		INNER JOIN chart_groups as g ON g.id = c.account_group
		WHERE c.account_code = '$account'");
	$row=$fical_year->fetch(PDO::FETCH_ASSOC);
	$class_id=$row['class_id'];

	
	$stmt=$db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE account_no='$account' AND DATE(date_entry2) BETWEEN '$begin' AND '$end'");
					if($stmt->rowCount()>0){
						$row=$stmt->fetch(PDO::FETCH_ASSOC);
							$TOTAL_CREDITS=$row['TOTAL_CREDITS'];
							$TOTAL_DEBITS=$row['TOTAL_DEBITS'];
			
						if($class_id == '1' or $class_id == '5'){
							$bal = $TOTAL_DEBITS-$TOTAL_CREDITS;
							$response_main['code']='DR';
						}else{
							$bal = $TOTAL_CREDITS - $TOTAL_DEBITS;
							$response_main['code']='CR';
						}	
							
							$response_main['amount']=$bal;
						
						}else{
							$response_main['amount']=0;
						}

				echo  json_encode($response_main);
	
	
	
}



if(isset($_POST["account_checker"])){
	$account_string = $_POST["account_checker"];
	$account_payable = $_POST["account_payable"];
	
	$account = str_replace("account_payable", "", $account_string);
	$stmt=$db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE insurance_no='$account' and account_no='$account_payable' AND DATE(date_entry2) BETWEEN '$begin' AND '$end' ");
					if($stmt->rowCount()>0){
						$row=$stmt->fetch(PDO::FETCH_ASSOC);
							$TOTAL_CREDITS=$row['TOTAL_CREDITS'];
							$TOTAL_DEBITS=$row['TOTAL_DEBITS'];
			
							$bal = $TOTAL_CREDITS - $TOTAL_DEBITS;
							$response_main['amount']=$bal;
						
						}else{
							$response_main['amount']=0;
						}

				echo  json_encode($response_main);
}

if(isset($_POST["account_checker_deposit"])){
	$account_string = $_POST["account_checker_deposit"];
	
	// Check if this is a walk-in patient (starts with 'patient_deposit_walkin_')
	if (strpos($account_string, "patient_deposit_walkin_") !== false) {
		// Handle walk-in patient - use transc_code as hospital_no
		$transc_code = str_replace("patient_deposit_walkin_", "", $account_string);
		
		// Calculate balance for walk-in patient using transc_code in hospital_no field
		$stmt = $db->query("SELECT 
				sum(dr_amt) as TOTAL_DEBITS, 
				sum(cr_amt) as TOTAL_CREDITS
			FROM chart_ledger 
			WHERE hospital_no='$transc_code' and account_no='2121' 
			AND DATE(date_entry2) BETWEEN '$begin' AND '$end'");
			
		if($stmt->rowCount() > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$TOTAL_CREDITS = $row['TOTAL_CREDITS'];
			$TOTAL_DEBITS = $row['TOTAL_DEBITS'];
			$current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
			$response_main['amount'] = $current_balance;
		} else {
			$response_main['amount'] = 0;
		}
		
		$response_main['hospital_no'] = $transc_code; // Store transc_code as hospital_no
		$response_main['hmo'] = 1000; // Default HMO for walk-in patients
		$response_main['patient_type'] = 'walk_in';
		
	} else {
		// Handle regular patient (existing logic)
		$account = str_replace("patient_deposit", "", $account_string);

		$fical_year = $db->query("SELECT hmo_no FROM enrollee WHERE hospital_no='$account'");
		$row=$fical_year->fetch(PDO::FETCH_ASSOC);
		$hmo_no=$row['hmo_no'];
		if($hmo_no == 1000){
			$my_s =" hospital_no='$account'";
		}else{
			$my_s =" insurance_no = '$hmo_no'";
		}

		$stmt=$db->query("SELECT 
				sum(dr_amt) as TOTAL_DEBITS, 
				sum(cr_amt) as TOTAL_CREDITS
			FROM chart_ledger WHERE $my_s and account_no='2121' AND DATE(date_entry2) BETWEEN '$begin' AND '$end'");
		if($stmt->rowCount()>0){
			$row=$stmt->fetch(PDO::FETCH_ASSOC);
							$TOTAL_CREDITS=$row['TOTAL_CREDITS'];
						$TOTAL_DEBITS=$row['TOTAL_DEBITS'];
						$current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
						$response_main['amount']=$current_balance;
		}else{
			$response_main['amount']=0;
			
		}

			$response_main['hospital_no']=$account;
			$response_main['hmo']=$hmo_no;
			$response_main['patient_type'] = 'regular';
	}
	
	echo json_encode($response_main);
}


if(isset($_POST["account_checker2"])){
	$account_string = $_POST["account_checker2"];
	$accountNo_hmo = $_POST["accountNo_hmo"];
	
	$account = str_replace("account_hmo", "", $account_string);
	$stmt=$db->query("SELECT 
					sum(dr_amt) as TOTAL_DEBITS, 
					sum(cr_amt) as TOTAL_CREDITS
				FROM chart_ledger WHERE insurance_no='$account' and account_no='$accountNo_hmo' AND DATE(date_entry2) BETWEEN '$begin' AND '$end' ");
					if($stmt->rowCount()>0){
						$row=$stmt->fetch(PDO::FETCH_ASSOC);
							$TOTAL_CREDITS=$row['TOTAL_CREDITS'];
							$TOTAL_DEBITS=$row['TOTAL_DEBITS'];
			
							$bal = $TOTAL_DEBITS-$TOTAL_CREDITS;
							$response_main['amount']=$bal;
						
						}else{
							$response_main['amount']=0;
						}

				echo  json_encode($response_main);
	
}

?>