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



include 'remita_constants.php';
$amount = $_POST["amt"];
$timesammp=DATE("dmyHis");		
$orderID = $_POST["txn_ref"];
$payerName = $_POST["payerName"];
$payerEmail = $_POST["payerEmail"];
$payerPhone = $_POST["payerPhone"];
$paymenttype = $_POST["paymenttype"];
$stdid = $_POST["stdid"];

$TK="token=$RegNumb;candname=$payerName;stdid=$stdid;view=rpt_remita;token=fr_rt";
        include_once('encodecourl.php');
    	$TK=encodeurl("$TK","$mykeyurl");	

$responseurl = PATH . "main.php?TK=" .$TK;
$concatString = MERCHANTID . SERVICETYPEID . $orderID . $amount . $responseurl . APIKEY;
$hash = hash('sha512', $concatString);


$query_rstSelect = sprintf("SELECT * FROM payment_log WHERE PaymentID=%s", GetSQLValueString($orderID, "text"));
$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
$row_rstSelect = mysqli_fetch_array($rstSelect);

		if(mysqli_num_rows($rstSelect)==0){
			$CandName =addslashes($CandName);

		$setdate=date('Y-m-d');
 $insertSQL = sprintf("INSERT INTO payment_log(applicant_id,candname,PaymentCategory,PaymentItem,PaymentID,RRR,PaymentLevel,PaymentSession,PaymentState,PaymentDate,AmountAuthorized,ResponseDescription,CurrentSessionPayment,requeryCount) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
 
  GetSQLValueString($stdid, "text"),
  GetSQLValueString($payerName, "text"),
  GetSQLValueString($_POST["PaymentCategory"], "text"),
  GetSQLValueString($_POST["PaymentItem"], "text"),
  GetSQLValueString($orderID, "text"),
    GetSQLValueString(' ', "text"),
  GetSQLValueString('600', "text"),
  GetSQLValueString('2016/2017', "text"),
  GetSQLValueString('pending', "text"),
  GetSQLValueString($setdate, "text"),
  GetSQLValueString($amount, "text"),
  GetSQLValueString(' ', "text"),
  GetSQLValueString(' ', "text"),
  GetSQLValueString('0', "text"));

 
  $Result1 =  mysqli_query($conn,$insertSQL) or die(mysqli_connect_errno());

		}
?>
<html>
<p>You will be redirected to Remita in few seconds.......</p>
<form action="<?php echo GATEWAYURL; ?>" id="remita_form" name="remita_form" method="POST">
<input id="merchantId" name="merchantId" value="<?php echo MERCHANTID; ?>" type="hidden"/>
<input id="serviceTypeId" name="serviceTypeId" value="<?php echo SERVICETYPEID; ?>" type="hidden"/>
<input id="amt" name="amt" value="<?php echo $amount; ?>" type="hidden"/>
<input id="responseurl" name="responseurl" value="<?php echo $responseurl; ?>" type="hidden"/>
<input id="hash" name="hash" value="<?php echo $hash; ?>" type="hidden"/>
<input id="payerName" name="payerName" value="<?php echo $payerName; ?>" type="hidden"/>
<input id="paymenttype" name="paymenttype" value="<?php echo $paymenttype; ?>" type="hidden"/>
<input id="payerEmail" name="payerEmail" value="<?php echo $payerEmail; ?>" type="hidden"/>
<input id="payerPhone" name="payerPhone" value="<?php echo $payerPhone; ?>" type="hidden"/>
<input id="orderId" name="orderId" value="<?php echo $orderID; ?>" type="hidden"/>
</form>
<script type="text/javascript">document.getElementById("remita_form").submit();</script>
</html>