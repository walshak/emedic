<?php

include('connect_line.php');


$querys="SELECT * FROM payment_log WHERE applicant_id='$stdid' and PaymentCategory='Registration Fee' and PaymentState='Paid' and PaymentSession='$pp'";	
	$results = mysqli_query($dbc, $querys);	
	if(mysqli_num_rows($results)==1){
		while ($row = mysqli_fetch_array($results)){$transactionreference=$row['PaymentID'];}
		include_once('payrpt_pg.php');

	}elseif($student_status=='PGFresh'){
include_once('goto_interwish.php');
	
		}elseif($student_status=='PGReturn'){
include_once('payrequest_pg_sem.php');

							
					}else{
	$query2="SELECT * FROM payment_log WHERE applicant_id='$stdid' and PaymentCategory='Registration Fee' and 	PaymentState='pending' and PaymentSession='$pp'";	
	$result2 = mysqli_query($dbc, $query2);	
				
				if(mysqli_num_rows($result2)==0){
					include_once('payrequest_pg_sem.php');
				}else{
					$PaymentCategory='Registration Fee';		
					include_once('fail_transact_pg_sem.php');
				}					
}

?>


