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
$amt='5200';
$session='2016/2017';


$query_rstSelect = sprintf("SELECT * FROM pgapplication WHERE applicant_id= %s", GetSQLValueString($stdid, "text"));
$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
$row_rstSelect = mysqli_fetch_array($rstSelect);
		
		
		$stdid = $row_rstSelect['applicant_id'];
		$CandName = $row_rstSelect['display_fullname'];
		$PaymentItem = $row_rstSelect['course1'];
		
		$email=$row_rstSelect['email'];
		
			
	
	$rdp=0; for($i=0; $i<5; $i++){$rd=rand(0, 9); $rdp=$rdp.$rd;}
	$txn_ref= $rdp.$stdid;
	


	?>


<!DOCTYPE HTML>
<head>
<title>
Remita Payment Gateway
</title>
</style>
</head>
<body>
<form action="processpayment.php" form method="post" enctype="multipart/form-data" >
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
          <input name="PaymentCategory" type="hidden" id="PaymentCategory" value="Restitution Fee" />
        </strong></td>
        <td class="fieldname">Restitution Fee</td>
      </tr>
      <tr>
        <td>Payment Type:</td>
        <td class="fieldname"><select class="required-entry" title="Credit Card Type" name="paymenttype" id="paymenttype" autocomplete="off">
          <option>-- Select Payment Type --</option>
        
						<option value="VISA"> Visa</option>
						<option value="MASTERCARD"> MasterCard</option>
                        <option value="RRRGEN">BANK BRANCH Payment Using (RRR)</option>
        </select></td>
      </tr>
     
     
      <tr>
        <td><strong>
          <input name="stdid" type="hidden" id="stdid" value="<?php echo $stdid; ?>" />
          <input type="hidden" name="session" id="session" value="<?php echo $session; ?>" />
          <input type="hidden" name="amt" id="amt" value="<?php echo $amt; ?>" />
          <input type="hidden" name="payerPhone" id="payerPhone" value="<?php echo '080'; ?>" />
			<input type="hidden" name="paycat" id="paycat" value="<?php echo $paycat; ?>" />
         
            <input type="hidden" name="payerEmail" id="payerEmail" value="<?php if($email==''){echo'info@futminna.edu.ng';}else{echo $email;} ?>" />
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