<?php
include('connect_line.php');

$querys="SELECT * FROM e_data WHERE stdid='$stdid'";	
	$results = mysqli_query($dbc, $querys);		
	while ($row = mysqli_fetch_array($results)){
		$RegNumb = $row['RegNumb'];
		$CandName = $row['CandName'];
		$PaymentItem = $row['dept'];
		$level=$row['level'];		
}
		
	
				$amt='1075000';
				$amt_display='N10,750';
					$rdp=0; for($i=0; $i<5; $i++){$rd=rand(0, 9); $rdp=$rdp.$rd;}
					$txn_ref= 'B'.$rdp.$stdid;

		
			
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
<form action="goto_interwish_accmfee.php" form method="post" enctype="multipart/form-data" >
        <input type="hidden" name="form1">
  <table class="altrowstable" id="alternatecolor" width="583" border="1">
    <tbody>
      
      
      <tr>
        <td colspan="2" class="fieldname"><div align="center">Payment for <?php echo $campLocation; ?> Accommodation booking</div></td>
      </tr>
      <tr>
        <td colspan="2" bgcolor="#FF6600" class="fieldname"><div align="center" class="style4">Are you sure you want to submit these Payment Data to CollegePay FUT Minna?</div></td>
      </tr>
      <tr>
        <td class="fieldname">Booked Hostel Data</td>
        <td class="fieldname"><?php echo $hostelid ?></td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Amount:
          <input name="amt" type="hidden" id="amt" value="<?php echo $amt; ?>" />
        </strong></td>
        <td class="fieldname"><?php echo $amt_display ?></td>
      </tr>
      <tr>
        <td class="fieldname"><strong>Transaction Reference Number:
          <input name="txn_ref" type="hidden" id="txn_ref" value="<?php echo $txn_ref; ?>" />
        </strong></td>
        <td class="fieldname"><?php echo $txn_ref ?></td>
      </tr>
      
      <tr>
        <td width="296"><strong>Candidates Name:
          <input name="CandName" type="hidden" id="CandName" value="<?php echo $CandName; ?>" />
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
          <input name="PaymentCategory" type="hidden" id="PaymentCategory" value="Registration Fee" />
        </strong></td>
        <td width="271" class="fieldname">Accommodation Fee</td>
      </tr>
      

      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td><strong>
          <input name="stdid" type="hidden" id="stdid" value="<?php echo $stdid; ?>" />
          <input type="hidden" name="level" id="level" value="<?php echo $level; ?>" />
          <input name="campLocation" type="hidden" id="campLocation" value="<?php echo $campLocation; ?>" />
        </strong></td>
        <td></td>
      </tr>
      <tr>
        <td><img src="banner.png" width="232" height="41" /></td>
        <td><input class="btn primary" name="SUBMIT" value="Submit Payment" type="submit" /></td>
      </tr>
    </tbody>
  </table>
  <br>
</form>
</body>
</html>
