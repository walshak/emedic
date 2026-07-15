<?php require_once('../Connections/Conn.php'); ?>

<?php

if (isset($_POST['saveMgt2'])) {
	session_start();
	/// include("../Connections/Conn.php");
	include('objects.php');
	include('helpers.php');
	$appointment_number = cleanInput($_POST['app_no']);
	$hospital_no = cleanInput($_POST['hospital_no']);
	$visit_status = cleanInput($_POST['visit_status']);
	$msg_type = $_POST['msg_type'];
	$tag = $_POST['tag'];

	// if($_POST['PC']!=''){$complains = '<i><strong>' . 'Presenting Complaints: ' . '</strong></i><br>'. $_POST['PC'];}
	//// diagnosis

	$diagnosis = '<i><strong>' . 'Diagnosis: ' . '</strong></i><br>';
	///// SELECTED DIAGNOSIS
	if ($_POST['diagnosisInput'] != '') {
		$diagnosis = $diagnosis . $_POST['diagnosisInput'];
	}

	$diagnosis2 = '';

	if ($_POST['diagnosis'] != '') {
		$diagnosis2 .=  $_POST['diagnosis'];
	}
	if ($_POST['comment3'] != '') {
		$diagnosis2 .= ' [ ' . $_POST['comment3'] . ' ]';
	}
	if ($_POST['comment'] != '') {
		if ($_POST['comment'] != 'N/A') {
			$diagnosis2 .=  ' [ ' . $_POST['comment'] . ' ]';
		}
	}
	if ($_POST['comment2'] != '') {
		if ($_POST['comment2'] != 'N/A') {
			$diagnosis2 .= ' [ ' . $_POST['comment2'] . ' ]';
		}
	}
	$diagnosis = $diagnosis . $diagnosis2;


	$error_status = 1;
	// $error_msg = 'Error : Consultation Notes Not Saved!';
	$error_msg = 'Error : Diagnosis Not Saved!';

	//$complains= '<table width="100%" class="table">
	//<tr><td width="50%">'. $complains . '</td><td width="50%">'. $diagnosis . '</td></tr></table>';

	$complains = '<div width="50%">' . $diagnosis . '</div>';

	$error_status = 1;
	$error_msg = 'Error : Consultation Notes not saved...';
	$save_note = saveToNotes($db, $appointment_number, $hospital_no, $complains, 'DR', 'D',  $_SESSION['fullname'], null, $_SESSION["id"], true);


	if ($save_note) {
		$error_status = 2;
		$error_msg = 'Success : Diagnosis saved';

		$stmt = $db->prepare("UPDATE  apptm SET doctor_id = ?, queue_lock = '1' WHERE hospital_no=?  AND appt_no = ?  AND doctor_id IS NULL  order by sn DESC LIMIT 1");
		$stmt->execute(array($_SESSION["id"], $hospital_no, $appointment_number));
		if ($visit_status == 'new') {
			$stmt = $db->prepare("UPDATE  enrollee SET visit_status = 'old' WHERE hospital_no=?  ");
			$stmt->execute(array($hospital_no));
		}

		$fullname = $_SESSION['fullname'];
		$fullname = str_replace("'", "", $fullname);
		$stmt = $db->prepare("UPDATE  patient_ap_services SET drug_status = '1', dsp_by='$fullname' WHERE hospital_no=?  and app_no=? and serv_group='Consultation' ");
		$stmt->execute(array($hospital_no, $appointment_number));
	}

	echo json_encode(["status" => 200, 'message' => $error_msg]);
	exit;
}






