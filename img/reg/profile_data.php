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


//$stdid='M140';

$current_session='2015/2016';


$query_rstSelect = sprintf("SELECT * FROM e_data_profile WHERE stdid= %s", GetSQLValueString($stdid, "text"));
$rstSelect =  mysqli_query($conn,$query_rstSelect) or die(mysqli_connect_errno());
$row_rstSelect = mysqli_fetch_array($rstSelect);
$totalRows_rstSelect = mysqli_num_rows($rstSelect);
	if($totalRows_rstSelect==0){
		
		  $insertSQL = sprintf("INSERT INTO e_data_profile (stdid) VALUES (%s)",
                       GetSQLValueString($stdid, "text"));

  
  $Result1 =  mysqli_query($conn,$insertSQL) or die(mysqli_connect_errno());
  
	}else{
			$paddr = $row_rstSelect['paddr'];
			$phone = $row_rstSelect['phone'];
			$marital = $row_rstSelect['marital'];
			$religion = $row_rstSelect['religion'];
			$next_name = $row_rstSelect['next_name'];
			$next_rel = $row_rstSelect['next_rel'];
			$next_addr = $row_rstSelect['next_addr'];
			$next_phone = $row_rstSelect['next_phone'];
			$blood_group = $row_rstSelect['blood_group'];
	}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["update_profile"])) && ($_POST["MM_update"] == "pdetail")) {

$stdid3=GetSQLValueString($_POST['stdid3']);
//$Personal_Address=addslashes(($_POST['Personal_Address']));
//$next_addr=addslashes(($_POST['next_addr']));
//$next_addr=addslashes(($_POST['Next_of_Kin_Name']));

$updateSQL = sprintf("UPDATE e_data_profile SET paddr=%s, phone=%s,blood_group=%s, marital=%s, religion=%s, next_name=%s, next_rel=%s, next_addr=%s, next_phone=%s, session=%s WHERE stdid=%s",
			GetSQLValueString($_POST['Personal_Address'], "text"),
			GetSQLValueString($_POST['phone'], "text"),
			GetSQLValueString($_POST['blood_group'], "text"),
			GetSQLValueString($_POST['marital'], "text"),
			GetSQLValueString($_POST['religion'], "text"),
			GetSQLValueString($_POST['Next_of_Kin_Name'], "text"),
			GetSQLValueString($_POST['next_Kin_relation'], "text"),
			GetSQLValueString($_POST['next_addr'], "text"),
			GetSQLValueString($_POST['next_phone'], "text"),
			GetSQLValueString($_POST['current_session'], "text"),			
			GetSQLValueString($_POST['stdid3'], "text"));

 
  $Result1 =  mysqli_query($conn,$updateSQL) or die(mysqli_connect_errno());

include_once('encodeco.php');
$urlstdid=encode("$stdid3","$mykey");

$MM_restrictGoTo = "main.php?stdid=$urlstdid&view=adm";
header("Location: ". $MM_restrictGoTo);
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">

<script type="text/javascript">
$(document).ready(function(){
                     $("#datepicker").datepicker( {
					showOn: "button", buttonImage: "images/calendar.gif", buttonImageOnly: true, 
					 changeMonth: true,
					 changeYear: true,
					 yearRange: "-100: +0",
					 dateFormat: "dd/mm/yy"
               });
			   })
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
        </script>
        
      <form action="<?php echo $editFormAction; ?>" method="POST" enctype="multipart/form-data" name="pdetail" id="pdetail" onsubmit="MM_validateForm('Personal_Address','','R','phone','','R','marital','','R','religion','','R','blood_group','','R','Next_of_Kin_Name','','R','next_Kin_relation','','R','next_addr','','R','next_phone','','R');return document.MM_returnValue">
  <br />
  <table width="500" border="1" class="altrowstable" id="alternatecolor2">
    <tr>
      <td width="217">Permanent Address:</td>
      <td width="267" colspan="2"><textarea name="Personal_Address" id="Personal_Address"><?php echo $paddr; ?></textarea></td>
    </tr>
    <tr>
      <td>Personal Mobile Number(s):</td>
      <td colspan="2"><input name="phone" type="text" id="phone" value="<?php echo $phone; ?>" size="25" /></td>
    </tr>
    <tr>
      <td>Marital Status:</td>
      <td colspan="2"><select name="marital" id="marital">
        <option value="<?php echo $marital; ?>"><?php echo $marital; ?></option>
        <option value="Single ">Single </option>
        <option value="Married ">Married </option>
        <option value="Widowed ">Widowed </option>
        <option value="Divorced ">Divorced </option>
        <option value="Separated ">Separated </option>
        <option>-- select --</option>
      </select></td>
    </tr>
    <tr>
      <td>Religion:</td>
      <td colspan="2"><select name="religion" id="religion">
        <option value="<?php echo $religion; ?>"><?php echo $religion; ?></option>
        <option value="Islam">Islam</option>
        <option value="Christianity">Christianity</option>
        <option>-- select --</option>
      </select></td>
    </tr>
    <tr>
      <td>Blood Group:</td>
      <td colspan="2"><select name="blood_group" id="blood_group">
        <option value="<?php echo $blood_group; ?>" selected="selected"><?php echo $blood_group; ?></option>
        <option>-- select --</option>
        <option value="O-">O-</option>
        <option value="O+">O+</option>
        <option value="A+">A+</option>
        <option value="A-">A-</option>
        <option value="B-">B-</option>
        <option value="B+">B+</option>
        <option value="AB-">AB-</option>
        <option value="AB+">AB+</option>
      </select></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td>Next of Kin Name</td>
      <td colspan="2"><input name="Next_of_Kin_Name" type="text" id="Next_of_Kin_Name" value="<?php echo $next_name; ?>" /></td>
    </tr>
    <tr>
      <td>Next of Kin Relationship</td>
      <td colspan="2"><label for="l">
        <input name="next_Kin_relation" type="text" id="next_Kin_relation" value="<?php echo $next_rel; ?>" />
      </label></td>
    </tr>
    <tr>
      <td>Next of Kin Address:</td>
      <td colspan="2"><textarea name="next_addr" id="next_addr"><?php echo $next_addr; ?></textarea></td>
    </tr>
    <tr>
      <td>Next of Kin Phone Number:</td>
      <td colspan="2"><input name="next_phone" type="text" id="next_phone" value="<?php echo $next_phone; ?>" /></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
      <td><input type="hidden" name="stdid" id="stdid" value ="<?php echo $stdid; ?>" />
      <label for="stdid3"></label>
      <input name="stdid3" type="hidden" id="stdid3" value="<?php echo $stdid; ?>" />
      <input type="hidden" name="current_session" id="current_session" value ="<?php echo $current_session; ?>" />
      </td>
      <td><input type="submit" name="update_profile" id="update_profile" value="Update Profile" /></td>
      <td><?php echo '<a href="main.php?' . 'stdid=' . $urlstdid .'&view=' . 'print_profile' .'">Preview/Print</a>'; ?></td>
      
    </tr>
  </table>
  <p>&nbsp;</p>
          <input type="hidden" name="MM_update" value="pdetail" />
</form>
<label for="dob"></label>


