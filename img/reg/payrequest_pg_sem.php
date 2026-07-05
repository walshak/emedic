<?php require_once('Connections/ePConn.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
	Global $conn;
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }
//$theValue="lk";
  $theValue = function_exists("mysqli_real_escape_string") ? mysqli_real_escape_string($conn, $theValue) : mysqli_escape_string($conn, $theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}



$query_rscheckco = sprintf("SELECT * FROM pgapplication WHERE applicant_id = %s", GetSQLValueString($stdid, "text"));
$rscheckco =  mysqli_query($conn,$query_rscheckco) or die(mysqli_connect_errno());
$row_rscheckco = mysqli_fetch_array($rscheckco);
$totalRows_rscheckco = mysqli_num_rows($rscheckco);

 //$exst_session=$row_rscheckco['level_ses'];
		$RegNumb = $row_rscheckco['applicant_id'];
		$CandName = $row_rscheckco['display_fullname'];
		$PaymentItem = $row_rscheckco['programme_title'] . ': ' . $row_rscheckco['course1'];
		$student_status  = $row_rscheckco['student_status'];
		$course_time  = $row_rscheckco['course_time'];
		$programme_title =$row_rscheckco['programme_title'];
		
			if (trim(strtoupper($row_rscheckco['course1']))=='DISASTER RISK MANAGEMENT AND DEVELOPMENT STUDIES'){
					if ($programme_title=='Masters'){
						$amtstring= 'MDRM_' . $student_status . '_' . $course_time;
					}else{
						$amtstring= 'PGDRM_' . $student_status . '_' . $course_time;
					}
			} elseif (trim(strtoupper($row_rscheckco['course1']))=='SUSTAINABLE URBAN DEVELOPMENT'){
						$amtstring= 'MSUD_' . $student_status . '_' . $course_time;
			} elseif (trim(strtoupper($row_rscheckco['course1']))=='URBAN ECOLOGY'){
						$amtstring= 'MSUD_' . $student_status . '_' . $course_time;
			} else {
						$amtstring= trim($programme_title) . '_' . $student_status . '_' . $course_time;
			}
		
	$rdp=0; for($i=0; $i<5; $i++){$rd=rand(0, 9); $rdp=$rdp.$rd;}
	$txn_ref= $rdp.$stdid;
	
	/// fees query 
	
	
$query_rsfees = sprintf("SELECT * FROM fees WHERE prog_title = %s and session=%s", GetSQLValueString($amtstring, "text"),GetSQLValueString($pp, "text"));
$rsfees =  mysqli_query($conn,$query_rsfees) or die(mysqli_connect_errno());
$row_rsfees = mysqli_fetch_array($rsfees);
//$totalRows_rscheckco = mysqli_num_rows($rsfees);
			$reg_amount = $row_rsfees['amt_sem'];
			
			
		?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<style type="text/css">
<!--
.style3 {	font-size: 16px
}
.style4 {color: #FFFFFF}
-->
</style>

<script LANGUAGE="JavaScript">
<!--
function ValidateForm(form){
ErrorText= "";
if ( ( form.phd[0].checked == false ) && ( form.phd[1].checked == false ) ) { alert ( "Please choose your PhD: Full Time or Part Time" ); return false; }
if (ErrorText= "") { form.submit() }
}
-->
</script>

</head>

<body>
<form action="goto_interwish_pg.php" form method="post" enctype="multipart/form-data" >
        <input type="hidden" name="MAX_FILE_SIZE">
  <table class="altrowstable" id="alternatecolor" width="583" border="1">
    <tbody>
      
      
      <tr>
        <td colspan="2" class="fieldname"><div align="center"></div></td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#FF6600" class="fieldname"><div align="center" class="style4">Are you sure you want to submit these Payment Data to CollegePay FUT Minna?</div></td>
      </tr>
      <tr>
        <td class="fieldname">&nbsp;</td>
        <td class="fieldname">&nbsp;</td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Amount:
            <input name="amt" type="hidden" id="amt" value="<?php  echo $reg_amount; ?>" />
        </strong></td>
        <td class="fieldname"><?php  echo 'N' . number_format($reg_amount,2); ?> 
          
  
		</td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Transaction Reference Number:
          <input name="txn_ref" type="hidden" id="txn_ref" value="<?php echo $txn_ref; ?>" />
        </strong></td>
        <td class="fieldname"><?php echo $txn_ref; ?></td>
      </tr>
      
      <tr>
        <td width="296"><strong>Candidates Name:
          <input name="CandName" type="hidden" id="CandName" value="<?php echo $CandName; ?>" />
        </strong></td>
        <td class="fieldname"><?php echo $CandName; ?></td>
      </tr>
      
      <tr>
        <td width="296"><strong>Payment Item:
          <input name="PaymentItem" type="hidden" id="PaymentItem" value="<?php echo $PaymentItem; ?>" />
        </strong></td>
        <td><span class="fieldname"><?php echo $PaymentItem; ?></span></td>
      </tr>
      <tr>
        <td><strong>Payment Category
          <input name="PaymentCategory" type="hidden" id="PaymentCategory" value="Registration Fee" />
        </strong></td>
        <td width="271" class="fieldname">Registration Fee</td>
      </tr>
      

      <tr>
        <td><strong>Course Time:
            
        </strong></td>
        <td><span class="fieldname"><?php echo $course_time; ?></span></td>
      </tr>
      <tr>
        <td><strong>Student Status: </strong></td>
        <td><span class="fieldname"><?php echo $student_status; ?></span></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td></td>
      </tr>
      <tr>
        <td><strong>
          <input name="stdid" type="hidden" id="stdid" value="<?php echo $stdid; ?>" />
          <input type="hidden" name="tagging" id="tagging" value="<?php echo $tagging; ?>" />
          <input type="hidden" name="session" id="session" value="<?php echo $pp; ?>" />
        </strong></td>
        <td></td>
      </tr>
      <tr>
        <td><img src="banner.png" width="232" height="41" /></td>
        <td><input class="btn primary" name="SUBMIT" value="Submit Payment" onClick="ValidateForm(this.form)" type="submit" /></td>
      </tr>
    </tbody>
  </table>
  <br>
</form>
</body>
</html>
