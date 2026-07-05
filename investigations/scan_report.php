<?php include("../Connections/Conn.php");?>

<?php

session_start();

if(isset($_POST["print_report"])){
	
	$scan_request_NO=$_POST['scan_rq'];
	$business_service_center=$_POST['business_service_center'];
if($business_service_center=='IN'){
		$buz="i";
}else{
		$buz="e";
	}
	header("location:printscan.php?$buz=$scan_request_NO");
	
}


if (isset($_POST['capture_investigation'])) {
    $Request_ID = $_POST['scan_rq'];
    $fullname = $_SESSION['fullname'];
    $specimen_taken = 'capture';
    $setdate = date('Y-m-d H:i:s');

    $stmt = $db->prepare("UPDATE lab_manage SET collected_by=:fullname, collected_date=:setdate, data_capture_status=:status, collected_specimen=:specimen WHERE labrequest_no=:request_id");

    $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
    $stmt->bindParam(':setdate', $setdate, PDO::PARAM_STR);
    $stmt->bindParam(':status', 'capture', PDO::PARAM_STR);
    $stmt->bindParam(':specimen', 'capture', PDO::PARAM_STR);
    $stmt->bindParam(':request_id', $Request_ID, PDO::PARAM_INT);

    $stmt->execute();
}

if (isset($_POST["paste_btn"])) {
    $paste_tem_sn = $_POST["template_sn"];

    $stmt = $db->prepare("SELECT * FROM invsti_template WHERE sn=:template_sn");
    $stmt->bindParam(':template_sn', $paste_tem_sn, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $template_note = $row['template_2'];
    }
}

if (isset($_POST["edit"])) {
    $scan_rq = $_POST["scan_rq"];

    $stmt = $db->prepare("UPDATE lab_manage SET data_capture_status=:status WHERE labrequest_no=:request_id");
    $stmt->bindParam(':status', 'result', PDO::PARAM_STR);
    $stmt->bindParam(':request_id', $scan_rq, PDO::PARAM_INT);
    $stmt->execute();

    header("location:scan_report.php?e=$scan_rq");
}

if (isset($_POST["reject_button"])) {
    $RQ_No = $_POST["scan_rq"];
    include("apr_combl.php");

    $update = "UPDATE lab_manage SET data_capture_status=:status WHERE labrequest_no=:request_id";
    $stmt = $db->prepare($update);
    $stmt->bindParam(':status', 'reject', PDO::PARAM_STR);
    $stmt->bindParam(':request_id', $RQ_No, PDO::PARAM_INT);
    $stmt->execute();

    $sub = CheckWaitingList(); // Assuming CheckWaitingList() is a function or method
}


if(isset($_POST["save_result"])){
		//// data enrtry taff sending for approval 
		
			  	$RQ_No=$_POST["scan_rq"];
			  	$cr=$_POST["cr"];
	 					// include("apr_combl.php");
						 
		$request_status="result";
		if($_POST["lab_sci_name"]==''){
			$lab_sci_name="";
			$lab_sci_speciality="";
			$entered_by=$_POST['entered_by'];
		}else{
			$lab_sci_name=$_POST['lab_sci_name'];
			$lab_sci_speciality=$_POST['lab_sci_speciality'];
			$entered_by=$_POST['entered_by'];
		}
		
		$approved_by='';
	
	$sub2=update_report($lab_sci_name,$lab_sci_speciality,$request_status,$entered_by,$approved_by,$cr);
				$sub=CheckWaitingList();
		
}

if(isset($_POST["approve"])){
	$scan_rq=$_POST['scan_rq'];
				  	$cr=$_POST["cr"];

				  	$RQ_No=$_POST["scan_rq"];
	 					 include("apr_combl.php");

	if((isset($_POST["radiologist"]) and $_POST["radiologist"]!='') or $_SESSION['speciality']=='Radiologist') {

				if(isset($_POST["radiologist"]) and $_POST["radiologist"]!='') {
									$radiologist_name=$_POST["radiologist"];
					}elseif($_SESSION['speciality']=='Radiologist'){
						$radiologist_name=$_SESSION["fullname"];
							}else{
							$radiologist_name='';
					}
					
			$request_status="approve";
			
		if($_SESSION['speciality']=='Radiologist'){
				$lab_sci_name=$_POST['lab_sci_name'];
				$lab_sci_speciality=$_POST['lab_sci_speciality'];
			$approved_by=$_SESSION['fullname'];
			$entered_by=$_POST['entered_by'];			
		}else{
				$lab_sci_name=$_POST['lab_sci_name'];
				$lab_sci_speciality=$_POST['lab_sci_speciality'];
			$entered_by=$_POST['entered_by'];
			$approved_by=$radiologist_name;
		}
		
$sub2=update_report($lab_sci_name,$lab_sci_speciality,$request_status,$entered_by,$approved_by,$cr);
				$sub=CheckWaitingList();
		
	}else{
	header("location:scan_report.php?e=$scan_rq&rd");
	}
	
}


