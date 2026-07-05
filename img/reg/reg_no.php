<?php
set_time_limit(0);
$counter=1;
$dbc = mysqli_connect('localhost', 'root', '$kan@e-portalgk', 'pg_database');

$query1314="SELECT * FROM TABLE17";	
$result1314 = mysqli_query($dbc, $query1314);
	
	while ($row1314 = mysqli_fetch_array($result1314)){
		$pg_id = $row1314['pg_id'];

		$query1415="SELECT * FROM  reg_password where stdid='$pg_id'";	
		$result1415 = mysqli_query($dbc, $query1415);		
		$rowcount1415=mysqli_num_rows($result1415);

//echo "<br>";

if ($rowcount1415>0){
	$updt="UPDATE reg_password SET session ='2015/2016' WHERE stdid='$pg_id'";					
	$result = mysqli_query($dbc, $updt);
	}

}

echo "Finish";		

?>




