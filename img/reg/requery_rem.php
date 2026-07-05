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



require 'remita_constants.php';
//$orderID = "";
//if( isset( $_GET['orderID'] )) {
//$orderID = $_GET["orderID"];
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
?>
<html>
<head>
<title></title>
</head>
<body>

		<?php if($response_code == '01' || $response_code == '00') { ?>
		<?php
				//echo $orderID;
				$setdate=date("Y-m-d");
		$updateSQL = sprintf("UPDATE payment_log SET PaymentState=%s, ResponseDescription=%s, PaymentDate=%s WHERE PaymentID=%s",
				GetSQLValueString('Paid', "text"),
				GetSQLValueString('Paid Successfully', "text"),
				GetSQLValueString($setdate, "text"),
				GetSQLValueString($orderID, "text"));
						 
				$Result1 =  mysqli_query($conn,$updateSQL) or die(mysqli_connect_errno());		
		
		?>    
            <?php
            
            $query_rstSelect = sprintf("SELECT * FROM payment_log WHERE PaymentID= %s", GetSQLValueString($orderID, "text"));
            $rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
            $row_rstSelectRpt = mysqli_fetch_array($rstSelect);
            ?>        
                 <table class="altrowstable" id="alternatecolor" width="583" border="1">
                    <tbody>
                          <tr>
        <td colspan="2" class="fieldname">&nbsp;</td>
      </tr>
                      <tr>
                        <td width="225" class="fieldname"><strong>Student  ID:</strong></td>
                        <td width="335" class="fieldname"><?php echo $row_rstSelectRpt['applicant_id']; ?></td>
                      </tr>
                    
                      <tr>
                        <td><strong> Name:</strong></td>
                        <td class="fieldname"><?php echo $row_rstSelectRpt['candname']; ?></td>
                      </tr>
                      <tr>
                        <td><strong>Payment Category:</strong></td>
                        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentCategory']; ?></td>
                      </tr>
                      <tr>
                        <td><strong>Study Course:</strong></td>
                        <td><span class="fieldname"><?php echo $row_rstSelectRpt['PaymentItem']; ?></span></td>
                      </tr>
                      <tr>
                        <td class="fieldname"><strong>Transaction ID:</strong></td>
                        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentID']; ?></td>
                      </tr>
                      <tr>
                        <td class="fieldname"><strong>Amount:</strong></td>
                        <td class="fieldname"><strong style="font-size:14px" ><?php echo number_format($row_rstSelectRpt['AmountAuthorized'],2,'.',','); ?></strong></td>
                      </tr>
                      <tr>
                        <td><strong>Payment Status:</strong></td>
                        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentState']; ?></td>
                      </tr>
                      <tr>
                        <td class="fieldname">Payment Date:</td>
                        <td class="fieldname"><?php echo date('d M, Y', strtotime($row_rstSelectRpt['PaymentDate'])); ?></td>
                      </tr>
                      <tr>
                        <td class="fieldname">Payment Session</td>
                        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentSession']; ?></td>
                      </tr>
                      <tr>
                        <td class="fieldname">Current Level:</td>
                        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentLevel']; ?></td>
                      </tr>
                    </tbody>
                  </table>
                         
            <br><br><br>
            <a href="javascript:window.print()"><button  class="btn primary">
                      <div align="center" class="btn primary">Click Here to Print</div>
                      </button>
            </a>  
            
			<?php }else if($response_code == '021') { ?>
			<?php
				$updateSQL = sprintf("UPDATE payment_log SET RRR=%s WHERE PaymentID=%s",
				GetSQLValueString($rrr, "text"),
				GetSQLValueString($orderID, "text"));
						 
				$Result1 =  mysqli_query($conn,$updateSQL) or die(mysqli_connect_errno());
            ?>    

            <div align="right" style="font-size:18px">Remita Retrieval Reference(RRR): &nbsp;&nbsp;&nbsp;<br></div>
            <div align="right"><strong style="font-size:35px"><?php echo $rrr;?>&nbsp;&nbsp;</strong></div>
            <br>
            <br>
            
            <?php
            
            $query_rstSelect = sprintf("SELECT * FROM payment_log WHERE PaymentID= %s", GetSQLValueString($orderID, "text"));
            $rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
            $row_rstSelectRpt = mysqli_fetch_array($rstSelect);
            ?>
            <table class="altrowstable" id="alternatecolor" width="583" border="1">
                <tbody>
                  <tr>
                    <td width="225" class="fieldname"><strong>Student ID:</strong></td>
                    <td width="335" class="fieldname"><?php echo $row_rstSelectRpt['applicant_id'];  ?></td>
                  </tr>
                  <tr>
                    <td><strong> Name:</strong></td>
                    <td class="fieldname"><?php echo $row_rstSelectRpt['candname']; ?></td>
                  </tr>
                  <tr>
                    <td><strong>Payment Category:</strong></td>
                    <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentCategory']; ?></td>
                  </tr>
                  <tr>
                    <td class="fieldname"><strong>Transaction ID:</strong></td>
                    <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentID']; ?></td>
                  </tr>
                  <tr>
                    <td class="fieldname"><strong>Amount:</strong></td>
                    <td class="fieldname"><?php echo number_format($row_rstSelectRpt['AmountAuthorized'],2,'.',','); ?></td>
                  </tr>
                  <tr>
                    <td class="fieldname"><strong>Date Generated:</strong></td>
                    <td class="fieldname"><?php echo date('d M, Y', strtotime($row_rstSelectRpt['PaymentDate'])); ?></td>
                  </tr>
                </tbody>
              </table>
            <hr>
            <div style=" font-size:14px;">Print this page and proceed to any Bank for payment</div>           
                            
            <br><br><br>
            <a href="javascript:window.print()"><button  class="btn primary">
                      <div align="center" class="btn primary">Click Here to Print</div>
                      </button>
            </a>                    
                    <?php }	else{ ?>
                                    <h2>Your Transaction was not Successful</h2>
                                    <?php if ($rrr !=null){ ?>
                                     <p>Your Remita Retrieval Reference is <span><b><?php echo $rrr; ?></b></span><br />
                                    <?php } ?> 
                                      <p><b>Reason: </b><?php echo $response_message; ?><p>
                     <?php }?>

        
</body>
</html>