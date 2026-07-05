<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Transaction Failed</title>
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
<form action="" form method="post" enctype="multipart/form-data" >
        <input type="hidden" name="MAX_FILE_SIZE">
  <table width="597" height="110" border="0" align="center">
    <tr>
      <td height="10" colspan="4" class="tfont">&nbsp;</td>
    </tr>
    <tr>
      <td  height="40" colspan="4" bgcolor="#FFFF66" class="tfont"><p class="style3">  NOTICE<br />
        <br />
        <strong>the following transaction(s) are not successfully<br />
        </strong><strong>you can click on &quot;RE-QUERY&quot; link to requery each transactions for number of attempt<br />
        <br />
        OR pick TRANSACTION CODE sent to your Mobile Phone from your bank Alert<br />
        </strong><br />
        <strong>If  all the requery is NOT successful. </strong><strong><strong>Initiate new payment below if your BANK ACCOUNT have not been DEBITED.</strong></strong><br />
      </p></td>
    </tr>
    <tr>
      <td height="16" class="tfont">&nbsp;</td>
      <td class="tfont">&nbsp;</td>
      <td class="tfont">&nbsp;</td>
      <td class="tfont">&nbsp;</td>
    </tr>
    <tr>
      <td width="130" height="16" class="tfont"><strong>Transaction ID</strong></td>
      <td width="139" class="tfont"><strong><em><span class="style3"> (Requery below)</span></em></strong></td>
      <td width="192" class="tfont"><strong><strong>Transaction Status</strong></strong></td>
      <td width="118" class="tfont"><strong>Requery Attempts</strong></td>
    </tr>
    <tr>
      <td height="16" class="tfont">&nbsp;</td>
      <td class="tfont">&nbsp;</td>
      <td class="tfont">&nbsp;</td>
      <td class="tfont">&nbsp;</td>
    </tr>
   </table>
  <table width="597" height="24" border="0" align="center">
    <?php
	
	include('connect_line.php');
	
	if($p=='4'){
	echo $pp='2014/2015';
	}else{
	echo $pp='2015/2016';
	}

	$query="SELECT * FROM payment_log WHERE applicant_id ='$stdid' and PaymentCategory='Registration Fee' and PaymentSession='$pp'";	
	$result = mysqli_query($dbc, $query);
			while ($row = mysqli_fetch_array($result)){
			
               // $session=$row['PaymentSession'];
				$amt=$row['AmountAuthorized'];	?>
    <tr>
      <td width="130" height="20" class="tfont"><?php echo $row['PaymentID']; ?></td>
      <td width="143"><?php echo '<a href="accptfee_try_pg.php?' . 'txnRef=' . $row['PaymentID'] . '&stdid=' . $urlstdid .  '&amt=' . $amt.'">RE-QUERY</a>'; ?></td>
      <td width="188" ><?php echo $row['ResponseDescription']; ?></td>
      <td width="118" ><?php echo $row['requeryCount']; ?></td>
    </tr>
    <?php 
	
	} ?>
  </table>
  
  
  <table width="597" height="65" border="0" align="center">
    <tr>
      <td colspan="4" bgcolor="#FFFFFF" class="tfont">&nbsp;</td>
    </tr>
    <tr>
      <td width="579" colspan="4" bgcolor="#FF0000" class="tfont"><div align="center" class="style3"></div></td>
    </tr>
    <tr>
      <td height="21" colspan="4" class="tfont">&nbsp;</td>
    </tr>
    <tr>
   <?php
   $en_urlstdid=$stdid;
include_once('encodeco.php');
$urlstdid=encode("$en_urlstdid","$mykey");

   ?>
      <td height="20" colspan="4" class="tfont"><div align="center"><?php echo '<a href="main.php?' . 'stdid=' . $urlstdid .'&view=' . 'payreq_try_pg' . '&amt=' . $amt . '&p=' . $p .'">Initiate New Payment</a>'; ?></div>
        <div align="center"></div></td>
    </tr>
  </table>
</form>
</body>
</html>
