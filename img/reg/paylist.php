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


$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["new_pay2"])) && ($_POST["MM_update"] == "pdetail")) {
	header("location:main.php?view=paylist&stdid=$urlstdid&p=1");
}

if ((isset($_POST["pay_now"])) && ($_POST["MM_update"] == "pdetail")) {
	
	$pay_type=$_POST['pay_type'];
		$session=$_POST['session'];
		$stdid=$_POST['stdid'];
		$pay_detail=$pay_type . '_' . $session;
			
			include_once('encodeco.php');
				$url_pay=encode("$pay_detail","$mykey");


$query_rscheckco = sprintf("SELECT * FROM pgapplication WHERE applicant_id = %s", GetSQLValueString($stdid, "text"));
$rscheckco =  mysqli_query($conn,$query_rscheckco) or die(mysqli_connect_errno());
$row_rscheckco = mysqli_fetch_array($rscheckco);
$totalRows_rscheckco = mysqli_num_rows($rscheckco);
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

/// GET 

$query_rsfees = sprintf("SELECT * FROM fees WHERE prog_title=%s and session=%s", GetSQLValueString($amtstring, "text"),GetSQLValueString($session, "text"));
$rsfees =  mysqli_query($conn,$query_rsfees) or die(mysqli_connect_errno());
$row_rsfees = mysqli_fetch_array($rsfees);
//$h=$row_rsfees['amt'];

if(mysqli_num_rows($rsfees)>0){
				$session_amt = $row_rsfees['amt'];
			$sem_amt = $row_rsfees['amt_sem'];
} else{
				$session_amt = '0';
			$sem_amt = '0';
	}
			
if($pay_type=='SemesterOne' and $sem_amt>0){
		header("location:main.php?view=pg_fees2&stdid=$urlstdid&pdesc=$url_pay");
}elseif($pay_type=='SemesterTwo' and $sem_amt>0){
		header("location:main.php?view=pg_fees2&stdid=$urlstdid&pdesc=$url_pay");
	}elseif($pay_type=='session' and $session_amt>0){
		header("location:main.php?view=pg_fees2&stdid=$urlstdid&pdesc=$url_pay");
	}elseif($pay_type=='Restitution_fee'){
		header("location:main.php?view=pg_fees2&stdid=$urlstdid&pdesc=rest");		
	}else{
		header("location:main.php?view=paylist&stdid=$urlstdid&err");
		
		}

//include_once('encodeco.php');
//$urlstdid=encode("$stdid3","$mykey");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Payment List</title>
<style type="text/css">
<!--
.style1 {	font-family: Verdana, Arial, Helvetica, sans-serif
}
.tfont {	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 12px;
	font-weight: normal;
	color: #000033;
}
.style2 {
	color: #FFFFFF;
	font-weight: bold;
}
.style3 {	color: #FF0000;
	font-weight: bold;
}
-->
</style>
</head>

<body>
      <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="pdetail" id="pdetail">
        <input type="hidden" name="MAX_FILE_SIZE">

<br />

 
    <?php
	
	include('connect_line.php');


$query="SELECT * FROM payment_log WHERE applicant_id ='$stdid' and PaymentCategory='Registration Fee' and PaymentState='Paid'";	
	$result = mysqli_query($dbc, $query);
		if (mysqli_num_rows($result)>0){
				$pay_status=1;
				
				?>
                 <table class="altrowstable" id="alternatecolor4" width="500" border="1">
      
      
      <td width="192" class="tfont"><strong><strong>Session</strong></strong></td>
      <td width="139" class="tfont"><strong>Payment ID</strong></td>
      <td width="118" class="tfont"><strong>Amount</strong></td>

                
                <?php 
				}
			while ($row = mysqli_fetch_array($result)){
			
               // $session=$row['PaymentSession'];
				//$amt=$row['AmountAuthorized'];	?>
    <tr>
            <td width="130" height="20" class="tfont"><?php echo $row['PaymentSession']; ?></td>
            <td width="130" height="20" class="tfont"><?php echo $row['PaymentID']; $payid=$row['PaymentID']; ?></td>

      <td width="130" height="20" class="tfont"><?php echo $row['AmountAuthorized']; ?></td>
      <td width="143"><?php echo '<a href="main.php?' . '&stdid=' . $urlstdid .  '&view=payrpt_pg' . '&txnRef=' . $payid  .'">Print</a>'; ?></td>
    </tr>
    <?php 
	
	} ?>
  </table>
  <p>&nbsp;</p>
  <p>
<?php 	   			

if ($new_pay=='1' and $student_status=='PGReturn'){ ?>
       <table>
     <tr>
      <td>Session:</td>
      <td colspan="2"><select name="session" id="session">
             <option selected="selected" value="">Select ...</option>

        <option value="2014/2015">2014/2015</option>
        <option value="2015/2016">2015/2016</option>
		<option value="2016/2017">2016/2017</option>
        <option value="2016/2017">2017/2018</option>
        <option>-- select --</option>
      </select></td>
    </tr>
    <tr>
      <td>Payment Type:</td>
      <td colspan="2"><select name="pay_type" id="pay_type">
       <option selected="selected" value="">Select ...</option>
              <option value="session">Session(Complete)</option>
        <option value="SemesterOne">1st Semester(Part Payment)</option>
        <option value="SemesterTwo">2nd Semester(Part Payment)</option>
      </select></td>
      </tr>
      <tr>
      		<td><input type="submit" name="pay_now" id="pay_now" value="Pay Now" /></td>

    </tr>

</table>


<?php }elseif($err=='1' and $student_status=='PGReturn'){ ?>
     
<?php }elseif($new_pay!='1' and $student_status=='PGReturn'){ ?>

<input type="submit" name="new_pay2" id="new_pay2" value="New Payment" />

<?php } ?>

<?php if ($student_status=='PGFresh' and $pay_status!=1){ ?>

             <input type="hidden" name="session" id="session" value ="2017/2018" /> 
 <input type="hidden" name="pay_type" id="pay_type" value ="session" /> 
            <input type="submit" name="pay_now" id="pay_now" value="Start Payment/Continue" />

<?php } ?>

  </p>
  
  
 <input type="hidden" name="stdid" id="stdid" value ="<?php echo $stdid; ?>" /> 
           <input type="hidden" name="MM_update" value="pdetail" />

</form>
</body>
</html>
