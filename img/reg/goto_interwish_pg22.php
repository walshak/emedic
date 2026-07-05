<?php

// drop here with student registration number

//if (isset($_GET['stdid'])){
//$urlstdid=$_GET['stdid'];
//}
  //encode stdid
//include('encodeco.php');
//$stdid=decode("$urlstdid","$mykey"); 
include('connect_line.php');

 $stdid=$_POST['stdid'];
 $RegNumb=$_POST['RegNumb'];
 $CandName=$_POST['CandName'];
 $PaymentItem=$_POST['PaymentItem'];
 $tagging=$_POST['tagging']; // decide on the level 
 $txn_ref=$_POST['txn_ref'];
// $amt=$_POST['amt'];
  $PaymentCategory='Registration Fee';
  
if($stdid!=''){
	

			

	// PhD
	if ($tagging=='PhD'){
		if (isset($_POST['phd'])){
			$phdType=$_POST['phd'];
		
		if ($phdType=='PhD_f'){
		$pay_item_id='114';
		$txn_ref='114N'.$txn_ref;
$hhash=$txn_ref.'428811410025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
		 $amt='10025000';
		 $amt2='N100,250.00';

			}
			
		if ($phdType=='PhD_p'){
		$pay_item_id='115';
		$txn_ref='115N'.$txn_ref;
$hhash=$txn_ref.'42881159025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));	
		$amt='9025000';	
		$amt2='N90,250.00';	

			}
		}else {
			$MM_restrictGoTo = "index.php";
			header("Location: ". $MM_restrictGoTo); 			
		}
	}

	// M.Tech/M.Eng
	if ($tagging=='M'){
	$pay_item_id='116';
	$txn_ref='116N'.$txn_ref;
$hhash=$txn_ref.'42881168025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
		$amt='8025000';
		$amt2='N80,250.00';

	}
	
		// Fut Minna PGD Full Time
	if ($tagging=='PGD'){
	$pay_item_id='117';
	$txn_ref='117N'.$txn_ref;
$hhash=$txn_ref.'42881179025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='9025000';
				 $amt2='N90,250.00';
	}
	
		// Fut Minna CDRM – DS Maters
	if ($tagging=='MDRM'){
	$pay_item_id='118';
	$txn_ref='118N'.$txn_ref;
$hhash=$txn_ref.'428811815025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='15025000';
				 $amt2='150,250.00';

	}
	
			//Fut Minna CDRM – DS PGD
	if ($tagging=='PGDDRM'){
	$pay_item_id='119';
	$txn_ref='119N'.$txn_ref;
$hhash=$txn_ref.'428811912025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='12025000';
				 $amt2='N120,250.00';

	}
	
			// CHSUD - MSUD
	if ($tagging=='MSUD'){
	$pay_item_id='120';
	$txn_ref='120N'.$txn_ref;
$hhash=$txn_ref.'428812010025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='10025000';
				 $amt2='N100,250.00';

	}
	
			// CHSUD - MUE
	if ($tagging=='MUE'){
	$pay_item_id='121';
	$txn_ref='121N'.$txn_ref;
$hhash=$txn_ref.'428812110025000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='10025000';
				 $amt2='N100,250.00';
	}
				 
				 ////////////////==========================================
				 
				 	// PhD additional amount
					///=================================================================
					
	if ($tagging=='PhD1'){
		if (isset($_POST['phd'])){
			$phdType=$_POST['phd'];
		
		if ($phdType=='PhD_f'){
		$pay_item_id='114';
		$txn_ref='114L'.$txn_ref;
$hhash=$txn_ref.'428811410525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
		 $amt='10525000';
		 $amt2='N105,250.00';

			}
			

		if ($phdType=='PhD_p'){
		$pay_item_id='115';
		$txn_ref='115L'.$txn_ref;
$hhash=$txn_ref.'42881159525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));	
		$amt='9525000';	
		$amt2='N95,250.00';	

			}
		}else {
			$MM_restrictGoTo = "index.php";
			header("Location: ". $MM_restrictGoTo); 			
		}
	}

	// M.Tech/M.Eng
	if ($tagging=='M1'){
	$pay_item_id='116';
	$txn_ref='116L'.$txn_ref;
$hhash=$txn_ref.'42881168525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
		$amt='8525000';
		$amt2='N85,250.00';

	}
	
		// Fut Minna PGD Full Time
	if ($tagging=='PGD1'){
	$pay_item_id='117';
	$txn_ref='117L'.$txn_ref;
$hhash=$txn_ref.'42881179525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='9525000';
				 $amt2='N95,250.00';
	}
	
		// Fut Minna CDRM – DS Maters
	if ($tagging=='MDRM1'){
	$pay_item_id='118';
	$txn_ref='118L'.$txn_ref;
$hhash=$txn_ref.'428811815525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='15525000';
				 $amt2='155,250.00';

	}
	
			//Fut Minna CDRM – DS PGD
	if ($tagging=='PGDDRM1'){
	$pay_item_id='119';
	$txn_ref='119L'.$txn_ref;
$hhash=$txn_ref.'428811912525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='12525000';
				 $amt2='N125,250.00';

	}
	
			// CHSUD - MSUD
	if ($tagging=='MSUD1'){
	$pay_item_id='120';
	$txn_ref='120L'.$txn_ref;
$hhash=$txn_ref.'428812010525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='10525000';
				 $amt2='N105,250.00';

	}
	
			// CHSUD - MUE
	if ($tagging=='MUE1'){
	$pay_item_id='121';
	$txn_ref='121L'.$txn_ref;
$hhash=$txn_ref.'428812110525000https://eportal.futminna.edu.ng/accptfee_prt_pg.php494ADE2D410E55FA0F1A76DE790673343D09C04446F338B2F1D4627264EAE355A929BF6DFD025318D37383317B8A63991B2FF6F150B29A418E1019455EEA4EF9';
		$myhash=strtoupper(hash('SHA512', $hhash));
				 $amt='10525000';
				 $amt2='N105,250.00';
				 
	}
	
$querys="SELECT * FROM payment_log WHERE PaymentID='$txn_ref'";	
	$results = mysqli_query($dbc, $querys);	

			if(mysqli_num_rows($results)==0){
$CandName=mysql_real_escape_string($CandName);

	$query = "INSERT INTO payment_log VALUES('$stdid', '$CandName','$PaymentCategory', '$PaymentItem', '$txn_ref', 'PG', '2013/2014', 'pending','','$amt2','','','0','')";
}

		mysqli_query($dbc, $query);
		mysqli_close($dbc);			
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Loading Payment Gateway</title>
<style type="text/css">
<!--
.style3 {	font-size: 16px
}
.style4 {color: #FFFFFF}
-->
</style>
</head>

<body>
<form action="https://webpay.interswitchng.com/paydirect/pay" form method="post" enctype="multipart/form-data" name="login_form" id="login_form" >
        <p>
          <input type="hidden" name="MAX_FILE_SIZE">
        </p>
  <p>&nbsp; </p>
  <table width="583" border="0" align="center" class="altrowstable" id="alternatecolor">
    <tbody>
      
      
      <tr>
        <td colspan="2" class="fieldname"><div align="center"><img src="arr.jpeg" width="27" height="25" /><img src="arr.jpeg" width="27" height="25" /><img src="arr.jpeg" width="27" height="25" /></div></td>
        </tr>
      <tr>
        <td width="296" colspan="2" align="center" valign="middle" bgcolor="#FF6600" class="fieldname"><div align="center" class="style4">
          <p>&nbsp;</p>
          <p><span class="inline-inputs"><strong>Connecting to CollegePay FUT Minna. Please Wait ...</strong></span></p>
          <p>&nbsp;</p>
        </div></td>
      </tr>
      
      <tr>
        <td> <input name="product_id" type="hidden" value="4288" />
    
  <input name="pay_item_id" type="hidden" value="<?php echo $pay_item_id; ?>" />
    
  <input name="amount" type="hidden" value="<?php echo $amt; ?>" />
    
  <input name="currency" type="hidden" value="566" />
    
  <input name="site_redirect_url" type="hidden" value="https://eportal.futminna.edu.ng/accptfee_prt_pg.php" /><input name="site_name" type="hidden" value="http://www.abc.com" />  <input name="cust_name_desc" type="hidden" value="Name" />
    
  <input name="pay_item_name" type="hidden" value="FUT Minna Registration Fee" />
    
  <input name="local_date_time" type="hidden" value="" /></td>
        <td><input name="txn_ref" type="hidden" id="txn_ref" value="<?php echo $txn_ref; ?>" />  <input name="cust_id" type="hidden" value="<?php echo $stdid; ?>" />
    
  <input name="cust_id_desc" type="hidden" value="Customer ID" />
    
  <input name="cust_name" type="hidden" value="<?php echo $PaymentCategory .' : '. $CandName; ?>" /><input name="hash" id="hash" type="hidden" value="<?php echo $myhash; ?>" /></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>     </td>
      </tr>
    </tbody>
  </table>
  <input name="camefrom" type="hidden">
  <br>
</form>

<?php 

echo  "<script language=\"javascript\" type=\"text/javascript\">";
				    echo "document.login_form.submit()";     
                 echo "</script>";     
                echo  "<noscript>";
				echo "<input type=\"submit\" value=\"verify submit\">";

?>
</body>
</html>

<?php
}else{
	
$MM_restrictGoTo = "index.php";
header("Location: ". $MM_restrictGoTo); 
}
?>