if (isset($_POST['saveMgt'])) {
	session_start();
	include("../Connections/Conn.php");
	include('objects.php');
	include('helpers.php');
	header('Content-Type: application/json');


	$appointment_number = cleanInput($_POST['app_no']);
	$hospital_no = cleanInput($_POST['hospital_no']);
	$visit_status = cleanInput($_POST['visit_status']);
	$doctor_id = cleanInput($_POST['doctor_id']);
	$doctor_name = cleanInput($_POST['doctor_name']);
	$msg_type = $_POST['msg_type'];
	$tag = $_POST['tag'];

	////========================================================

	$notes = $_POST['mgt_notes'];


	$error_status = 1;
	$error_msg = 'Error : Documentation Notes not saved...';
	$save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, $tag, $msg_type,  $doctor_name, null, $doctor_id, true);

	if ($save_note) {
		$error_status = 2;
		$error_msg = 'Success : Documentation Notes saved';

		/* 	$stmt = $db->prepare("UPDATE  apptm SET doctor_id = ?, queue_lock = '1' WHERE hospital_no=?  AND appt_no = ?  AND doctor_id IS NULL  order by sn DESC LIMIT 1");
		$stmt->execute(array($doctor_id, $hospital_no, $appointment_number));
		if ($visit_status == 'new') {
			$stmt = $db->prepare("UPDATE  enrollee SET visit_status = 'old' WHERE hospital_no=?  ");
			$stmt->execute(array($hospital_no));
		}

		$fullname = $doctor_name;
		$fullname = str_replace("'", "", $fullname);
		$stmt = $db->prepare("UPDATE  patient_ap_services SET drug_status = '1', dsp_by='$fullname' WHERE hospital_no=?  and app_no=? and serv_group='Consultation' ");
		$stmt->execute(array($hospital_no, $appointment_number)); */
	}

	echo json_encode(["status" => 200, 'message' => $error_msg]);
	exit;
}
?>



