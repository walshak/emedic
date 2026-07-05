<?php
   session_start();
	
    include("../Connections/Conn.php");

if(isset($_POST['patient_emr'])){
	$response_main = array( 
    'status' => '0',
    'name' => '',
    'wallet' => '0',
	'message' => ''
);
		$patient_emr =trim($_POST['patient_emr']);
		$cash = $_POST['cash'];
		$emr = trim($_POST['emr']);
	
if($patient_emr == $emr){
		$response_main['status']=0;
		$response_main['message']= 'Same EMR Number Found Between the Beneficiary and Account Owner!';
		echo  json_encode($response_main);
		exit;

}else{	
		$stmt2=$db->query("SELECT * FROM enrollee WHERE hospital_no='$patient_emr' and hospital_no!='$emr'");
			if($stmt2->rowCount()>0){
				$row = $stmt2->fetch(PDO::FETCH_ASSOC);
				
	        $fullname = $row['surname'] . ' ' . $row['fname']. ' ' . $row['oname'];

		$stmt=$db->query("SELECT account_code FROM chart_accounts WHERE account_name='Patient Deposit'");
		if($stmt->rowCount()>0){$row=$stmt->fetch(PDO::FETCH_ASSOC);
								 $wallet_account=$row['account_code'];
									$error_wallet=0;}else{ $error_wallet==1;}	
			
	$stmt=$db->query("SELECT sum(dr_amt) as TOTAL_DEBITS, sum(cr_amt) as TOTAL_CREDITS 
	FROM chart_ledger WHERE hospital_no='$patient_emr' and account_no='$wallet_account'");
			if($stmt->rowCount()>0){
				$row=$stmt->fetch(PDO::FETCH_ASSOC);
								$TOTAL_CREDITS=$row['TOTAL_CREDITS'];
							$TOTAL_DEBITS=$row['TOTAL_DEBITS'];
					}
				
			$current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;		
			$wallet_amount= $current_balance;	
			///===================== WALLET BALA	
			
				
				
				if($cash > $wallet_amount){
					
							$response_main['status']=2;
							$response_main['message']= 'You Can Not Continue Because Selected Services Is More Than Account Owner Amount.';
				}else{
							$response_main['status']=1;
							$response_main['message']= 'Patient Found!';
							$bal = $wallet_amount - $cash; 
							
				}
							$response_main['wallet']= '<strong>ACCOUNT OWNER AMOUNT: </strong>' . number_format($wallet_amount) . '<hr>' . 
								'<strong>ACCOUNT OWNER BALANCE: </strong>' . number_format($bal) ;
							$response_main['name']= '<strong  style="color:red;">ACCOUNT OWNER: </strong><BR>' . $fullname . '<hr>';	
				echo  json_encode($response_main);
	exit;		
			
		}else{
			
										$response_main['status']=0;
							$response_main['message']= 'Patient Not Found!';
				echo  json_encode($response_main);
	exit;
			
		}
		}
}




if(isset($_POST['hospital_no'])){
 
	
		$hospital_no = $_POST['hospital_no'];
	
	    $stmt = $db->prepare("SELECT sum(pay) total_debit FROM patient_ap_services WHERE hospital_no  = ? AND cr = 2 and paystatus = 0 ");
        $stmt->execute(array($hospital_no));
        $admittedTodayRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $Total_total_debit = $admittedTodayRow['total_debit'];	   
	
		$stmt = $db->prepare("SELECT sum(pay) total_credits FROM patient_ap_services WHERE hospital_no  = ? AND cr = 1 and paystatus = 0 ");
        $stmt->execute(array($hospital_no));
        $admittedTodayRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $Total_total_credits = $admittedTodayRow['total_credits'];
	
	
	        echo json_encode( ['status' => 200, 
            'Total_total_debit' => $Total_total_debit,
            'Total_total_credits' => $Total_total_credits
           // 'patient_seen_thismonth_count' => $patient_seen_thismonth_count,
           // 'admitted_today_count' => $admitted_today_count,
            ///'total_procedure_request' => $total_procedure_request
             ] );
    exit;
	
}





?>