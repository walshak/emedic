<?php
session_start();
include '../../Connections/Conn.php'; 
define('staff_p', '../../uploads/staff/');
define('enrollee_p', '../../uploads/enrollee/');
ob_start();

date_default_timezone_set('Africa/Lagos');
$setdate=date("Y-m-d");
$setdatetime=date("Y-m-d H:i:s");
   
if (isset($_SESSION['username']) and $_SESSION['username']!='' and $_SESSION['fullname']!=''){

	try{
				$uname=$_SESSION['username'];
			$rights=$_SESSION['rights'];
			$primary_rights=$_SESSION['primary_rights'];
			$fullname=$_SESSION['fullname'];
			$specialist=$_SESSION['specialist'];
			$unit_head=$_SESSION['unit_head'];
			$dept_id=$_SESSION['dept_id'];
			$ECode_logged=$_SESSION['EmployeeCode'];
			$bill_account_status= $_SESSION['bill_account_status'];
	
	require_once('../../Connections/Conn.php'); 
	
	$stmt22 = $db->query("SELECT sn FROM admin_users_logs WHERE username='$uname' order by sn desc limit 1");
		if($stmt22->rowCount()>0){
				$rowx = $stmt22->fetch(PDO::FETCH_ASSOC);
				//$sn=$rowx['sn'];
				$_SESSION['last_login_id']=$rowx['sn'];
	}

	}catch(Exception $e){
		//var_dump($e);
	}
	

} else{
	try{
			$time=time();
//			error_reporting(0);
			ob_start();ob_clean();

			if(!empty($_SESSION['username']))
			{ 
				
				
				if(isset($_SESSION['last_action'])){
					if(($time-$_SESSION['last_action'])>60000){
					unset($_SESSION['username']);
					unset($_SESSION['rights']);
					unset($_SESSION['primary_rights']);
					unset($_SESSION['fullname']);
					unset($_SESSION['last_action']);
					unset($_SESSION['specialist']);
					unset($_SESSION['unit_head']);
					session_destroy();
					header('location:../../index.php');

					}
					
				}else{
					$_SESSION['last_action']=time();
//					return true;
				}
			
				
			}
			else
			{
			//	var_dump($_SESSION);
				header("location: ../../index.php");

			}
	}catch(Exception $e){
		var_dump($e);
	}
	
}


?>
<!DOCTYPE html>
<html>

<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		
        
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../font-awesome/css/font-awesome.css" rel="stylesheet">
	

    <link href="../../css/plugins/iCheck/custom.css" rel="stylesheet">
    
        <link href="../../css/plugins/summernote/summernote.css" rel="stylesheet">
    <link href="../../css/plugins/summernote/summernote-bs3.css" rel="stylesheet">

    <link href="../../css/plugins/chosen/chosen.css" rel="stylesheet">

    <link href="../../css/plugins/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

    <link href="../../css/plugins/cropper/cropper.min.css" rel="stylesheet">

    <link href="../../css/plugins/switchery/switchery.css" rel="stylesheet">

    <link href="../../css/plugins/jasny/jasny-bootstrap.min.css" rel="stylesheet">

    <link href="../../css/plugins/nouslider/jquery.nouislider.css" rel="stylesheet">

    <link href="../../css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">

    <link href="../../css/plugins/clockpicker/clockpicker.css" rel="stylesheet">

    <link href="../../css/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet">
    
    <link href="../../css/plugins/datapicker/datepicker3.css" rel="stylesheet">

    <link href="../../css/plugins/ionRangeSlider/ion.rangeSlider.css" rel="stylesheet">
    <link href="../../css/plugins/ionRangeSlider/ion.rangeSlider.skinFlat.css" rel="stylesheet">

    <link href="../../css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="../../css/plugins/dataTables/dataTables.responsive.css" rel="stylesheet">
    <link href="../../css/plugins/dataTables/dataTables.tableTools.min.css" rel="stylesheet">
    
        <link href="../../css/plugins/dataTables/datatables.min.css" rel="stylesheet">

        <!-- c3 Charts -->
    <link href="../../css/plugins/c3/c3.min.css" rel="stylesheet">
	   
    <link href="../../css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="../../css/others.css" rel="stylesheet">
    <link href="../../css/plugins/textSpinners/spinners.css" rel="stylesheet">	   
    <link href="../../css/animate.css" rel="stylesheet">
    <link href="../../css/style.css" rel="stylesheet">
    <link href="../../css/sweetalert2.min.css" rel="stylesheet">
    <link href="../../css/select2.min.css" rel="stylesheet">
    <link href="../../css/select2-bootstrap.min.css" rel="stylesheet">
    <link href="../../css/bootstrap-timepicker.min.css">
	<link href="../../css/timepicker.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../js/vendors/editor/dist/ui/trumbowyg.css">
	<style>

.search-table-outter { overflow-x: scroll; }
.blink {
  animation: blink 1s steps(1, end) infinite;
}

@keyframes blink {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}	
	
.nav.nav-tabs > li.active {
	background-color:aquamarine;
	
}
	.nav.nav-tabs > li {
	 list-style-position:inside;
    border-top: 1px solid #B2BEB5;
    border-right: 1px solid #B2BEB5;
    border-left: 1px solid #B2BEB5;
		border-radius: 3px;
}
	
	
</style>
<?php

if (strtoupper($_SESSION['password']) == 'STAFF123') {
    header("location:../../profile/index.php?profile=$uname&changepassword");
}

include '../admin_previleges.php';

$responsible = $_SESSION['fullname'];
$setdate = date('Y-m-d');
$year = date('Y');
$patient_acct_status = 1;

include_once '../../inc/alert_msg.php'; 
?>

<title>WebMedic | <?php echo $_SESSION['Designation']; ?> - Accounting</title>
</head>