function update_report($lab_sci_name,$lab_sci_speciality,$request_status,$entered_by,$approved_by,$cr){
	
include("../Connections/Conn.php");


if (isset($_POST["scan_rq"])) {
    $scan_rq = $_POST["scan_rq"];

    // Check if lab request exists
    $stmtChk = $db->prepare("SELECT * FROM lab_manage WHERE labrequest_no = :request_id");
    $stmtChk->bindParam(':request_id', $scan_rq, PDO::PARAM_INT);
    $stmtChk->execute();

    if ($stmtChk->rowCount() > 0) {
        $row = $stmtChk->fetch(PDO::FETCH_ASSOC);
        $result_note = $row['result_note'];
        $test_id = $row['test_id'];
        $test_name = $row['test_name'];
        $new_report = $_POST['scan_report'];

        // Insert into lab_update_rpt if result_note is not empty and new_report is different
        if (!empty($result_note) && $new_report != $result_note) {
            $add_row = $db->prepare("INSERT INTO lab_update_rpt (labrequest_no, test_id, test_name, result_note) 
                                     VALUES (:scan_rq, :test_id, :test_name, :result_note)");
            $add_row->bindParam(':scan_rq', $scan_rq, PDO::PARAM_INT);
            $add_row->bindParam(':test_id', $test_id, PDO::PARAM_INT);
            $add_row->bindParam(':test_name', $test_name, PDO::PARAM_STR);
            $add_row->bindParam(':result_note', $result_note, PDO::PARAM_STR);
            $add_row->execute();
        }

        // Update lab_manage table with new_report and other details
        $setdate = date("Y-m-d H:i:s");
        $update = $db->prepare("UPDATE lab_manage SET 
                                result_note = :new_report,
                                lab_sci_name = :lab_sci_name,
                                lab_sci_speciality = :lab_sci_speciality,
                                result_date = :result_date,
                                data_capture_status = :request_status,
                                entered_by = :entered_by,
                                approved_by = :approved_by 
                                WHERE labrequest_no = :scan_rq");

        $update->bindParam(':new_report', $new_report, PDO::PARAM_STR);
        $update->bindParam(':lab_sci_name', $lab_sci_name, PDO::PARAM_STR);
        $update->bindParam(':lab_sci_speciality', $lab_sci_speciality, PDO::PARAM_STR);
        $update->bindParam(':result_date', $setdate, PDO::PARAM_STR);
        $update->bindParam(':request_status', $request_status, PDO::PARAM_STR);
        $update->bindParam(':entered_by', $entered_by, PDO::PARAM_STR);
        $update->bindParam(':approved_by', $approved_by, PDO::PARAM_STR);
        $update->bindParam(':scan_rq', $scan_rq, PDO::PARAM_INT);
        $update->execute();

        // Perform additional actions if $cr is 1
        if ($cr == 1) {
            $one = '1';
            $invoice_no = mt_rand(1000000, 9999999);
            $update_invoice = $db->prepare("UPDATE patient_ap_services SET 
                                           cr = :one,
                                           invoice_no = :invoice_no,
                                           invoice_by = :invoice_by,
                                           invoice_status = :invoice_status,
                                           drug_status = :drug_status 
                                           WHERE drug_sn = :scan_rq");

            $update_invoice->bindParam(':one', $one, PDO::PARAM_INT);
            $update_invoice->bindParam(':invoice_no', $invoice_no, PDO::PARAM_INT);
            $update_invoice->bindParam(':invoice_by', $_SESSION["fullname"], PDO::PARAM_STR);
            $update_invoice->bindParam(':invoice_status', $one, PDO::PARAM_INT);
            $update_invoice->bindParam(':drug_status', $one, PDO::PARAM_INT);
            $update_invoice->bindParam(':scan_rq', $scan_rq, PDO::PARAM_INT);
            $update_invoice->execute();
        }
    } else {
        header("location: mgt.php");
        exit; // Ensure script stops execution after redirection
    }
}



function CheckWaitingList(){
	include("../Connections/Conn.php");
	
	
	if (isset($_POST["emr_no"])) {
		$emr_no = $_POST["emr_no"];
	
		// Prepare and execute the query
		$stmtChk = $db->prepare("SELECT * FROM lab_manage WHERE patient = :emr_no AND data_capture_status = 'queue'");
		$stmtChk->bindParam(':emr_no', $emr_no, PDO::PARAM_STR);
		$stmtChk->execute();
	
		if ($stmtChk->rowCount() > 0) {
			// Redirect to scan_report.php with query parameter 'w' if records found
			header("location: scan_report.php?w=" . urlencode($emr_no));
			exit; // Ensure script stops execution after redirection
		} else {
			// Redirect to scan_report.php with query parameter 'approve' if no records found
			header("location: scan_report.php?approve=" . urlencode($emr_no));
			exit; // Ensure script stops execution after redirection
		}
	}	
	
	}
	
	

if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "attachement")) {
	
	$scan_request_NO=$_POST['scan_rq'];
			$desc=$_POST['desc'];
		$emr_no=$_POST["emr_no"];

define('UPLOAD_DIR', 'attach/');

if (!empty($_FILES["file_attach"])) {
	
    $file_attach = $_FILES["file_attach"];

    if ($file_attach["error"] !== UPLOAD_ERR_OK) {
        echo "<p>An error occurred.</p>";
        exit;
    }

    // ensure a safe filename
    $name = preg_replace("/[^A-Z0-9._-]/i", "_", $file_attach["name"]);

    // don't overwrite an existing file
    $i = 0;
    $parts = pathinfo($name);
    while (file_exists(UPLOAD_DIR . $name)) {
        $i++;
        $name = $parts["filename"] . "-" . $i . "." . $parts["extension"];
    }

    // preserve file from temporary directory
    $success = move_uploaded_file($file_attach["tmp_name"], UPLOAD_DIR . $scan_request_NO.'_' .$desc.'_'. $name);
    if (!$success) { 
        echo "<p>Unable to save file.</p>";
        exit;
    }

    // set proper permissions on the new file
    chmod(UPLOAD_DIR . $scan_request_NO.'_' .$desc.'_'. $name, 0644);

		$sql = "UPDATE lab_manage SET attachment = :attachment WHERE labrequest_no = :labrequest_no";
		$stmt = $db->prepare($sql);
		
		// Bind parameters
		$attachment = $scan_request_NO . '_' . $desc . '_' . $name;
		$labrequest_no = $_POST['scan_rq'];
		$stmt->bindParam(':attachment', $attachment, PDO::PARAM_STR);
		$stmt->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
	
		$stmt->execute();
		
		
	header("location:scan_report.php?approve=$emr_no&ach");


	

$msg=1;
}
}

?>


<!DOCTYPE html>
<html>

<head>

	<?php include("../inc/header.php"); ?>
<body>

    <div id="wrapper">

    <?php include("../inc/nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
   <?php include("../inc/nav_header.php"); ?>
       
	<?php
	
	///echo $_SESSION['speciality']
			
			
    if(isset($_GET["r"])){
		$search=$_GET["r"];
	}elseif(isset($_GET["e"])){
			$search=$_GET["e"];
	}	
	
	$sql = "SELECT * FROM lab_manage WHERE labrequest_no = :labrequest_no";
	$stmt = $db->prepare($sql);
	$stmt->bindParam(':labrequest_no', $search, PDO::PARAM_STR);
	$stmt->execute();
	
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if ($row) {
		$hosp_no = $row['patient'];
		$patient_name = $row['patient_name'];
		$report_status = $row['data_capture_status'];
		$entered_by = $row['entered_by'];
		$approved_by = $row['approved_by'];
		$business_service_center = $row['business_service_center'];
		$test_id = $row['test_id'];
		$collected_by = $row['collected_by'];
		$collected_date = $row['collected_date'];
		$request_note = $row['request_note'];
	}
	
	
			
			/// =%s, collected_date=%s, d
			
		$Current_date=date("Y-m-d");			
			$date1 = new DateTime($Current_date);
			$date2 = new DateTime($row['result_date']);
			$diff = $date2->diff($date1);	
			$day=$diff->format('%a');				
	
	///// lock if approve 
			
		if($report_status=='approve' and $day>3){
			$enable_edit=1;
		}else{
			$enable_edit=0;
		}
			
    $stmt_list=$db->query("SELECT paystatus FROM patient_ap_services WHERE drug_sn='$search' and paystatus=1");
			if($stmt_list->rowCount()>0){
				$pay_lock=0;	
				$cr='0';	
						}else{	
				$cr='1';
	
$oncredit=$_SESSION['oncredit'];
$oncredit_adm=$_SESSION['oncredit_adm'];	
				
	/// check if patient on-admission
	if($oncredit==1){
		$enable_cr_post=1;
	}else{
	$stmt_list=$db->query("SELECT hospital_no FROM admission WHERE hospital_no='$hosp_no' and adm_status='3'");
		if($stmt_list->rowCount()>0 and $oncredit_adm==1){
			$enable_cr_post=1;	
		}else{$enable_cr_post=0;}
	}
}
			
if($enable_cr_post==1){
$pay_lock=0;
}
				
		?>	
 
        <div class="wrapper wrapper-content">
        <div class="row">
              <div class="col-lg-3">
                <div class="ibox float-e-margins">
                    <div class="ibox-content mailbox-content">
                        <div class="file-manager">
                            <div class="space-25"></div>
                           
<?php
							

if(isset($_GET["approve"])){
			$hosp_no=$_GET["approve"];
			$title ="Approved Investigation(s)";
}elseif(isset($_GET['pending'])){
	$hosp_no=$_GET["pending"];
	$title ="Pending Investigation(s)";
}elseif(isset($_GET['w'])){
	$hosp_no=$_GET["w"];
	$title ="Pending Investigation(s)";
}elseif(isset($_GET['delete'])){
	$hosp_no=$_GET["delete"];
	$title ="Canceled Investigation(s)";
}elseif(isset($_GET['reject'])){
	$hosp_no=$_GET["reject"];
	$title ="Rejected Investigation(s)";
}elseif(isset($_GET['attach'])){
	$hosp_no=$_GET["attach"];
	$title ="Attached Documents";
}elseif(isset($_GET['archive'])){
	$hosp_no=$_GET["archive"];
	$title ="Archived";
}

?>							
<table width="100%" style="font-size: 17px;">
	<tr>
		<td><strong>Hospital Number:</strong></td><td><?php echo $hosp_no; ?></td>
	</tr>	
	<tr>
		<td><strong>Name:</strong></td><td><?= $patient_name; ?></td>
	</tr>
</table><br>							

							
                            <h2>Folders</h2>
							<ul class="folder-list m-b-md" style="padding: 0">
                            
<?php 		

$stmt_count = $db->prepare("SELECT data_capture_status, attachment FROM lab_manage WHERE patient = :hosp_no AND section = 'Radiology'");
$stmt_count->bindParam(':hosp_no', $hosp_no, PDO::PARAM_STR);
$stmt_count->execute();
if ($stmt_count->rowCount() > 0) {
							$capture_C=0; $approv_count=0; $reject_c=0;$attachment=0;
				while($row_c=$stmt_count->fetch(PDO::FETCH_ASSOC)){
					
						if($row_c['data_capture_status']=='approve'){
							$approv_count=$approv_count+1;
						}
						if($row_c['data_capture_status']=='capture' or $row_c['data_capture_status']=='queue' or $row_c['data_capture_status']=='result'){
							$capture_C=$capture_C+1;
						}
					if($row_c['data_capture_status']=='reject'){
							$reject_c=$reject_c+1;
						}
					if($row_c['attachment']!=''){
							$attachment=$attachment+1;
						}						
						
				}
		
					  
		}?>                
        <?php if($capture_C>0){ ?>            
    <li><a href="scan_report.php?pending=<?php echo $hosp_no; ?>" style="font-size: 16px; color: black;"> <i class="fa fa-folder-open-o "></i> Requests Pending <span class="label label-warning pull-right" style="font-size: 16px;"><?php echo $capture_C; ?></span> </a></li>
                            <?php } ?>

    <li><a href="scan_report.php?approve=<?php echo $hosp_no; ?>" style="font-size: 16px; color: black;"> <i class="fa fa-folder-open "></i> Approved Reports 
    <span class="label label-success pull-right" style="font-size: 16px;"><?php echo $approv_count; ?></span> </a></li>
    <li><a href="scan_report.php?reject=<?php echo $hosp_no; ?>" style="font-size: 16px; color: black;"> <i class="fa fa-times-circle"></i>Rejected
    		<?php if($reject_c>0){?><span class="label label-danger pull-right" style="font-size: 16px;"><?php echo $reject_c; ?></span><?php } ?>
    </a></li>
    <li><a href="scan_report.php?attach=<?php echo $hosp_no; ?>" style="font-size: 16px; color: black;"> <i class="fa fa-file-text-o"></i> Documents (Attachments) <span class="label label-white pull-right" style="font-size: 16px;"><?php echo $attachment; ?></span></a></li>
    <li><a href="scan_report.php?delete=<?php echo $hosp_no; ?>" style="font-size: 16px; color: black;"> <i class="fa fa-trash-o"></i> Canceled Requests</a></li>
    <li><a href="scan_report.php?archive=<?php echo $hosp_no; ?>" style="font-size: 16px; color: black;"> <i class="fa fa-archive"></i> Archived (Past Reports)</a></li>
								
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>  



            <div class="col-lg-9 animated fadeInRight">
            <div class="mail-box-header">
                <div class="pull-right tooltip-demo">
                    <a href="scan_report.php?pending=<?php echo $hosp_no; ?>" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Close & Return to Investigations"><i class="fa fa-times"></i> Close</a>
                </div>
                <h2>
                    Report<?php echo ' /' . '<small>'.$title.'</small>'; ?>
                </h2>
                <?php echo $row['test_name'];?>
                
            </div>
            
            
    <form  class="form-horizontal" action="<?php echo $editFormAction; ?>" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">
            
                <div class="mail-box">
                
<?php if(isset($_GET['e']) or isset($_POST["paste_btn"])){?>

					<?php 
					if($report_status=='queue'){?>
					
					<div class="mail-body">
					<h3 style="color: brown">Request Note:</h3><h4><?= $request_note; ?><h/4><hr>
					<h3><u>Investigation Status:</u> On-Queue</h3>
					<P>Have you started running this Investigation?<br>
					Click Capture button below to show is <strong>On-going and Awaits Result</strong>:</P>
					
					<button class="btn btn-success btn-sm"  type="submit" name="capture_investigation" >
						<i class="fa fa-times"></i> Capture</button>
					</div>

					
					
					<?php }else {
					
					if($report_status=='capture' or $report_status=='result'){ ?>
					<div class="mail-body">
					<h3  style="color: brown">Request Note:</h3><h4><?= $request_note; ?></h4><hr>
					<h3><u>Investigation Status:</u> Captured/On-going &nbsp; [ Result Pending ]</h3>
					<p style="font-size: 15px; "><strong>Captured by: </strong> <?=  $collected_by; ?> </p>
					<p style="font-size: 15px; "><strong>Date Captured: </strong><?php echo date('d,M y h:i a', strtotime($collected_date)); ?> </p>
					</div>
					<?php } ?>
	
				   <div class="mail-body">

							<div class="form-group">
							<div class="col-sm-10">
					<?php $stmt2 = $db->prepare("SELECT * FROM invsti_template WHERE sn = :test_id");
$stmt2->bindParam(':test_id', $test_id, PDO::PARAM_STR);
$stmt2->execute();

if ($stmt2->rowCount() > 0) {
	?>

							<strong>Click to use default Template</strong>

							<input type="hidden" name="template_sn" value="<?php echo $test_id; ?>"/>
							<button class="btn btn-success btn-xs" title="Paste Template" type="submit" name="paste_btn" id="paste_tem"><i class="fa fa-paste"></i> Paste</button>


							<?php }else{ ?>
							<div class="alert alert-warning"><strong>Report Template Not Available. </strong>
								<?php //if($_SESSION['create1']==1){ ?>
									<a href="setup.php?template=<?php echo $test_id; ?>">Click here to create template</a></div>
								<?php //} ?>
							<?php } ?>
								</div>

							</div>


					</div>
					
                    <div class="mail-text h-200">
              <textarea name="scan_report" id="scan_report" cols="45" rows="5" maxlength="160" class="summernote" placeholder="">
				<?php 
			   		if(isset($_POST["paste_btn"])){
						echo $template_note;
					}else{
					
					if ($row['result_note']!=""){
				  			 echo $row['result_note'];
					}else{echo '<div style="font-size:16px;"> Type your Report Here </div>';}
					
				}?>
				
				</textarea>  
                       
						<div class="clearfix"></div>
                        </div>
					
					<?php } ?>
					
					
					
                    <div class="mail-body text-left tooltip-demo">

<div class="pull-left">

<?php if($_SESSION['enter']=="1" and $_SESSION['approve']=="1" 
		 and strtoupper($_SESSION['speciality'])=="RADIOLOGIST" and 
		 	($report_status=='result' or $report_status=='capture' or $report_status=='reject')){ ?>               
<button class="btn btn-sm btn-primary" title="Click Here to Save Reports" type="submit" name="save_result">
<i class="fa fa-reply"></i>&nbsp; SAVE & APPROVE LATER</button> 

&nbsp; | &nbsp;	
	
<button class="btn btn-success btn-sm" title="Save & Approve" type="submit" name="approve" id="approve"><i class="fa fa-check-circle"></i>SAVE & COMPLETED</button>
	
<?php } ?>

<?php if($_SESSION['enter']=="1" and $_SESSION['approve']=="1" 
		 and strtoupper($_SESSION['speciality'])!="RADIOLOGIST" and 
		 	($report_status=='result' or $report_status=='capture' or $report_status=='reject')){ ?>               
<?php
	$stmtRd=$db->query(sprintf("SELECT u.fullname FROM invsti_users as i inner join admin_users as u on u.username=i.username WHERE speciality='Radiologist'")); ?>
            <strong style="color:#F00"> Select Radiologist</strong> 
            <select name="radiologist" class="form-control" required>
               <option selected="selected" value="">Select radiologist...</option>
                <?php while ($rwrx =$stmtRd->fetch(PDO::FETCH_ASSOC)){ ?>
            <option value="<?php echo $rwrx['fullname']; ?>"<?php if($approved_by==$rwrx['fullname']){?>selected<?php } ?> ><?php echo $rwrx["fullname"]; ?></option>
                      <?php } ?>
            </select>
			<hr>	
	
<button class="btn btn-primary" title="Click Here to Save Reports" type="submit" name="save_result">
<i class="fa fa-reply"></i>&nbsp; SAVE & APPROVE LATER</button> 

&nbsp; | &nbsp;	
	
<button class="btn btn-success" title="Save & Approve" type="submit" name="approve" id="approve"><i class="fa fa-check-circle"></i>&nbsp;SAVE & COMPLETED</button>	
	
	
<?php } ?>	
	

<?php if($_SESSION['enter']=="1" and $_SESSION['approve']=="0" 
		 and strtoupper($_SESSION['speciality'])!="RADIOLOGIST" and 
		 	($report_status=='result' or $report_status=='capture')){ ?>               
	
<button class="btn btn-primary" title="Click Here to Save Reports" type="submit" name="save_result">
<i class="fa fa-reply"></i>&nbsp; SAVE & APPROVE LATER</button> 

	
<?php } ?>		
	
	
<?php if($report_status=='approve'){ ?>
<button class="btn btn-success" title="Print Reports" type="submit" name="print_report" id="print_report"><i class="fa fa-print"></i> Preview & Print</button>
<?php } ?>

</div>

<div class="pull-right">
<input type="button" name="attach_file" value="Attachment" title="Attachment Document to this Report" data-target="#modal" id="<?php echo $search .'__'. $row['test_name']; ?>" class="btn btn-white btn-sm attachment"> 
</div>
<div class="clearfix"></div>
   
<?php } ?>

											
						
<?php if(isset($_GET['r'])){?>
						
		<div class="mail-body">
		<h3  style="color: brown">Request Note:</h3><h4><?= $request_note; ?></h4><hr>
		<h3><u>Investigation Status:</u> Approved</h3>
		<p style="font-size: 15px; "><strong>Captured by: </strong> <?=  $collected_by; ?> </p>
		<p style="font-size: 15px; "><strong>Date Captured: </strong><?php echo date('d,M y h:i a', strtotime($collected_date)); ?> </p>
		<hr>
		
		<?php echo $row['result_note'] .'<br>'; ?>
            <br><br><br>
         <div class="pull-right">
			 <?php if($row['approved_by']!=""){echo '<strong>APPROVED BY:</strong>' .'<br>'. $row['approved_by'];} ?></div>
         
           <div class="clearfix"></div>
       
       <div class="">
       		<?php if(($report_status=='result' or $report_status=='approve') and $_SESSION['approve']=='1' and $pay_lock==0 and $enable_edit==0){?>
                     <button class="btn btn-warning" title="Edit" type="submit" name="edit" id="edit"><i class="fa fa-pencil-square"></i> Edit Report</button>
                    &nbsp;&nbsp; 
            <?php } ?> 

       </div>
       		<?php if(($report_status=='queue') and $_SESSION['approve']=='1' and $pay_lock==0){?>
              <div class="pull-right">
       <button class="btn btn-danger btn-sm" title="Reject" type="submit" name="reject_button" id="reject_button"><i class="fa fa-times"></i> Reject</button>
       </div>
       		 <?php } ?>  
       </div>  
<?php } ?>                  

						
						
						
						
						

<?php if(isset($_GET['pending']) or isset($_GET['approve']) or isset($_GET['reject']) or isset($_GET['w']) or isset($_GET['delete'])){
	
	if(isset($_GET['approve'])){
		$link='r';
		$search_tag="data_capture_status='approve'";	
	}elseif(isset($_GET['pending']) or isset($_GET['w'])){
		$link='e';
		$search_tag="(data_capture_status='queue' or data_capture_status='result' or data_capture_status='capture')";	
	}elseif(isset($_GET['reject'])){
		$link='e';
		$search_tag="data_capture_status='reject'";	
	}elseif(isset($_GET['w'])){
		$link='e';
		$search_tag="data_capture_status='reject'";	
	}elseif(isset($_GET['delete'])){
		$link='e';
		$search_tag="data_capture_status='delete'";	
	}
		
$stmt2=$db->query("SELECT * FROM lab_manage WHERE patient='$hosp_no' and section='Radiology' and $search_tag");
			if($stmt2->rowCount()>0){  ?>
            <div class="mail-body">
<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;" >                 
                                                <thead>
                                                <tr>
                                                    <th data-toggle="true">Requested Date</th>
                                                    
                                                    <th data-toggle="true">Investigation</th>
													<th data-toggle="true">RQ.#</th>
                                                    <th data-toggle="true">Requester</th>
                                                    <th data-toggle="true">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                    while($roww=$stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                                                       <tr>  
                                     <td><?php echo date('d,M y h:i a', strtotime($roww['request_date']));?></td>
									<td><?php echo $roww['test_name']; ?></td>					   
            <td>
	<a href="scan_report.php?<?= $link; ?>=<?php echo $roww['labrequest_no']; ?>" class="btn btn-success btn-xs">Open Investigation</a></td>
                                                        <td><?php echo $roww['request_by']; ?></td>
                                                        <td><?php
											$approved_by3=$roww['approved_by'];		 
					if($roww['data_capture_status']=="result"){echo'Report Ready Awaits Approval';
					}elseif($roww['data_capture_status']=="queue"){echo "On-Queue";
					}elseif($roww['data_capture_status']=="capture"){echo "Captured";
					}elseif($roww['data_capture_status']=="approve"){echo "Report Approved $approved_by3"; 
					
					if($roww['business_service_center']=='IN'){
								$buz="i";
						}else{
								$buz="e";
							}
					?> &nbsp;&nbsp; <a href="printscan.php?<?php echo $buz .'='. $roww['labrequest_no'];?>" class="btn btn-primary btn-xs"><i class="fa fa-print"></i> &nbsp;Print &nbsp;</a>
					<?php }else{echo $roww['data_capture_status'];} ?></td>
</tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                </tbody>
                                                </table>
                                        <?php }else{ ?>
  <div class=" alert alert-warning">No Record to Display</div>
										<?php }                          


} ?>  
        
<?php if(isset($_GET['attach'])){
	
	$hosp_no=$_GET['attach'];
	
	$stmt2=$db->query("SELECT * FROM lab_manage WHERE patient='$hosp_no' and attachment!=''");
			if($stmt2->rowCount()>0){  ?>
            <div class="mail-body">
<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;">                 
                                                <thead>
                                                <tr>
                                                    <th data-toggle="true">Requested Date</th>
                                                    <th data-toggle="true">RQ.#</th>
                                                    <th data-toggle="true">Type</th>
                                                    <th data-toggle="true">Patient</th>
                                                    <th data-toggle="true">Requester</th>
                                                    <th data-toggle="true">Attachment</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                    while($roww=$stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
                                                       <tr>  
                                     <td><?php echo date('d,M y h:i a', strtotime($roww['request_date']));?></td>
            <td><a href="scan_report.php?e=<?php echo $roww['labrequest_no']; ?>"><?php echo $roww['labrequest_no']; ?></a></td>
                                                        <td><?php echo $roww['test_name']; ?></td>
                                                        <td><?php
	$part=explode(" ",$roww['patient_name']); echo $part[0] . ' ' . 
	substr($part[1], 0, 1) . '. ' ;?></td>
                                                        <td><?php echo $roww['request_by']; ?></td>
                                                        <td><a href="<?php  echo 'attach/' . $roww['attachment'];?>">Download</a></td>
</tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                </tbody>
                                                </table>
                                        <?php }else{ ?>
  <div class=" alert alert-warning">No Attachements</div>
										<?php }                          


} ?>                 
          
   <?php if(isset($_GET['archive'])){ 
    $archive=$_GET['archive'];
   
   ?>
<div class="mail-body">

<?php 

$stmt=$db->query("SELECT * FROM rdc_xray WHERE patient_no='$archive' order by date_captured desc");
if($stmt->rowCount()>0){?>
                
                					<hr>
                               <div class="alert alert-success">
                               
                               <?php echo $stmt->rowCount() . ' requests found ' ; ?>
                                </div>      
                               <table class="table table-striped table-bordered table-hover dataTables-example" style="font-size: 15px;" >                 
                                                <thead>
                                                <tr>
                                                     <th data-toggle="true">#</th>
                                                     <th data-toggle="true">Result Date</th>
                                                    <th data-toggle="true">Patient #</th>
                                                    <th data-toggle="true">Lab Tech Name</th>
                                                    <th data-toggle="true">Status</th>
                                                </tr>
                                                </thead>
                                                <tbody>
            
                                                    <?php 
                                                        $n=1;
                                                        while($roww=$stmt->fetch(PDO::FETCH_ASSOC)) {                                                        ?>
                                                       <tr> 
                                                       <td><?php echo $n; ?></td> 
                                     		<td><?php echo date('d,M y h:i a', strtotime($roww['date_captured']));?></td>
                                          <td><?php echo $roww['patient_no']; ?></td>
                                                        <td><?php echo $roww['lab_tech']; ?></td>
                                              <td><a href="scan_print_old.php?emr=<?php echo $archive .'&id='.$roww['patient_id'] ; ?>">[ See Result ]</a></td>
                                                        </tr>
                                                    <?php 
                                                       $n++;	
                                                    }?>
                                                        
                                                </tbody>
                                                </table>
                                   <?php }else{ ?>
                                  		 <div class="alert alert-warning">No Result found</div>
                                   <?php } 
								   
 } ?>        
        
          
          
                    
                </div>
                    <input type="hidden" name="test_id" id="test_id" value="<?php echo $test_id; ?>">
                    
                    <input type="hidden" name="scan_rq" id="scan_rq" value="<?php echo $row['labrequest_no']; ?>">
                    <input type="hidden" name="desc" value="<?php echo $row['labrequest_no'] .'/'.  $row['patient_name']; ?>" />
                    <input type="hidden" name="emr_no" id="emr_no" value="<?php echo $row['patient']; ?>">
                    <input type="hidden" name="patient_id" value="<?php echo $row['patient']; ?>" />
                    <input type="hidden" name="patient_type" value="<?php echo $row['business_service_center']; ?>" />
                    
                    <input type="hidden" name="report_status" id="report_status" value="<?php echo $report_status; ?>">
                    <input type="hidden" name="cr" id="cr" value="<?php echo $cr; ?>">
                 
                  <input type="hidden" name="business_service_center" id="business_service_center" value="<?php echo $business_service_center; ?>">
                  
             <?php if(strtoupper($_SESSION['speciality'])!="RADIOLOGIST"){ ?>
				
						<input type="hidden" name="entered_by" value="<?php if($row['entered_by']!=""){echo $row['entered_by'];}else{echo $_SESSION['fullname'];} ?>"/>
                        <input type="hidden" name="lab_sci_name" value=""/>
                        <input type="hidden" name="lab_sci_speciality" value=""/>
            <?php }else{ ?>
                <input type="hidden" name="entered_by" value="<?php if($row['entered_by']!=""){echo $row['entered_by'];}else{echo $_SESSION['fullname'];} ?>"/>
                <input type="hidden" name="lab_sci_name" value="<?php if($row['lab_sci_name']!=""){echo $row['lab_sci_name'];}else{echo $_SESSION['fullname'];} ?>"/>
                <input type="hidden" name="lab_sci_speciality" value="<?php if($row['lab_sci_speciality']!=""){echo $row['lab_sci_speciality'];}else{echo $_SESSION['speciality'];} ?>"/>	
  		 	<?php } ?>


                </form>
                

                
            </div>
  
  


 
<div class="modal inmodal fade" id="attach_modal" tabindex="-1" role="dialog"  aria-hidden="true" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-sm" >
        <div class="modal-content">
           <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id=""></h4>
			</div>

                <div class="modal-body">  
                
                <form method="POST" id="attach_body" enctype="multipart/form-data" action="scan_report.php" >
						  
                <div class="form_sep">
                <strong>Attachment</strong>
                </div>                          
                        
                <div class="form_sep">
                <label for="desc">Description</label>
                <input type="file" name="file_attach" id="file_foto" class="form-control" />
                </div>    
                
                <div class="form_sep">
                <label for="desc">Description</label>
                <input type="text" name="desc" class="form-control">
                </div>    
                        
                        
                        
                            <div class="form_sep">

  <button class="btn btn-primary btn-xs" type="submit" name="add_request" >Attach</button>&nbsp;&nbsp;
  <a href="" class="btn btn-warning btn-xs Cancel_lab_request" >Cancel</a>
                                
                                </div>
                                
                 				<input type="hidden" name="scan_rq" id="scan_rq" value="<?php echo $row['labrequest_no']; ?>">
                 <input type="hidden" name="emr_no" id="emr_no" value="<?php echo $row['patient']; ?>">
                                 <input type="hidden" name="MM_update" value="attachement" /> 
                               
                                </form>
                </div>  
           </div>  
      </div>  
 </div>
          
          
          <?php include("search_modal.php") ?>
             
        </div>
        </div>
        <?php include("../inc/footer.php"); ?> 

        </div>
        </div>

    
    <!-- Mainly scripts -->
    <script src="../js/jquery-2.1.1.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="../js/inspinia.js"></script>
    <script src="../js/plugins/pace/pace.min.js"></script>
    <script src="../js/plugins/chosen/chosen.jquery.js"></script>

    <!-- iCheck -->
    <script src="../js/plugins/iCheck/icheck.min.js"></script>

    <!-- SUMMERNOTE -->
    <script src="../js/plugins/summernote/summernote.min.js"></script>
    
    <script src="../js/plugins/toastr/toastr.min.js"></script>
    
	<?php if(isset($_GET['ach'])){ ?>
    <script>toastr.success('Attachment Uploaded Successfully!', 'Attachment', {timeOut: 2000})</script>
    <?php } ?>
    
    
    <script>
	



	
	$(document).on('click', '.attachment', function(){  
          var attachement_id = $(this).attr("id"); 
		  	 var res = attachement_id.split("__");
			 
			//	$('#scan_request_no').val(res[0]);
				$('.modal-title').text('New Attachment:  ' + res[0] + ' (' + res[1] + ')'); 
				$('#attach_modal').modal('show');  
			//	$('#attach_body').html(data); 
                
     }); 

	
        $(document).ready(function(){
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });


            $('.summernote').summernote();

        });
		
	
			$(".chosen-select").chosen({ allow_single_deselect: true, enable_search_threshold: 10,no_results_text:'Oops, nothing found!', width:"100%" });
		$('.chosen-drop').css({"width": "100%", "white-space": "nowrap"})
		
		
        var edit = function() {
            $('.click2edit').summernote({focus: true});
        };
        var save = function() {
            var aHTML = $('.click2edit').code(); //save HTML If you need(aHTML: array).
            $('.click2edit').destroy();
        };


		 
    </script>
</body>

</html>
