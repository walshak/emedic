<?php require_once('Connections/ePConn.php'); ?>

<?php
 include_once('encodeco.php');
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

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



if (isset($_GET['stdid'])) {
$stdid = $_GET['stdid'];
// $stdid=decode($stdidurl,"$mykey"); 
   }
   
   
$compl=$_GET['compl'];

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}
  define('GW_UPLOADPATH', 'pg_signature/');
    define('MAXFILESIZE', 15000);      // 32 KB


//$pixid = $uname.'.'.'jpg';
	//	 $target_file_foto = UPLOADPATH . $pixid; //generate the destination path

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "qcand")) {
	
$stdid=$_POST['stdid'];
	$part=explode("/", $stdid);
$stdid=$part[0];
	$urlstdid=$part[1];
	
//$unameurl = $_GET['uname'];
 //  $uname=decodeurl($unameurl,"$mykeyurl"); 
  // }// Processing the image FOTO
//if(!file_exists($target_file_foto)){ 
$file_foto_type = $_FILES['file_foto']['type'];
$file_foto_size = $_FILES['file_foto']['size'];
$TempSrc= $_FILES['file_foto']['tmp_name']; // Temp name of image file stored in PHP tmp folder
	
if(($file_foto_size < 0) || ($file_foto_size > MAXFILESIZE)){
$complain="The size of the Signature should not be more than 40kb";
header ("Location: upload_sign.php?convno=$file_foto_size&compl=$complain");
exit;
}elseif (($file_foto_type == 'image/jpeg')|| ($file_foto_type == 'image/jpg')) {

			///list($width, $height) = getimagesize("$TempSrc"); 
			//	if(($width==170)&&($height==150)){
		 //  $up_refid = strtoupper($stdid).'.'.'jpg';
		 //  	$target = GW_UPLOADPATH .'/' . 'signature_'. $stdid;
			
			 //   if (move_uploaded_file($_FILES['file_foto']['tmp_name'], $target)){}
			//	}else{
			//		$complain="Please use following dimensions for your Signature; Width=170pixel and Height=150pixel";
			//		header ("Location: upload_sign.php?convno=$file_credentials_type&compl=$complain&stdid=$stdid");
			//exit;
			//	}
		   
		  // $stdid = strtoupper($stdid).'.'.'jpg';
		   $target = GW_UPLOADPATH .'/' . 'sign_'. $stdid.'.'.'jpg';
			
 move_uploaded_file($_FILES['file_foto']['tmp_name'], $target);
$complain="Signature uploaded, Close Window and Refresh the main page. OR click Admission Data link";
header ("Location: upload_sign.php?stdid=$stdid&compl=$complain");
	echo $success=1; 
exit;

}else{
$complain="The Signature must be in jpeg format";
header ("Location: upload_sign.php?stdid=$stdid&compl=$complain");
///$success=0;
exit;
}
}

//	echo $success;
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
   
    <link rel="stylesheet" type="text/css" href="login_files/bootstrap-1.css">
<link rel="stylesheet" type="text/css" href="login_files/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="login_files/waeup-base.css">


<title>Signature Upload</title>


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

.error_strings{ font-family:Verdana; font-size:14px; color:#660000; background-color:#ff0;}
.style10 {color: #FF0000}
-->
    </style>
    <script language="javascript" src="calendar/calendar.js"></script>

 
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
</head>
  <body>
  <div class="container">
      
      <div class="content">
        <div><form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="qcand" onsubmit="MM_validateForm('file_foto','','R');return document.MM_returnValue">
   
           <label>Upload Passport.<br />
          <br />
          <img src="" width="120" height="117" alt="" />
          <span class="style10"><?php echo $compl ?></span><br />
          <br />
          
          
  <?php if($compl!="Signature uploaded, Close Window and Refresh the main page. OR click Admission Data link"){?>
       
  <table width="712" class="form-table" id="login" summary="Table for entering login information">
    <tbody>
      <tr>
        <td width="69%" align="left" valign="top"><label></label>
  
          <input type="file" name="file_foto" id="file_foto" />
        </label></td>
      </tr>
      <tr>
        <td align="left"><label>
        <input class="btn primary" name="SUBMIT" value="Submit" type="submit" />
        </label></td>
      </tr>
      <tr>
        <td align="left"><label><input type="button" value="Close" onclick="window.close();"></label></td>
      </tr>
    </tbody>
  </table>
  <?php }else{ ?>
  
  <label><input type="button" value="Close" onclick="window.close();"></label>
  
  <?php } ?>
  
  <div align="center"></div>
<div align="center"></div>
        <input type="hidden" name="MM_update" value="qcand" />
        <input name="stdid" type="hidden" id="stdid" value="<?php echo $stdid; ?>" />
        </form></div>
        <div id="footer" class="footer">
          
        </div>
      </div>
  </div>

</body></html>




