
<?php

include('connect_line.php');
$stdid='1234567';

$querys="SELECT * FROM adms2013 WHERE stdid='$stdid'";	
	$results = mysqli_query($dbc, $querys);	
		
	//if(mysqli_num_rows($results)==1){
	//$results = mysqli_query($querys) or die('cannot query database:' . mysql_error());
	while ($row = mysqli_fetch_array($results)){
	
			$RegNumb = $row['RegNumb'];
			$CandName = $row['CandName'];
			$statelga = $row['StateOfOrigin'];
			$sex = $row['Sex'];
			$School = $row['UPASE_COURSE'];
			$Course = $row['FUTMX_SCH_CODE'];
			$email = $row['email'];
}

?>

			<table class="altrowstable" id="alternatecolor" width="500" border="1">
  <tr>
    <td width="162">JAMB Registration No.</td>
    <td width="322"><?php echo $stdid; ?></td>
  </tr>
  <tr>
    <td>Candidate Name:</td>
    <td><?php echo $CandName; ?></td>
  </tr>
  <tr>
    <td>State:</td>
    <td><?php echo $statelga; ?></td>
  </tr>
  <tr>
    <td>Gender:</td>
    <td><?php echo $sex; ?></td>
  </tr>
  <tr>
    <td>Department:</td>
    <td><?php echo $Course; ?></td>
  </tr>
  <tr>
    <td>School:</td>
    <td><?php echo $School; ?></td>
   
  </tr>
    <tr>
    <td>Email Address:</td>
    <td><?php echo $email; ?></td>
   
  </tr>
</table>