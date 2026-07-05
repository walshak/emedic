<?php

include('connect_line.php');

$querys="SELECT * FROM payment_log WHERE RegistrationNumber='$regno' and PaymentCategory='Acceptance Fee'";	
	$results = mysqli_query($dbc, $querys);	
		
	if(mysqli_num_rows($results)==1){
		while ($row = mysqli_fetch_array($results)){$PaymentState = $row['PaymentState'];} // payment status
					
						if ($PaymentState=='Paid') {
									include(); 
							} else{	
							// pull over payment request
										
								include(); }
								
								
					} else {
					// initiate transaction
					$rdp=0;
					for($i=0; $i<5; $i++){
					 $rd=rand(0, 9);
					  $rdp=$rdp.$rd;
					  }
	}
?>