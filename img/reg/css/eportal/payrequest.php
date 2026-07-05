<?php
$txn_ref='1209899';

 		$hhash=$txn_ref.'42881022525000http://www.futminna.edu.ng/upase_sc2013/responses.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
	$myhash=strtoupper(hash('SHA512', $hhash));
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
<form action="https://webpay.interswitchng.com/paydirect/pay" form method="post" enctype="multipart/form-data" >
        <input type="hidden" name="MAX_FILE_SIZE">
  <table id="login"  class="form-table" summary="Table for entering login information">
    <tbody>
      
      
      <tr>
        <td colspan="2" class="fieldname"><div align="center"><?php // echo '<img src="' . GW_UPLOADPATH . $screenshot . '" alt ="Fitures Image" />' ?></div></td>
        </tr>
      <tr>
        <td colspan="2" class="fieldname">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#FF6600" class="fieldname"><div align="center">
          <p class="style2 style3"> Confirm your entries before clicking on Payment button!</p>
          </div></td>
        </tr>
      
      <tr>
        <td class="fieldname">&nbsp;</td>
        <td class="fieldname">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#FF6600" class="fieldname"><div align="center" class="style4">Are you sure you want to submit Payment?</div></td>
      </tr>
      <tr>
        <td class="fieldname">&nbsp;</td>
        <td class="fieldname">&nbsp;</td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Amount:</strong></td>
        <td class="fieldname"><strong>N25,000.00</strong></td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Transaction Reference Number:</strong></td>
        <td class="fieldname"><?php echo $txn_ref ?></td>
        </tr>
      
      <tr>
        <td width="225"><strong>Candidates Name:</strong></td>
        <td class="fieldname"><?php echo $name; ?></td>
        </tr>
      
      <tr>
        <td width="225"><strong>Payment Item:</strong></td>
        <td><span class="fieldname"><?php echo $statelga; ?></span></td>
        </tr>
      <tr>
        <td><strong>Payment Category</strong></td>
        <td width="335" class="fieldname"><?php echo $sex; ?></td>
      </tr>
      

      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td> <input name="product_id" type="hidden" value="4288" />
    
  <input name="pay_item_id" type="hidden" value="102" />
    
  <input name="amount" type="hidden" value="2525000" />
    
  <input name="currency" type="hidden" value="566" />
    
  <input name="site_redirect_url" type="hidden" value="http://www.futminna.edu.ng/upase_sc2013/responses.php" /><input name="site_name" type="hidden" value="http://www.abc.com" />  <input name="cust_name_desc" type="hidden" value="Name" />
    
  <input name="pay_item_name" type="hidden" value="UPASE" />
    
  <input name="local_date_time" type="hidden" value="" /></td>
        <td><input name="txn_ref" type="hidden" id="txn_ref" value="<?php echo $txn_ref; ?>" />  <input name="cust_id" type="hidden" value="<?php echo $regno; ?>" />
    
  <input name="cust_id_desc" type="hidden" value="Customer ID" />
    
  <input name="cust_name" type="hidden" value="<?php echo $name; ?>" /><input name="hash" id="hash" type="hidden" value="<?php echo $myhash; ?>" /></td>
      </tr>
      <tr>
        <td colspan="2"><div align="center">
          <input class="btn primary" name="SUBMIT" value="Submit  Payment" type="submit" />
        </div></td>
        </tr>
    </tbody>
  </table>
  <input name="camefrom" type="hidden">
  <br>
</form>
</body>
</html>
