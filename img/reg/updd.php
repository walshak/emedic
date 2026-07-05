<?php
//set_time_limit(0);
//$counter=1;
include('connect_line.php');

	
//2013/2014


$query1314="SELECT * FROM  TABLE13";	
	$result1314 = mysqli_query($dbc, $query1314);
	 //$rowcount1314=mysqli_num_rows($result1314);
 
	while ($row1314 = mysqli_fetch_array($result1314)){
	//	$level1314=$row1314['PaymentLevel'];	
		//	$RegNumb = $row1314['RegNumb'];
			$stdid = $row1314['COL1'];
			//$title = $row1314['title'];
		   	//$applicant_id = $row1314['applicant_id'];
			
	$updt="UPDATE pgapplication SET state=3 WHERE applicant_id='$stdid'";					
$result = mysqli_query($dbc, $updt);	


}

echo "Finish";		

?>