<div class="gray-bg">

	<div class="wrapper wrapper-content">


		<div class="row">

			<div class="col-sm-2 b-r">
				<div class="form-group">
					<label><strong style="font-size:14px; color: black;">Patient Allergies</strong></label><br>
					<table>
						<tr>

							<td>
								<a href='#' id="new-allergy-btn" class="btn btn-sm btn-danger" style="font-size: 15px;">Add Patient Allergies</a>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="col-sm-6">
				<div class="form-group">
					<label><strong style="font-size:14px; color: black;"><i>SELECT YOUR PREFER <u style="color:blue;">DOCUMENTATION</u> TEMPLATE BELOW:</i></strong> </label>
					<select class="form-control" name="doc_template" id="doc_template" onChange="load_template()" style="font-size:17px;">
						<option value="">-- Select --</option>
						<option value="">Blank Document</option>
						<?php
						$dept_id = $_SESSION['dept_id'];
						$stmt2 = $db->query("SELECT * FROM services_templates where department_id='$dept_id'");
						if ($stmt2->rowCount() > 0) {
							while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
						?>
								<option value="<?php echo $row['id']; ?>"><?php echo $row['template_name']; ?></option>
						<?php }
						} ?>
					</select>
				</div>
			</div>


			<div class="col-sm-4" align="center">

				<?php

				$vb_date = date('Y-m-d');
				$bp_vitals_stmt = $db->prepare("SELECT bp FROM vital_sign 
	WHERE hospital_no = ? and date(date_ap)='$vb_date' ORDER BY sn desc LIMIT 1");
				$bp_vitals_stmt->execute(array($hospital_no));

				if ($bp_vitals_stmt->rowCount() > 0) {
					$row = $bp_vitals_stmt->fetch(PDO::FETCH_ASSOC);
					$vpp = $row["bp"];
				}

				$bp_vitals_stmt2 = $db->prepare("SELECT temp FROM vital_sign 
WHERE hospital_no = ? and date(date_ap)='$vb_date' ORDER BY sn desc LIMIT 1");
				$bp_vitals_stmt2->execute(array($hospital_no));

				if ($bp_vitals_stmt2->rowCount() > 0) {
					$row = $bp_vitals_stmt2->fetch(PDO::FETCH_ASSOC);
					$temp = $row["temp"];
				}

				if ($bp_vitals_stmt->rowCount() > 0 or $bp_vitals_stmt2->rowCount() > 0) {
					echo "<strong style='color:brown; font-size: 17px;'>[ See Last VITAL Signs Taken Below ] </strong><br>";
				}
				?>
				<strong style="color: black; font-size: 17px;">
					<?php
					if ($bp_vitals_stmt2->rowCount() > 0) {
						echo 'Temp: ' . $temp . '<sup>o</sup>C';
					}
					if ($bp_vitals_stmt->rowCount() > 0) {
						echo '  |   Blood Pressure: ' . $vpp . '<em>mmHg</em>';
					}

					?>
				</strong>
			</div>





			<!--			<div class="col-sm-3">	
			<div class="form-group">
					<label><strong style="font-size:14px;">Design your Documentation Template</strong></label><br>
		<button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#design_template_mdll">
                Click Here
                </button>				
			</div>   
			</div> -->


		</div>


		<div class="row">
			<div class="col-lg-10">

				<div class="ibox float-e-margins" id="template_type">


				</div>


				<div id="icd_10_type">

					<div class="ibox float-e-margins" id="template_type">
						<div class="ibox-title">
							<h3 class="req" style="color: black;">Enter Patient's Documentation:</h3>
						</div>



						<div class="ibox-content">

							<?php if ($_SESSION['fullname'] != '' and $_SESSION['id'] != '') { ?>

								<div class="row">
									<div class="col-sm-12">

										<div id="autosave-status" style="font-size: 14px; color: gray; "> <i class="fa fa-save"></i> Autosave Enabled</div>
										<div id="autosaving-status" style="font-size: 14px; color: orange; display: none;">Autosaving...</div>
										<div name="mgt_notes" id="mgt_notes" data-hospital_no="<?php echo $_GET['hosp_no'] ?? ''; ?>" data-app_no="<?php echo $_GET['app'] ?? ''; ?>" data-doctor="<?php echo $_SESSION['fullname']; ?>" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px; height: 500px;"></div>


										<div class="pull-right"><small>Template Name: [ <?= $template_name; ?> ]</small></div>
										<div class="form_sep">


										</div>

									</div>
								</div>



								<button class="btn btn-success" id="save-mgt-button" style="color: white; font-size: 17px; width: 220px;">Save Notes</button>


								<?php if (strtoupper($services_name) == 'EMERGENCY') { ?>
									<input type="hidden" id="tag" value="EMR">
									<input type="hidden" id="msg_type" value="note">
								<?php } else { ?>
									<input type="hidden" id="tag" value="MF">
									<input type="hidden" id="msg_type" value="Antenatal">
								<?php } ?>


								<input type="hidden" id="doctor_name" value="<?= $_SESSION['fullname']; ?>">
								<input type="hidden" id="doctor_id" value="<?= $_SESSION['id']; ?>">
						</div>

					<?php } else {
								header("location:index.php");
							} ?>


					</div>
				</div>

			</div>

			<div class="col-lg-2">
				<a href="#" class="btn btn-sm btn-w-m btn-primary " id="see-a-specialist-notes-btn" <?php ($isOnAppoint || $admission_info != null ? "" : "disabled") ?> style="color: white; font-size: 14px; ">See Doctor</a>

			</div>
		</div>


	</div>

</div>

<input type="hidden" name="visit_status" id="visit_status" value="<?php echo $visit_status; ?>">

<div class="modal inmodal" id="design_template_mdll" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content animated bounceInRight">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

				<h4 class="modal-title">Create Documentation Template</h4>
			</div>

			<div class="modal-body" id="start_application_preview_body">
				<form method="post" id="upload_data" action="index.php">

					<div class="form-group">
						<label>Template Title </label>
						<input type="text" name="template_title" placeholder="Enter Template Ittle" class="form-control" required>
					</div>


					<label>Paste Template Below and Save</label>
					<div class="mail-text h-200">
						<textarea name="create_template" id="create_template" class="trumbowygEditor"></textarea>
						<div class="clearfix"></div>
					</div>

					<div class="form-group">
						<button class="btn btn-success" type="submit" name="save_custom_template" id="save_custom_template">Save</button>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>

<?php include("../inc/footer_scripts.php"); ?>

<script>
	<?php if (isset($_GET['action'])) { ?>
		document.getElementById('icd_10_type').style.display = 'none';
		document.getElementById('template_type').style.display = 'block';
	<?php } else { ?>
		document.getElementById('icd_10_type').style.display = 'block';
		document.getElementById('template_type').style.display = 'none';

	<?php } ?>



	function myFunction() {
		document.getElementById('icd_10_type').style.display = 'block';
		document.getElementById('template_type').style.display = 'none';
	}
</script>

<?php include_once('../inc/autosave_widget.php'); ?>