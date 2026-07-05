<?php

include('connect_line.php');

// drop here with student registration number

$querys="SELECT * FROM payment_log WHERE applicant_id='$stdid' and PaymentCategory='$paycat' and PaymentState='Paid' and PaymentSession='2016/2017'";	
	$results = mysqli_query($dbc, $querys);	
	if(mysqli_num_rows($results)>=1){
		while ($row = mysqli_fetch_array($results)){$transactionreference=$row['PaymentID'];}
		include_once('payrpt_remita.php');}
	 
	else{

	$query2="SELECT * FROM payment_log WHERE applicant_id='$stdid' and PaymentCategory='$paycat' and PaymentState='pending' and PaymentSession='2016/2017'";	
	$result2 = mysqli_query($dbc, $query2);	
				
				if(mysqli_num_rows($result2)==0){
				
include_once('restitutionclosed.php');
				//include_once('payrequest_remita.php');
				//include_once('payrequest_rem.php');
						//			include_once('accptfee_closed.php');			
							} else {
									
							//	$PaymentCategory='Acceptance Fee';		
				include_once('paylist_rem.php');
								//include_once('accptfee_closed.php');

//close restitution fee

							}
}

?>



