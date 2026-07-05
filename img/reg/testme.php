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


$stdid='PG719879';


$query_rscheckco = sprintf("SELECT programmes.CODE,pgapplication.programme_title,pgapplication.session,reg_password.ID_NUMBER FROM pgapplication, programmes,reg_password WHERE pgapplication.course1=programmes.programme and pgapplication.applicant_id=reg_password.stdid and applicant_id=%s", GetSQLValueString($stdid, "text"));
$rscheckco =  mysqli_query($conn,$query_rscheckco) or die(mysqli_connect_errno());
$row_rscheckco = mysqli_fetch_array($rscheckco);
$totalRows_rscheckco = mysqli_num_rows($rscheckco);
		
$CODE  = $row_rscheckco['CODE'];
	 $ID_NUMBER  = $row_rscheckco['ID_NUMBER'];
	 $programme_title  = $row_rscheckco['programme_title'];
	 $session  = $row_rscheckco['session'];

if($programme_title=='PGD'){
  echo $programme_title . '/' . $CODE. '/' . substr($session,0,5);
}else{
  echo $programme_title . '/' . $CODE. '/' . substr($session,0,5);	
	}


?>