<?php include("../Connections/Conn.php");?>

<?php

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
$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_GET["i"])) {
	$lab_request_id = $_GET["i"];
	$sms="i";
		 mysql_select_db($database_conn, $conn);
	    $query_rstSelectt = sprintf("SELECT lab.*, e.phone FROM lab_manage as lab inner join enrollee as e on e.hospital_no=lab.patient WHERE labrequest_no=%s", GetSQLValueString($lab_request_id, "text"));
                    $rstSelect2 = mysql_query($query_rstSelectt, $conn) or die(mysql_error());	
			$roww=mysql_fetch_array($rstSelect2);
			$patient_name=$roww['patient_name'];
			$patient_no=$roww['patient'];
			$phone=$roww['phone'];
			$sender='habu';	
}


if (isset($_GET["e"])) {
	$lab_request_id = $_GET["e"];
	$sms="e";
		 mysql_select_db($database_conn, $conn);
    $query_rstSelectt = sprintf("SELECT lab.*, e.phone FROM lab_manage as lab inner join pharm_ext as e on e.transc_code=lab.patient WHERE labrequest_no=%s", GetSQLValueString($item_to_search, "text"));
                    $rstSelect2 = mysql_query($query_rstSelectt, $conn) or die(mysql_error());	
			$roww=mysql_fetch_array($rstSelect2);
			$patient_name=$roww['patient_name'];
			$patient_no=$roww['patient'];
			$phone=$roww['phone'];
			$sender='habu';	
}

$url1 = "http://www.50kobo.com/tools/xml/Sms.php";
$flash = 1;
$result = '';


if (isset($_POST["send_sms"])) {

    $username = $_POST['username'];
    $userpassword = $_POST['passwd'];

    $sendername = substr($_POST['sender_name'],0,11);
    $recipient = $_POST['telephone'];

    $message =$_POST['message'];
    if ( get_magic_quotes_gpc() ) {
            $message = stripslashes($_POST['message']);
    }
    $message = substr($_POST['message'],0,160);
    $listname = '';

    //Send the sms before re-loading the script page
    $result = 'Nothing sent';

    $result = useGet(); #Uncomment this line then comment the next line to use HTTP GET for sending the message
   // $result = useXML();   #This method sends the message using HTTP POST requestin XML
   
   
   /// add
   /*
   $setdate=date('Y-m-d H:i:s');
   $msg=$_POST["message_title"] .'<br>'. $_POST["message"];
   
   $update = sprintf("INSERT INTO sms(patient_no,patient_name,phone,message,sender,date_sent) VALUES (%s,%s,%s,%s,%s,%s)",
		GetSQLValueString($_POST["patient_no"], "text"),
		GetSQLValueString($_POST["patient_name"], "text"),
		GetSQLValueString($_POST["telephone"], "text"),
		GetSQLValueString($msg, "text"),
		GetSQLValueString($_POST["sender_name"], "text"),
		GetSQLValueString($setdate, "text"));
	mysql_select_db($database_conn, $conn);
	$Result1 = mysql_query($update, $conn) or die(mysql_error());
    */
}


//The main function that collects the information and sends it

	function useGET(){
		global $url1, $username, $userpassword, $flash, $sendername, $message, $listname, $recipient;

		$getrequest = $url1
			."?username=".urlencode($username)."&"
			."password=".urlencode($userpassword)."&"
			."sender=".urlencode($sendername)."&"
			."message=".urlencode($message)."&"
			."flash=".urlencode($flash)."&"
			."sendtime="."&"
			."listname=".$listname."&"
			."mobile=".$recipient
			;

		return postRequestData($getrequest);
	}



      //Function to connect to SMS sending server using GET request
	function postRequestData($getrequest){
		$url=$getrequest;
                $response = '';
		$fp = @fopen($url, 'rb', false);
		if (!$fp) {
			echo ("Problem with $url.<br> Url is inaccessible");
			return false;
		}
		stream_set_timeout($fp, 0, 250);
		try{
                    $response = @stream_get_contents($fp);
		     if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
                     }
		 }
                 catch(Exception $e){
                     echo $e->getMessage();
                 }
		echo $response;
}




?>


<!DOCTYPE html>
<html>

	<?php include("../inc/header.php"); ?>


