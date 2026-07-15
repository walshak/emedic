<?php
require_once('../Connections/Conn.php');

session_start();



try {

	if (isset($_POST['saveMgt2'])) {
		session_start();
		/// include("../Connections/Conn.php");
		include('objects.php');
		include('helpers.php');
		$appointment_number = cleanInput($_POST['app_no']);
		$hospital_no = cleanInput($_POST['hospital_no']);
		$visit_status = cleanInput($_POST['visit_status']);
		// $date_of_birth = $_POST['date_of_birth'];
		// print_r($_POST);


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
		$error_msg = 'Error : Diagnosis Not Saved!';
		$complains = '<div width="50%">' . $diagnosis . '</div>';

		$error_status = 1;
		$error_msg = 'Error : Consultation Notes not saved...';
		$save_note = saveToNotes($db, $appointment_number, $hospital_no, $complains, 'DR', 'D', $_SESSION['fullname'], null, $_SESSION["id"], true);


		if ($save_note) {
			$error_status = 2;
			$error_msg = 'Success : Diagnosis saved';

			// First attempt to set doctor_id and lock queues if doctor_id is not yet assigned
			$stmt = $db->prepare("
				UPDATE apptm 
				SET doctor_id = ?, queue_lock = '1', re_queue_lock = '1', vital_lock = '1'
				WHERE hospital_no = ? AND appt_no = ? 
				AND (doctor_id IS NULL OR doctor_id = 0 OR doctor_id = '')
				ORDER BY sn DESC 
				LIMIT 1");
			$stmt->execute([$doctor_id, $hospital_no, $appointment_number]);

			// If no row was affected, lock queues without setting doctor_id
			if ($stmt->rowCount() === 0) {
				$stmt = $db->prepare("
					UPDATE apptm 
					SET queue_lock = '1', re_queue_lock = '1', vital_lock = '1'
					WHERE hospital_no = ? AND appt_no = ? 
					ORDER BY sn DESC 
					LIMIT 1
				");
				$stmt->execute([$hospital_no, $appointment_number]);
			}

			$credit_status = $_POST['credit_status'];

			if ($visit_status == 'new') {
				$stmt = $db->prepare("UPDATE enrollee SET visit_status = 'old' WHERE hospital_no = ?");
				$stmt->execute([$hospital_no]);

				$stmt = $db->prepare("UPDATE patient_ap_services SET drug_status = '1', dsp_by = ?, cr = ? WHERE hospital_no = ? AND paystatus = 0 AND item_services = 'New File'");
				$stmt->execute([$fullname, $credit_status, $hospital_no]);
			}

			$fullname = $_SESSION['fullname'];
			$stmt = $db->prepare("UPDATE patient_ap_services SET drug_status = '1', dsp_by = ?, cr = ? WHERE hospital_no = ? AND app_no = ? AND serv_group = 'Consultation'");
			$stmt->execute([$fullname, $credit_status, $hospital_no, $appointment_number]);
		}

		$stmt = $db->prepare("SELECT 1 FROM notes WHERE notes_type = 'D' AND app_no = ? LIMIT 1");
		$stmt->execute([$appointment_number]);
		$d_status = ($stmt->fetchColumn()) ? 'seen' : 'notseen';

		echo json_encode(["status" => 200, 'message' => $error_msg, 'd_status' => $d_status]);
		exit;
	}


	if (isset($_POST['saveMgt']) or isset($_POST['saveMgt_physio'])) {
		session_start();
		include("../Connections/Conn.php");
		include('objects.php');
		include('helpers.php');
		header('Content-Type: application/json');

		if (isset($_POST['saveMgt'])) {
			$icdcode_group = cleanInput($_POST['icdcode_group']);
			$date_of_birth = $_POST['date_of_birth'];
			$con_appointment_number = $_POST['con_appointment_number'];
			$diagnosisList = explode('<br/>', $_POST['diagnosisList']);
			$tag = 'DR';
			$msg_type = 'C';
		} else {
			$tag = 'PY';
			$msg_type = 'Physio';
		}

		$appointment_number = cleanInput($_POST['app_no']);
		$hospital_no = cleanInput($_POST['hospital_no']);
		$visit_status = cleanInput($_POST['visit_status']);
		$doctor_id = cleanInput($_POST['doctor_id']);
		$doctor_name = cleanInput($_POST['doctor_name']);



		if (isset($_POST['saveMgt'])) {

			// diagnosis part ====/////////////////////////////////////////

			$check_if_empty = 0;

			$diagnosis = '<i><strong>' . 'Diagnosis: ' . '</strong></i><br>';
			///// SELECTED DIAGNOSIS
			if ($_POST['diagnosisInput'] != '') {
				$diagnosis = $diagnosis . $_POST['diagnosisInput'];
				$check_if_empty = 1;
			}

			$diagnosis2 = '';

			if ($_POST['diagnosis'] != '') {
				$diagnosis2 .=  $_POST['diagnosis'];
				$check_if_empty = 1;
			}
			if ($_POST['comment3'] != '') {
				$diagnosis2 .= ' [ ' . $_POST['comment3'] . ' ]';
				$check_if_empty = 1;
			}
			if ($_POST['comment'] != '') {
				if ($_POST['comment'] != 'N/A') {
					$diagnosis2 .=  ' [ ' . $_POST['comment'] . ' ]';
					$check_if_empty = 1;
				}
			}
			if ($_POST['comment2'] != '') {
				if ($_POST['comment2'] != 'N/A') {
					$diagnosis2 .= ' [ ' . $_POST['comment2'] . ' ]';
					$check_if_empty = 1;
				}
			}
			$diagnosis = $diagnosis . $diagnosis2;

			if ($check_if_empty == 1) {

				$complains = '<div width="50%">' . $diagnosis . '</div>';

				$error_status = 1;
				$error_msg = 'Error : Documentation Notes not saved...';
				$save_note = saveToNotes($db, $appointment_number, $hospital_no, $complains, 'DR', 'D', $doctor_name, null, $doctor_id, true);
			}
		}

		////========================================================

		$notes = $_POST['mgt_notes'];
		$now_setdate = date('Y-m-d H:i:s');

		$error_status = 1;
		$error_msg = 'Error : Documentation Notes not saved...';
		$save_note = saveToNotes($db, $appointment_number, $hospital_no, $notes, $tag, $msg_type, $doctor_name, null, $doctor_id, true);

		if ($save_note) {
			$error_status = 2;
			$error_msg = 'Success : Documentation Notes saved';

			if (count($diagnosisList) > 1 and isset($_POST['saveMgt'])) {
				foreach ($diagnosisList as $key => $diagnosis_) {
					if (!empty($diagnosis_)) {
						$insertdiagnosis = $db->prepare("INSERT INTO diagnosis_tracking 
					(hospital_no, diagnosis, group_id, created_at,date_of_birth, app_no)
					VALUES (?, ?, ?, ?, ?, ?) ");
						$insertdiagnosis->execute([$hospital_no, $diagnosis_, $icdcode_group, $now_setdate, $date_of_birth, $con_appointment_number]);
					}
				}
			}

			// First try to assign doctor if doctor_id is NULL, 0, or empty
			$stmt = $db->prepare("UPDATE apptm SET doctor_id = ?, queue_lock = '1', re_queue_lock = '1', vital_lock = '1' WHERE hospital_no = ? AND appt_no = ? AND (doctor_id IS NULL OR doctor_id = 0 OR doctor_id = '') ORDER BY sn DESC LIMIT 1");
			$stmt->execute([$doctor_id, $hospital_no, $appointment_number]);

			if ($stmt->rowCount() === 0) {
				// Fallback: just lock the queues if doctor is already assigned
				$stmt = $db->prepare("UPDATE apptm SET queue_lock = '1', re_queue_lock = '1', vital_lock = '1' WHERE hospital_no = ? AND appt_no = ? ORDER BY sn DESC LIMIT 1");
				$stmt->execute([$hospital_no, $appointment_number]);
			}

			$credit_status = $_POST['credit_status'];
			if ($visit_status == 'new') {
				$stmt = $db->prepare("UPDATE enrollee SET visit_status = 'old' WHERE hospital_no = ?");
				$stmt->execute([$hospital_no]);

				$stmt = $db->prepare("UPDATE patient_ap_services SET drug_status = '1', dsp_by = ?, cr = ? WHERE hospital_no = ? AND paystatus = 0 AND item_services = 'New File'");
				$stmt->execute([$fullname, $credit_status, $hospital_no]);
			}

			$fullname = $doctor_name;
			$stmt = $db->prepare("UPDATE patient_ap_services SET drug_status = '1', dsp_by = ?, cr = ? WHERE hospital_no = ? AND app_no = ? AND serv_group = 'Consultation'");
			$stmt->execute([$fullname, $credit_status, $hospital_no, $appointment_number]);
		}


		$stmt = $db->prepare("SELECT 1 FROM notes WHERE notes_type = 'D' AND app_no = ? LIMIT 1");
		$stmt->execute([$appointment_number]);
		$d_status = ($stmt->fetchColumn()) ? 'seen' : 'notseen';

		echo json_encode(["status" => 200, 'message' => $error_msg, 'd_status' => $d_status]);
		exit;
	}
} catch (\Exception $e) {
	echo $e->getMessage();
}


$stmt = $db->prepare("SELECT 1 FROM notes WHERE notes_type = 'D' AND app_no = ? LIMIT 1");
$stmt->execute([$appointment_number]);
$d_status = ($stmt->fetchColumn()) ? 'seen' : 'notseen';



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
					<label>
						<strong style="font-size:14px; color: black;">
							<i>SELECT YOUR PREFERRED <u style="color:blue;">DOCUMENTATION</u> TEMPLATE BELOW:</i>
						</strong>
					</label>
					<select class="form-control" name="doc_template" id="doc_template" onchange="load_template()" style="font-size:17px;">
						<option value="">-- Select --</option>
						<option value="blank">Blank Document</option>
						<?php
						$dept_id = $_SESSION['dept_id'];
						$stmt2 = $db->query("SELECT * FROM services_templates WHERE category = 'Consultation' AND department_id = '$dept_id'");
						if ($stmt2->rowCount() > 0) {
							while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
								$id = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
								$name = htmlspecialchars($row['template_name'], ENT_QUOTES, 'UTF-8');
								echo "<option value=\"$id\">$name</option>";
							}
						}
						?>
					</select>
				</div>
			</div>



			<div class="col-sm-4 text-center">
				<?php
				$vb_date = date('Y-m-d');
				$temp = $vpp = null;

				// Fetch latest BP
				$bp_stmt = $db->prepare("SELECT bp FROM vital_sign WHERE hospital_no = ? AND DATE(date_ap) = ? ORDER BY sn DESC LIMIT 1");
				$bp_stmt->execute([$hospital_no, $vb_date]);
				if ($bp_row = $bp_stmt->fetch(PDO::FETCH_ASSOC)) {
					$vpp = $bp_row['bp'];
				}

				// Fetch latest Temp
				$temp_stmt = $db->prepare("SELECT temp FROM vital_sign WHERE hospital_no = ? AND DATE(date_ap) = ? ORDER BY sn DESC LIMIT 1");
				$temp_stmt->execute([$hospital_no, $vb_date]);
				if ($temp_row = $temp_stmt->fetch(PDO::FETCH_ASSOC)) {
					$temp = $temp_row['temp'];
				}

				// Display section header if any vitals found
				if ($vpp || $temp) {
					echo "<strong style='color:brown; font-size: 17px;'>[ See Last VITAL Signs Taken Below ]</strong><br>";
				}
				?>

				<strong style="color: black; font-size: 17px;">
					<?php
					if ($temp !== null) {
						echo 'Temp: ' . htmlspecialchars($temp) . '<sup>o</sup>C';
					}

					if ($vpp !== null) {
						echo ($temp !== null ? ' &nbsp; | &nbsp; ' : '') . 'Blood Pressure: ' . htmlspecialchars($vpp) . ' <em>mmHg</em>';
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
			<div class="col-lg-12">

				<div class="ibox float-e-margins" id="template_type">


				</div>


				<div id="icd_10_type">

					<div class="ibox float-e-margins" id="template_type">
						<div class="ibox-title">
							<h3 class="req" style="color: black;">1. Enter Patient's Documentation:</h3>
						</div>



						<div class="ibox-content">

							<?php if ($_SESSION['fullname'] != '' and $_SESSION['id'] != '') { ?>

								<div class="row">
									<div class="col-sm-10">
										<div id="autosave-status" style="font-size: 14px; color: gray; "> <i class="fa fa-save"></i> Autosave Enabled</div>
										<div id="autosaving-status" style="font-size: 14px; color: orange; display: none;">Autosaving...</div>
										<div name="mgt_notes" id="mgt_notes" data-hospital_no="<?php echo $_GET['hosp_no'] ?>" data-app_no="<?php echo $_GET['app'] ?>" data-doctor="<?php echo $_SESSION['fullname'] ?>" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px; height: 450px;"></div>


										<div class="pull-right"><small>Template Name: [ <?= $template_name; ?> ]</small></div>
										<div class="form_sep">


										</div>

									</div>

									<div class="col-sm-2">
										<p style="color:darkgreen; font-size:16px;">View patient appointments & medical summary</p>
										<input type="button" name="sumary_visits" value="Patient Summary" data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-primary summary_visits" />

										<hr>
										<label for="reg_textarea_message" style="color: black;">TAKE THE FOLLOWING: </label>
										<div class="form_sep">


											<?php $plan_edit_mode = 1; ?>

											<h3>Add Medication Plan/<br>Pre-Admission:</h3>
											<input type="button" name="edit_users" value="Add Note" data-target="#modal" id="<?php echo $hospital_no . '___' . $plan_edit_mode . '___' . $appointment_number . '___' . $adm_status; ?>" class="btn btn-success add_view_plan" />
											<hr>

											<button type="button" class="btn  btn-success block full-width m-b" id="____review_of_system_btn____"><strong>Review of System</strong></button>
											<br>
											<button type="button" class="btn  btn-info block full-width m-b" id="____physical_examination_btn____"><strong>Physical Examination</strong></button>
											<br>
											<a href='#' class="btn  btn-primary block full-width m-b" id="____add_edit_social_hx_btn____"> <b>Social History</b></a>
											<br>
											<a href='#' class="btn  btn-primary block full-width m-b" id="____add_past_med_hx_btn____"> <b>Past Medical History</b></a>

										</div>
									</div>
								</div>


								<?php if ($_SESSION['rights'] != 'PY') { ?>
									<hr>

									<h3 class="" style="color: black;">2. Enter Patient's Diagnosis Below:</h3>
									<label for="" style="color: sienna; ">[ Enter the Diagnosis Name or ICD-10/ICPC-2 code & Click the "+" to Add More Diagnosis ]</label>
									<div class="row">
										<div class="col-sm-6">
											<div class="p-4" style="padding: 10px;">
												<select name="diagnosis_group" id="icdcode_group" class="form-control" style="font-size:16px;">
													<option value=""> -- Select Diagnosis Group -- </option>
													<?php if (strtoupper($_SESSION['h_code']) == 'POLICE') { ?>

														<option value="2" selected>IDSR</option>
														<option value="3">GENDER-BASED VIOLENCE</option>
														<option value="4">NON-COMMUNICABLE/OTHER DISEASES</option>
														<option value="1">ICDCODES</option>


													<?php } else { ?>

														<option value="1" selected>ICDCODES</option>
														<option value="2">IDSR</option>
													<?php } ?>

												</select>


												<table width="100%">
													<tr>
														<td width="95%">
															<textarea class="typeahead form-control  " data-provide="typeahead" id="search_icdcodes_input" name="search_icdcodes_input" cols="30" rows="1" style="font-size:17px" placeholder="Enter Diagnosis Here"></textarea>
														</td>
														<td width="5%">
															<a class="btn btn-success" onclick="addDiagnosis()" id="addSelectedDrugBtn" title="Add More Diagnosis" style="font-size: 16px;">
																<i class="fa fa-plus"></i> </a>
														</td>
													</tr>
												</table>
												<select name="comment" id="comment" class="form-control" style="font-size:16px;">
													<option selected="selected" value=""> -- Select Comment -- </option>
													<option value="Query">Query</option>
													<option value="Differential">Differential</option>
													<option value="Confirmed">Confirmed</option>
													<option value="N/A">Not Applicable</option>
												</select>

												<select name="comment2" id="comment2" class="form-control" style="font-size:16px;">
													<option selected="selected" value=""> -- Select Comment II -- </option>
													<option value="Acute">Acute</option>
													<option value="Chronic">Chronic</option>
													<option value="Recurrent">Recurrent</option>
													<option value="N/A">Not Applicable</option>

												</select>
												<input type="hidden" id="date_of_birth" value="<?= $dob; ?>">
												<input type="hidden" name="con_appointment_number" id="con_appointment_number" value="<?= $appointment_number; ?>">
												<input type="hidden" name="diagnosisInputList_input" id="diagnosisInputList_input" class="form-control">
												<input type="hidden" name="diagnosisInput" id="diagnosisInput" cols="30" rows="10">
												<div id="diagnosisSelected"></div>
											</div>

										</div>

										<div class="col-sm-6">
											<div class="form_sep">
												<label for="reg_textarea_message" class="" style="color: black;">Diagnosis (Other Comments):</label>
												<textarea name="comment3" id="comment3" cols="30" rows="3" class="form-control" style="font-size:15px" placeholder="Other Diagnosis Here"></textarea>
											</div>
										</div>
									</div>

									<br>

									<button class="btn btn-success" id="save-mgt-button" style="color: white; font-size: 17px; width: 220px;">Save Documentation</button>

								<?php } else { /// physiotheraphy
								?>
									<button class="btn btn-success" id="save-mgt-button_physio" style="color: white; font-size: 17px; width: 220px;">Save Notes</button>
								<?php } ?>



								<input type="hidden" id="doctor_name" value="<?= $_SESSION['fullname']; ?>">
								<input type="hidden" id="doctor_id" value="<?= $_SESSION['id']; ?>">
								<input type="hidden" id="D_status" value="<?= $d_status; ?>">
								<input type="hidden" id="credit_status" value="<?= $credit_status; ?>">
						</div>

					<?php } else {
								header("location:index.php");
							} ?>


					</div>
				</div>

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

<script>
	// Legacy autosave removed - now handled by autosave_widget.php
</script>

<script>
	var __diagnosis__ = [];
	var diagnosisInputList = [];
	var diagnosisInputList_input = '';




	function addDiagnosis() {
		var search_icdcodes_input = $('#search_icdcodes_input').val();
		var comment = $('#comment').val();
		var comment2 = $('#comment2').val();
		var comment3 = $('#comment3').val();
		if (search_icdcodes_input != '') {
			$('#diagnosisInputList_input').val($('#diagnosisInputList_input').val() + `${search_icdcodes_input}<br/>`);
			diagnosisInputList.push(search_icdcodes_input)
			__diagnosis__.push(` ${search_icdcodes_input} ${(comment != ''? '[ '+comment+' ]': '')} ${(comment2 != ''? '[ '+comment2+' ]': '')} ${(comment3 != ''? '[ '+comment3+' ]': '')}  <br>`);
			updateDiagnosisInput()
			$('#search_icdcodes_input').val('');
			$('#diagnosis_group').val('');
			$('#comment').val('');
			$('#comment2').val('');
			$('#comment3').val('');
		} else {
			if (search_icdcodes_input == '') {
				toastr.error('Enter Diagnosis Data', 'Error', {
					timeOut: 1000
				});
				exit;
			}
		}

	}


	function updateDiagnosisInput() {
		var tr = '';
		$('#diagnosisInput').val('')
		$('#diagnosisSelected').html('')
		__diagnosis__.forEach((element, index) => {
			$('#diagnosisInput').val($('#diagnosisInput').val() + element)
			tr += `
					<tr>
						<td>${element}</td>
						<td><a class="btn btn-xs btn-danger" onclick="removeDiagnosis(${index})"><i class="fa fa-minus"></i></a></td>
					</tr>
				`;
		});

		if (__diagnosis__.length > 0) {
			$('#diagnosisSelected').html(`<h4>Diagnosis Added: </h4><table class="table" border="1"><tr><th>Diagnosis</th><th></th></tr>${tr}</table>`)
		}


	}

	function removeDiagnosis(index) {
		__diagnosis__.splice(index, 1);
		diagnosisInputList.splice(index, 1);
		updateDiagnosisInput()

		$('#diagnosisInputList_input').val('');
		diagnosisInputList.forEach((element, index) => {
			$('#diagnosisInputList_input').val($('#diagnosisInputList_input').val() + `${element}<br/>`);
		})
	}


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