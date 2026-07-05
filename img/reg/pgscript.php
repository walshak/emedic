<?php
include_once('connect_line.php');
$i=0;

			 

					//$querys="SELECT * FROM e_data where status=0 and student_mode='PG'";
					//$results3 = mysqli_query($dbc, $querys);		



$querys44="SELECT * FROM payment_log WHERE PaymentLevel='PG' and PaymentState='Paid' and PaymentCategory='Registration Fee'";	
	$results44 = mysqli_query($dbc, $querys44);	
	
								while ($row1 = mysqli_fetch_array($results44)){
									$stdid = $row1['stdid'];

			//if(mysqli_num_rows($results44)>0){							

$updt2="UPDATE e_data SET status='5' WHERE stdid='$stdid' AND student_mode='PG' AND status=1";
$result2 = mysqli_query($dbc, $updt2);							
							
							$i++;

											//		}


							
				
			}
							
							
					
 	
				
	echo $i;			
			



			 
?>	


