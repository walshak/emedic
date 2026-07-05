<?php require_once('Connections/ePConn.php'); ?>
<?php
include('connect_line.php');


if($_POST["update_bld_grp"]){

$bld_grp=$_POST['bld_grp'];
$stdid=$_POST['stdid'];
$urlstdid=trim($_POST['urlstdid']);
$stmt="UPDATE pgapplication SET blood_grp='$bld_grp' WHERE applicant_id='$stdid'";
				//GetSQLValueString($_POST["msg"], "text"),
				//	GetSQLValueString($_POST['username'], "text"));
$db->exec($stmt);
	header("location:main.php?stdid=$urlstdid&view=adm");
}

//if (isset($_GET['stdid'])){
//$stdid=$_GET['stdid'];

//}

//include('encodeco.php');
//$stdid=decode("$urlstdid","$mykey");

//$stdid='M1303952';
$querys="SELECT * FROM  pgapplication WHERE applicant_id='$stdid'";	
	$results = mysqli_query($dbc, $querys);	
		
	//if(mysqli_num_rows($results)==1){
	//$results = mysqli_query($querys) or die('cannot query database:' . mysql_error());
	while ($row = mysqli_fetch_array($results)){
	
			 $RegNumb = $row['applicant_id'];
			$CandName = $row['display_fullname'];
			$sex = $row['sex'];
			//$School = $row['school'];
		//	$Course = $row['dept'];
			$email = $row['email'];
		//	$entry_session = $row['entry_session'];
		//	$matno = $row['MatNumber'];
			$statelga = $row['lga'];
			//$status = $row['status'];
			$nat = $row['nationality'];
					$discipline = $row['course1'];
			$currentsession = $row['session'];
			$student_mode= $row['student_mode'];
			$blood_grp= $row['blood_grp'];
	}

?>

<form id="form1" name="form1" method="post" action="admdata.php">
 

  <table class="altrowstable" id="alternatecolor4" width="500" border="1">
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td width="162">Application No.</td>
      <td width="322"><?php echo $RegNumb; ?></td>
    </tr>
    <tr>
      <td>Candidate Name:</td>
      <td><?php echo $CandName; ?></td>
    </tr>
    <tr>
      <td>Email Address:</td>
      <td><?php echo $email; ?></td>
    </tr>
    <tr>
      <td>State/LGA:</td>
      <td><?php echo $statelga; ?></td>
    </tr>
    <tr>
      <td>Nationality:</td>
      <td><?php echo $nat; ?></td>
    </tr>
    <tr>
      <td>Gender:</td>
      <td><?php echo $sex; ?></td>
    </tr>
    <tr>
      <td>Study Course:</td>
      <td><?php echo $discipline; ?></td>
    </tr>
    <tr>
      <td>Entry Session:</td>
      <td><?php echo $currentsession; ?></td>
    </tr>
   
   <?php if($blood_grp!=''){?>
       <tr>
      <td>Blood Group:</td>
      <td><?php echo $blood_grp; ?></td>
    </tr> 
     <?php }else{ ?>
       <tr>
       <td>
       <strong style="color:#F00; font-size:13px">UPDATE YOUR BLOOD GROUP</strong>
       </td>
      <td>
      
   <select name="bld_grp" id="bld_grp">
      <option value="">-- select --</option>
        <option value="O-">O-</option>
        <option value="O+">O+</option>
        <option value="A+">A+</option>
        <option value="A-">A-</option>
        <option value="B-">B-</option>
        <option value="B+">B+</option>
        <option value="AB-">AB-</option>
        <option value="AB+">AB+</option>
</select>
   
      </td>
    </tr> 

       <tr>
       <td>
       </td>
      <td>
	  
  
<input type="hidden" name="stdid" id="stdid" value ="<?php echo $stdid; ?>" /> 
  
<input type="hidden" name="urlstdid" id="urlstdid" value="<?php echo $urlstdid; ?>" />
<input type="submit" name="update_bld_grp" id="update_bld_grp" value="Update" />


      </td>
    </tr> 

 <?php } ?>
  </table>
  <p>&nbsp;</p>
  <p>&nbsp;</p>



            
  
</form>
<label for="dob"></label>

