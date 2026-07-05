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


require_once('remita_constants.php');

//if (isset($_GET['txnRef'])) {
//$transactionreference = $_GET['txnRef'];
$orderId = '030012PG610981';
//$amount = $_GET['amt'];
//}

$response_code ="";
$rrr = "";
$response_message = "";
//Verify Transaction
function remita_transaction_details($orderId){
		$mert =  MERCHANTID;
		$api_key =  APIKEY;
		$concatString = $orderId . $api_key . $mert;
		$hash = hash('sha512', $concatString);
		$url 	= CHECKSTATUSURL . '/' . $mert  . '/' . $orderId . '/' . $hash . '/' . 'orderstatus.reg';
		//  Initiate curl
		$ch = curl_init();
		// Disable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Set the url
		curl_setopt($ch, CURLOPT_URL,$url);
		// Execute
		$result=curl_exec($ch);
		// Closing
		curl_close($ch);
		$response = json_decode($result, true);
		return $response;
	}

	if($orderID !=null){
		$response = remita_transaction_details($orderID);
		$response_code = $response['status'];
		if (isset($response['RRR']))
			{
			$rrr = $response['RRR'];
			}
		$response_message = $response['message'];
}

//$en_urlstdid=substr($transactionreference,6);
//include_once('encodeco.php');
//$urlstdid=encode("$en_urlstdid","$mykey");

$TransactionDate=date('Y-m-d');

 if($response_code == '01' || $response_code == '00') { 

/*/ pull over my records
  $updateSQL = sprintf("UPDATE payment_log SET PaymentDate=%s, PaymentState=%s, ResponseDescription=%s WHERE PaymentID=%s",
                       GetSQLValueString($TransactionDate, "text"),
					   GetSQLValueString('Paid', "text"),
                       GetSQLValueString('Paid Successfully', "text"),
					   GetSQLValueString($transactionreference, "text"));

 
  $Result1 = mysqli_query($updateSQL) or die(mysqli_connect_errno());
  */
 
 echo 'paid';

}else{
echo $orderId;
}

