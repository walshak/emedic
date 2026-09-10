<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include('objects.php');
include('helpers.php');
include("../inc/credit_current_balance.php");
$setdate = date("Y-m-d");
$setdatetime = date("Y-m-d H:i:s");
$doctor_name = $_SESSION['fullname'];
$doctor_no = $_SESSION['id'];
?>

<!DOCTYPE html>
<html>
<?php include("../inc/header.php"); ?>
<style>
	.consultation-timer {
		position: fixed;
		top: 10px;
		right: 500px;
		background: #f8f9fa;
		padding: 10px 15px;
		border-radius: 5px;
		box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
		z-index: 1000;
	}


	.timer-display {
		font-size: 1.2em;
		font-weight: bold;
		color: #007bff;
	}

	.consultation-btn {
		margin: 5px;
		padding: 5px 10px;
		cursor: pointer;
	}

	.warning {
		color: #dc3545;
		font-weight: bold;
	}

	/* Your existing styles */
	.confirmation-dialog {
		position: fixed;
		top: 50%;
		left: 50%;
		transform: translate(-50%, -50%);
		background: white;
		padding: 20px;
		border-radius: 5px;
		box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
		z-index: 1001;
	}

	.confirmation-buttons {
		margin-top: 15px;
		text-align: center;
	}

	.confirmation-buttons button {
		margin: 0 10px;
		padding: 5px 15px;
	}


	/* Force XXL width regardless of theme or Bootstrap defaults */
	#add_view_plan_mdl .modal-dialog {
		width: 96vw !important;
		/* use viewport width, not % */
		max-width: 96vw !important;
		margin: 10px auto !important;
		/* keep a small edge gap + center */
	}

	/* Inspinia's .inmodal can set widths; override it too */
	#add_view_plan_mdl.inmodal .modal-dialog {
		width: 96vw !important;
		max-width: 96vw !important;
	}

	/* If you have a .container inside the modal, it caps width. Undo it. */
	#add_view_plan_mdl .container {
		width: 100% !important;
		max-width: none !important;
		padding-left: 0;
		padding-right: 0;
	}

	/* Mobile: use full width minus small margin */
	@media (max-width: 768px) {
		#add_view_plan_mdl .modal-dialog {
			width: calc(100vw - 20px) !important;
			max-width: none !important;
		}
	}


	.typeahead.dropdown-menu {
		max-height: 300px;
		overflow-y: auto;
		z-index: 1050;
	}
</style>

</style>
<script src="../js/jquery-3.1.1.min.js"></script>

