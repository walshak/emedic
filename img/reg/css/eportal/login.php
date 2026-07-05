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
<?php
// *** Validate request to login to this site.
if (!isset($_SESSION)) {
  session_start();
}

if (isset($_GET['errmsg1'])){
$errmsg1=$_GET['errmsg1'];
}

if (isset($_GET['errmsg2'])){
$errmsg2=$_GET['errmsg2'];
}
if (isset($_GET['errmsg3'])){
$errmsg3=$_GET['errmsg3'];
}
$loginFormAction = $_SERVER['PHP_SELF'];
if (isset($_GET['accesscheck'])) {
  $_SESSION['PrevUrl'] = $_GET['accesscheck'];
}

if (isset($_POST['E_stdid'])) {
  $loginUsername=$_POST['E_stdid'];
  $password=$_POST['E_pword'];
  $MM_fldUserAuthorization = "";
  //encode stdid
  include('encodeco.php');
 $enc=encode("$loginUsername","$mykey"); 
 
  $MM_redirectLoginSuccess = "admdata.php?stdid=$enc";
    $MM_redirectLoginFailed = "login.php?errmsg1=Inavild Login for existing user";
  $MM_redirecttoReferrer = false;
  
  
  $LoginRS__query=sprintf("SELECT stdid, pword FROM adms2013 WHERE stdid=%s AND pword=%s",
    GetSQLValueString($loginUsername, "text"), GetSQLValueString($password, "text")); 
   
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

//REQUEST FOR STUDENT ID AND PASSWORD
if (isset($_POST['F_jambNo'])) {
  $loginUsernameF=$_POST['F_jambNo'];
  $passwordF=$_POST['F_Names'];
  $MM_fldUserAuthorization = "";
  $MM_redirectLoginFailed = "login.php?errmsg2=Inavild Login for candidate requesting for Student ID and Password <BR> Click on 'Request for Student ID and Password' tab to try again";
  $MM_redirecttoReferrer = false;
  
  
  $LoginRS__query="SELECT * FROM adms2013 WHERE RegNumb='$loginUsernameF' AND CandName LIKE '%$passwordF%'";
  $LoginRS =  mysqli_query($conn,$LoginRS__query) or die(mysqli_connect_errno());

  //get info from the table
  $row_rsstdinfo = mysqli_fetch_array($LoginRS);
  
$stdid=$row_rsstdinfo['stdid'];
$pword=$row_rsstdinfo['pword'];
$email=$row_rsstdinfo['email'];
$CandName=$row_rsstdinfo['CandName'];
    $MM_redirectLoginSuccess = "sucessFresher.php?CandName=$CandName&email=$email";

  $loginFoundUser = mysqli_num_rows($LoginRS);
  if ($loginFoundUser) {
     $loginStrGroup = "";
    
    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
	//Connect to email program
   $to=$email;
   $stdid=$stdid;
   $pword=$pword;
   
  include('PHPMailer/test/testemail.php');

	
    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}

//REQUEST FOR PASSWORD
if (isset($_POST['FGT_stdid'])) {
  $loginUsernameFGT=$_POST['FGT_stdid'];
  $passwordFGT=$_POST['FGT_email'];
  $MM_fldUserAuthorization = "";
   $MM_redirectLoginFailed = "login.php?errmsg3=Inavild Login for recovering password. <BR> Click on 'Request for Password' tab to try again.";

  $MM_redirecttoReferrer = false;
  
  
  $LoginRS__query=sprintf("SELECT * FROM adms2013 WHERE stdid=%s AND email=%s",
    GetSQLValueString($loginUsernameFGT, "text"), GetSQLValueString($passwordFGT, "text")); 
   
  $LoginRS =  mysqli_query($conn,$LoginRS__query) or die(mysqli_connect_errno());
  $loginFoundUser = mysqli_num_rows($LoginRS);
  if ($loginFoundUser) {
     $loginStrGroup = "";
     //get info from the table
  $row_rsstdinfo = mysqli_fetch_array($LoginRS);
  
$stdid=$row_rsstdinfo['stdid'];
$pword=$row_rsstdinfo['pword'];
$email=$row_rsstdinfo['email'];
$CandName=$row_rsstdinfo['CandName'];
    $MM_redirectLoginSuccess = "sucessFresher.php?CandName=$CandName&email=$email";

    //declare two session variables and assign them
    $_SESSION['MM_Username'] = $loginUsername;
    $_SESSION['MM_UserGroup'] = $loginStrGroup;	      

    if (isset($_SESSION['PrevUrl']) && false) {
      $MM_redirectLoginSuccess = $_SESSION['PrevUrl'];	
    }
	
	 $to=$email;
   $stdid=$stdid;
   $pword=$pword;
 //  include('PHPMailer/test/testemail.php');

    header("Location: " . $MM_redirectLoginSuccess );
  }
  else {
    header("Location: ". $MM_redirectLoginFailed );
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="logincss/bootstrap-1.4.0.css">
<link rel="stylesheet" type="text/css" href="logincss/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="logincss/futminna.css">
<link rel="stylesheet" type="text/css" href="logincss/custom_theme.css">
<script type="text/javascript" src="logincss/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="logincss/jquery-ui.min.js"></script>
<script type="text/javascript" src="logincss/datepicker.js"></script>
<script type="text/javascript" src="logincss/bootstrap-tabs-1.4.0.js"></script>
<script type="text/javascript" src="logincss/bootstrap-dropdown-1.4.0.js"></script>
<link rel="shortcut icon" type="image/x-icon" href="http://portal.futminna.edu.ng/@@/waeup_custom/favicon.ico">

    <title>Federal University of Technology (ePortal)</title>
    <!--base href="http://localhost:8080/app/@@page"
     tal:attributes="href python: view.url(layout.site)" / --><meta name="robots" content="index, follow">
    <link rel="alternate" type="application/rss+xml" title="RSS feed of Federal University of Technology Minna" href="http://portal.futminna.edu.ng/feed.rss">
    <script src="SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
<link href="SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
.style9 {color: #FF0000}
-->
</style>
</head>
  <body>
    <div class="topbar" data-scrollspy="scrollspy">
      <div class="topbar-inner">
       
          <table width="101%" height="72" border="0" background="logincss/topBG-purple.png">
            <tr>
              <td width="13%" scope="col">&nbsp;</td>
              <td width="87%" valign="bottom" scope="col">&nbsp;</td>
            </tr>
          </table>
      
      </div>
    </div>

    <ul class="breadcrumb">
      <span>
  <li></li>
</span></ul>

<div class="container-fluid">
      <div class="sidebar">
        <div class="well">
          <div>
            <h5>How to use this page<br />
            (1) <u>Existing Users</u></h5>
            <p>This Tab is for those that have Student ID and Password already</p>
          </div>
          <div>
            <h5><u>(2) Request for Student ID and Password</u></h5>
            <p> This Tab is for freshers that just got admission</p>
          </div>
           <div>
            <h5><u>(3) Request for Password</u></h5>
            <p>For Student that forgot Password of the Portal.</p>
          </div>
        </div>
      </div>
      <div class="span13 content">
        <p><strong>ABOUT LOGIN</strong></p>
        <div id="TabbedPanels1" class="TabbedPanels">
  <ul class="TabbedPanelsTabGroup">
    <li class="TabbedPanelsTab" tabindex="0">Login (Existing Users)</li>
    <li class="TabbedPanelsTab" tabindex="0">Request for Student ID and Password</li>
    <li class="TabbedPanelsTab" tabindex="0">Request for Password</li>
  </ul>
  <div class="TabbedPanelsContentGroup">
    <div class="content">
      <form id="frmTab1" name="frmTab1" method="POST" action="<?php echo $loginFormAction; ?>">
  <table width="425" border="0">
    <tr>
      <td width="25%" scope="col">Student ID</td>
      <td width="75%" scope="col"><label>
        <input type="text" name="E_stdid" id="E_stdid" />
      </label></td>
    </tr>
    <tr>
      <td>Password</td>
      <td><label>
        <input type="text" name="E_pword" id="E_pword" />
      </label></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td align="left"><div align="left"><span class="style9">
        <input class="btn primary" type="submit" name="button2" id="button2" value="Submit" />
      </span></div>
        <label></label></td>
    </tr>
  </table>
</form>
      <div align="center"><span class="style9"><?php echo $errmsg1.$errmsg2.$errmsg3; ?></span><br />
        </div>
    </div>
    <div class="TabbedPanelsContent"><form id="frmTab2" name="frmTab2" method="post" action="<?php echo $loginFormAction; ?>">
  <table width="70" border="0">
    <tr>
      <td width="25%" scope="col">JAMB Registration No.</td>
      <td width="75%" scope="col"><label>
        <input type="text" name="F_jambNo" id="F_jambNo" />
      </label></td>
    </tr>
    <tr>
      <td>One of Your Names</td>
      <td><label>
        <input type="text" name="F_Names" id="F_Names" />
      </label></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
     <td align="left"><div align="left"><span class="style9">
        <input class="btn primary" type="submit" name="button2" id="button2" value="Submit" />
      </span></div>
        <label></label></td>
    </tr>
  </table>
</form>
 <div align="center"><span class="style9"><?php // echo $errmsg2; ?></span><br />
        </div>
</div>
     <div class="TabbedPanelsContent"><form id="frmTab3" name="frmTab3" method="post" action="<?php echo $loginFormAction; ?>">
  <table width="420" border="0">
    <tr>
      <td width="25%" scope="col">Student ID</td>
      <td width="75%" scope="col"><label>
        <input type="text" name="FGT_stdid" id="FGT_stdid" />
      </label></td>
    </tr>
    <tr>
      <td>Email Address</td>
      <td><label>
        <input type="text" name="FGT_email" id="FGT_email" />
      </label></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
     <td align="left"><div align="left"><span class="style9">
        <input class="btn primary" type="submit" name="button2" id="button2" value="Submit" />
      </span></div>
        <label></label></td>
    </tr>
  </table>
</form>
 <div align="center"><span class="style9"><?php //echo $errmsg3; ?></span><br />
        </div>
</div>
  </div>
</div>
        <div id="footer" class="footer">
          <div>
            <br />
            <br />
          </div>
          <div>
            <div> <br />
    <table width="70" border="0">
      <tr>
        <td width="83%" scope="col">Powered by ITS, FUT Minna</td>
        <td width="17%" align="right" scope="col">Copyright © 2013 </td>
      </tr>
    </table>
    </div></div>
        </div>
      </div>
  </div>
  

<div id="ui-datepicker-div" class="ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all ui-helper-hidden-accessible"></div>
<script type="text/javascript">
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
</script>
</body></html>