<body>

<div id="wrapper">

   <?php include("../inc/nav_side.php"); ?>


<div id="page-wrapper" class="gray-bg">
   <?php include("../inc/nav_header.php"); ?>
   

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Layouts</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.php">Home</a>
            </li>
            <li class="active">
                <strong>Layouts</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>

        <div class="wrapper wrapper-content  animated fadeInRight">
            <div class="row">
                <div class="col-lg-5">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Prepare Message to Send</h5>
                        </div>
                        <div class="ibox-content">

                
 <form method="POST" id="collect_specimen_form">
 
   <?php
    if(!empty($_POST) ){
  	if( (int)$result == 100 ){
		print('Message sent');
	}
	else{
		print("Message not sent - Error code: $result");
	}
    }
  ?>
                <div class="form_sep">
                  <label for="reg_input_no" class="">Patient Details:</label><br>
                	<strong><i class="fa fa-user"></i>&nbsp; <?php echo $patient_name  .'  / ' ?><i class="fa fa-phone"></i> &nbsp; <?php echo $phone;  ?></strong>
            </div>
            
              <div class="form_sep">
                  <label for="reg_input_no" class="">Message Title</label>
                <input type="text" id="message_title" name="message_title" class="form-control" value="Lab Result">
            </div>
            
            <div class="form_sep">   
                  <label for="reg_input_no" class="">Message</label>
  <textarea name="message" id="message" cols="15" rows="10" class="form-control" data-minlength="10">Ready for Collection</textarea>
            </div>
                <div class="form_sep">
                 <input type="submit" name="send_sms" id="send_sms" value="Send" class="btn btn-success btn-xs" />
             </div>
             
                <input type="hidden" name="labrequest_no"  id="labrequest_no" />
                <input name="username" type="hidden" id="username" value="clickhab@gmail.com"/>
                <input name="passwd" type="hidden" id="passwd" value="goodness" />
                <input name="sender_name" type="hidden" id="name" value="<?php echo $sender; ?>" />
                <input name="telephone" type="hidden" id="telephone" value="<?php echo '8036360635'// $phone; ?>" />
                  <input name="patient_name" type="hidden" id="patient_name" value="<?php echo $patient_name; ?>" />
                <input name="patient_no" type="hidden" id="patient_no" value="<?php echo $patient_no; ?>" />


               
 	 </form> 
                     
                
                        </div>
                    </div>


                </div>
                <div class="col-lg-7">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Data Display</h5>
                            <div class="ibox-tools">
                            <a href="<?php if(isset($_GET['scan'])){?>printscan.php?<?php echo $sms .'='. $lab_request_id;}else{?>printlab.php?<?php echo $sms .'='. $lab_request_id;} ?>"  class="btn btn-danger btn-xs" ><strong><i class="fa fa-times"></i> &nbsp;Close</strong></a>
                            </div>
                        </div>
                        <div class="ibox-content">


                  

                            
                        </div>
                    </div>


                </div>
            </div>

        </div>
           
                    </div>
        </div>

<?php include("../inc/footer_scripts.php"); ?>


    
    
        <!-- Data Tables -->
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
   
   <script>
        $(document).ready(function() {
            $('.dataTables-example').dataTable({
                responsive: true,
                "dom": 'T<"clear">lfrtip',
                "tableTools": {
                    "sSwfPath": "js/plugins/dataTables/swf/copy_csv_xls_pdf.swf"
                }
            });

            /* Init DataTables */
            var oTable = $('#editable').dataTable();

            /* Apply the jEditable handlers to the table */
            oTable.$('td').editable( '../example_ajax.php', {
                "callback": function( sValue, y ) {
                    var aPos = oTable.fnGetPosition( this );
                    oTable.fnUpdate( sValue, aPos[0], aPos[1] );
                },
                "submitdata": function ( value, settings ) {
                    return {
                        "row_id": this.parentNode.getAttribute('id'),
                        "column": oTable.fnGetPosition( this )[2]
                    };
                },

                "width": "90%",
                "height": "100%"
            } );


        });

        function fnClickAddRow() {
            $('#editable').dataTable().fnAddData( [
                "Custom row",
                "New row",
                "New row",
                "New row",
                "New row" ] );

        }
    </script>
     
</body>

</html>
