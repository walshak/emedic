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



$json = file_get_contents('php://input');
$arr=json_decode($json,true);
	try {
		if($arr!=null)
			{
				foreach($arr as $key => $value)
					{
						$orderRef = $value['orderRef'];
						//Confirm transaction Status to be sure it is coming from Remita
						$response =  remita_transaction_details($orderRef);
						$response_code = $response['status'];
						$rrr = $response['rrr'];
						$response_reason = $response['message'];
						$rrr = $response['RRR'];
						$transactiondate = $response['transactiondate'];
					    $orderId = $response['orderId'];
						if($response_code == '01' || $response_code == '00')
							{
								// update 
$updateSQL = sprintf("UPDATE payment_log SET PaymentState=%s,transactiondate=%s WHERE PaymentID=%s or RRR=%s",
					   GetSQLValueString('Paid', "text"),
					   GetSQLValueString($transactiondate, "text"),
					   GetSQLValueString($orderId, "text"),
					   GetSQLValueString($rrr, "text"));

 
  $Result1 =  mysqli_query($conn,$updateSQL) or die(mysqli_connect_errno());		
									
							}
					}
				exit('OK');
			}
		
		}
		catch (Exception $e) {
			exit('Not OK');
		}
		include 'remita_constants.php';
function remita_transaction_details($orderId){
			$mert =  MERCHANTID;
		$api_key =  APIKEY;
		
	//$mert =  "";
	//$api_key = "";
	$mode = "Live";
	$hash_string = $orderId . $api_key . $mert;
	$hash = hash('sha512', $hash_string);
	if( $mode == 'Test' ){
		$query_url = 'http://www.remitademo.net/remita/ecomm';
		}
	else if( $mode == 'Live' ){
		$query_url = 'https://login.remita.net/remita/ecomm';
		}
	$url 	= $query_url . '/' . $mert  . '/' . $orderId . '/' . $hash . '/' . 'orderstatus.reg';
	$result = file_get_contents($url);
    $response = json_decode($result, true);
    return $response;
}
?>