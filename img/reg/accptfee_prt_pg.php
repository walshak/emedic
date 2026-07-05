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


require_once('Connections/ePConn.php'); 

$productid=4288;

$mackey='494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
$hashdata=$productid . $transactionreference . $mackey;
$transcStatus='hash:' . strtoupper(hash('SHA512',$hashdata));

$ch = curl_init('https://webpay.interswitchng.com/paydirect/api/v1/gettransaction.json?productid='.$productid.'&transactionreference='.$transactionreference.'&amount='.$amt.'00');

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array($transcStatus,'Content-Type: application/json')
);
$result = curl_exec($ch);
$ar=json_decode($result,true);

$response_code=$ar['ResponseCode'];
$TransactionDate=$ar['TransactionDate'];

$a=array("Your Transaction Was Successful"=>"00","Refer to Financial Institution"=>"01","Refer to Financial Institution, Special Condition"=>"02","Invalid Merchant"=>"03","Pick-up card"=>"04","Do Not Honor"=>"05","Error"=>"06","Pick-Up Card, Special Condition"=>"07","Honor with Identification"=>"08","Request in Progress"=>"09","Approved by Financial Institution, Partial"=>"10","Approved by Financial Institution, VIP"=>"11","Invalid Transaction"=>"12","Invalid Amount"=>"13","Invalid Card Number"=>"14","No Such Financial Institution"=>"15","Approved by Financial Institution, Update Track 3"=>"16","Customer Cancellation"=>"17","Customer Dispute"=>"18","Re-enter Transaction"=>"19","Invalid Response from Financial Institution"=>"20","No Action Taken by Financial Institution"=>"21","Suspected Malfunction"=>"22","Unacceptable Transaction Fee"=>"23","File Update not Supported"=>"24","Unable to Locate Record"=>"25","Duplicate Record"=>"26","File Update Field Edit Error"=>"27","File Update File Locked"=>"28","File Update Failed"=>"29","Format Error"=>"30","Bank Not Supported"=>"31","Completed Partially by Financial Institution"=>"32","Expired Card, Pick-Up"=>"33","Suspected Fraud, Pick-Up"=>"34","Contact Acquirer, Pick-Up"=>"35","Restricted Card, Pick-Up"=>"36","Call Acquirer Security, Pick-Up"=>"37","PIN Tries Exceeded, Pick-Up"=>"38","No Credit Account"=>"39","Function not Supported"=>"40","Lost Card, Pick-Up"=>"41","No Universal Account"=>"42","No Investment Account"=>"44","Insufficient Funds"=>"51","No Check ccount"=>"52","No Savings Account"=>"53","Expired Card"=>"54","Incorrect PIN"=>"55","No Card Record"=>"56","Transaction not permitted to Cardholder"=>"57","Transaction not permitted on Terminal"=>"58","Suspected Fraud"=>"59","Contact Acquirer"=>"60","Exceeds Withdrawal Limit"=>"61","Restricted Card"=>"62","Security Violation"=>"63","Original Amount Incorrect"=>"64","Exceeds withdrawal frequency"=>"65","Call Acquirer Security"=>"66","Hard Capture"=>"67","Response Received Too Late"=>"68","PIN tries exceeded"=>"75","Reserved for Future Postilion Use"=>"76","Intervene, Bank Approval Required"=>"77","Intervene, Bank Approval Required for Partial Amount"=>"78","Cut-off in Progress"=>"90","Issuer or Switch Inoperative"=>"91","Routing Error"=>"92","Violation of law"=>"93","Duplicate Transaction"=>"94","Reconcile Error"=>"95","System Malfunction"=>"96","Exceeds Cash Limit"=>"98","Unexpected error"=>"A0","Transaction not permitted to card holder, via channels"=>"A4","Transaction Status Unconfirmed"=>"Z0","Transaction Error"=>"Z1","Bank account error"=>"Z2","Bank collections account error"=>"Z3","Interface Integration Error"=>"Z4","Duplicate Reference Error"=>"Z5","Incomplete Transaction"=>"Z6","Transaction Split Pre-processing Error"=>"Z7","Invalid Card Number, via channels"=>"Z8","Transaction not permitted to card holder, via channels"=>"Z9");

$ResponseDescription=array_search($response_code,$a);
// decide

$en_urlstdid=substr($transactionreference,6);
include_once('encodeco.php');
$urlstdid=encode("$en_urlstdid","$mykey");

if ($response_code=='00')
{
// pull over my records
  $updateSQL = sprintf("UPDATE payment_log SET PaymentDate=%s, PaymentState=%s, ResponseDescription=%s WHERE PaymentID=%s",
                       GetSQLValueString($TransactionDate, "text"),
					   GetSQLValueString('Paid', "text"),
                       GetSQLValueString($ResponseDescription, "text"),
					   GetSQLValueString($transactionreference, "text"));

 
  $Result1 =  mysqli_query($conn,$updateSQL) or die(mysqli_connect_errno());

// pull over my records
  $updateSQL1 = sprintf("UPDATE pgapplication SET state=3 WHERE applicant_id='$en_urlstdid'");

 
  $Result11 =  mysqli_query($conn,$updateSQL1) or die(mysqli_connect_errno());

$viewme='payrpt_pg';
include_once('main.php');

} else {
	
// pull over my records
  $updateSQL = sprintf("UPDATE payment_log SET ResponseDescription=%s WHERE PaymentID=%s",
                       GetSQLValueString($ResponseDescription, "text"),
					   GetSQLValueString($transactionreference, "text"));

 
  $Result1 =  mysqli_query($conn,$updateSQL) or die(mysqli_connect_errno());

$viewme='fail_transac_pg';
include_once('main.php');
}
?>

