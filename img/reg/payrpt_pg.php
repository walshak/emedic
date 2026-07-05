	<?php
	include('connect_line.php'); 

    	$querys="SELECT * FROM payment_log WHERE PaymentID='$transactionreference'";	
			$results = mysqli_query($dbc, $querys);		
			while ($row = mysqli_fetch_array($results)){
			
			$CandName = $row['candname'];
			$PaymentCategory = $row['PaymentCategory'];
			$PaymentItem = $row['PaymentItem'];
			$PaymentID = $row['PaymentID'];
			//$PaymentLevel = $row['PaymentLevel'];
			$PaymentSession = $row['PaymentSession'];
			$PaymentState = $row['PaymentState'];
			$PaymentDate = $row['PaymentDate'];
			$AmountAuthorized = $row['AmountAuthorized'];
			$ResponseDescription = $row['ResponseDescription'];
			$CurrentSessionPayment = $row['CurrentSessionPayment'];
			$amt=$AmountAuthorized-250;
		}
		

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PG Online Payment Slip</title>

<script language="javascript">
function Clickheretoprint()
{ 
  var disp_setting="toolbar=yes,location=no,directories=yes,menubar=yes,"; 
      disp_setting+="scrollbars=yes,width=800, height=400, left=100, top=25"; 
  var content_vlue = document.getElementById("content").innerHTML; 
  
  var docprint=window.open("","",disp_setting); 
   docprint.document.open(); 
   docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');          
   docprint.document.write(content_vlue); 
   docprint.document.close(); 
   docprint.focus(); 
}
</script>

<style>

.button {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
}

</style>

</head>
<body>

<div id="content">

<div style="height:120px; width:auto; margin:10px;  ">
<img src="PG_header.jpg" width="600" height="88" /></td>
</div>

<div>
<table width="100%">
	<tr>
  	<td>       
            <table width="306" style="border:1px solid #CCCCCC; border-collapse:collapse; font-size:14px; font:Calibri;">
 			 <tr height="30px" style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC;">
        <td style="border:1px solid #CCCCCC; padding:10px; background-color:#CCC;" width="136" align="right"><strong>Detials:</strong></td>
        <td style="border:1px solid #CCCCCC; padding:10px;" width="250"><p><strong><?php echo $RegNumb; ?><br />
          <?php echo $stdid; ?><br />
        </strong><?php echo $CandName; ?> </p></td>
              </tr>
 			 <tr height="30px" style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC;">
                <td style="border:1px solid #CCCCCC; padding:10px; background-color:#CCC; " width="136" align="right"><strong>Date:</strong></td>
                <td style="border:1px solid #CCCCCC; padding:10px;" width="250"><?php echo $PaymentDate; ?></td>
              </tr>
            </table>
  	</td>
  	<td>       
            <table width="146" style="border:1px solid #CCCCCC; border-collapse:collapse; font-size:14px; font:Calibri;" align="right">
            <tr height="40px" style="padding:15px;font-family:Gadugi;border:0px solid #333;">
                <td width="136"><div style="font-size:15px; font:Calibri; padding:15px; background-color:#333; font-size:20px; color:#FFF; text-align:center; "><strong>Status</strong></div></td>
              </tr>
            <tr height="40px" style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC;">
                <td width="136"><div style="font-size:15px; font:Calibri; padding:15px;"><?php if($PaymentState='paid' or $PaymentState='Paid'){echo 'Paid Successful';}else{echo 'Not Paid';}?> <br />
                  <?php if($CurrentSessionPayment=='session'){echo "[Session]";}else{echo "[Semester]";}?>
                  
                </div></td>
              </tr>
            </table>
  	</td>
  	</tr>
    
</table>
</div>

<br />
<table width="829" style="border:0px solid #CCCCCC; border-collapse: collapse; font-size:14px; font:Calibri;">
  <tr height="90px" style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC; background-color:#CCC;">
    <td style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC;"><strong>Service Name</strong></td>
    <td style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC;" ><strong>Programme</strong></td>
    <td style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC;" ><strong>Session</strong></td>
    <td style="padding:15px;font-family:Gadugi;border:1px solid #CCCCCC;" ><span class="fieldname"><div align="right"><strong>Transaction Code</strong></div></span></td>
  </tr>
  <tr height="90px">
    <td style="padding:10px;font-family:Gadugi;border:1px solid #CCCCCC;"><span class="fieldname"><?php echo $PaymentCategory; ?></span></td>
    <td style="padding:10px;font-family:Gadugi;border:1px solid #CCCCCC;" ><span class="fieldname"><?php echo $PaymentItem; ?></span></td>
    <td style="padding:10px;font-family:Gadugi;border:1px solid #CCCCCC;"><span class="fieldname"><?php echo $PaymentSession; ?></span></td>
    <td style="padding:10px;font-family:Gadugi;border:1px solid #CCCCCC;"><span class="fieldname"><?php echo $PaymentID; ?></span></td>
  </tr>
</table>
<br />

<div align="right">
<table width="466">
  <tr>
    <td width="295"><div align="right" style="border-right: 1px solid #000; font-family:Gadugi; font-size:13px;">Amount&nbsp;</div></td>
    <td width="152"><div style="font-family:Gadugi; font-size:14px" align="right">&#8358 <?php echo number_format($amt, 2, '.', ',')?></div></td>
  </tr>
  <tr>
    <td><div align="right" style="border-right: 1px solid #000; font-family:Gadugi; font-size:13px;">Bank Charge&nbsp;</div></td>
    <td width="152"><div style="font-family:Gadugi; font-size:14px" align="right">&#8358 250.00</div></td>
  </tr> 
</table>
<br />
<table width="306">
  <tr>
    <td align="right" width="136"><div style="margin-bottom:-22px; font-family:Calibri; ">Amount Paid</div></td>
    <td width="154"><div style="font-size:40px; font-family:Gadugi; "><span style="font-family:Gadugi;">&#8358;</span><?php echo number_format($AmountAuthorized, 2, '.', ',')?></div></td>
  </tr>
</table>

</div>
</div>

        <div class="pull-right" style="margin-right:100px;">
        <a href="javascript:Clickheretoprint()" style="font-size:20px;"><button>Click Here to PRINT</button></a>
        </div>
  
  
</body>
</html>