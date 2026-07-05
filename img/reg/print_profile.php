
<?php

include('connect_line.php');

if (isset($_GET['stdid'])){
$stdid=$_GET['stdid'];
//$candname=$_GET['candname'];
}

//include('encodeco.php');
//$stdid=decode("$urlstdid","$mykey");

//$stdid='M1303952';
$querys="SELECT * FROM e_data_profile WHERE stdid='$stdid'";	
	$results = mysqli_query($dbc, $querys);	
		if(mysqli_num_rows($results)==0){
			//mysqli_query($dbc,"INSERT INTO e_data_profile(stdid) VALUES('$stdid')");
		}
	//$results = mysqli_query($querys) or die('cannot query database:' . mysql_error());
	while ($row = mysqli_fetch_array($results)){
	
			 $paddr = $row['paddr'];
			$phone = $row['phone'];
			$marital = $row['marital'];
			$religion = $row['religion'];
			$next_name = $row['next_name'];
			$next_rel = $row['next_rel'];
			$next_addr = $row['next_addr'];
			$next_phone = $row['next_phone'];
			$blood_group = $row['blood_group'];
	}
	
	$querys2="SELECT * FROM pgapplication WHERE applicant_id ='$stdid'";	
	$results2 = mysqli_query($dbc, $querys2);	
		if(mysqli_num_rows($results2)==0){
			//mysqli_query($dbc,"INSERT INTO e_data_profile(stdid) VALUES('$stdid')");
		}
	//$results = mysqli_query($querys) or die('cannot query database:' . mysql_error());
	while ($row2 = mysqli_fetch_array($results2)){
	
			 $CandName = $row2['display_fullname'];
			
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table class="altrowstable" id="alternatecolor4" width="500" border="1">
  <tr>
    <td width="204">&nbsp;</td>
    <td width="280">&nbsp;</td>
  </tr>
  <tr>
    <td>Candidate Name:</td>
    <td><?php echo $CandName; ?></td>
  </tr>
  <tr>
    <td>Phone Number:</td>
    <td><?php echo $phone; ?></td>
  </tr>
  <tr>
    <td>Parmanent Address:</td>
    <td><?php echo $paddr; ?></td>
  </tr>
  <tr>
    <td>Religion</td>
    <td><?php echo $religion; ?></td>
  </tr>
  <tr>
    <td>Blood Group:</td>
    <td><?php echo $blood_group; ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>Next of Kin Name</td>
    <td><?php echo $next_name; ?></td>
  </tr>
  <tr>
    <td>Next of Kin Relationship</td>
    <td><?php echo $next_rel; ?></td>
  </tr>
  <tr>
    <td>Next of Kin Address:</td>
    <td><?php echo $next_addr; ?></td>
  </tr>
  <tr>
    <td>Next of Kin Phone Number:</td>
    <td><?php echo $next_phone; ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><a href="javascript:window.print()"><button  class="btn primary">
          <div align="center" class="btn primary">Click Here to Print</div>
          </button>
        </a></td>
    <td>&nbsp;</td>
  </tr>
</table>
</body>
</html>