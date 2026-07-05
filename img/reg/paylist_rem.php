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



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<style type="text/css">
.tfont div {
	color: #F00;
}
</style>
</head>

<body>

<table width="583" border="1" class="altrowstable" id="alternatecolor2">
        <tr>
    </tr>
    <tr>
    </tr>
    <tr>
      <td  class="tfont">&nbsp;</td>
      <td  class="tfont">&nbsp;</td>
      <td  class="tfont">&nbsp;</td>
      <td class="tfont">&nbsp;</td>
    </tr>
    <td width="130"  class="tfont"><strong>Session</strong></td>
      <td width="240"  class="tfont"><strong>Transaction ID</strong></td>
      <td width="240"  class="tfont"><strong>RRR</strong></td>
      <td width="240"  class="tfont"><strong>Category</strong></td>
      <td width="300" class="tfont"><strong>Status</strong></td>
      <td width="300" class="tfont"><strong>Date</strong></td>
    </tr>
    <?php
		
		$query_rstSelect = sprintf("SELECT * FROM payment_log WHERE applicant_id='$stdid' and PaymentCategory='Restitution Fee' limit 15");
		$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
		$row_rstSelect_row = mysqli_num_rows($rstSelect);
		
		if ($row_rstSelect_row>0){
			while($row_rstSelect = mysqli_fetch_array($rstSelect)){?>
            <tr>
            		<td><?php echo $session; ?></td>
			      	<td><?php echo $row_rstSelect['PaymentID']; ?></td>
                    <td><?php echo $row_rstSelect['RRR']; ?></td>
					<td><?php echo $row_rstSelect['PaymentCategory']; ?></td>
                    <td><?php echo $row_rstSelect['PaymentState']; ?></td>
                    <td><?php echo date('d, M Y', strtotime($row_rstSelect['PaymentDate'])); ?></td>
                    <td><?php 
					

$TK="token=$RegNumb;candname=$payerName;stdid=$stdid;view=requery_remita;token=fr_rt";
        include_once('encodecourl.php');
    	$TK=encodeurl("$TK","$mykeyurl");	

			
		$orderID=$row_rstSelect['PaymentID'];		
						if($row_rstSelect['PaymentState']=='pending'){
					echo '<a href="main.php?TK=' .$TK . '&orderID=' . $orderID.'"> Requery</a>'; ?></td>
                    		<?php }else{
					echo '<a href="main.php?TK=' .$TK . '&orderID=' . $orderID.'"> Print</a>'; ?></td>
                            <?php }?>
             </tr>
         <?php } }?>

</table>
<p>&nbsp;</p>
<table width="383" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="10" colspan="4" align="left" class="tfont"><div align="center">Warning: Initiate new payment only after you 've satisfied that your account has not been debited</div>
        <div align="center"></div></td>
  </tr>
  <tr>
    <td height="10" colspan="4" class="tfont">&nbsp;</td>
  </tr>
  <tr>
    <td height="20" colspan="4" align="center" class="tfont"><?php
	$amt=5200;
	// echo '<a href="main.php?' . 'stdid=' . $urlstdid .'&view=' . 'payreq_try_rem' . '&paycat=' . $paycat . '&session=' . $session . '&amt=' . $amt .  '" onclick="return(YNconfirm());">Initiate New Payment</a>'; ?></td>
  </tr>
</table>
<p>&nbsp;</p>
</body>
</html>