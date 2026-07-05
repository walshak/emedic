<?php include("../Connections/Conn.php");?>

<?php
session_start();

if (isset($_POST["submit_edit"])) {
    $update = "UPDATE lab_result SET field_value = :edit_note WHERE lab_no = :rq_no";
    $stmt = $db->prepare($update);
    
    $stmt->bindParam(':edit_note', $_POST['edit_note'], PDO::PARAM_STR);
    $stmt->bindParam(':rq_no', $_POST['rq_no'], PDO::PARAM_STR);
    
    $stmt->execute();
    
    header("Location: mgt.php?rp");
    exit;
}



if (isset($_POST["submit_result"])) {
	 
	if(isset($_POST['input_type3'])){
 		$input_type3=$_POST['input_type3'];
					}else{
						$input_type3='';
						}
	
	if($input_type3=='values' and !empty($_POST['fvalue'])){	
						for ($i=0; $i < count($_POST['fvalues']); $i++ ) { 
					
							///echo 'klll;';
				
							$stmt_chk = $db->prepare("SELECT * FROM lab_scan_input_results WHERE lab_request_no=:lab_request_no AND test_no=:test_no AND field_no=:field_no AND value_sn=:value_sn");
							$stmt_chk->bindParam(':lab_request_no', $_POST['rq_no'][$i], PDO::PARAM_STR);
							$stmt_chk->bindParam(':test_no', $_POST['test_noo'][$i], PDO::PARAM_STR);
							$stmt_chk->bindParam(':field_no', $_POST['row_fields_fields'][$i], PDO::PARAM_STR);
							$stmt_chk->bindParam(':value_sn', $_POST['values_sn'][$i], PDO::PARAM_STR);
							$stmt_chk->execute();
							
							if ($stmt_chk->rowCount() == 0) {
		//echo $_POST['row_fields_fields'][$i];
		
				if($_POST['fvalues'][$i]==''){
						$result='NIL';
						$result='NIL';
										}else{
								$result=$_POST['fvalues'][$i];
							}					
			  
							$add_row = $db->prepare("INSERT INTO lab_scan_input_results (lab_request_no, test_no, field_no, value_sn, value_title, value_ref, result, entered_by) 
							VALUES (:lab_request_no, :test_no, :field_no, :value_sn, :value_title, :value_ref, :result, :entered_by)");
	
	$add_row->bindParam(':lab_request_no', $_POST['rq_no'][$i], PDO::PARAM_STR);
	$add_row->bindParam(':test_no', $_POST['test_noo'][$i], PDO::PARAM_STR);
	$add_row->bindParam(':field_no', $_POST['row_fields_fields'][$i], PDO::PARAM_STR);
	$add_row->bindParam(':value_sn', $_POST['values_sn'][$i], PDO::PARAM_STR);
	$add_row->bindParam(':value_title', $_POST['values_title'][$i], PDO::PARAM_STR);
	$add_row->bindParam(':value_ref', $_POST['values_fre'][$i], PDO::PARAM_STR);
	$add_row->bindParam(':result', $result, PDO::PARAM_STR);
	$add_row->bindParam(':entered_by', $_SESSION['fullname'], PDO::PARAM_STR);
	
	$add_row->execute();
	
			
	}else{
		
		
		
					   
	
	
		
							//// result was eneter before////	
							$rwxs=$stmt_chk->fetch(PDO::FETCH_ASSOC);
						if($_POST['fvalues'][$i]==''){
								$new_result='NIL';
									}else{
								$new_result=$_POST['fvalues'][$i];
								}				
				

								if($_POST['values_sn'][$i]==$rwxs['value_sn'] and $new_result==$rwxs['result']){
									}else{
									//// SAVE OLD RESULT % UPDATE MAIN TABLE
									$add_row = $db->prepare("INSERT INTO lab_scan_input_results_old (lab_request_no, test_no, field_no, value_sn, value_title, value_ref, result, entered_by) 
									VALUES (:lab_request_no, :test_no, :field_no, :value_sn, :value_title, :value_ref, :result, :entered_by)");
			
			$add_row->bindParam(':lab_request_no', $rwxs['lab_request_no'], PDO::PARAM_STR);
			$add_row->bindParam(':test_no', $rwxs['test_no'], PDO::PARAM_STR);
			$add_row->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);
			$add_row->bindParam(':value_sn', $rwxs['value_sn'], PDO::PARAM_STR);
			$add_row->bindParam(':value_title', $rwxs['value_title'], PDO::PARAM_STR);
			$add_row->bindParam(':value_ref', $rwxs['value_ref'], PDO::PARAM_STR);
			$add_row->bindParam(':result', $rwxs['result'], PDO::PARAM_STR);
			$add_row->bindParam(':entered_by', $rwxs['entered_by'], PDO::PARAM_STR);
			
			$add_row->execute();
				
			//// main table
	/// update manage lab table
	$setdate = date('Y-m-d H:i:s');
	$update = $db->prepare("UPDATE lab_scan_input_results 
							SET result = :new_result 
							WHERE lab_request_no = :lab_request_no 
							AND test_no = :test_no 
							AND field_no = :field_no 
							AND value_sn = :value_sn");
	
	$update->bindParam(':new_result', $new_result, PDO::PARAM_STR);
	$update->bindParam(':lab_request_no', $_POST['rq_no'][$i], PDO::PARAM_STR);
	$update->bindParam(':test_no', $rwxs['test_no'], PDO::PARAM_STR);
	$update->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);
	$update->bindParam(':value_sn', $rwxs['value_sn'], PDO::PARAM_STR);
	
	$update->execute();
			
			
			}
//////////////////====================================================================================		
		
	}
}
		

	}
	


	
	for ($i=0; $i <= count($_POST['fvalue']); $i++ ) {
		
		 $result_date=$_POST["result_date"][$i];
					if($result_date==''){
						$result_date=date("Y-m-d");
							}else{
						$result_date=$_POST["result_date"][$i];	
							}
		
		
	 $labrequest_no=$_POST['labrequest_no'][$i];
	
	 $stmt_chk = $db->prepare("SELECT * FROM lab_result WHERE field_no = :field_no AND field_name = :field_name AND lab_no = :lab_no");
	 $stmt_chk->bindParam(':field_no', $_POST['fno'][$i], PDO::PARAM_STR);
	 $stmt_chk->bindParam(':field_name', $_POST['fname'][$i], PDO::PARAM_STR);
	 $stmt_chk->bindParam(':lab_no', $_POST['labrequest_no'][$i], PDO::PARAM_STR);
	 $stmt_chk->execute();
	 
	 if($stmt_chk->rowCount() == 0) {
			if($_POST['fvalue'][$i]==''){
								$result='NIL';
									}else{
								$result=$_POST['fvalue'][$i];
								
								}
	  
$add_row = $db->prepare("INSERT INTO lab_result 
                         (field_no, field_name, field_value, field_ref, lab_no, test_no, test_name, specimen_collected, comment, notes, RQ_type, result_date, lab_sci_name, lab_sci_speciality, entered_by) 
                         VALUES 
                         (:field_no, :field_name, :field_value, :field_ref, :lab_no, :test_no, :test_name, :specimen_collected, :comment, :notes, :RQ_type, :result_date, :lab_sci_name, :lab_sci_speciality, :entered_by)");

// Bind parameters
$add_row->bindParam(':field_no', $_POST['fno'][$i], PDO::PARAM_STR);
$add_row->bindParam(':field_name', $_POST['fname'][$i], PDO::PARAM_STR);
$add_row->bindParam(':field_value', $result, PDO::PARAM_STR);  // Assuming $result is defined elsewhere
$add_row->bindParam(':field_ref', $_POST['fre'][$i], PDO::PARAM_STR);
$add_row->bindParam(':lab_no', $_POST['labrequest_no'][$i], PDO::PARAM_STR);
$add_row->bindParam(':test_no', $_POST['test_no'][$i], PDO::PARAM_STR);
$add_row->bindParam(':test_name', $_POST['test_name'][$i], PDO::PARAM_STR);
$add_row->bindParam(':specimen_collected', $_POST['Specimen'][$i], PDO::PARAM_STR);
$add_row->bindParam(':comment', $_POST['comment'][$i], PDO::PARAM_STR);
$add_row->bindParam(':notes', $_POST['notes'][$i], PDO::PARAM_STR);
$add_row->bindParam(':RQ_type', $_POST['RQ_type'][$i], PDO::PARAM_STR);
$add_row->bindParam(':result_date', $result_date, PDO::PARAM_STR);  // Assuming $result_date is defined elsewhere
$add_row->bindParam(':lab_sci_name', $_POST['lab_sci_name'][$i], PDO::PARAM_STR);
$add_row->bindParam(':lab_sci_speciality', $_POST['lab_sci_speciality'][$i], PDO::PARAM_STR);
$add_row->bindParam(':entered_by', $_POST['entered_by'][$i], PDO::PARAM_STR);

// Execute the prepared statement
$add_row->execute();

				}else{
					
//// result was eneter before////	
							$rwxs=$stmt_chk->fetch(PDO::FETCH_ASSOC);
						//	echo $rwxs['result'];
						if($_POST['fvalue'][$i]==''){
								$new_result='NIL';
									}else{
								$new_result=$_POST['fvalue'][$i];
								}
					   
					   
								
								if($_POST['fno'][$i]==$rwxs['field_no'] and $new_result==$rwxs['field_value'] and $_POST['test_name'][$i]==$rwxs['test_name']){
									}else{
										//echo $_POST['fno'][$i];
										
									//// SAVE OLD RESULT % UPDATE MAIN TABLE
// Assuming $db is your PDO object

// Prepare the INSERT statement with placeholders
$add_row = $db->prepare("INSERT INTO lab_result_old 
                         (field_no, field_name, field_value, field_ref, lab_no, test_no, test_name, comment, RQ_type, result_date, entered_by) 
                         VALUES 
                         (:field_no, :field_name, :field_value, :field_ref, :lab_no, :test_no, :test_name, :comment, :RQ_type, :result_date, :entered_by)");

// Bind parameters
$add_row->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);
$add_row->bindParam(':field_name', $rwxs['field_name'], PDO::PARAM_STR);
$add_row->bindParam(':field_value', $rwxs['field_value'], PDO::PARAM_STR);
$add_row->bindParam(':field_ref', $rwxs['field_ref'], PDO::PARAM_STR);
$add_row->bindParam(':lab_no', $_POST['labrequest_no'][$i], PDO::PARAM_STR);
$add_row->bindParam(':test_no', $rwxs['test_no'], PDO::PARAM_STR);
$add_row->bindParam(':test_name', $rwxs['test_name'], PDO::PARAM_STR);
$add_row->bindParam(':comment', $rwxs['comment'], PDO::PARAM_STR);
$add_row->bindParam(':RQ_type', $rwxs['RQ_type'], PDO::PARAM_STR);
$add_row->bindParam(':result_date', $rwxs['result_date'], PDO::PARAM_STR);
$add_row->bindParam(':entered_by', $rwxs['entered_by'], PDO::PARAM_STR);

// Execute the prepared statement
$add_row->execute();

			
			//// main table
	/// update manage lab table
$setdate=date('Y-m-d H:i:s');
// Prepare the UPDATE statement with placeholders
$update = $db->prepare("UPDATE lab_result 
                       SET field_value = :field_value, comment = :comment, result_date = :result_date 
                       WHERE lab_no = :lab_no AND field_no = :field_no");

// Bind parameters
$update->bindParam(':field_value', $_POST['fvalue'][$i], PDO::PARAM_STR);
$update->bindParam(':comment', $_POST['comment'][$i], PDO::PARAM_STR);
$update->bindParam(':result_date', $setdate, PDO::PARAM_STR);
$update->bindParam(':lab_no', $_POST['labrequest_no'][$i], PDO::PARAM_STR);
$update->bindParam(':field_no', $rwxs['field_no'], PDO::PARAM_STR);

// Execute the prepared statement
$update->execute();
		
			}					
		}
	}

   
for ($i=0; $i < count($_POST['labrequest_no2']); $i++) {
		
		$labrequest_no=$_POST['labrequest_no2'][$i];
		$paystatus=$_POST['paystatus'][$i];
		$cr=$_POST['cr'][$i];
	/// update manage lab table

$approve_status=$_POST["approve_result"][$i];
	if($approve_status==1){
		$data_capture_status='approve';
		$approved_by=$_SESSION['fullname'];
	}else{
		$data_capture_status='result';
		$approved_by='';			
	}

	
// Assuming $db is your PDO object, $setdate is defined earlier, and other variables are set

// Prepare the UPDATE statement with placeholders
$update = $db->prepare("UPDATE lab_manage 
                        SET result_note = :result_note, 
                            result_comment = :result_comment, 
                            lab_sci_name = :lab_sci_name, 
                            lab_sci_speciality = :lab_sci_speciality, 
                            entered_by = :entered_by, 
                            result_date = :result_date, 
                            data_capture_status = :data_capture_status, 
                            approved_by = :approved_by 
                        WHERE labrequest_no = :labrequest_no");

// Bind parameters
$update->bindParam(':result_note', $_POST['notes'][$i], PDO::PARAM_STR);
$update->bindParam(':result_comment', $_POST['comment'][$i], PDO::PARAM_STR);
$update->bindParam(':lab_sci_name', $_POST['lab_sci_name'][$i], PDO::PARAM_STR);
$update->bindParam(':lab_sci_speciality', $_POST['lab_sci_speciality'][$i], PDO::PARAM_STR);
$update->bindParam(':entered_by', $_POST['entered_by'][$i], PDO::PARAM_STR);
$update->bindParam(':result_date', $setdate, PDO::PARAM_STR);
$update->bindParam(':data_capture_status', $data_capture_status, PDO::PARAM_STR); // Assuming $data_capture_status is defined
$update->bindParam(':approved_by', $approved_by, PDO::PARAM_STR); // Assuming $approved_by is defined
$update->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR); // Assuming $labrequest_no is defined

// Execute the prepared statement
$update->execute();

	
	if($paystatus==0 and $cr==0){
	//// post on credit ////
// Assuming $db is your PDO object, $labrequest_no is defined earlier, and $_SESSION["fullname"] is set

$one = '1';
$invoice_no = mt_rand(1000000, 9999999);

// Prepare the UPDATE statement with placeholders
$update = $db->prepare("UPDATE patient_ap_services 
                        SET cr = :cr,
                            invoice_no = :invoice_no,
                            invoice_by = :invoice_by,
                            invoice_status = :invoice_status,
                            drug_status = :drug_status 
                        WHERE drug_sn = :drug_sn");

// Bind parameters
$update->bindParam(':cr', $one, PDO::PARAM_INT); // Assuming cr is an integer
$update->bindParam(':invoice_no', $invoice_no, PDO::PARAM_INT); // Assuming invoice_no is an integer
$update->bindParam(':invoice_by', $_SESSION["fullname"], PDO::PARAM_STR); // Assuming fullname is a string
$update->bindParam(':invoice_status', $one, PDO::PARAM_INT); // Assuming invoice_status and drug_status are integers
$update->bindParam(':drug_status', $one, PDO::PARAM_INT);
$update->bindParam(':drug_sn', $labrequest_no, PDO::PARAM_STR); // Assuming drug_sn is a string

// Execute the prepared statement
$update->execute();

			}
	}

///header("location:mgt.php?rp");
		  
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
        <h2>Result Sheet</h2>
        <ol class="breadcrumb">
            <li>
                <a href="index.html">Home</a>
            </li>
            <li class="active">
                <strong>Result Sheet</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">

    </div>
</div>

        <div class="wrapper wrapper-content  animated fadeInRight">
            <div class="row">
                <div class="col-lg-4">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Result Data Sheet Setting</h5>
                        </div>
                        <div class="ibox-content">


<?php 


							
	
	
if(isset($_POST['display_result_sheet'])){
	
$patientno=$_POST["hosp_no"];
$patient_name=$_POST["patient_name"];
						
		$query_part='';
		$part_se=1;
		$RQ_type='sl';
			
?>


			<h4>Patient # / Name: &nbsp; &nbsp; <br><br><?php $error=0; echo $patientno . ' / ' . $patient_name; ?></h4><hr>

<br><br><br><br>
         
<a href="mgt.php" class="btn btn-danger btn-sm"><i class="fa fa-times"></i>&nbsp;&nbsp; Close </a>                            
                        </div>
               </div>


                </div>
                <div class="col-lg-8">
                    <div class="ibox ">
                        <div class="ibox-title">
                            <h5>Result Data Sheet</h5>
                        </div>
                        <div class="ibox-content">
                        
                       

<?php

if(!empty($_REQUEST['inv'])) {
			 $pro_inv = $_REQUEST['inv'];
for($i=0;$i<count($pro_inv);$i++)
		{
	
	/// $_detail=$roww[3].'__'.$roww[1].'__'.$roww[6].'__'.$roww[10].'__'.$roww[12].'__'.$roww[9].'__'.$roww[15].'__'.$roww[13].'__'.$roww[16];
	
			 $main_data = $pro_inv[$i];
			$row_test = explode("__",$main_data);
			
	$labrequest_no=$row_test[2];
			$lab_combo=$row_test[5];
			$data_capture_status=$row_test[7];
			$collected_specimen_2=$row_test[8];
			$cr=$row_test[9];
			$test_id=$row_test[0];
	$Specimen=$row_test[3];
	$request_note=$row_test[6];
	// $collected_specimen_2=$row_test[9];
	
			if($Specimen=='Not Specified'){
					$collected_specimen=$collected_specimen_2;
				}
	
	if($lab_combo==1){ 
	
	$cb_no=$test_id;
	$stmt2=$db->query("SELECT c.test_id, l.test FROM lab_combos_items as c inner join lab_scan as l on c.test_id=l.sn WHERE combos_id='$cb_no'");
		while($row_test=$stmt2->fetch(PDO::FETCH_ASSOC)) {
			$test_name=$row_test['test'].'<br>';
			$test_id=$row_test['test_id'];
			$data= $test_id .'__' . $test_name .'__'. $labrequest_no .'__'. '' . '__' . $cb_no;
			$all_data.= $data .',';
		}
	}else{
		
			$test_name=$row_test[1];
			$test_id=$row_test[0];
			$Specimen=$row_test[3];
			$data= $test_id .'__' . $test_name .'__'. $labrequest_no .'__'. $Specimen . '__0';
			$all_data.= $data .',';
	}
}


	$all_data=rtrim($all_data, ", ");
$myArray = explode(',', $all_data);
	
//print_r($myArray);
foreach ($myArray as $value) {
  ///echo "$value <br>";
			$row_test = explode("__",$value);
			
			$test_id=$row_test[0];
			$test_name=$row_test[1];
			$labrequest_no=$row_test[2];
			?>			   
				   <div class="form_sep" style="background-color:#CF9; height:25px; padding:5px">
   <strong style="color:#F00">FILL RESULT:&nbsp; <?php echo $test_name ?></strong>
   </div>
   
                       <div class="form_sep">
					<table width="100%">
						 <tr>
							<td>
							 <strong>Preferred Specimen:</strong> &nbsp; <?php if($Specimen!=''){echo $Specimen;}else{echo 'Not Available';} ?>
							</td>
							 <td>
							 <strong>Request Note: </strong> &nbsp; <?php echo $request_note;///}else{echo 'Not Available';} ?>
							 </td>
						</tr>  
					</table>	   

							
							
							</div>
            <?php   
					   
/// SAVE IF NOT EXIST 

$stmt = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = :test_id");
$stmt->bindParam(':test_id', $test_id, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    $field_type = 'report';
    $add_row = $db->prepare("INSERT INTO lab_scan_fields(test_no, field, field_type) VALUES (:test_id, :test_name, :field_type)");
    $add_row->bindParam(':test_id', $test_id, PDO::PARAM_STR);
    $add_row->bindParam(':test_name', $test_name, PDO::PARAM_STR);
    $add_row->bindParam(':field_type', $field_type, PDO::PARAM_STR);
    $add_row->execute();
}
				   
					   
$stmt = $db->prepare("SELECT * FROM lab_scan_fields WHERE test_no = :test_id ORDER BY sn");
$stmt->bindParam(':test_id', $test_id, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
			
			//// body of one REQUEST -----------------------------------------------------------------------------
			
			$Total_field_count=$stmt->rowCount();
			while($row_fields=$stmt->fetch(PDO::FETCH_ASSOC)){ ?>
            
            
            
 <div class="form_sep">
     <?php

	 	$field_no=$row_fields['sn'];
		$field_type=$row_fields['field_type'];
		$field=$row_fields['field'];
			
//// chec=k if result was entered before /////
$stmt = $db->prepare("SELECT * FROM lab_result WHERE field_no = :field_no AND lab_no = :labrequest_no");
$stmt->bindParam(':field_no', $field_no, PDO::PARAM_STR);
$stmt->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $rwsp = $stmt->fetch(PDO::FETCH_ASSOC);
    $rslt_spm = $rwsp['specimen_collected'];
    $field_value = $rwsp['field_value'];
    $result_comment = $rwsp['comment'];
    $result_note = $rwsp['notes'];
    $result_date = $rwsp['result_date'];
    $edit = 1;
} else {
    $edit = 0;
}

	
		if($rslt_spm!=''){
				$collected_specimen=$rslt_spm;
		}
	
			if($result_date==''){
				$result_date=date("Y-m-d");
						}else{
				$result_date=$rwsp['result_date'];		
			}
				
				if($field!='report'){
			?>
	 
	 <table width="100%">
	 	<tr>
		 <td width="50%">
			<label for="reg_input_no" class="">Specimen</label>
<select name="Specimen[]" data-placeholder="Select" class="form-control" id="speciment_taken" required >
                        <?php if ($collected_specimen!=''){?>
          <option value="<?php echo $collected_specimen; ?>" selected="selected"><?php echo $collected_specimen; ?></option>
                        <?php }else{ ?>
              <option value="">Select...</option>
              			<?php } ?>
                <option value="Aspirate">Aspirate</option>
                <option value="Urine">Urine</option>
                <option value="Blood">Blood</option>
				<option value="C.S.F">C.S.F</option>
                <option value="Ear Swab">Ear Swab</option>
                <option value="Eye Swab">Eye Swab</option>
                <option value="Fluids">Fluids</option>
                <option value="No Specimen Required">No Specimen Required</option>
                <option value="Pap Smear">Pap Smear</option>
                <option value="Semen">Semen</option>
                <option value="Skin Scraping">Skin Scraping</option>
                <option value="Sputum">Sputum</option>
                <option value="Stool">Stool</option>
                <option value="Throat Swab">Throat Swab</option>
                <option value="Tissue">Tissue</option>
                <option value="Urethral Swab">Urethral Swab</option>
                <option value="Bence Jones Protein (Urine)">Bence Jones Protein (Urine)</option>
                <option value="Vaginal Swab">Vaginal Swab</option>
                <option value="Wound Swab">Wound Swab</option>
                                                    </select>
	 			</td>
	 			<td width="50%" align="right">
				
					<br>
	<button class="btn btn-sm btn-info" id="" onclick="accept_speciment('<?= $labrequest_no; ?>', '<?= $_SESSION['fullname']; ?>')">Specimen Taken Notice </button>
			
			</td>
		 
		 </tr>
	 </table>
	 
            </div>
        <?php } ?>            
				
                    <div class="form_sep">   
                          <table width="100%"><tr><td><label for="reg_input_no" class=""><?php echo $row_fields['field']; ?></label></td><td align="right"><div align="right"><label for="reg_input_no" style="text-align:right"><?php if ($row_fields['reference']!=''){echo 'Reference' . '( ' . $row_fields['reference'] .' )';} ?></label></div></td></tr></table>
 <form method="post" action="fillrslt_sheet.php">                          
<?php 
echo $row_fields['field_type'];
				
if($row_fields['field_type']=='options'){
                      ////////////// OPTION 

					  $input_type = 'option';
					  $stmt_opt = $db->prepare("SELECT * FROM lab_scan_rlts_opt WHERE field_id_no = :field_id_no ORDER BY sn");
					  $stmt_opt->bindParam(':field_id_no', $row_fields['sn'], PDO::PARAM_STR);
					  $stmt_opt->execute();
					  
					  if ($stmt_opt->rowCount() > 0) {?>
                                <select name="fvalue[]" class="form-control" data-required="true">
                         <?php if($edit==1 and $field_type=='options'){?>
               <option selected="selected" value="<?php echo $field_value;?>"><?php echo $field_value;?></option> 
                         <?php }else{?>
                      		   <option selected="selected" value="">Select ...</option>  
                         <?php }?>       
                                          
                            <?php while($row_opt=$stmt_opt->fetch(PDO::FETCH_ASSOC)){	?>
                                <option value="<?php echo $row_opt['options']; ?>"><?php echo $row_opt['options']; ?></option>
                            <?php } ?>
                             </select> 
                             
<input type="hidden" name="input_ty[]" value="option" />

                    <?php } ?>
                    
                            
                            
<?php }elseif($row_fields['field_type']=='values'){ 
							  ////////////// VALUES  	
	
		///echo $row_fields['sn'];
	
		$input_type='values';	
		if($edit==1 and $field_type=='values'){

			$disp_ref="value_ref";
			$disp="value_sn";
			$disp_options="value_title";
			$stmt_values = $db->prepare("SELECT * FROM lab_scan_input_results WHERE field_no = :field_no AND lab_request_no = :lab_request_no ORDER BY sn");
			$stmt_values->bindParam(':field_no', $row_fields['sn'], PDO::PARAM_STR);
			$stmt_values->bindParam(':lab_request_no', $labrequest_no, PDO::PARAM_STR);
			$stmt_values->execute();

				 }else{
					 $disp_options="options";
					 $disp_ref="reference";
					 $disp="sn";	


					 $stmt_values = $db->prepare("SELECT * FROM lab_scan_rlts_values WHERE field_id_no = :field_id_no ORDER BY sn");
					 $stmt_values->bindParam(':field_id_no', $row_fields['sn'], PDO::PARAM_STR);
					 $stmt_values->execute();						
				 }
							if($stmt_values->rowCount()>0){  ?>
        
        <table width="100%" cellpadding="5" cellspacing="5">
<?php while($row_vxls=$stmt_values->fetch(PDO::FETCH_ASSOC)){ ?>
			
													
       <tr><td><input type="text" name="fvalues[]" class="form-control" value="<?php if ($row_vxls['result']!=''){echo $row_vxls['result'];} ?>" placeholder="Enter result for <?php echo $row_vxls['options']; ?>"></td><td>&nbsp;<strong>Ref.:</strong>&nbsp;<?php echo $row_vxls["$disp_ref"]; ?>
            <input type="hidden" name="values_fre[]" value="<?php echo $row_vxls["$disp_ref"]; ?>" />
            <input type="hidden" name="values_title[]" value="<?php echo $row_vxls["$disp_options"]; ?>" />
            <input type="hidden" name="values_sn[]" value="<?php echo $row_vxls["$disp"]; ?>" />
            <input type="hidden" name="rq_no[]" value="<?php echo $labrequest_no; ?>" />
            <input type="hidden" name="test_noo[]" value="<?php echo $test_id; ?>" />
            <input type="hidden" name="row_fields_fields[]" value="<?php echo $row_fields['sn']; ?>" /> 
       </td></tr>
       <?php } ?> 
		</table>
<input type="hidden" name="input_ty[]" value="values" /> 
<input type="hidden" name="fvalue[]" value="values" />
<input type="hidden" name="input_type3" value="values" />

                 
<?php } ?>

                             
<?php }elseif($row_fields['field_type']=='report'){ 
									///// ENTER REPORT
									
							$input_type='report';
							
						if($edit==1 and $field_type=='report'){
								$default_tem=$field_value;
							}else{


								$stmtrpt = $db->prepare("SELECT template_2 FROM invsti_template WHERE sn = :test_id");
								$stmtrpt->bindParam(':test_id', $test_id, PDO::PARAM_STR);
								$stmtrpt->execute();
								
								if ($stmtrpt->rowCount() > 0) {
									$row_NOTE = $stmtrpt->fetch(PDO::FETCH_ASSOC);
									$default_tem = $row_NOTE['template_2'];
												
									}else{ ?>
	 
				<div class="form-group">
			<label><strong style="font-size:14px; color: black;"><u>Consult</u> using existing Template(s) Below:</strong> </label>
				<select class="form-control" name="doc_template" id="doc_template" onChange="load_template()" style="font-size:17px;">
				<option value="">-- Select --</option>
				<option value="">Blank Document</option>
			<?php		
			$stmt2= $db->query("SELECT * FROM services_templates where category='Consultation'");
						if($stmt2->rowCount()>0){ 
						while($row=$stmt2->fetch(PDO::FETCH_ASSOC)){
					?>
				<option value="<?php echo $row['id']; ?>"><?php echo $row['template_name']; ?></option>
					<?php } }?>
				</select> 
			</div> 
			<?php } ?>
								 
			<div class="ibox-content no-padding">					
				<div name="mgt_notes" id="mgt_notes" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px; height: 500px;"></div>
			</div>	
							
			<?php }
                   ?>   

                           
<input type="hidden" name="input_ty[]" value="report" /> 

                             
<?php }elseif($row_fields['field_type']=='value'){ 
							//// SINGLE
							$input_type='single';
							?>
                            
<input type="hidden" name="input_ty[]" value="single" /> 
<input type="text"  name="fvalue[]" class="form-control" value="<?php if($edit==1 and $field_type=='value'){echo $field_value;} ?>"
placeholder="Enter result for <?php echo $row_fields['field']; ?>"
/>

                  <?php } ?>

     

<input type="hidden" name="fname[]" value="<?php echo $row_fields['field']; ?>" />
<input type="hidden" name="fre[]" value="<?php echo $row_fields['reference']; ?>" />
<input type="hidden" name="fno[]" value="<?php echo $row_fields['sn']; ?>" />
<input type="hidden" name="labrequest_no[]" value="<?php echo $labrequest_no; ?>" />              
<input type="hidden" name="RQ_type[]" value="<?php echo $RQ_type; ?>" /> 
 <input type="hidden" name="result_date[]" value="<?php echo $result_date; ?>"/>
 <input type="hidden" name="test_no[]" value="<?php echo $test_id; ?>"/>
  <input type="hidden" name="test_name[]" value="<?php echo $test_name; ?>"/>
 	
                     <?php  }
					$total_c=$total_c+$Total_field_count;
					 ?>
            <br>
                          <div class="form_sep">
                            <label for="reg_input_no" class="">COMMENT</label>
  <textarea name="comment[]" cols="15" rows="2" class="form-control" data-minlength="10" placeholder="Enter result for COMMENT"><?php echo $result_comment; ?></textarea>
                            </div>
						 <div class="form_sep">
						 <input name="approve_result[]" type="checkbox"  value="1" <?php if($data_capture_status=='approve'){ ?>checked <?php }?> >&nbsp;<strong style="color: red;">Check to Complete/Approve Result</strong></label>
							</div>  
							
							
							
                            <div class="form_sep">
                             <input type="hidden" value=" " name="notes[]" />
<!--  <textarea name="notes[]" cols="15" rows="2" class="form-control" data-minlength="10" placeholder="Note"></textarea>
-->                            </div> 
                             


<?php
if($input_type=='options' or $input_type=='values'){

	$stmt_rlstX = $db->prepare("SELECT * FROM lab_scan_input_results_old WHERE lab_request_no = :labrequest_no AND test_no = :test_id");
	$stmt_rlstX->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
	$stmt_rlstX->bindParam(':test_id', $test_id, PDO::PARAM_STR);
	$stmt_rlstX->execute();
	
	if ($stmt_rlstX->rowCount() > 0) {?>
      <strong style="font-size:15px; color:#F00;">Previous Results Captured</strong>
      <table class="table table-striped table-bordered table-hover dataTables-example" >                 
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Previous Value</th>
                    <th>Reference/Entered by</th>
                </tr>
            </thead>
    <tbody>      
	<?php	while($row_dx = $stmt_rlstX->fetch(PDO::FETCH_ASSOC)){ ?>
                <tr>
                <td><?php echo $row_dx['value_title']; ?></td>
                <td><?php echo $row_dx['result']; ?></td>
                <td><?php echo $row_dx['value_ref'] . ' /By: ' . $row_dx['entered_by'] .' /Date: ' . date("d-m-Y", strtotime($row_dx['date_time'])); ?></td>
                </tr>
		<?php } ?>       
  </tbody></table> 
  <hr>  
<?php } 
}else{
	
	$stmt_rlst = $db->prepare("SELECT * FROM lab_result_old WHERE lab_no = :labrequest_no");
	$stmt_rlst->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
	$stmt_rlst->execute();
	
	if ($stmt_rlst->rowCount() > 0) { ?>
      <strong style="font-size:15px; color:#F00">Previous Results Captured</strong>
            <table class="table table-striped table-bordered table-hover dataTables-example" >                 
                    <thead>
                    <tr>
                        <th>Field</th>
                        <th>Previous Value</th>
                    </tr>
                    </thead>
            <tbody>               
<?php	while($row_d = $stmt_rlst->fetch(PDO::FETCH_ASSOC)){ ?>
            <tr>
            <td> <?php echo $row_d['field_name'] .'<br>'.$row_d['field_ref']  . ' /By: ' . $row_d['entered_by'] .' /Date: ' . date("d-m-Y", strtotime($row_d['result_date'])); ?> </td>
            <td> <?php echo $row_d['field_value']; ?> </td>
            </tr>
<?php } ?>
</tbody></table>
<hr>

	
<?php }
}

?>

<?php if($_SESSION['speciality']=="Data Operator"){ ?>
			<input type="hidden" name="entered_by[]" value="<?php echo $_SESSION['fullname']; ?>"/>
			<input type="hidden" name="lab_sci_name[]" value=""/>
			<input type="hidden" name="lab_sci_speciality[]" value=""/>
<?php }else{ ?>
			<input type="hidden" name="entered_by[]" value="<?php echo $_SESSION['fullname']; ?>"/>
			<input type="hidden" name="lab_sci_name[]" value="<?php echo $_SESSION['fullname']; ?>"/>
			<input type="hidden" name="lab_sci_speciality[]" value="<?php echo $_SESSION['speciality']; ?>"/>	
           
<?php } ?>
                            
     <input type="hidden" name="labrequest_no2[]" value="<?php echo $labrequest_no; ?>" />         
     <input type="hidden" name="paystatus[]" value="<?php echo $paystatus; ?>" />         
     <input type="hidden" name="cr[]" value="<?php echo $cr; ?>" />         
                        
                                        		 
		<?php			 
//// body of one REQUEST ---------------------END        END--------------------------------------------------------
				 
		}else{
			///$error=1;
			 ?>
						

				
                  <strong style="color:#F00">No Result Template Set. Set Result Template before you can continue ... </strong>
			
		<?php }
					  	
}	
	
					  ?>
                      
<?php if ($error==0){?> 
                      
<input type="hidden" name="input_type" value="<?php echo $input_type; ?>" />      
<button class="btn btn-success btn-sm" type="submit" name="submit_result" ><i class="fa fa-check"></i>&nbsp;&nbsp; Submit Result</button>
<input type="hidden" name="Total_field_count"  id="Total_field_count"  value="<?php echo $total_c; ?>" />
<input type="hidden" name="post_type"  id="post_type"  value="<?php echo $post; ?>" />

<?php } ?>

</form>	
			
	<?php }else{ ?>		
<div class="alert alert-danger">No test display sheet to view now </div>            
					<?php } ?>

                        


	<?php  if(isset($_GET['edit'])){ 
            $rq_no=$_GET['edit'];
			$stmt_rlst = $db->prepare("SELECT * FROM lab_result WHERE lab_no = :rq_no");
			$stmt_rlst->bindParam(':rq_no', $rq_no, PDO::PARAM_STR);
			$stmt_rlst->execute();
			
			if ($stmt_rlst->rowCount() > 0) {
        $row_NOTE=$stmt_rlst->fetch(PDO::FETCH_ASSOC);

?>
        
        <h4>Edit result & Click Update button to save </h4>
        
<form method="post" action="fillrslt_sheet.php">

    <div class="mail-text h-200">
    <textarea name="edit_note" id="edit_note" cols="45" rows="5" class="summernote" placeholder="Type Your Message Here" ><?php echo $row_NOTE['field_value']; ?></textarea>  
    </div> 
     
    <input type="hidden" name="rq_no" value="<?php echo $_GET['edit']; ?>" /> 
    <button class="btn btn-success btn-sm" type="submit" name="submit_edit" ><i class="fa fa-check"></i>&nbsp;&nbsp; Update</button>
</form>

                    <?php } 
	
}

	
}elseif(isset($_POST['complete_result'])){
	
if(!empty($_REQUEST['inv'])) {
	$pro_inv = $_REQUEST['inv'];
for($i=0;$i<count($pro_inv);$i++)
		{
	
//// approve and complete here
			$main_data = $pro_inv[$i];
			$row_test = explode("__",$main_data);
			$labrequest_no=$row_test[2];
			//$lab_combo=$row_test[5];
			$update = "UPDATE lab_manage SET approved_by = :fullname, data_capture_status = :status 
			WHERE labrequest_no = :labrequest_no AND data_capture_status = 'result'";
 $stmt = $db->prepare($update);
 $stmt->bindParam(':fullname', $_SESSION["fullname"], PDO::PARAM_STR);
 $stmt->bindParam(':status', 'approve', PDO::PARAM_STR);
 $stmt->bindParam(':labrequest_no', $labrequest_no, PDO::PARAM_STR);
 $stmt->execute();
 
	
}
	header("location:mgt.php?sv");	
}else{ ?>
	<h2>Invalid Selection</h2>
	<a href="mgt.php" class="btn btn-danger btn-sm">Go Back</a>						
	
<?php }
	
	
}else{
	header("location:mgt.php");
}
					?>
                    
                   
                    
                            
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
	<script src="../js/vendors/editor/dist/trumbowyg.js"></script>

   
   <script>

	function accept_speciment(lb, fullname){
				
				var speciment_taken = document.getElementById('speciment_taken').value;
				if(speciment_taken==''){
							toastr.error('Empty Specimen', 'specimen', {timeOut: 5000});
				}else{
		             $.ajax({  
                     url:"fetch_labtest.php",  
                     method:"POST",  
                     data:{lab_req_number:lb, fullname:fullname, speciment_taken:speciment_taken}, 
                     success:function(data){  
						 	toastr.success('Request Status Changed to Speciment Taken', 'Saved', {timeOut: 5000});                         
                     }  
                });  
				}
		
	}	
           $(document).ready(function(){
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });


            $('.summernote').summernote();

        });
		
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
	   
function load_template() {
	
	var doc_template = document.getElementById('doc_template').value;
	toastr.info('Please wait...', '', {timeOut: 5000})
		$.ajax({
			 url:"../inc/text_editor2.php",
			 method:"POST",
			 data:{load_template:doc_template},
			 success:function(data){
				
				 
     			toastr.clear();
                $("#mgt_notes").html(data);
			}
       	});		
}

            $(document).ready(function(){
                $('.trumbowygEditor').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['undo', 'redo'], // Only supported in Blink browsers
                    ['formatting'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat'],
                    ['fullscreen']
                ]
            });
            })
			   	   
    </script>
     
</body>

</html>
