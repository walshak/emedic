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


//$stdid='M140';
$amt='1000';


$query_rstSelect = sprintf("SELECT * FROM e_data WHERE stdid= %s", GetSQLValueString('M160', "text"));
$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
$row_rstSelect = mysqli_fetch_array($rstSelect);
		
		$new_level="";
		$RegNumb = $row_rstSelect['RegNumb'];
		$CandName = $row_rstSelect['CandName'];
		$PaymentItem = $row_rstSelect['dept'];
		$level=$row_rstSelect['level'];	
		$student_mode=$row_rstSelect['student_mode'];
		$student_mode2=$row_rstSelect['student_mode'];
		$status=$row_rstSelect['status'];
			if ($student_mode=='RS'){
				$new_level=$level+100;
				} else{
				$new_level=$level;
					}
			
	
	$rdp=0; for($i=0; $i<5; $i++){$rd=rand(0, 9); $rdp=$rdp.$rd;}
	$txn_ref= $rdp.$stdid;
	
// des able
// FRESH STUDENT CLEARANCE STATUS
if($student_mode=='FS'){
		$query_rstSelect = sprintf("SELECT * FROM payment_log WHERE stdid='$stdid' and PaymentState='Paid' and PaymentSession='$current_session' and  PaymentCategory='Acceptance'");
		$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
			if (mysqli_num_rows($rstSelect)>0){$accpt_fee=1;}
}

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
</head>

<body>
<form action="goto_interwish_remita.php" form method="post" enctype="multipart/form-data" >
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
        <td width="271" class="fieldname">&nbsp;</td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Amount:
          <input name="amt2" type="hidden" id="amt2" value="<?php echo 'N' . $amt; ?>" />
        </strong></td>
        <td class="fieldname"><?php echo 'N' . $amt; ?></td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Transaction Reference Number:
          <input name="txn_ref" type="hidden" id="txn_ref" value="<?php echo $txn_ref; ?>" />
        </strong></td>
        <td class="fieldname"><?php echo $txn_ref ?></td>
      </tr>
      
      <tr>
        <td width="296"><strong>Candidates Name:
          <input name="payerName" type="hidden" id="payerName" value="<?php echo $CandName; ?>" />
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
          <input name="PaymentCategory" type="hidden" id="PaymentCategory" value="<?php echo 'Pay Test'; ?>" />
        </strong></td>
        <td class="fieldname"><?php echo $paycat; ?></td>
      </tr>
      <tr>
        <td>Payment Type:</td>
        <td class="fieldname"><select class="required-entry" title="Credit Card Type" name="paymenttype" id="paymenttype" autocomplete="off">
          <option>-- Select Payment Type --</option>
 						<option value="VERVE"> Verve Card</option>
						<option value="VISA"> Visa</option>
						<option value="MASTERCARD"> MasterCard</option>
						<option value="POCKETMONI"> PocketMoni</option>
						<option value="POS"> POS</option>
						<option value="ATM"> ATM</option>
						<option value="BANK_BRANCH">BANK BRANCH</option>
						<option value="BANK_INTERNET">BANK INTERNET</option>
						<option value="REMITA_PAY"> Remita Account Transfer</option>
                        <option value="RRR"> RRR</option>
        </select></td>
      </tr>
      <tr>
        <td>Email</td>
        <td class="fieldname"><input name="payerEmail" required value="" type="email"></td>
      </tr>
      
      <tr>
        <td colspan="2" align="center" bgcolor="#FF0000"><p>WARNING! <br />
        DON'T PROCEED IF THIS IS NOT YOUR CURRENT LEVEL FOR THIS SESSION. <br />
        REPORT TO I.T.S DEPARTMENT</p></td>
      </tr>
      <tr>
        <td><strong> Payment Level:
          <input type="hidden" name="level" id="level" value="<?php echo $new_level; ?>" />
        </strong></td>
        <td><?php echo $new_level; ?></td>
      </tr>
      <tr>
        <td><strong>
          <input name="stdid" type="hidden" id="stdid" value="<?php echo $stdid; ?>" />
          <input type="hidden" name="session" id="session" value="<?php echo $session; ?>" />
          <input type="hidden" name="amt" id="amt" value="<?php echo $amt; ?>" />
			<input type="hidden" name="paycat" id="paycat" value="<?php echo $paycat; ?>" />
            <input type="hidden" name="student_mode" id="student_mode" value="<?php echo $student_mode; ?>" />
        </strong></td>
        <td></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>
        <?php
		echo '<input class="btn primary" name="SUBMIT" value="Submit Payment" type="submit" />'; 					
		?>
		</td>
      </tr>
      <tr>
        <td><img src="banner.png" width="300" height="65" /></td>
        <td>&nbsp;</td>
      </tr>
    </tbody>
  </table>
  <br>
</form>
</body>
</html>



