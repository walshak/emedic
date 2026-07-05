<?php
include_once('appvars.php');
include_once('connect_line.php');

  define('GW_UPLOADPATH', 'pg_signature/');


if (isset($_GET['TK'])){
$TKurl=$_GET['TK'];
//decode url
include_once('encodecourl.php');
$TK2url=decodeurl("$TKurl","$mykeyurl");
$TKexplode=explode(";", "$TK2url");
$stdid=$TKexplode_stdid[1]=explode("=", "$TKexplode[2]");
$stdid=$stdid[1];
$view=$TKexplode_view[1]=explode("=", "$TKexplode[3]");	
$viewme=$view[1];	
$level=$TKexplode_level[1]=explode("=", "$TKexplode[4]");
$level=$level[1];
$dept=$TKexplode_dept[1]=explode("=", "$TKexplode[5]");
$dept=$dept[1];
$sess=$TKexplode_sess[1]=explode("=", "$TKexplode[6]");
$sess=$sess[1];
$sessCourse=$TKexplode_sess[1]=explode("=", "$TKexplode[6]");
$sessCourse=$sessCourse[1];
$matno=$TKexplode_matno[1]=explode("=", "$TKexplode[7]");
$matno=$matno[1];
}


if (isset($_GET['p'])){
//include_once('encodeco.php');
$new_pay='1';	
}

if (isset($_GET['TK'])){
include_once('encodeco.php');
$urlstdid=encode("$stdid","$mykey");	
}

if (isset($_GET['stdid'])){
$urlstdid=$_GET['stdid'];
include_once('encodeco.php');
$stdid=strtoupper(decode("$urlstdid","$mykey"));	
}


/// remita
$orderID = "";
if( isset( $_GET['orderID'] )) {
$orderID = $_GET["orderID"];
}

if (isset($_GET['enstdid'])){
$urlstdid=$_GET['enstdid'];
include_once('encodeco.php');
$stdid=strtoupper(decode("$urlstdid","$mykey"));
}


if (isset($_GET['pdesc'])){
$pdesc=$_GET['pdesc'];
		if($pdesc=='rest'){
 $session='2017/2018';
 $pay_type=$pay_details[0];
 			
		}else{
include_once('encodeco.php');
$pdesc=decode("$pdesc","$mykey");
$pay_details=explode("_", "$pdesc");
 $session=$pay_details[1];
 $pay_type=$pay_details[0];
		}
	
}

if (isset($_GET['pdesc2'])){
$pdesc=$_GET['pdesc2'];
include_once('encodeco.php');
$pdesc=decode("$pdesc","$mykey");
$pay_details=explode("_", "$pdesc");
 $session=$pay_details[1];
 $amt=$pay_details[0];
 $pay_type=$pay_details[2];	
}



 if (isset($_GET['err'])){
$err=1;
}

 if (isset($_GET['amt'])){
$amt=$_GET['amt'];
}

if (isset($_GET['p'])){
	$p=$_GET['p'];
	
	if($p=='4'){
	echo $pp='2014/2015';
	}else{
	echo $pp='2015/2016';
	}
}

$transactionreference=$_GET['txnRef'];	

 if (isset($_GET['msg'])){
$msg=$_GET['msg'];
}

 if (isset($_GET['view'])){
$viewme=$_GET['view'];
}

$querys="SELECT * FROM pgapplication, reg_password WHERE applicant_id='$stdid'";	
	$results = mysqli_query($dbc, $querys);	
	while ($row = mysqli_fetch_array($results)){
				
				$RegNumb = $row['applicant_id'];
				$surname = $row['surname'];
				$firstname = $row['firstname'];
				$fname = $row['dob'];
				$student_status=$row['student_status'];
				$application_number=$row['application_number'];
				$status=$row['state'];
				$MM=$row['futminna_Phd'];

				
			   if ($status==0)
			   {$candstatus='Admitted';} 
			   elseif ($status==1)
			   {$candstatus='Unpaid';}
			  elseif ($status==3)
			   {$candstatus='Paid';}
			   else
			   {$candstatus='Undecided';}
}