<body>
	<div id="wrapper">
		<?php include("nav_side.php"); ?>
		<div id="page-wrapper" class="gray-bg">
			<?php include("nav_header.php"); ?>
			<?php
			$first_visit = false;
			$hosp_no = cleanInput($_GET['hosp_no']);
			$hospital_no = null;
			$patient_name = null;
			$sex = null;

			// base64_encode($_SESSION['username'] . '___' . $hospital_no);
			if (isset($_GET['cvn'])) {
				$base64_encode = cleanInput(base64_decode($_GET['cvn']));
				$ppt = explode('___', $base64_encode);

				if ($ppt[0] == $_SESSION['username'] && $hosp_no == $ppt[1]) {
					$appt__numberrr = $ppt[2];

					$updateSQL = "UPDATE apptm SET app_by = :app_by WHERE appt_no = :appt_no AND hospital_no = :hospital_no";
					$stmt = $db->prepare($updateSQL);
					$stmt->bindParam(':app_by', $_SESSION['username'], PDO::PARAM_INT);
					$stmt->bindParam(':appt_no', $appt__numberrr, PDO::PARAM_INT);
					$stmt->bindParam(':hospital_no', $ppt[1], PDO::PARAM_STR);
					$stmt->execute();

					header("location:patient.php?hosp_no=$hosp_no&mgt&app=$appt__numberrr");
				}
			}

			// Function to start new consultation


			// Function to end consultation
			function endConsultation($doctor_no, $appointment_number, $hospital_no, $db)
			{

				// Get the ongoing consultation
				$ongoing = checkOngoingConsultation($doctor_no, $db);
				if (!$ongoing) {
					return false;
				}

				// Calculate duration in minutes
				$start = new DateTime($ongoing['start_time']);
				$now = new DateTime();
				$interval = $now->diff($start);
				$duration = $interval->i; // Difference in minutes

				$stmt = $db->prepare("UPDATE  apptm SET queue_lock = '1',re_queue_lock = '1' WHERE hospital_no=?  AND appt_no = ? ");
				$stmt->execute(array($hospital_no, $appointment_number));

				$stmt = $db->prepare("UPDATE consultations SET end_time = NOW(), duration = ?, status = 'Closed' WHERE id = ?");
				$stmt->execute(array($duration, $ongoing['id']));

				//$stmt = $db->prepare("UPDATE  enrollee SET visit_status = 'old' WHERE hospital_no=? AND visit_status='new'");
				//$stmt->execute(array($hospital_no));
			}

			// Function to check for ongoing consultation
			function checkOngoingConsultation($doctor_no, $db)
			{
				$stmt = $db->prepare("SELECT * FROM consultations WHERE doctor_id = ? AND status = 'On-going' ORDER BY start_time DESC LIMIT 1");
				$stmt->execute(array($doctor_no));
				return $stmt->fetch(PDO::FETCH_ASSOC);
			}


			$old_hostpital_no = '';
			$patient_info = $Patient->getByHospitalNo($hosp_no);

			$ap_type = 0;
			if (!empty($patient_info)) {
				$dob = $patient_info->dob;
				$visit_status = $patient_info->visit_status;
				$patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
				$sex = $patient_info->gender;
				$insurance_type = $patient_info->insurance_type;
				$insurancen_no = $patient_info->hmo_no;
				$interest = $patient_info->interest;
				$token = $patient_info->token;
				$add_minus = $patient_info->add_minus;
				$payment_mode = $patient_info->payment_mode;
				$services_access = $patient_info->services_access;
				$manage_hx = $patient_info->patient_manage_hx;
				$patient_manage_by = $patient_info->patient_manage_by;
				$vip = $patient_info->vip;


				if ($vip == 1) {
					$stmt = $db->prepare("SELECT 1 FROM manage_patients_vip_staff WHERE hospital_no = ? AND user_id = ? AND status=1");
					$stmt->execute([$hosp_no, $_SESSION['id']]);
					if ($stmt->rowCount() > 0 || $_SESSION['rights'] === 'MD') {
					} else {
						header("Location: index.php?vip");
						exit;
					}
				}

				$age = 0;
				$age_full = null;
				if (!empty($dob)) {
					$components = preg_split("/-/", $dob);
					$year = $components[0];
					$age = date('Y') - $year;
					$age_full = $age . ' yrs';

					if ($age == 0) {
						$month = abs(date('m') - $components[1]);
						$age_full = $month . ' Months';
					}
				}
				$hospital_no = $hosp_no;
				$old_hostpital_no = $patient_info->old_hospital_no;
				$personal_credit_limit = $patient_info->credit_limit;



				///=========================================================== CONSULTATION STATUS /////////////////



			} else {
				header('location:../index.php');
				exit;
			}



			include_once('../inc/_patient_post_actions.php');

			///////////////////// CHECK ADMISSION STATUS
			$isOnAdmission = false;
			$admission_info = null;
			$admission_app_no = null;
			$discharge_note = null;
			$adm_status = null;
			$adm_id = null;
			$credit_limit_bal = 0;

			$stmt = $db->prepare("SELECT 
					sn, app_no, adm_status, date_admit, discharge_note, 
					room_bed_sn, care_giver, care_giver_phone, 
					room_bed, relationship, reason_adm, admit_type 
				FROM admission 
				WHERE (adm_status = 3 OR adm_status = 0) AND hospital_no = ? 
				ORDER BY sn DESC 
				LIMIT 1");

			$stmt->execute([$hospital_no]);

			if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$isOnAdmission = true;

				// Avoid JSON conversion, assign directly
				$adm_id             = $row['sn'];
				$appointment_number = $row['app_no'];
				$admission_app_no   = $appointment_number;
				$adm_status         = (int)$row['adm_status'];
				$date_admit         = $row['date_admit'];
				$discharge_note     = $row['discharge_note'];
				$room_bed_sn        = $row['room_bed_sn'];
				$admission_sn       = $row['sn'];
				$care_giver         = $row['care_giver'];
				$care_giver_phone   = $row['care_giver_phone'];
				$room_bed           = $row['room_bed'];
				$relationship       = $row['relationship'];
				$reason_adm         = $row['reason_adm'];
				$admit_type         = $row['admit_type'];

				// Check if admission is active
				//if ($adm_status === 3) {
				$emr = $hospital_no;
				$general_credit_limit =	$_SESSION['credit_limit_status'];
				$items = call_current_balance($db, $emr, $general_credit_limit);
				$current_balance =  $items["current_balance"];
				$credit_limit_bal  = $items['bal_credit_limit'];
				///}
			}




			$cur_date_time = date('Y-m-d H:i:s');
			$isOnAppoint = false;
			//$appointment_number = null;
			$patientAppoint = null;
			$patient_access_type = null;
			$appointment_interest = null;
			$patient_insurance = null;
			/////////////////////// CHECK IF PATIENT HAS OPPENED APPOINTMENT, THEN GET THE ///////////////
			/////////// IF THE LOGGED IN USER IS A SPECIALIST

			if (isset($_GET['c'])) {
				$source_cdd = $_GET['c'];
				$other_doc = base64_decode($_GET['c']);
			}
			$params = [$hospital_no, $cur_date_time];

			if (!empty($_SESSION['specialist'])) {
				$SQL_STRING = " (app_by = ? OR app_by = ? OR doctor_id = ?) ";
				$params[] = $_SESSION['username'];
				$params[] = $_SESSION['specialist'];
				$params[] = $_SESSION['id'];
			} else {
				$SQL_STRING = " (app_by = ? OR app_by = 'anydoctor' OR referal_doc = ? OR doctor_id = ?) ";
				$params[] = $_SESSION['username'];
				$params[] = $_SESSION['username'];
				$params[] = $_SESSION['id'];
			}

			$att_appointment_narrow = '';
			if (isset($_GET['app'])) {
				$att_appointment_narrow = " AND appt_no = ? ";
				$params[] = $_GET['app'];
			}
			$sql = "
				SELECT *
				FROM apptm
				WHERE hospital_no = ?
				AND app_expiration_date >= ?
				AND $SQL_STRING
				AND status NOT IN ('discharge', 'cancelled')
				$att_appointment_narrow
				ORDER BY sn
				LIMIT 1
			";

			$patientOnQueuestmt = $db->prepare($sql);
			$patientOnQueuestmt->execute($params);

			if ($patientOnQueuestmt->rowCount() > 0) {
				$patientAppoint = $patientOnQueuestmt->fetch();
				$appointment_number = $patientAppoint['appt_no'];
				$ap_type = $patientAppoint['ap_type'];
				$patient_insurance = $patientAppoint['insurance'];
				$patient_access_type = $patientAppoint['ap_type'];
				$cr = $patientAppoint['cr'];
				$appointment_interest = $interest;

				$isOnAppoint = false;


				$stmt = $db->prepare("SELECT pay, paystatus FROM patient_ap_services  
				WHERE  hospital_no = ? AND app_no = ?  AND (serv_group='Consultation' OR cat_type = 'Dialysis' OR cat_type = 'Procedure' OR cat_type = 'Transplant')  ");
				$stmt->execute([$hospital_no, $appointment_number]);

				if ($stmt->rowCount() > 0 || $cr > 0 || $credit_limit_bal > 0) {
					$row = $stmt->fetch();
					$pay = $row['pay'];
					$paystatus = $row['paystatus'];

					if ($paystatus == 1) {
						$isOnAppoint = true;
						$credit_status = 0;
					} elseif ($paystatus == 0 && ($isOnAdmission && $credit_limit_bal >= $pay || $cr > 0)) {
						$isOnAppoint = true;
						$credit_status = 1;
					} else {
						$isOnAppoint = false;
						$credit_status = 0;
					}

					if ($visit_status === 'new' && $cr == 0 && $credit_limit_bal == 0) {
						$stmt = $db->prepare("SELECT 1 
											  FROM patient_ap_services 
											  WHERE hospital_no = ? 
												AND paystatus = 0 
												AND item_services = 'New File' 
											  LIMIT 1");
						$stmt->execute([$hospital_no]);

						if ($stmt->fetchColumn()) {
							$isOnAppoint = false;
							$error_status = 1;
							$error_msg = 'Alert: PATIENT HAS NOT PAID FOR NEW FILE';
						}
					}
				}
			}

			if (isset($_GET['app'])) {
				if ($isOnAppoint == true) {
					$appointment_number = cleanInput($_GET['app']);
				}
			}


			if (isset($_POST['end_consultation'])) {
				endConsultation($doctor_no, $appointment_number, $hosp_no, $db);
				unset($_SESSION['current_consultation']);
				header("location:index.php");
			}


			$current_tab = 'medical_hx';
			$consultation_note_taken = true;

			// Define all relevant keys to check
			$post_keys = ['add_med', 'add_med_chart', 'add_feeding', 'add_intake', 'add_output'];
			$get_keys  = ['dv', 'cl', 'fn', 'fd', 'st', 'feeding', 'med_chart', 'in_out', 'fluid', 'adm_note', 'oxygen'];

			// Check if any of the keys exist in $_POST or $_GET
			if (
				array_intersect_key(array_flip($post_keys), $_POST) ||
				array_intersect_key(array_flip($get_keys), $_GET)
			) {
				$current_tab = 'charts';
			}



			/// med_services

			//Needed in amission_script
			$patient_list_arr = [$hospital_no];
			if (isset($_GET["action"])) {
				$current_tab = 'mgt';
			}

			if (isset($_POST['save-vitals-btn']) || isset($_GET['vitals'])) {
				$current_tab = 'vitals';
				$vital_page = empty($_GET['vitals']) ? 0 : $_GET['vitals'];
			} elseif (isset($_GET['adm'])) {
				$current_tab = 'adm';
			} elseif (isset($_GET['mgt'])) {
				$current_tab = 'mgt';
			} elseif (isset($_GET['drug'])) {
				$current_tab = 'drug';
			} elseif (isset($_GET['lab'])) {
				$current_tab = 'lab';
				$lab_page = empty($_GET['lab']) ? 0 : $_GET['lab'];
			} elseif (isset($_GET['imaging'])) {
				$current_tab = 'imaging';
				$rad_page = empty($_GET['imaging']) ? 0 : $_GET['imaging'];
			}

			if (
				isset($_REQUEST['post_med_service_req'])
				|| isset($_GET['editMedService'])
				|| isset($_GET['printMedService'])
				|| isset($_GET['DeleteMedService'])
				|| isset($_GET['medService'])

			) {
				$current_tab = 'med_services';
			}


			/// echo $current_tab;

			?>
			<script>
				var consultation_note_taken = "<?= $consultation_note_taken; ?>";
			</script>


			<div class="wrapper wrapper-content">
				<input type="hidden" id="button_title" value="<?php echo ($_SESSION['rights'] == 'PY') ? 'DOCUMENTATION' : 'CONSULTATION'; ?>">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">

								<?php if ($vip == 1) {
									echo '<h5 style="color:blue; ">VIP PATIENT</h5>';
								} else {
									echo '<h5>Patient Dashboard  </h5>';
								} ?>


								<div id='count_chats'></div>
								</h5>
							</div>
							<div class="ibox-content">

								<div class="row">
									<div class="col-sm-8 b-r">
										<?php include_once('_patient_dashboard_profile_light.php');
										?>
									</div>
									<div class="col-sm-4">

										<?php
										include('../inc/tem_bp_alert.php');
										include_once('_patient_dashboard_links.php'); ?>
										<input type="hidden" value="<?= $appointment_number; ?>" id="con_appointment_number2">
									</div>

								</div>
								<hr>



								<div class="row">
									<div class="col-md-8 col-lg-12">
										<div class="light-card">
											<div class="tabs-container">
												<ul class="nav nav-tabs">
													<li id="medical_hx" class="<?= ($current_tab == 'medical_hx' ? "active" : ""); ?>">
														<a data-toggle="tab" href="#med-hx-tab" style="color: black; font-size:15px;"><i class="fa fa-database"></i> [ Medical History ] </a>
													</li>
													<?php if ($isOnAppoint) { ?>
														<li id="mgt" class="<?= ($current_tab == 'mgt' ? "active" : ""); ?>">
															<a data-toggle="tab" href="#mgt-tab" style="color: black; font-size:15px;" onclick="mgt_tab()"><i class="fa fa-user-md"></i><span id="note_color"><?php echo ($_SESSION['rights'] == 'PY') ? 'DOCUMENTATION' : 'CONSULTATION'; ?></span></a>
														</li> <?php } ?>

													<?php if ($total_admission_count > 0 || $adm_status == 3) { ?>
														<li class="<?= ($current_tab == 'adm' ? "active" : ""); ?>">
															<a data-toggle="tab" href="#adm-tab" style="color: black; font-size:15px;"><i class="fa fa-bed"></i>
																<?php if ($adm_status == 3) {
																	echo "[ Doctor's Ward Round ]";
																} else {
																	echo 'Admission Data';
																} ?>
															</a>
														</li>
													<?php } ?>

													<?php if ($adm_status == 3) { ?>
														<li class="<?= ($current_tab == 'nurse_report' ? "active" : ""); ?>"><a data-toggle="tab" href="#adm-tab_nurse" onClick="nurse_progress_hx('<?= $appointment_number; ?>','<?= $hospital_no; ?>')" style="color: black; font-size:15px;"><i class="fa fa-bed"></i>Nurse Reports</a></li>
													<?php  } ?>

													<li class="<?= ($current_tab == 'vitals' ? "active" : ""); ?>"><a data-toggle="tab" href="#vitals-tab" style="color: black; font-size:15px;"><i class="fa fa-google-wallet"></i>VITALS</a></li>
													<?php if ($_SESSION['rights'] != 'PY') { ?>
														<li class="<?= ($current_tab == 'lab' ? "active" : ""); ?>"><a data-toggle="tab" href="#lab-tab" onClick="check_adm_note('<?= $appointment_number; ?>','<?= $hospital_no; ?>','<?= $adm_status; ?>')" style="color: black; font-size:15px;"><i class="fa fa-flask"></i>LAB</a></li>
													<?php } ?>
													<li class="<?= ($current_tab == 'imaging' ? "active" : ""); ?>"><a data-toggle="tab" href="#imaging-tab" onClick="check_adm_note('<?= $appointment_number; ?>','<?= $hospital_no; ?>','<?= $adm_status; ?>')" style="color: black; font-size:15px;"> <i class="fa fa-film"></i>IMAGING </a></li>

													<li class="<?= ($current_tab == 'drug' ? "active" : ""); ?>"><a data-toggle="tab" href="#med-tab" onClick="check_adm_note('<?= $appointment_number; ?>','<?= $hospital_no; ?>','<?= $adm_status; ?>')" style="color: black; font-size:15px;"><i class="fa fa-plus-square"></i>DRUG/PLAN</a></li>

													<li class="<?= ($current_tab == 'med_services' ? "active" : ""); ?>"><a data-toggle="tab" href="#med-service-tab" style="color: black; font-size:15px;"><i class="fa fa-medkit"></i>MEDICAL SERVICES</a></li>
												</ul>

												<div class="tab-content">

													<div id="med-hx-tab" class="tab-pane <?= ($current_tab == 'medical_hx' ? "active" : ""); ?>">

														<div id="vitals_summary">
															<?php $vitals_hx_url = '../inc/_ward_round_vitals.php'; ?>
														</div>
														<?php if ($_SESSION['rights'] != 'PY') { ?>
															<br>
															<input type="button" name="sumary_visits" value="See Summary Visits" data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-danger btn-xs summary_visits" />
															&nbsp;| &nbsp;
															<button type="submit" id="switch_hx2" class="btn btn-xs btn-info" onClick="med_hx_refresh()">
																<strong>&nbsp;Refresh Notes</strong></button>
															&nbsp;| &nbsp;

															<button type="submit" id="procedure" class="btn btn-xs btn-warning" onClick="general_view2('procedure','1')">
																<strong>&nbsp;View Procedure Notes</strong></button>
															&nbsp;| &nbsp;
															<button type="submit" id="plan" class="btn btn-xs btn-default" onClick="general_view2('plan','1')">
																<strong>&nbsp;View Doctor's Plan</strong></button>
															&nbsp;| &nbsp;
															<button type="submit" id="nurse_report" class="btn btn-xs btn-primary" onClick="general_view2('nurse_report','1')">
																<strong>&nbsp;View Nursing Reports</strong></button>
															&nbsp;| &nbsp;
															<button type="submit" id="ward_round" class="btn btn-xs btn-primary" onClick="general_view2('ward_round','1')">
																<strong>&nbsp;View Ward Round Reports</strong></button>
															&nbsp;| &nbsp;
															<button type="submit" id="physio" class="btn btn-xs btn-info" onClick="general_view2('physio','1')">
																<strong>&nbsp;Physiotherapy Notes</strong></button>
															&nbsp;| &nbsp;

															<button type="submit" id="medical_notes" class="btn btn-xs btn-warning" onClick="general_view('medical_notes','1')">
																<strong>&nbsp;Medical Service Notes</strong></button>
															&nbsp;| &nbsp;

															<button type="submit" id="switch_hx" class="btn btn-xs btn-success" onClick="general_view('all_notes','1')">
																<strong>&nbsp;All Notes</strong></button>

														<?php } ?>
														<?php

														if (!empty($old_hostpital_no)) {
														?>
															&nbsp;| &nbsp;
															<button type="submit" id="switch_hx3" class="btn btn-xs btn-danger" onClick="load_vistamedic_notes('consultation')">
																<strong>&nbsp;OLD EMR NOTES</strong></button>

															<input type="hidden" value="<?= $old_hostpital_no; ?>" name="old_hostpital_no" id="old_hostpital_no">
														<?php
														}

														?>


														<br>
														<br>

														<div class="animated fadeInRight" id="scroll_here_hx">
															<?php include_once('_medication_hx.php');
															?>
														</div>
													</div>


													<?php if ($isOnAppoint) { ?>
														<div id="mgt-tab" class="tab-pane <?= ($current_tab == 'mgt' ? " active" : ""); ?>">
															<div class="animated fadeInRight">
																<div class="">
																	<div class="light-card">
																		<?php include_once('_appointment_mgt_2.php');
																		?>
																	</div>
																</div>
															</div>
														</div>
													<?php } ?>

													<?php if ($total_admission_count > 0 || $adm_status == 3) { ?>
														<div id="adm-tab" class="tab-pane <?= ($current_tab == 'adm' ? " active" : ""); ?>">
															<div class="animated fadeInRight">
																<div class="">
																	<?php include('../inc/_admission_doctor.php'); ?>
																</div>
															</div>
														</div>
													<?php  } ?>

													<?php if ($adm_status == 3) { ?>
														<div id="adm-tab_nurse" class="tab-pane <?= ($current_tab == 'adm_rounds' ? " active" : ""); ?>">
															<div class="animated fadeInRight">
																<div class="" id="_progress_notes_hx"></div>
															</div>
														</div>
													<?php  } ?>

													<div id="vitals-tab" class="tab-pane  <?= ($current_tab == 'vitals' ? "active" : ""); ?>">
														<div class="animated fadeInRight"><br>
															<div class="row">
																<div class="col-md-8 b-r">
																	<div id="vitals_hx__wrap"></div>
																</div>

																<div class="col-md-4">
																	<?php
																	//if ($isOnAppoint || $admission_info != null ) {
																	include('../inc/vitals.php');
																	//}
																	?>

																</div>
															</div>
														</div>
													</div>

													<div id="lab-tab" class="tab-pane <?= ($current_tab == 'lab' ? "active" : ""); ?>">
														<div class="animated fadeInRight"><br>

															<div class="text-right">
																<?php
																//   if ($isOnAppoint || $admission_info != null ) {
																?>
																<button class="btn btn-primary" onclick="openLabTestModal('<?= $hospital_no; ?>', '<?= $appointment_number; ?>', 'Laboratory')" id="add_lab_id"><i class="fa fa-plus-square"></i>&nbsp;&nbsp;Add <u>New</u> Request</button> |
																<?php //}
																?>
																<button class="btn btn-danger labs-on-queue-btn " arial-hospital-no="<?= $hospital_no; ?>" arial-lab-section="Laboratory">Confirm Request Status / Upload Results</button>
																<?php
																if (isset($old_hostpital_no)) {
																	if ($old_hostpital_no != '' && $old_hostpital_no != null && $old_hostpital_no != 'NULL') {
																		echo '
																			
																			 | <buttton class="btn btn btn-success" id="vista_notes_modal_btn" 
																				arial-data = "Lab"
																				arial-hospitalnno = "' . $old_hostpital_no . '"
																				>OLD EMR LAB</buttton>
																		';
																	}
																}

																?>
															</div>
															<div class="">

																<div>
																	<?php
																	$investigations_lab_hx_url = '_investigation_hx.php';
																	include_once('_investigation_hx.php'); ?>
																</div>
															</div>

														</div>
													</div>
													<div id="imaging-tab" class="tab-pane <?= ($current_tab == 'imaging' ? "active" : ""); ?>">
														<div class="animated fadeInRight"><br>

															<div class="text-right">
																<?php
																// if ($isOnAppoint || $admission_info != null ) {
																?>
																<button class="btn btn-primary" onclick="openLabTestModal('<?= $hospital_no; ?>', '<?= $appointment_number; ?>', 'Radiology')" id="add_rad_id"><i class="fa fa-plus-square"></i>&nbsp;&nbsp;Add <u>New</u> Request</button> |
																<?php //}
																?>
																<button class="btn btn-danger labs-on-queue-btn " arial-hospital-no="<?= $hospital_no; ?>" arial-lab-section="Radiology">Confirm Request Status / Upload Results</button>
																<?php
																if (isset($old_hostpital_no)) {
																	if ($old_hostpital_no != '' && $old_hostpital_no != null && $old_hostpital_no != 'NULL') {

																		echo '
																			
																			 | <buttton class="btn btn btn-success" id="vista_notes_modal_btn" 
																				arial-data = "Scan"
																				arial-hospitalnno = "' . $old_hostpital_no . '"
																				>OLD EMR IMAGE</buttton>
																		';
																	}
																}

																?>
															</div>
															<div class="">
																<div>
																	<?php
																	$investigations_image_hx_url = '_imaging_hx.php';
																	include_once('_imaging_hx.php');
																	?>
																</div>
															</div>

														</div>
													</div>

													<div id="med-tab" class="tab-pane <?= ($current_tab == 'drug' ? "active" : ""); ?>">
														<div class="animated fadeInRight"><br>
															<div class="text-right">
																<?php if ($isOnAppoint || $isOnAdmission) {
																	$plan_edit_mode = 1;
																} else {
																	$plan_edit_mode = 0;
																}
																?>
															</div>
															<div class="card" style=" padding: 20px">

																<div class="">
																	<?php
																	$med_fetch_url = 'fetch_set.php';
																	$save_drug_url = 'controllers/_saveMedication.php';
																	$save_investigation_url = 'controllers/_saveInvestigation.php';
																	include_once("components/_medications.php");
																	?>
																</div>
																<hr>
																<div style="max-height:400px; overflow:auto">
																	<?php
																	$drug_plan_hx_url = '_drug_hx.php';
																	include_once('_drug_hx.php');
																	?>
																</div>
															</div>

														</div>
													</div>
													<?php //if ($_SESSION['rights'] != 'PY') {
													?>
													<div id="med-service-tab" class="tab-pane <?= ($current_tab == 'med_services' ? "active" : ""); ?>">
														<div class="animated fadeInRight"><br>

															<?php

															if (isset($_REQUEST['DeleteMedService'])) {

																try {
																	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable PDO error reporting

																	$id = intval(base64_decode($_GET['DeleteMedService']));
																	///exit;

																	$stmt = $db->prepare("SELECT * FROM notes_services WHERE app_service_tbl_id = ?");
																	$stmt->execute([$id]);

																	if ($stmt->rowCount() > 0) {
																		$note = $stmt->fetch(PDO::FETCH_ASSOC);
																		$paystatus = 0;
																		$app_service_id = $note['app_service_id'];

																		if (!empty($note['app_service_id'])) {
																			$stmt = $db->prepare("SELECT paystatus, sn, drug_status FROM patient_ap_services WHERE sn = ? AND created_by = ?");
																			$stmt->execute([$id, $_SESSION['id']]);
																			$payment = $stmt->fetch(PDO::FETCH_ASSOC);

																			if ($payment) {
																				$paystatus = intval($payment['paystatus']);
																				$drug_status = intval($payment['drug_status']);

																				if ($paystatus == 0 && $drug_status == 0) {
																					$db->beginTransaction();

																					// Delete from patient_ap_services
																					$delete1 = $db->prepare("DELETE FROM patient_ap_services WHERE sn = ? AND drug_status = 0");
																					$deleted1 = $delete1->execute([$app_service_id]);

																					// Delete from notes_services
																					$delete2 = $db->prepare("DELETE FROM notes_services WHERE app_service_id = ?");
																					$deleted2 = $delete2->execute([$app_service_id]);

																					if ($deleted1 && $deleted2) {
																						$db->commit();
																						echo "<b style='color:blue;'>Records deleted successfully.</b>";
																					} else {
																						$db->rollBack();
																						$error1 = $delete1->errorInfo();
																						$error2 = $delete2->errorInfo();
																						echo "<b style='color:red;'>Transaction failed. Changes reverted.<br>
                                  Error1: {$error1[2]}<br>Error2: {$error2[2]}</b>";
																					}
																				} else {
																					echo "<b style='color:red;'>Cannot delete — service already processed or billed.</b>";
																				}
																			} else {
																				echo "<b style='color:red;'>Related service not found in patient services.</b>";
																			}
																		} else {
																			echo "<b style='color:red;'>Invalid service reference (missing app_service_tbl_id).</b>";
																		}
																	} else {
																		echo "<b style='color:red;'>Service notes not found!</b>";
																	}
																} catch (PDOException $e) {
																	if ($db->inTransaction()) {
																		$db->rollBack();
																	}
																	echo "<b style='color:red;'>Database Error:</b> " . htmlspecialchars($e->getMessage()) .
																		" <br><b>File:</b> " . basename($e->getFile()) .
																		" <br><b>Line:</b> " . $e->getLine();
																} catch (Exception $e) {
																	if ($db->inTransaction()) {
																		$db->rollBack();
																	}
																	echo "<b style='color:red;'>General Error:</b> " . htmlspecialchars($e->getMessage()) .
																		" <br><b>File:</b> " . basename($e->getFile()) .
																		" <br><b>Line:</b> " . $e->getLine();
																}
															}



															if (isset($_REQUEST['printMedService'])) {
																$id = intval(base64_decode($_GET['printMedService']));
																$stmt = $db->prepare("SELECT * FROM notes_services WHERE id = ?");
																$stmt->execute([$id]);
																if ($stmt->rowCount() > 0) {
																	$note = $stmt->fetch();
																	$paystatus = 0;
																	if (!empty($note['app_service_tbl_id'])) {
																		$stmt = $db->prepare("SELECT paystatus FROM patient_ap_services WHERE sn = ?");
																		$stmt->execute([$note['app_service_tbl_id']]);
																		$payment = $stmt->fetch();

																		$paystatus = intval($payment['paystatus']);
																	} else {
																		echo 'Service notes not found';
																	}
																	include_once('../_gen_med_services_print.php');
																} else {
																	echo 'Service notes not found!';
																}
															?>

															<?php
															} else {
															?>
																<div class="row">
																	<div class="col-lg-5">
																		<?php



																		if (isset($_REQUEST['post_med_service_req']) || isset($_REQUEST['updateMedService'])) {
																			$current_tab = 'med_services';
																			include_once('../_med_services_process.php');
																		}


																		//	include_once('../_gen_med_services_hx.php');
																		$gen_med_services_hx_url = '../_gen_med_services_hxx.php';


																		?>
																		<div id="wait_loading" style="color: red; font-size: 14px; ">Please Wait ...</div>
																		<div id="gen_med_services__wrap"></div>
																	</div>





																	<div class="col-lg-7">
																		<?php
																		include_once('../_med_services_form.php');
																		?>
																	</div>
																</div>
															<?php
															}

															?>


														</div>
													</div>
													<?php ///}
													?>

												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>


			<div class="modal inmodal fade" id="vista_notes_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
				<div class="modal-dialog modal-xl">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title" id="">Patient Investigation Reports</h4>
						</div>
						<div class="modal-body" id="vista_notes_modal_body">
						</div>
					</div>
				</div>
			</div>


			<?php if ($other_doc == 'other_doc') { ?>

				<div class="modal inmodal fade" id="other_doc_confirmation_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="">PATIENT CONSULTATION</h4>
							</div>
							<div class="modal-body">

								<h3>CONVERT PATIENT TO YOUR QUEUE?</h3>

								<hr>
								<?php $bb = base64_encode($_SESSION['username'] . '___' . $hospital_no . '___' . $_GET['app']); ?>
								<a href="patient.php?hosp_no=<?= $hospital_no; ?>&cvn=<?= $bb; ?>" class="btn btn-success">CONSULT</a>
								&nbsp; : &nbsp;
								<a href="index.php" class="btn btn-danger">CLOSE</a>

							</div>
						</div>
					</div>
				</div>

			<?php } ?>



			<div class="modal inmodal" id="patient_manager_modal" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-body" id="patient_manager_body" style="height: 650px; overflow-y: auto;">

						</div>

						<input type="hidden" name="make_vip" id="make_vip" value="<?php echo $make_vip; ?>" />
						<input type="hidden" name="manage_hx" id="manage_hx" value="<?php echo $manage_hx; ?>" />
						<input type="hidden" name="patient_manage_by" id="patient_manage_by" value="<?php echo $patient_manage_by; ?>" />
						<input type="hidden" name="vip" id="vip" value="<?php echo $vip; ?>" />
						<input type="hidden" name="hospital_no_manage" id="hospital_no_manage" value="<?php echo $hospital_no; ?>" />
						<input type="hidden" name="doctor_id" id="doctor_id" value="<?php echo $doctor_id; ?>" />
						<input type="hidden" name="manage_status" id="manage_status" value="<?php echo $manage_status; ?>" />
						<input type="hidden" name="patient_manage_hx" id="patient_manage_hx" value="<?php echo $patient_manage_hx; ?>" />
					</div>
				</div>
			</div>


			<?php include("../inc/patient_alert.php"); ?>
			<?php include("../inc/footer.php"); ?>
		</div>
	</div>


	<?php include("../inc/footer_scripts.php"); ?>



	<script src="../js/plugins/chosen/chosen.jquery.js"></script>
	<script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
	<script src="../js/plugins/datapicker/select2.full.min.js"></script>
	<script src="../js/jquery-ui.js"></script>
	<script src="../js/typeahead.jquery.min.js"></script>
	<script src="../inc/_special_scripts.js"></script>
	<script src="../js/vendors/editor/dist/trumbowyg.js"></script>
	<script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
	<script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>
	<link rel="stylesheet" href="../js/select2/css/select2.min.css">
	<script src="../js/select2/js/select2.min.js"></script>

	<?php if (isset($_GET['mgt'])) { ?>
		<script>
			$(document).ready(function() {
				mgt_tab();
			});
		</script>
	<?php } ?>


	<script>
		<?php if ($other_doc == 'other_doc') { ?>
			$('#other_doc_confirmation_modal').modal('show');
		<?php } ?>


		function patient_manager_modal() {

			var make_vip = document.getElementById("make_vip").value;
			var vip = document.getElementById("vip").value;
			var hospital_no_manage = document.getElementById("hospital_no_manage").value;
			var doctor_id = document.getElementById("doctor_id").value;
			var manage_status = document.getElementById("manage_status").value;
			var patient_manage_hx = document.getElementById("patient_manage_hx").value;
			var manage_hx = document.getElementById("manage_hx").value;
			var patient_manage_by = document.getElementById("patient_manage_by").value;

			$.ajax({
				url: "manage_patient_fetch.php",
				method: "POST",
				data: {
					make_vip: make_vip,
					vip: vip,
					hospital_no_manage: hospital_no_manage,
					doctor_id: doctor_id,
					manage_status: manage_status,
					patient_manage_by: patient_manage_by,
					manage_hx: manage_hx,
					patient_manage_hx: patient_manage_hx
				},
				success: function(data) {

					$('.modal-title').text("Admitted Reason(s)");
					$('#patient_manager_modal').modal('show');
					$('#patient_manager_body').html(data);
					toastr.clear();

				}
			});
		}

		$(document).ready(function() {

			$('#doctor_names').select2({
				placeholder: 'Search and Select ...',
				minimumInputLength: 2,
				ajax: {
					url: 'get_search.php',
					type: 'POST',
					dataType: 'json',
					delay: 250,
					data: function(params) {
						return {
							search: params.term,
							hosp_number: '<?= $hospital_no; ?>',
							mode: 'doctor_names' // Key difference
						};
					},
					processResults: function(data) {
						return {
							results: data
						};
					},
					cache: true
				}
			});


			$('#search_anything').select2({
				placeholder: 'Search and Select ...',
				minimumInputLength: 2,
				ajax: {
					url: 'get_search.php',
					type: 'POST',
					dataType: 'json',
					delay: 250,
					data: function(params) {
						return {
							search: params.term,
							hosp_number: '<?= $hospital_no; ?>',
							mode: 'search_anything' // Key difference
						};
					},
					processResults: function(data) {
						return {
							results: data
						};
					},
					cache: true
				}
			});

			$('#app_service_tbl_id_').select2({
				placeholder: 'Search and Select Services',
				minimumInputLength: 2,
				ajax: {
					url: 'get_search.php',
					type: 'POST',
					dataType: 'json',
					delay: 20,
					data: function(params) {
						return {
							search: params.term,
							mode: 'all_app_service' // Key difference
						};
					},
					processResults: function(data) {
						return {
							results: data
						};
					},
					cache: true
				}
			});

		});

		$(document).on('click', '#vista_notes_modal_btn', function() {
			$('#vista_notes_modal').modal('show')
			toastr.info('Please Wait .... ', 'Processing', {
				timeOut: 5000
			})

			const notes_type = $(this).attr('arial-data');
			const hospital_no = $(this).attr('arial-hospitalnno');

			$.ajax({
				url: '_medication_hx.php',
				method: "POST",
				data: {
					load_vistamedic_notes: true,
					hospital_no: hospital_no,
					notes_type: notes_type
				},
				success: function(response) {
					$("#vista_notes_modal_body").html(response);

					toastr.clear();
				},
				error: function(err) {
					console.log(err)
				}
			});
		})

		$(document).ready(function() {
			$('.trumbowygEditor').trumbowyg({
				btns: [
					['viewHTML'],
					['undo', 'redo'], // Only supported in Blink browsers
					['formatting'],
					['strong', 'em', 'del'],
					['superscript', 'subscript'],
					['fontsize'],
					['foreColor', 'backColor'],
					['link'],
					['insertImage'],
					['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
					['unorderedList', 'orderedList'],
					['horizontalRule'],
					['removeformat'],
					['fullscreen']
				],
				plugins: {
					fontsize: {
						sizeList: [
							'12px',
							'14px',
							'16px',
							'18px',
							'20px',
							'24px',
							'32px',
							'48px',
						]
					}
				}
			});

			$('#__genserviceNote___')
				.trumbowyg()
				.on('tbwchange', function() {
					$('#genserviceNote').val($('#__genserviceNote___').html())
				});

		});




		$(document).on('click', '#new-allergy-btn', function() {
			$('#newPatientAllergiesModal').modal('show');
		});


		$(document).on('click', '.previous_adm_reason', function() {
			var prv_adm_hx = $(this).attr("id");
			toastr.warning('Processing, please wait...', 'Attention', {
				timeOut: 13000
			})
			$.ajax({
				url: "_ward_review.php",
				method: "POST",
				data: {
					prv_adm_hx: prv_adm_hx
				},
				success: function(data) {

					$('.modal-title').text("Admitted Reason(s)");
					$('#previous_adm_reason_mdl').modal('show');
					$('#previous_adm_reason_body').html(data);
					toastr.clear();

				}
			});
		});


		function countWords(mgt_notes) {
			var wordsArray = mgt_notes.trim().split(/\s+/);
			return wordsArray.length;
		}

		function save_allergies() {

			var hospital_no = $('#hospital_no').val();
			var app_no = $('#app_no').val();
			var allergies_notes = $('#allergies_notes').val();

			$.ajax({
				url: "_patient_allergies.php",
				method: "POST",
				data: {
					hospital_no: hospital_no,
					app_no: app_no,
					allergies_notes: allergies_notes
				},
				success: function(data) {
					alert(data);
					window.location.reload(true);
					//	document.getElementById("patient-alert-tbody").innerHTML = data;
				}
			});

		}


		function mgt_tab() {

			var hosp_no = "<?php echo $hosp_no; ?>";
			var patient_name = "<?php echo addslashes($patient_name); ?>";
			var doctor_no = "<?php echo $doctor_no; ?>";
			var doctor_name = "<?php echo addslashes($doctor_name); ?>";


			$.ajax({
				url: "consultation_queue.php",
				method: "POST",
				dataType: "json",
				data: {
					mgt_tab: true,
					hospital_no: hosp_no,
					patient_name: patient_name,
					doctor_no: doctor_no,
					doctor_name: doctor_name
				},
				success: function(response) {
					checkOngoingConsultation();
				},
				error: function(xhr, status, error) {
					console.log("XHR Response:", xhr.responseText);
					console.log("Status:", status);
					console.log("Error Thrown:", error);

					///alert("Error: " + xhr.responseText);
				}

			});

		}


		$(document).on('click', '#save-mgt-button_physio', function() {

			var visit_status = document.getElementById("visit_status").value;
			let mgt_notes = $("#mgt_notes").html();

			var wordCount = countWords(mgt_notes);
			if (wordCount <= 5) {
				toastr.error('Please enter at least five words!', 'Error', {
					timeOut: 1000
				})
				exit;
			}


			var doctor_id = document.getElementById("doctor_id").value;
			var doctor_name = document.getElementById("doctor_name").value;
			var credit_status = document.getElementById("credit_status").value;

			if (mgt_notes != '') {

				toastr.info('Please wait...', 'Saving', {
					timeOut: 1000
				})

				$.ajax({
					url: "_appointment_mgt_2.php",
					method: "POST",
					data: {
						saveMgt_physio: true,
						mgt_notes,
						app_no: window.appointment_number,
						hospital_no: window.hospital_no,
						visit_status,
						doctor_id,
						credit_status,
						doctor_name
					},
					success: function(response) {

						if (response.status == 200) {
							toastr.success(response.message, 'Success', {
								timeOut: 1000
							})
							med_hx_refresh();
							$('#medical_hx').addClass('active');
							$('#med-hx-tab').addClass('active');
							$('#mgt').removeClass('active');
							$('#mgt-tab').removeClass('active');
							document.getElementById("D_status").value = response.d_status;

						} else {
							toastr.error(response.message, 'Error', {
								timeOut: 1000
							})
						}
						$("#mgt_notes").html(" ")
						check_adm_note_clr();

					}
				});
				check_adm_note_clr();

				/////alert();

			} else {

				toastr.error('Invalid Documentation!', 'Empty', {
					timeOut: 1000
				})
			}





		})


		$(document).on('click', '#save-mgt-button', function() {


			var visit_status = document.getElementById("visit_status").value;
			let mgt_notes = $("#mgt_notes").html();

			var wordCount = countWords(mgt_notes);
			if (wordCount <= 5) {
				toastr.error('Please enter at least five words!', 'Error', {
					timeOut: 1000
				})
				exit;
			}


			var diagnosis = document.getElementById("search_icdcodes_input").value;
			var comment = document.getElementById("comment").value;
			var comment2 = document.getElementById("comment2").value;
			var comment3 = document.getElementById("comment3").value;
			var diagnosisInput = document.getElementById("diagnosisInput").value;
			var diagnosisList = document.getElementById("diagnosisInputList_input").value;
			var visit_status = document.getElementById("visit_status").value;
			var date_of_birth = document.getElementById("date_of_birth").value;
			var con_appointment_number = document.getElementById("con_appointment_number").value;

			var icdcode_group = document.getElementById("icdcode_group").value;
			var doctor_id = document.getElementById("doctor_id").value;
			var doctor_name = document.getElementById("doctor_name").value;
			var D_status = document.getElementById("D_status").value;
			var credit_status = document.getElementById("credit_status").value;



			if (diagnosisList == '' && search_icdcodes_input != '' && (D_status == '' || D_status == 'notseen')) {
				y = confirm('Do you want to proceed without spsecifying a diagnosis?');
				if (y == false) {
					if (!window.search_icdcodes_result || window.search_icdcodes_result.length === 0) {
						toastr.error('Please select a diagnosis from the dropdown', 'Error', {
							timeOut: 2000
						});
						return 0;
					} else {
						const isFound = window.search_icdcodes_result.some(d => d.name === diagnosis);

						if (!isFound) {
							toastr.error('Please select a diagnosis from the dropdown', 'Error', {
								timeOut: 2000
							});
							return 0;
						}

						diagnosisList = `${diagnosis}<br/>`;
					}
				} else {
					diagnosisList = '';
				}
			}



			if (mgt_notes != '') {

				///	alert(credit_status);

				toastr.info('Please wait...', 'Saving', {
					timeOut: 1000
				})

				$.ajax({
					url: "_appointment_mgt_2.php",
					method: "POST",
					data: {
						saveMgt: true,
						mgt_notes,
						app_no: window.appointment_number,
						hospital_no: window.hospital_no,
						visit_status,
						diagnosis,
						comment,
						comment2,
						comment3,
						diagnosisInput,
						doctor_id,
						doctor_name,
						icdcode_group,
						date_of_birth,
						diagnosisList,
						credit_status,
						con_appointment_number
					},
					success: function(response) {
						if (response.status == 200) {
							toastr.success(response.message, 'Success', {
								timeOut: 1000
							})
							med_hx_refresh();
							$('#medical_hx').addClass('active');
							$('#med-hx-tab').addClass('active');
							$('#mgt').removeClass('active');
							$('#mgt-tab').removeClass('active');
							document.getElementById("D_status").value = response.d_status;
						} else {
							toastr.error(response.message, 'Error', {
								timeOut: 1000
							})
						}
						$("#mgt_notes").html(" ")
						check_adm_note_clr();

					}
				});
				check_adm_note_clr();



			} else {

				toastr.error('Invalid Documentation!', 'Empty', {
					timeOut: 1000
				})
			}

		})



		$(document).on('click', '#save-patient-alert_2', function() {
			var alert_note = $('#patient-alert-notes').val();
			var hospital_no = $('#patient_alert_hospital_no').val();
			var who_should_see = $('#who_should_see').val();

			$.ajax({
				url: "../inc/set_alert_patients.php",
				method: "POST",
				data: {
					alert: alert_note,
					hospital_no: hospital_no,
					who_should_see: who_should_see,
					save: true
				},
				success: function(response) {

					var json = JSON.parse(response);
					if (json["status"] == 200) {
						toastr.success(json["message"], 'Success', {
							timeOut: 5000
						})
						$('#patient-alert-notes').val("");
						display_alert('<?php echo $hospital_no; ?>');
					} else {
						toastr.error(json["message"], 'Error', {
							timeOut: 5000
						})
					}
				}
			});
		});

		function display_alert_2(hosp_no) {
			$.ajax({
				url: "../inc/set_alert_patients.php",
				method: "POST",
				data: {
					check_alert_2X: hosp_no
				},
				success: function(data) {
					///alert(data); // Debugging alert to see the response
					document.getElementById("patient-alert-tbody_2").innerHTML = data;
				}
			});
		}



		function display_alert(hosp_no) {

			$.ajax({
				url: "../inc/set_alert_patients.php",
				method: "POST",
				data: {
					check_alert: hosp_no
				},
				success: function(data) {
					///alert(data); // Debugging alert to see the response
					document.getElementById("patient-alert-tbody").innerHTML = data;
				}
			});
		}

		function delete_alert(sn) {
			var rr = confirm("Are you sure you want to DELETE?");
			if (rr === true) {
				$.ajax({
					url: "../inc/set_alert_patients.php",
					method: "POST",
					data: {
						delete_notes: sn
					},
					success: function(data) {
						toastr.success('Refresh to confirm delete status', 'Attention', {
							timeOut: 5000
						})
					}
				});
			}
		}


		function openModal_fx(id) {
			$('#' + id).modal('show');
			display_alert_2('<?php echo $hospital_no; ?>');
		}


		$(document).on('click', '.service_review_link', function() {
			var add_review = $(this).attr("id");
			$.ajax({
				url: "_ward_review.php",
				method: "POST",
				data: {
					add_review_post: add_review
				},
				success: function(data) {

					$('.modal-title').text("Doctor's Ward Rounds and Service Review");
					$('#ward_review_mdl').modal('show');
					$('#ward_review_body').html(data);
				}
			});
		});


		$(document).on('click', '.vitals_links', function() {
			var vistal_hx = document.getElementById('vistal_hx').value;

			var hosp_number = $(this).attr("id");
			$.ajax({
				url: "../inc/vitals.php",
				method: "POST",
				data: {
					vitals_links: hosp_number,
					vistal_hx: vistal_hx
				},
				success: function(data) {

					$('.modal-title').text("Vitals Signs History");
					$('#vitals_links_mdl').modal('show');
					$('#vitals_links_body').html(data);
				}
			});
		});

		$(document).on('click', '.add_view_plan', function() {

			var hosp_number = $(this).attr("id");
			$.ajax({
				url: "../inc/plan_add_view.php",
				method: "POST",
				data: {
					add_view_plan: hosp_number
				},
				success: function(data) {

					$('.modal-title').text("Add / View Notes");
					$('#add_view_plan_mdl').modal('show');
					$('#add_view_plan_body').html(data);
				}
			});
		});

		$(document).on('click', '.summary_visits', function() {

			var hosp_number = $(this).attr("id");

			$.ajax({
				url: "../inc/summary_visits.php",
				method: "POST",
				data: {
					visit_hospital_no: hosp_number
				},
				success: function(data) {
					$('.modal-title').text("Summary Visits");
					$('#add_view_plan_mdl').modal('show');
					$('#add_view_plan_body').html(data);
				}
			});
		});


		function load_template() {

			// document.getElementById('icd_10_type').style.display ='none'; 
			document.getElementById('template_type').style.display = 'block';

			var doc_template = document.getElementById('doc_template').value;
			toastr.info('Please wait...', '', {
				timeOut: 5000
			})
			$.ajax({
				url: "../inc/text_editor2.php",
				method: "POST",
				data: {
					load_template: doc_template
				},
				success: function(data) {


					toastr.clear();
					$("#mgt_notes").html(data);
				}
			});
		}





		$(document).on('change', '#filter_adm_room_bed', function() {
			filterTbleFx('filter_adm_room_bed', 'adm_list_tbl')
		})

		$(document).on('keyup', '#search_adm_room_bed_input', function() {
			filterTbleFx('search_adm_room_bed_input', 'adm_list_tbl')
		})

		function filterTbleFx(id_, table_) {
			var filter = $('#' + id_).val();
			filter = filter.toUpperCase();

			var rows = document.querySelector("#" + table_ + " tbody").rows;

			for (var i = 0; i < rows.length; i++) {
				var firstCol = rows[i].cells[1].textContent.toUpperCase();
				var secondCol = rows[i].cells[2].textContent.toUpperCase();
				var thirdCol = rows[i].cells[3].textContent.toUpperCase();
				var fourCol = rows[i].cells[4].textContent.toUpperCase();
				// var fiveCol = rows[i].cells[5].textContent.toUpperCase();
				if (
					firstCol.indexOf(filter) > -1 ||
					secondCol.indexOf(filter) > -1 ||
					thirdCol.indexOf(filter) > -1 ||
					fourCol.indexOf(filter) > -1
					// fiveCol.indexOf(filter) > -1
				) {
					rows[i].style.display = "";
				} else {
					rows[i].style.display = "none";
				}
			}
		}

		/*
				function filterTbleFunction(id_, table_, first_col, last_col) {
					var filter = $('#' + id_).val();
					filter = filter.toUpperCase();

					var rows = document.querySelector("#" + table_ + " tbody").rows;

					for (var i = 0; i < rows.length; i++) {
						let match = false
						let col = null;
						for (var j = first_col; j < last_col; j++) {
							col = rows[i].cells[j].textContent.toUpperCase();
							if (col.indexOf(filter) > -1) {
								match = true
								break;
							}
						}

						if (match) {
							rows[i].style.display = "";
						} else {
							rows[i].style.display = "none";

						}

					}
				}

				*/


		// Debounce function to limit how often the search executes
		function debounce(func, wait) {
			let timeout;
			return function() {
				const context = this,
					args = arguments;
				clearTimeout(timeout);
				timeout = setTimeout(() => {
					func.apply(context, args);
				}, wait);
			};
		}

		// Improved search function
		function filterTableFunction(id_, table_, first_col, last_col) {
			var filter = document.getElementById(id_).value.trim().toUpperCase();
			var rows = document.querySelector("#" + table_ + " tbody").rows;

			// Remove previous highlights
			document.querySelectorAll('.highlight').forEach(el => {
				el.classList.remove('highlight');
				const parent = el.parentNode;
				parent.replaceChild(document.createTextNode(el.textContent), el);
				parent.normalize();
			});

			for (var i = 0; i < rows.length; i++) {
				let match = false;

				// Skip if row has no cells or is a loading row
				if (rows[i].cells.length === 0 ||
					(rows[i].cells[0].querySelector && rows[i].cells[0].querySelector('h3.text-danger'))) {
					rows[i].style.display = "none";
					continue;
				}

				// Check each specified column
				for (var j = first_col; j < last_col; j++) {
					if (j >= rows[i].cells.length) continue;

					const cell = rows[i].cells[j];
					const cellText = cell.textContent.toUpperCase();

					// Split search terms and check if ALL terms appear in the cell
					const searchTerms = filter.split(/\s+/).filter(term => term.length > 0);

					if (searchTerms.length === 0) {
						match = true;
						break;
					}

					match = searchTerms.every(term => cellText.includes(term));

					if (match) {
						// Highlight matching text
						if (filter.length > 0) {
							highlightMatches(cell, filter);
						}
						break;
					}
				}

				rows[i].style.display = match ? "" : "none";
			}
		}

		// Highlight matching text in cells
		function highlightMatches(element, filter) {
			const text = element.textContent;
			const matchStart = text.toUpperCase().indexOf(filter);

			if (matchStart >= 0) {
				const matchEnd = matchStart + filter.length;
				const before = text.substring(0, matchStart);
				const match = text.substring(matchStart, matchEnd);
				const after = text.substring(matchEnd);

				element.innerHTML = before + '<span class="highlight">' + match + '</span>' + after;
			}
		}

		// Initialize search functionality
		document.addEventListener('DOMContentLoaded', function() {
			const searchInput = document.getElementById('medication_search_input');
			const clearButton = document.getElementById('clear_search');

			// Debounced search (300ms delay)
			const debouncedSearch = debounce(function() {
				filterTableFunction('medication_search_input', 'medication___notes___table', 0, 2);
			}, 300);

			// Set up event listeners
			if (searchInput) {
				searchInput.addEventListener('input', debouncedSearch);
			}

			if (clearButton) {
				clearButton.addEventListener('click', function() {
					searchInput.value = '';
					filterTableFunction('medication_search_input', 'medication___notes___table', 0, 2);
					searchInput.focus();
				});
			}

			// Initial filter to hide loading message when real data loads
			if (searchInput) {
				filterTableFunction('medication_search_input', 'medication___notes___table', 0, 2);
			}
		});

		var substringMatcher = function(strs) {
			return function findMatches(q, cb) {
				var matches, substringRegex;

				// an array that will be populated with substring matches
				matches = [];

				// regex used to determine if a string contains the substring `q`
				substrRegex = new RegExp(q, 'i');

				// iterate through the pool of strings and for any string that
				// contains the substring `q`, add it to the `matches` array
				$.each(strs, function(i, str) {
					if (substrRegex.test(str)) {
						matches.push(str);
					}
				});

				cb(matches);
			};
		};
	</script>


	<?php
	$investigations_controller_url = 'controllers/_investigations.php';
	$medication_controller_url = 'controllers/_medication.php';
	$investigations_hx_url = '_investigation_hx.php';
	include_once('_medication_investigation_modal.php');
	include('_medical_hx_scripts.php');
	include('_drug_plan_scripts.php');
	include('_vitals_script.php');

	//include('_admission_scripts.php');
	include('../inc/_other_modals.php');
	//include('../inc/save_vital_script.php');
	include('../modal_lock.php');
	include_once('_review_of_system.php');
	include_once('_physical_exams.php');
	include_once('_patient_social_hx.php');
	include_once('_patient_past_med_hx.php');
	include('_gen_med_services_script.php');
	?>



	<?php
	if (isset($_POST['edit-note-btn'])) {
		$id = intval(cleanInput($_POST['id']));
		$date_entry = $_POST['date_entry'];
		$date_entry2 = $_POST['date_entry2'];
		$notes_type = cleanInput($_POST['notes_type']);

		$stmt = $db->prepare("SELECT * FROM notes WHERE sn = ? LIMIT 1");
		$stmt->execute(array($id));
		$note = $stmt->fetch();
		$notes = $note['notes'];
	?>
		<div class="modal inmodal fade" id="edit_note_modal2" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false">
			<div class="modal-dialog modal-lg" style="width: 65%;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

						<h4 class="modal-title"></h4>
					</div>

					<form action="patient.php?hosp_no=<?= $hospital_no; ?>" method="post">
						<div name="edit_note_editor" id="edit_note_editor" class="trumbowygEditor" cols="30" rows="10" style="font-size: 17px;"><?php echo $notes; ?></div>


						<?php
						if ($date_entry2 == '') {
							$update_dated = $date_entry;
						} else {
							$update_dated = $date_entry2;
						}
						?>


						<input type="hidden" name="id" value="<?= $id; ?>">
						<input type="hidden" name="date_entry2" value="<?= $update_dated; ?>">

						<div class="modal-footer">
							<button class="btn  btn-primary" name="save_edit_note_btn">Save</button>
							&nbsp; &nbsp;&nbsp; &nbsp;|
							&nbsp; &nbsp;&nbsp; &nbsp;
							<button class="btn btn-sm btn-danger btn-sm" data-dismiss="modal">Close</button>
						</div>
						<input type="hidden" value="<?= $hospital_no; ?>" name="hospital_no">
					</form>


				</div>
			</div>
		</div>
		<script>
			$(document).ready(() => {
				$('#edit_note_modal2').modal('show');
			});
		</script>

	<?php
	}
	?>

	<script src="../inc/save_vital_script.js"></script>

	<script>
		$(document).on('change', '#filterByMonth', function() {
			const month_year = $(this).val();
			let hospital_no = $(this).attr('hosp');

			$.ajax({
				url: '../inc/_drug_charts.php',
				method: "POST",
				data: {
					hospital_no: hospital_no,
					month_year: month_year
				},
				success: function(response) {
					console.log(response)
					$("#chart_body").html(response);
					toastr.clear();
				},
				error: function(err) {
					console.log(err)
				}
			});

		});

		function delete_investig(sn) {
			// alert('Are you sure you want to Deleted?');

			var result = confirm("Are you sure you want to delete this item?");
			if (result) {
				$.ajax({
					url: "../inc/send_remainder.php",
					method: "POST",
					data: {
						deletete: sn
					},
					success: function(data) {

						var json = JSON.parse(data);
						if (json["status"] == 0) {
							toastr.success(json["message"], 'Success', {
								timeOut: 5000
							});
						} else {
							toastr.error(json["message"], 'Error', {
								timeOut: 5000
							});

						}
					}
				});

			}
		}




		function send_reminder(sn) {
			$.ajax({
				url: "../inc/send_remainder.php",
				method: "POST",
				data: {
					send_reminder: sn
				},
				success: function(data) {
					toastr.info('Specimen Collection and Test Reminder Sent Successful!', 'REMINDER !! ', {
						timeOut: 5000
					})
				}
			});
		}

		function doctor_result_status(sn) {


			var result = confirm("Do you want to document this investigation? If yes (OK), go to the Medical Services tab/page and upload the result.");
			if (result) {
				$.ajax({
					url: "../inc/send_remainder.php",
					method: "POST",
					data: {
						doctor_result_status: sn
					},
					success: function(data) {

						var jsonn = JSON.parse(data);

						if (jsonn["status"] == 1) {
							toastr.success(jsonn["message"], 'Success', {
								timeOut: 5000
							});
							////window.location.href = "patient.php?hosp_no=" + jsonn["hospital_no"] + '&editMedService=' +  jsonn["last_id"] ;
							window.location.href = "patient.php?hosp_no=" + jsonn["hospital_no"] + '&editMedService';

						} else {
							toastr.error(jsonn["message"], 'Attention', {
								timeOut: 5000
							});
						}

					}
				});
			}
		}


		<?php

		if ($error_status == 1) { ?>
			toastr.error('<?php echo $error_msg ?>', 'Error', {
				timeOut: 5000
			})

		<?php } elseif ($error_status == 2) {
		?>
			toastr.success(' <?php echo $error_msg ?> ', 'Success', {
				timeOut: 5000
			})
		<?php

		}

		?>



		function loadPatientStats(hospitalNo, apptNo, patientManageBy) {
			$.ajax({
				url: "patient_stats.php",
				type: "POST",
				data: {
					hospital_no: hospitalNo,
					appt_no: apptNo,
					patient_manage_by: patientManageBy
				},
				dataType: "json",
				success: function(response) {
					if (response.success) {
						$("#totalVisits").text(response.visits);
						$("#lastVisit").text(response.last_visit ? response.last_visit : "N/A");
						$("#totalAdmissions").text(response.admissions);

						if (response.admission_status == 1) {
							$("#_admit_status_").text('Admit Disabled');
							document.getElementById("send-admission-request-btn").style.display = "none";
						}
						// Manage By
						if (response.manage_by.fullname) {
							$("#manageBy").html("<strong>Manage By: </strong><i>" + response.manage_by.fullname + "</i>");
						}
						if (response.manage_by.history) {
							$("#manageHistory").html(response.manage_by.history);

						}
						$("#mgt_by").val(response.manage_by.mgt_by);
					} else {
						alert("Error: " + response.error);
					}
				},
				error: function(xhr, status, error) {
					console.log("AJAX Error:", error);
				}
			});
		}

		// Example usage
		loadPatientStats("<?= $hospital_no ?>", "<?= $appointment_number ?>", "<?= $patient_manage_by ?>");


		$(document).on('click', '#see-a-specialist-notes-btn', function() {

			var hospital_no = document.getElementById("hospital_no").value;
			$.ajax({
				url: "../inc/seeSpecialistNotesModal.php",
				method: "POST",
				data: {
					___hospital_no: hospital_no
				},
				success: function(data) {
					$('#seeSpecialistNotes_body').html(data);
					$('#seeSpecialistNotesModal').modal('show');
				}
			});
		});



		$(document).on('click', '#send-admission-request-btn', function() {
			//alert();
			$('#adm_request').modal('show');
		});


		// $('#patient-edit-alert').hide('slow');

		var fluids_report_loaded = false;
		$(document).on('click', '.fluids_report_', function() {
			var fluids_report_id = $(this).attr("id");
			if (fluids_report_id != '') {
				$('#fluids_report_mdl').modal('show');
				if (fluids_report_loaded == false) {
					$.ajax({
						url: "../nursing/fetch_set2.php",
						method: "POST",
						data: {
							fluids_report_id: fluids_report_id
						},
						success: function(data) {

							// $('.modal-title').text('View All Reports'); 
							$('#fluids_report_body').html(data);
							fluids_report_loaded = true;

						}
					});
				}

			}
		});
	</script>
	<script>
		$(document).ready(function() {
			var search_specialist_result = [];
			$('#typeahead_search_specialist').typeahead({



				source: function(query, query_response) {


					$.ajax({
						url: "controllers/specialist.php",
						method: "POST",
						data: {
							search_specialist: true,
							action: 'search_specialist',
							input_text: $('#typeahead_search_specialist').val()
						},
						dataType: "json",
						success: function(data) {

							search_specialist_result = data;
							query_response($.map(data, function(item) {

								return item.name;

							}));
						}

					})
				},
				updater: function(item) {
					search_specialist_result.forEach(element => {
						if (element.name == item) {
							$('#typeahead_search_specialist_id').val(element.id)
							$('#typeahead_search_specialist_name').val(element.name)
							return item;
						}
					});
					return item
				}
			});

		});
	</script>

	<script>
		$(document).ready(function() {
			window.search_icdcodes_result = [];
			$('#search_icdcodes_input').typeahead({
				minLength: 4, // start searching after 2 characters
				items: 100, // 🔴 IMPORTANT: default is 8, increase it
				autoSelect: false, // optional, prevents auto selection

				source: function(query, process) {
					$.ajax({
						url: "controllers/icdcodes.php",
						method: "POST",
						data: {
							search_icdcodes: true,
							input_text: query,
							group_id: $('#icdcode_group').val()
						},
						dataType: "json",
						success: function(data) {

							// store full results globally if needed later
							window.search_icdcodes_result = data;

							// pass only names to typeahead
							process($.map(data, function(item) {
								return item.name;
							}));
						}
					});
				},

				updater: function(item) {
					return item; // value placed into input
				}
			});
		});





		$(document).ready(function() {
			window.search_icdcodes_result = [];
			$('#search_icdcodes_input_ward_round').typeahead({
				minLength: 4, // start searching after 2 characters
				items: 100, // 🔴 IMPORTANT: default is 8, increase it
				autoSelect: false, // optional, prevents auto selection

				source: function(query, process) {
					$.ajax({
						url: "controllers/icdcodes.php",
						method: "POST",
						data: {
							search_icdcodes: true,
							input_text: query,
							group_id: $('#icdcode_group_ward_round').val()
						},
						dataType: "json",
						success: function(data) {

							// store full results globally if needed later
							window.search_icdcodes_result = data;

							// pass only names to typeahead
							process($.map(data, function(item) {
								return item.name;
							}));
						}
					});
				},

				updater: function(item) {
					return item; // value placed into input
				}
			});
		});


		function check_adm_note_clr() {
			var noteColorElem = document.getElementById("note_color");
			if (noteColorElem) noteColorElem.style.color = 'black';
			var buttonTitleElem = document.getElementById("button_title");
			if (buttonTitleElem && noteColorElem) {
				noteColorElem.innerHTML = buttonTitleElem.value;
			}
		}

		function check_adm_note(appointment_number, hospital_no, adm_status) {
			$.ajax({
				url: "fetch_set.php",
				method: "POST",
				data: {
					appointment_number: appointment_number,
					hospital_no: hospital_no
				},
				success: function(data) {
					var jsonn = JSON.parse(data);

					if (adm_status != 3) {
						var noteColorElem = document.getElementById("note_color");
						var buttonTitleElem = document.getElementById("button_title");

						if (jsonn["status"] == 0) {
							toastr.error('Enter CONSULTATION NOTES & SAVED before you continue ...', 'Empty Notes!', {
								timeOut: 5000
							});
							if (noteColorElem) {
								noteColorElem.style.color = 'red';
								noteColorElem.innerHTML = '<strong>[ Add Consultation Notes ]</strong>';
							}
						} else {
							if (noteColorElem) {
								noteColorElem.style.color = 'black';
								if (buttonTitleElem) {
									noteColorElem.innerHTML = buttonTitleElem.value;
								}
							}
						}

					}
				}
			});
		}

		function refreshEditor(id) {
			$('#' + id).trumbowyg({});
		}

		var __diagnosis__ward_round = [];
		var diagnosisInputList_ward_round = [];
		var diagnosisInputList_input_ward_round = '';


		function addDiagnosis_ward_rounds() {


			var search_icdcodes_input_ward_round = $('#search_icdcodes_input_ward_round').val();
			var comment_ward_round = $('#comment_ward_round').val();
			var comment2_ward_round = $('#comment2_ward_round').val();
			var comment3_ward_round = $('#comment3_ward_round').val();

			if (search_icdcodes_input_ward_round != '') {

				$('#diagnosisInputList_input_ward_round').val($('#diagnosisInputList_input_ward_round').val() + `${search_icdcodes_input_ward_round}<br/>`);
				diagnosisInputList_ward_round.push(search_icdcodes_input_ward_round)
				__diagnosis__ward_round.push(` ${search_icdcodes_input_ward_round} ${(comment_ward_round != ''? '[ '+comment_ward_round+' ]': '')} ${(comment2_ward_round != ''? '[ '+comment2_ward_round+' ]': '')} ${(comment3_ward_round != ''? '[ '+comment3_ward_round+' ]': '')}  <br>`);
				updateDiagnosisInput_ward_round()
				$('#search_icdcodes_input_ward_round').val('');
				$('#diagnosis_group_ward_round').val('');
				$('#comment_ward_round').val('');
				$('#comment2_ward_round').val('');
				$('#comment3_ward_round').val('');
			} else {
				if (search_icdcodes_input_ward_round == '') {
					toastr.error('Enter Diagnosis Data', 'Error', {
						timeOut: 1000
					});
					exit;
				}
			}

		}


		function updateDiagnosisInput_ward_round() {
			var tr = '';
			$('#diagnosisInput_ward_round').val('')
			$('#diagnosisSelected_ward_round').html('')
			__diagnosis__ward_round.forEach((element, index) => {
				$('#diagnosisInput_ward_round').val($('#diagnosisInput_ward_round').val() + element)
				tr += `
					<tr>
						<td>${element}</td>
						<td><a class="btn btn-xs btn-danger" onclick="removeDiagnosis_ward_round(${index})"><i class="fa fa-minus"></i></a></td>
					</tr>
				`;
			});

			if (__diagnosis__ward_round.length > 0) {
				$('#diagnosisSelected_ward_round').html(`<h4>Diagnosis Added: </h4><table class="table" border="1"><tr><th>Diagnosis</th><th></th></tr>${tr}</table>`)
			}


		}

		function removeDiagnosis_ward_round(index) {
			__diagnosis__ward_round.splice(index, 1);
			diagnosisInputList_ward_round.splice(index, 1);
			updateDiagnosisInput_ward_round()

			$('#diagnosisInputList_input_ward_round').val('');
			diagnosisInputList_ward_round.forEach((element, index) => {
				$('#diagnosisInputList_input_ward_round').val($('#diagnosisInputList_input_ward_round').val() + `${element}<br/>`);
			})
		}


		$(document).on('click', '#save-ward_round-note', function() {

			let mgt_notes_wards = $("#mgt_notes_wards").html()
			var note_type_ = document.getElementById("note_type_").value;
			var mode = document.getElementById('mode').value;

			var doctor_id = document.getElementById('doctor_id').value;
			var date_entry = document.getElementById('date_entry').value;
			var date_entry2 = document.getElementById('date_entry2').value;
			var doctor_name = document.getElementById('doctor_name').value;
			var hospital_no = document.getElementById('hospital_no').value;
			var notes_type = document.getElementById('notes_type').value;

			var comment_ward_round = document.getElementById('comment_ward_round').value;
			var comment2_ward_round = document.getElementById('comment2_ward_round').value;
			var comment3_ward_round = document.getElementById('comment3_ward_round').value;
			var diagnosisInput_ward_round = document.getElementById('diagnosisInput_ward_round').value;



			if (mode == 'edit') {

				toastr.info('Please wait...', 'Saving', {
					timeOut: 1000
				})
				var notes_sn = document.getElementById('notes_sn').value;
				$.ajax({
					url: "_ward_review.php",
					method: "POST",
					data: {
						review_note: mgt_notes_wards,
						date_entry: date_entry,
						date_entry2: date_entry2,
						hospital_no: hospital_no,
						notes_type: notes_type,
						edit_notes_update: notes_sn
					},
					success: function(data) {

						var json = JSON.parse(data);
						if (json["status"] == 0) {
							$("#mgt_notes_wards").html(" ")
							toastr.success(json["message"], 'Updated', {
								timeOut: 5000
							});
							document.getElementById('edit___mode').innerHTML = '';
							//show_ward_notes();
							show_ward_notes(1);


						} else {
							toastr.error(json["message"], 'Error', {
								timeOut: 5000
							});

						}
					}
				});

			} else {

				if (note_type_ == '') {
					toastr.error('Select Note Type', 'Invalid Selection', {
						timeOut: 5000
					});
				} else {
					toastr.info('Please wait...', 'Saving', {
						timeOut: 1000
					})
					$.ajax({
						url: "../inc/_patient_post_actions.php",
						method: "POST",
						data: {
							saveMgt_ward_round: true,
							mgt_notes: mgt_notes_wards,
							app_no: window.appointment_number,
							hospital_no: window.hospital_no,
							note_type_: note_type_,
							doctor_id: doctor_id,
							doctor_name: doctor_name,
							diagnosisInput_ward_round: diagnosisInput_ward_round,
							comment_ward_round: comment_ward_round,
							comment2_ward_round: comment2_ward_round,
							comment3_ward_round: comment3_ward_round
						},
						success: function(response) {
							toastr.success(response, 'Attention', {
								timeOut: 1000
							})
							$("#mgt_notes_wards").html(" ");
							$('#diagnosisSelected_ward_round').html('')
							document.getElementById("note_type_").value = '';
							show_ward_notes(1);
						}
					});

				}
			}
			// window.location.href = "patient.php?hosp_no=" + window.hospital_no + '&progress';	
		})

		show_ward_notes(1);

		function show_ward_notes(page_num) {

			//alert();

			$.ajax({
				url: "../inc/_ward_round_notes_hx.php",
				method: "POST",
				data: {
					patient_ward_rounds: window.hospital_no,
					appointment_number: window.appointment_number,
					page_num: page_num
				},
				success: function(data) {
					var el = document.getElementById('data_displayed_ward_round');
					if (el) el.innerHTML = data;
				}
			});
		}

		window.page_num = 1;
		var adm_id = "<?= $adm_id; ?>"
		window.month_year = null;

		function view_charts(month_year = window.month_year, page = window.page_num, drug_name) {

			var hospital_no = document.getElementById('hospital_no').value;
			var appointment_number = document.getElementById('appointment_number').value;
			var show_chart_view = document.getElementById('show_chart_view').value;

			if (show_chart_view == '') {

				toastr.error('Invalid Selection!', 'Attention', {
					timeOut: 2000
				})

			} else {

				toastr.error('Please wait...', 'Processing', {
					timeOut: 40000
				})

				if (show_chart_view == 'Drug') {
					chart_type();


				} else {

					//// other charts
					$.ajax({
						url: "../inc/_other_charts_read_doc.php",
						method: "POST",
						data: {
							hospital_no: hospital_no,
							appointment_number: appointment_number,
							show_chart_view: show_chart_view
						},
						success: function(data) {
							$('.modal-title').text('Patient Chart');
							$('#chart_body').html(data);
							$('#chart_mdl').modal('show');
							toastr.clear();
						}
					});
				}
			}
		}

		function chart_by_adm(admn) {
			adm_id = admn
			chart_type()
		}

		function chart_type(month_year = window.month_year, page = window.page_num, drug_name) {
			window.page_num = page;
			window.month_year = month_year;
			let url_file = "../inc/_drug_charts.php";

			var hospital_no = document.getElementById('hospital_no').value;
			var appointment_number = document.getElementById('appointment_number').value;
			var show_chart_view = document.getElementById('show_chart_view').value;

			if (url_file != '') {
				$("#div_form_chart").show();
				toastr.success('Wait...', 'Waiting .. busy', {
					timeOut: 5000
				});
				$.ajax({
					url: url_file,
					method: "POST",
					data: {
						url_file,
						hospital_no,
						appointment_number,
						month_year: window.month_year,
						page: window.page_num,
						drug_name,
						adm_id
					},
					success: function(response) {
						$('.modal-title').text('Patient Drug Charts');
						$('#chart_body').html(response);
						$('#chart_mdl').modal('show');
						toastr.clear();
					},
					error: function(err) {
						console.log(err)
					}
				});

			} else {
				$("#div_form_chart").hide();
			}

		}

		function edit_progress_note(sn) {

			toastr.warning('Processing', 'Please wait ... ', {
				timeOut: 15000
			});
			$.ajax({
				url: "../inc/_ward_round_notes_hx.php",
				method: "POST",
				data: {
					edit_sn: sn
				},
				success: function(data) {

					/// alert(data);

					var json = JSON.parse(data);
					$("#mgt_notes_wards").html(json["notes"]);
					$("#mode_ward").html(json["notes"]);
					document.getElementById('mode').value = 'edit';
					document.getElementById('notes_sn').value = sn;
					document.getElementById('date_entry').value = json["date_entry"];
					document.getElementById('date_entry2').value = json["date_entry2"];
					document.getElementById('notes_type').value = json["notes_type"];
					document.getElementById('edit___mode').innerHTML = '<strong>[ Edit Note Below ]</strong>';
					toastr.clear();


				}
			});

		}


		$(document).on('click', '.bio_data_link', function() {
			var bio_data_id = $(this).attr("id");
			///	alert();

			if (bio_data_id != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						bio_data_id: bio_data_id
					},
					success: function(data) {

						$('.modal-title').text('Patient Bio - Data');

						$('#bio_data_body').html(data);
						$('#bio_data_modal').modal('show');
					}
				});
			}
		});


		function payNow(sale_sn, target, hospital_no) {

			if (target == 'medical_services') {
				var rr = confirm("Are you sure you want to Pay from Patient's Deposit?");
			} else {
				rr = true;
			}

			if (rr === true) {

				document.getElementById('pay_now' + sale_sn).innerHTML = "Wait ...";
				document.getElementById('pay_now' + sale_sn).disabled = true;

				$.ajax({
					url: "../payfrom_wallet.php",
					method: "POST",
					data: {
						sale_sn: sale_sn
					},
					success: function(data) {

						var jsonn = JSON.parse(data);
						if (jsonn["status"] == 1) {

							document.getElementById("pay_now" + sale_sn).innerHTML = 'Pay from Wallet';
							document.getElementById('pay_now' + sale_sn).disabled = false;
							toastr.error(jsonn["message"], 'Attention', {
								timeOut: 5000
							})
						} else {

							document.getElementById("pay_now" + sale_sn).innerHTML = 'Paid! Please Refresh';
							toastr.success('Successful. Refresh & Start Documentation!', 'Success', {
								timeOut: 5000
							})
						}

					}
				});
			}
		}
	</script>

	<script src="../js/idle.js"></script>
	<script>
		$(document).on('click', '#consumable-btn', function() {
			var hospital_no = document.getElementById('hospital_no_add_consumble').value;
			var appointment_number = null;

			$.ajax({
				url: "../inc/Consumable_Modal.php",
				method: "POST",
				data: {
					patient_show_consumable: hospital_no,
					appointment_number: appointment_number
				},
				success: function(data) {

					$('#Consumable_Modal_body').html(data);
					$('#Consumable_Modal').modal('show');
				}
			});
		});

		function patient_show_consumable() {

			var hospital_no = document.getElementById('hospital_no_add_consumble').value;
			var appointment_number = null;

			$.ajax({
				url: "../inc/fetch_consumable.php",
				method: "POST",
				data: {
					patient_show_consumable: hospital_no,
					appointment_number: appointment_number
				},
				success: function(data) {
					document.getElementById('data_displayed').innerHTML = data;

				}
			});


		}


		function reverse_pay_final(sale_sn) {

			var hosp_no = document.getElementById('hospital_no_add_consumble').value;
			var mode = 'return_all';
			var all_qtyy = 1;
			var specify_qtyy = '';
			var where = 'pharmacy';
			var pay_mode = 'cash';
			var drug_sn = null;
			var EX_or_IN = null;
			var app_no = null;
			var names = null;

			$.ajax({
				url: "../inc/inv_pro.php",
				method: "POST",
				data: {
					reverse_drug: mode,
					hosp_no: hosp_no,
					app_no: app_no,
					sale_sn: sale_sn,
					drug_sn: drug_sn,
					EX_or_IN: EX_or_IN,
					names: names,
					where: where,
					specify_qtyy: specify_qtyy,
					all_qtyy: all_qtyy
				},
				success: function(data) {


					var jsonn = JSON.parse(data);
					if (jsonn["status"] == 1) {

						document.getElementById('reverse_pay_final').disabled = false;
						toastr.error(jsonn["message"], 'Attention', {
							timeOut: 5000
						})
					} else {
						document.getElementById('reverse_pay_final').disabled = true;
						document.getElementById("reverse_pay_final").innerHTML = 'Done';
						toastr.success('Successful', 'Success', {
							timeOut: 5000
						})
					}

				}
			});

		}

		$(document).on('click', '.view_Edit_stock', function() {

			var _inv_id = $(this).attr("id");
			var res = _inv_id.split("__");

			if (_inv_id != '') {
				$.ajax({
					url: "../inc/fetch_consumable.php",
					method: "POST",
					data: {
						view_Edit_stock: res[0] + '__' + res[1]
					},
					success: function(data) {

						$('.modal-title').text('Notes: ' + res[1]);

						$('#view_Edit_stock_body').html(data);
						$('#Consumable_Modal').modal('hide');
						$('#view_Edit_stock_modal').modal('show');
					}
				});
			}


		});


		function save_price() {

			var ap_sn = document.getElementById('ap_sn').value;
			var stock_sn = document.getElementById('stock_sn').value;
			var edit_price = document.getElementById('edit_price').value;

			$.ajax({
				url: "../inc/fetch_consumable.php",
				method: "POST",
				data: {
					change_price: stock_sn,
					edit_price: edit_price,
					ap_sn: ap_sn
				},
				success: function(data) {
					alert(data);
				}
			});


		}


		function delete_xternal(sale_sn) {

			$.ajax({
				url: "../inc/fetch_consumable.php",
				method: "POST",
				data: {
					delete_consumable: sale_sn
				},
				success: function(data) {
					alert(data);
				}
			});

		}




		function dispense_xternal(sale_sn) {

			var rr = confirm("Are you sure you want to Dispense ?");

			if (rr === true) {
				var transaction = 'dispense_only';

				$.ajax({
					url: "../inc/inv_pro.php",
					method: "POST",
					data: {
						dispense_oncredit_external_sales: transaction,
						sale_sn: sale_sn
					},
					success: function(data) {

						alert(data);

					}
				});

			}


		}

		check_for_chat();

		function show_chats() {
			var patient_id;
			$.ajax({
				url: "../inc/pharm_doctor_chats_show.php",
				method: "POST",
				data: {
					patient_id: patient_id
				},
				success: function(data) {

					if (data) { // Check if the response is not empty
						$('#patient_pharm_doctor_chats_modal_body').html(data);
						$('#patient_pharm_doctor_chats_modal').modal('show');
					} else {
						console.error("Empty response from server");
					}
				}
			});
		}


		function check_for_chat() {
			var patient_id; // Replace with the actual patient ID or chat ID

			$.ajax({
				url: "pharm_doctor_chats_patient.php",
				method: "POST",
				data: {
					patient_id: patient_id
				},
				success: function(data) {

					if (data) { // Check if the response is not empty
						try {
							var jsonData = JSON.parse(data);
							if (jsonData["message0"] > 0) {
								document.getElementById('count_chats').innerHTML = '<h5 class="pull-right"><a href="javascript:void(0)" class="text-danger" onclick="show_chats()"><i>New Pharmacy Chats Message (' + jsonData["message0"] + ')</i></a>';
							}
							if (jsonData["message1"] > 0) {
								document.getElementById('count_chats').innerHTML = '<h5 class="pull-right"><a href="javascript:void(0)" class="text-danger" onclick="show_chats()">View Pharmacy Chats</a>';
							}
						} catch (e) {
							console.error("Error parsing JSON response:", e);
						}
					} else {
						console.error("Empty response from server");
					}

				}
			});
		}

		function disable_manage(sn) {
			$.ajax({
				url: "manage_patient_fetch.php",
				method: "POST",
				data: {
					disable_manage: sn
				},
				success: function(data) {
					alert(data);
				}
			});
		}

		function able_manage(sn) {
			$.ajax({
				url: "manage_patient_fetch.php",
				method: "POST",
				data: {
					able_manage: sn
				},
				success: function(data) {
					alert(data);
				}
			});
		}





		function reply_sn_notes(sn) {

			var reply_text = document.getElementById('reply_text').value;
			$.ajax({
				url: "../inc/pharm_doctor_chats.php",
				method: "POST",
				data: {
					sn_reply: sn,
					reply_text: reply_text
				},
				success: function(response) {
					alert(response);
					check_for_chat();
				},
				error: function(err) {
					console.log(err)
				}
			});
		}
	</script>

	<script>
		function resetControls() {
			// Reset regular form fields (dropdowns, inputs, dates)
			document.getElementById('reset_form').reset();

			// Reset Select2 fields
			$('#search_anything')
				.val('')
				.trigger('change')
				.html('<option value=""></option>'); // Ensure empty option is present

			$('#doctor_names')
				.val('')
				.trigger('change')
				.html('<option value=""></option>'); // Same here

			// If you're using Select2 with AJAX, optionally reload options if needed
			// $('#search_anything').select2('open'); // Optional to re-open if needed
		}




		let consultationIntervalId = null;

		function parseServerTimeToMs(timeString) {
			// If it's already an ISO string (date('c')), Date.parse should work.
			let ms = Date.parse(timeString);
			if (!isNaN(ms)) return ms;

			// Fallback: replace space between date and time with 'T' and try again
			const tStr = timeString.replace(' ', 'T');
			ms = Date.parse(tStr);
			if (!isNaN(ms)) return ms;

			// final fallback: try removing timezone fragments or use local parse
			try {
				return (new Date(timeString)).getTime();
			} catch (e) {
				return NaN;
			}
		}

		function startConsultationTimer(startTimeMs) {

			if (isNaN(startTimeMs)) {
				console.error('Invalid start time for timer:', startTimeMs);
				return;
			}

			// clear previous interval if exists
			if (consultationIntervalId) {
				clearInterval(consultationIntervalId);
				consultationIntervalId = null;
			}

			function updateTimer() {
				const now = Date.now();
				let elapsed = now - startTimeMs;
				if (elapsed < 0) elapsed = 0;

				const hours = Math.floor(elapsed / (1000 * 60 * 60));
				const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
				const seconds = Math.floor((elapsed % (1000 * 60)) / 1000);

				const display = String(hours).padStart(2, '0') + ":" +
					String(minutes).padStart(2, '0') + ":" +
					String(seconds).padStart(2, '0');

				const el = document.getElementById('timerDisplay');
				if (el) el.textContent = display;
			}

			updateTimer();
			consultationIntervalId = setInterval(updateTimer, 1000);
		}

		function checkOngoingConsultation() {
			const hosp_no = "<?php echo $hosp_no; ?>";
			const doctor_no = "<?php echo addslashes($doctor_no); ?>";

			$.ajax({
				url: "consultation_queue.php",
				method: "POST",
				dataType: "json",
				data: {
					hospital_no: hosp_no,
					doctor_no: doctor_no,
					check_on_going: true
				},
				success: function(response) {
					console.log('AJAX response:', response);

					// defensive checks
					if (!response) {
						console.error('Empty response from server');
						return;
					}

					// allow both numeric 1 and string '1'
					if (response.status == 1 || response.status === '1' || response.status === true) {
						// parse time safely
						const startTimeMs = parseServerTimeToMs(response.start_time);
						if (isNaN(startTimeMs)) {
							console.error('Could not parse start_time:', response.start_time);
							return;
						}
						startConsultationTimer(startTimeMs);
					} else {

						// if no ongoing consultation, hide the timer div
						const timerDiv = document.querySelector('.consultation-timer');
						if (timerDiv) timerDiv.style.display = 'none';

						// no ongoing consultation: reset timer display and clear interval
						const el = document.getElementById('timerDisplay');
						if (el) el.textContent = '00:00:00';
						if (consultationIntervalId) {
							clearInterval(consultationIntervalId);
							consultationIntervalId = null;
						}
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error('AJAX error:', textStatus, errorThrown, jqXHR.responseText);
					// optional: show message to user
				}
			});
		}

		$(document).ready(function() {
			checkOngoingConsultation();
		});
	</script>

	<?php include('new_results.php'); ?>
	<?php include_once('../inc/ai_toolkit_widget.php'); ?>
	<?php include_once('../inc/autosave_widget.php'); ?>

</body>

</html>