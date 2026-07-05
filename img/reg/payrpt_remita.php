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



$query_rstSelect = sprintf("SELECT * FROM payment_log WHERE PaymentID= %s", GetSQLValueString($transactionreference, "text"));
$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
$row_rstSelectRpt = mysqli_fetch_array($rstSelect);
$stdid = $row_rstSelectRpt['applicant_id'];
$PaymentLevel = $row_rstSelectRpt['PaymentLevel'];
	    ?>
        
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<table class="altrowstable" id="alternatecolor" width="583" border="1">
    <tbody>
      
      
      <tr>
        <td colspan="2" class="fieldname"><div align="center"><?php // echo '<img src="' . GW_UPLOADPATH . $screenshot . '" alt ="Fitures Image" />' ?></div></td>
        </tr>
      <tr>
        <td colspan="2" class="fieldname">&nbsp;</td>
      </tr>
      <tr>
        <td width="225" class="fieldname"><strong>Student  ID:</strong></td>
        <td width="335" class="fieldname"><?php echo $stdid; ?></td>
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
        <td class="fieldname"><strong><?php echo $row_rstSelectRpt['AmountAuthorized']; ?></strong></td>
      </tr>
      <tr>
        <td><strong>Payment Status:</strong></td>
        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentState']; ?></td>
      </tr>
      <tr>
        <td class="fieldname">Payment Date:</td>
        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentDate']; ?></td>
      </tr>
      <tr>
        <td class="fieldname">Payment Session</td>
        <td class="fieldname"><?php echo $row_rstSelectRpt['PaymentSession']; ?></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2"><a href="javascript:window.print()"><button  class="btn primary">
          <div align="center" class="btn primary">Click Here to Print</div>
          </button>
        </a></td>
      </tr>
    </tbody>
  </table>
</body>
</html>