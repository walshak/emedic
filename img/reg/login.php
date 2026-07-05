<?php include_once('ePConn.php'); ?>
<?php

include('encodeco.php');

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


// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

if (isset($_GET['errmsg'])) {
  $errmsg = GetSQLValueString($_GET['errmsg']);
}

if (isset($_GET['uname1'])) {
  $uname = GetSQLValueString($_GET['uname1']);
//  $errmsg='Application Has Been Created You Can Proceed! Please Take Note of Application Number: ' . $uname;
}

$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['applicant_no'])) {
	
  $loginUsername=GetSQLValueString(strtoupper($_POST['applicant_no']));
  $uname=GetSQLValueString($loginUsername);
  $password=GetSQLValueString($_POST['pwd']);
  $MM_fldUserAuthorization = "";
 
 $loginUsernameurl=encode("$loginUsername","$mykey"); 
  $MM_redirectLoginSuccess = "main.php?stdid=$loginUsernameurl&view=adm";
  $MM_redirectLoginFailed = "login.php?errmsg=Invalid Login&uname1=$uname";
  $MM_redirecttoReferrer = false;
  
  
  $LoginRS__query="SELECT stdid, pword FROM reg_password WHERE stdid='$loginUsername' AND pword='$password'";
    
  $LoginRS =  mysqli_query($conn,$LoginRS__query) or die(mysqli_connect_errno());
  $loginFoundUser = mysqli_num_rows($LoginRS);
  
  if ($loginFoundUser) {
     $loginStrGroup = "";
    
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="login_files/bootstrap-1.css">
<link rel="stylesheet" type="text/css" href="login_files/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="login_files/waeup-base.css">

    <title>Candidates Login</title>


   
<style type="text/css">
<!--
.ta10 {	background-image:url(images/form_bg.jpg);
	background-repeat:repeat-x;
	border:1px solid #d1c7ac;
	width: 700px;
	height: 300px;
	color:#333333;
	padding:3px;
	margin-right:4px;
	margin-bottom:8px;
	font-family:tahoma, arial, sans-serif;
}

.Main{
 margin:0 auto;
 width:1011px;
  height:2000px;

 padding-top:-50px;
 background-color: #FFFFFF;
 margin-top:-21px;
 position: inherit;
 z-index: 2;

}
.style2 {font-size: 12px}
.style3 {color: #CC0000}
.style4 {
	color: #FF0000;
	font-size: 11px;
}
.style6 {font-size: 10px}
-->
    </style>
  

  <script type="text/javascript">
<!--
function MM_validateForm() { //v4.0
  if (document.getElementById){
    var i,p,q,nm,test,num,min,max,errors='',args=MM_validateForm.arguments;
    for (i=0; i<(args.length-2); i+=3) { test=args[i+2]; val=document.getElementById(args[i]);
      if (val) { nm=val.name; if ((val=val.value)!="") {
        if (test.indexOf('isEmail')!=-1) { p=val.indexOf('@');
          if (p<1 || p==(val.length-1)) errors+='- '+nm+' must contain an e-mail address.\n';
        } else if (test!='R') { num = parseFloat(val);
          if (isNaN(val)) errors+='- '+nm+' must contain a number.\n';
          if (test.indexOf('inRange') != -1) { p=test.indexOf(':');
            min=test.substring(8,p); max=test.substring(p+1);
            if (num<min || max<num) errors+='- '+nm+' must contain a number between '+min+' and '+max+'.\n';
      } } } else if (test.charAt(0) == 'R') errors += '- '+nm+' is required.\n'; }
    } if (errors) alert('The following error(s) occurred:\n'+errors);
    document.MM_returnValue = (errors == '');
} }
//-->
  </script>
  <script type="text/javascript" language="javascript">
 window.onload=blinkOn;
 
function blinkOn()
{
  document.getElementById("blink").style.color="#ff0000"
  setTimeout("blinkOff()",500)
}
 
function blinkOff()
{
  document.getElementById("blink").style.color=""
  setTimeout("blinkOn()",500)
}
 
 
 
</script>
</head>
  <body>
  <div class="container">
    <div class="content">
      <h1>Postgraduate Online Registration</h1>
        <div>
<form action="<?php echo $loginFormAction; ?>" method="POST" name="form1" id="form1" onsubmit="MM_validateForm('applicant_no','','R','pwd','','R');return document.MM_returnValue"> 

  <table id="login" class="form-table" summary="Table for entering login information">
    <tbody>
      
      <tr>
        <td width="244" rowspan="7" valign="bottom" class="fieldname"><a href=""><img src="icon1.png" width="148" height="141" /></a></td>
        <td colspan="2" class="fieldname">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" class="fieldname"><span class="fieldname style3">Enter  Applicant Details below:</span></td>
      </tr>
      <tr>
        <td class="fieldname">PG Number</td>
        <td><input name="applicant_no" type="text" id="applicant_no" size="10" maxlength="10"  /></td>
      </tr>
      <tr>
        <td class="fieldname">Access Code</td>
        <td><input name="pwd" type="password" id="pwd" size="20" maxlength="20" /></td>
      </tr>
      <tr>
        <td width="164" class="fieldname">&nbsp;</td>
        <td width="306"><p>&nbsp;</p>
          <p>
            <input class="btn primary" name="SUBMIT" value="Login" type="submit" />
</p></td>
      </tr>
      
      <tr>
        <td colspan="2" align="center" class="fieldname"><p align="center" class="style4"><?php echo $errmsg; ?></p></td>
        </tr>
      <tr>
        <td class="fieldname"></td>
        <td></td>
      </tr>
      
      <tr>
        <td class="fieldname">&nbsp;</td>
        <td colspan="2" class="fieldname"><span class="style6"><em>Payment Platform</em></span><br />
          <img src="banner.png" width="182" height="34" /></td>
        </tr>
    </tbody>
  </table>
  <input name="camefrom" type="hidden">
  <hr />
  <span class="style6">Powered by ITS, Minna</span> <br />
  </form></div>
    
        </div>
  </div>
</body></html>