$querys="SELECT * FROM reg_password WHERE stdid='$stdid'";	
	$resultsss = mysqli_query($dbc, $querys);	
	while ($roww = mysqli_fetch_array($resultsss)){
		$adm_batch= $roww['adm_batch'];
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">
function altRows(id){
	if(document.getElementsByTagName){  
		
		var table = document.getElementById(id);  
		var rows = table.getElementsByTagName("tr"); 
		 
		for(i = 0; i < rows.length; i++){          
			if(i % 2 == 0){
				rows[i].className = "evenrowcolor";
			}else{
				rows[i].className = "oddrowcolor";
			}      
		}
	}
}
window.onload=function(){
	altRows('alternatecolor');
}

function confSubmit(form) {
if (confirm("Are you sure you want to submit for final clearance? You can't edit after clicking on this button")) {
form.submit();
}

else {
alert("You can save and continue later!");
}
}
</script>

<link rel="stylesheet" href="css/style.css">
<title>FUT Minna -eportal</title>

</head>

<body class="twoColHybRtHdr">
		<div id="headerBG">
              <img src="topBG-blue.png" width="1400" height="71" /></div>

					</div>
<div class="register">
  <div id="header">
    <h1>  <?php

// pg title

if ($viewme=='adm'){
echo 'Admission Data';
}

if ($viewme=='pg_Acceptance') {
echo 'Acceptance Form';
}

if ($viewme=='pg_fees') {
echo 'Registration Fees';
}
if ($viewme=='pg_fees_sem') {
echo 'Registration Fees Semester';
}

if ($viewme=='guarantor'){
echo "Guarantor's Form";
}

if ($viewme=='pg_personal'){
echo "Personal Data";
}

if ($viewme=='fail_transac_pg'){
echo "Fail Transactions";
}

if ($viewme=='payrpt_pg'){
echo 'Payment Details';
}

if ($viewme=='print_profile'){
echo 'Personal Data';
}

if ($viewme=='passport'){
echo 'Uploads';
}
  ?>

 
  </h1>
  <!-- end #header --></div>
  <div id="image">
    <div align="center"><a href="https://eportal.futminna.edu.ng/pg/reg/login.php">LOGOUT</a><br />
      <br />
      
<?php echo '<img src="https://eportal.futminna.edu.ng/pg/uploads/' . $stdid . '_passport.jpg'. '" alt ="Image" width="120" height="120" />';
//else{echo '<img src="https://eportal.futminna.edu.ng/pgappl2013adm/uploads/' . $application_number . '" alt ="Image" width="120" height="120" />';} 

 if ($student_status=='PGFresh'){
				$passpot = GW_UPLOADPATH .'/' . 'passport_'. $stdid.'.'.'jpg';
				
				if (!file_exists($file)) {
					$imagePath = "https://eportal.futminna.edu.ng/pg/uploads/" . $stdid . "_passport.jpg";
					$newPath = GW_UPLOADPATH ."/";
					$ext = '.jpg';
					$newName  = $newPath.'passport_'.$stdid.$ext;
					$copied = copy($imagePath , $newName);
				}
 }
 
?>
<br>
<?php
$file = GW_UPLOADPATH .'/' . 'sign_'. $stdid.'.'.'jpg';
if (file_exists($file)) {
	
// move passpoet too 
	$regist=0;
	?>
<img src="<?php echo $file; ?>" width="120" height="40" alt="No Image" />
<?php
} else {
		$regist=1;
    echo '<strong style="color:#F00">Upload Signature before Payment. See link below</strong>';
}

?>


      </div>
  </div>
  <div id="sidebar1">
    <h1><?php echo $candstatus .' | ' . $stdid ?></h1>
    <hr />
    <h1><?php echo 'Activities:' ?></h1>

     <li><?php echo '<a href="main.php?' . 'stdid=' . $urlstdid .'&view=' . 'adm' .'">Admission Data</a>'; ?></li>
     
      <?php //if ($MM=='MM'){
$TK="FAcctoken=$RegNumb;candname=$CandName;stdid=$stdid;view=remita;token=fr_rt;$CandName";
	include_once('encodecourl.php');
$TK=encodeurl("$TK","$mykeyurl");	
//	echo '<li><a href="main.php?' .'TK=' .$TK.'">Restitution Fee</a>' .'</li>';
	//}
	 ?>
 <li>
 <?php	if ($student_status=='PGFresh'){
		 if ($status>=0){
		
$TK="RAcctoken=$name;CandName=$CandName;RegNumb=$stdid;view=accptfrm;token=RegNumb;";
	include_once('encodecourl.php');
$TK=encodeurl("$TK","$mykeyurl");	
	echo '<a href="main.php?' .'TK=' .$TK.$CandName.'">Acceptance Form</a>';} else { echo 'Acceptance Form';}}else{ echo 'Acceptance Form';} ?>
      </li>
      
        
		 
<?php
		 
		 if($student_status=='PGFresh'){
			 if($regist==0){
		 	 	echo ' <li><a href="main.php?' . 'stdid=' . $urlstdid .'&view=' . 'paylist' .'">Registration Fee</a></li>';
			 }

					 }else{
						// if($stdid=='PG13485'){
		 	echo '<li><a href="main.php?' . 'stdid=' . $urlstdid .'&view=' . 'paylist' .'">Payment Log</a></li>'; 
						// }
			 }
		  ?>
         
    
<li>
<a href="" onclick="window.open('upload_sign.php?stdid=<?php echo $stdid .'/'.$urlstdid; ?>','popup','width=650,height=320,scrollbars=yes,resizable=no,toolbar=no,directories=no,location=no,menubar=no,status=no,left=0,top=0'); return false">Upload Signature</a>         </li>


    <li>
        <?php
		
		 if ($student_status=='PGFresh'){
		 if ($status>=0){
		
$TK="RAcctoken=$name;CandName=$CandName;RegNumb=$stdid;view=grtfrm;token=RegNumb;";
	include_once('encodecourl.php');
$TK=encodeurl("$TK","$mykeyurl");	
	echo '<a href="main.php?' .'TK=' .$TK.$CandName.'">Guarantor\'s Form</a>';} else { echo 'Guarantor\'s Form';}}else{echo 'Guarantor\'s Form';} ?>
      </li>
      
      
        <li><?php echo '<a href="main.php?' . 'stdid=' . $urlstdid .'&view=' . 'Person_Data' .'">Personal Data</a>'; ?></li>
        <li><a href="CourseForm.pdf">Download Course Form</a></li>

    <p>&nbsp;</p>
  <!-- end #sidebar1 --></div>
  <div id="mainContent">
  <?php


// pg title

if ($viewme=='adm'){
include_once('admdata.php');}

if ($viewme=='pg_fees') {
include_once('paytest_pg.php');}

if ($viewme=='pg_fees2') {
include_once('new_paytest.php');}

if ($viewme=='pg_fees_sem') {
include_once('paytest_pg_sem.php');}

if ($viewme=='fail_transac_pg'){
include_once('new_fail_transact_pg.php');}

if ($viewme=='fail_transac_pg_sem'){
include_once('fail_transact_pg_sem.php');}

if ($viewme=='payrpt_pg'){
include_once('payrpt_pg.php');}

 if ($viewme=='payreq_try_pg'){
include_once('new_payrequest_pg.php');}

 if ($viewme=='payreq_try_pg_sem'){
include_once('payrequest_pg_sem.php');}

if ($viewme=='response_fee'){
include_once('accptfee_prt_pg.php');
}

if ($viewme=='paylist'){
include_once('paylist.php');
}

if ($viewme=='accptfrm'){
//include_once('addcourse.php');
$TK="ACtoken=$name;candname=$CandName;stdid=$stdid;view=accptfrm;token=$RegNumb";
	include_once('encodecourl.php');
$TK=encodeurl("$TK","$mykeyurl");	
header ("Location: accptfrm.php?TK=$TK");
}

if ($viewme=='grtfrm'){
//include_once('addcourse.php');
$TK="ACtoken=$name;candname=$CandName;stdid=$stdid;view=grtfrm;token=$RegNumb";
	include_once('encodecourl.php');
$TK=encodeurl("$TK","$mykeyurl");	
header ("Location: grtfrm.php?TK=$TK");
}

 if ($viewme=='Person_Data'){
include_once('profile_data.php');}

if ($viewme=='print_profile'){
include_once('print_profile.php');}

//if ($viewme=='remita'){
//	$paycat='Restitution Fee';
//		include_once('paytest_remita.php');
//	}		

if ($viewme=='remita_list'){
		include_once('paylist_rem.php');
	}
	
	if ($viewme=='rpt_remita'){
		include_once('response_rem.php');
	}		
				if ($viewme=='requery_remita'){
		include_once('requery_rem.php');
				}
if ($viewme=='payreq_try_rem'){
		include_once('payrequest_rem.php');
				}
				
if ($viewme=='reqry'){
		include_once('requery_rem.php');
				}				
  ?>
  </div>
	<!-- This clearing element should immediately follow the #mainContent div in order to force the #container div to contain all child floats -->
	<br class="clearfloat" />
  </div>
<!-- end #container --></div>
	<div class="about">
      <div class="style1"> Powered by <a href="https://www.futminna.edu.ng">ITS-FUTMINNA</a><br />
      Copyright © FUTMINNA 2017      </div>
      <div><!-- end #footer -->
      </div>
</body>
</html>