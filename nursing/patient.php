<!DOCTYPE html>
<html>
<?php
include("../inc/session.php");
include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');
include("../inc/credit_current_balance.php");
$setdate = date("Y-m-d");
$setdatetime = date("Y-m-d H:i:s");








if (isset($_POST['change_care_giver_'])) {
	// Get hidden values
	$hospital_no  = $_POST['hosp_no_info'];
	$admission_sn = $_POST['admission_sn'];

	// Get form data
	$care_giver   = $_POST['care_giver_info'];
	$phone_number = $_POST['phone_info'];
	$relationship = $_POST['relation_info'];

	// Build update query using placeholders
	$updateSQL = "UPDATE admission 
             SET care_giver      = :care_giver,
                 care_giver_phone = :phone_number,
                 relationship     = :relationship
             WHERE sn = :sn AND hospital_no = :hospital_no";

	$stmt = $db->prepare($updateSQL);

	// Execute statement safely
	$stmt->execute([
		':care_giver'   => $care_giver,
		':phone_number' => $phone_number,
		':relationship' => $relationship,
		':sn'           => $admission_sn,
		':hospital_no'  => $hospital_no
	]);
}




if (isset($_GET['__sn_'], $_GET['hosp_no'])) {

	$hospital_no  = $_GET['hosp_no'];
	$admission_sn = $_GET['__sn_'];

	// Use prepared statement with placeholders
	$updateSQL = "UPDATE admission 
                  SET admit_type = :admit_type 
                  WHERE sn = :sn AND hospital_no = :hospital_no";

	$stmt = $db->prepare($updateSQL);

	$stmt->execute([
		':admit_type' => 'admit_p',
		':sn'         => $admission_sn,
		':hospital_no' => $hospital_no
	]);

	// Redirect back with modal trigger
	header("Location: patient.php?hosp_no={$hospital_no}&adm_req=1&openmodal=1");
	exit;
}


if (isset($_GET['idxs4444444444dsddsds'])) {
	$id = $_GET['idxs4444444444dsddsds'];
	$hosp_no = $_GET['hosp_no'];
	$sql = "DELETE FROM labour_summary WHERE id = :id";
	$statement = $db->prepare($sql);
	$statement->bindParam(':id', $id, PDO::PARAM_INT);
	if ($statement->execute()) {
		header("location:patient.php?hosp_no=$hosp_no&mess=Record deleted successfully");
	} else {
		header("location:patient.php?hosp_no=$hosp_no&mess=Could not delete the record");
	}
}


if (isset($_POST['labour_save'])) {
	include("../inc/labour_summary.php");
}

include("../inc/header.php");
?>

<script src="../js/jquery-3.1.1.min.js"></script>

<body>
	<div id="wrapper">

		<?php include("nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg">

			<?php include("nav_header.php");  ?>


			<?php
			$first_visit = false;
			$hosp_no = cleanInput($_GET['hosp_no']);

			$hospital_no = null;
			$patient_name = null;
			$sex = null;
			$window_mode = 'patient';


			$patient_info = $Patient->getByHospitalNo($hosp_no);
			$ap_type = 0;
			if (!empty($patient_info)) {
				$dob = $patient_info->dob;
				$visit_status = $patient_info->visit_status;
				$patient_name =  $patient_info->surname . ' ' . $patient_info->fname;
				$sex = $patient_info->gender;
				$insurance_type = $patient_info->insurance_type;
				$interest = $patient_info->interest;
				$token = $patient_info->token;
				$add_minus = $patient_info->add_minus;
				$payment_mode = $patient_info->payment_mode;
				$insurance_no = $patient_info->insurance_no;
				$vip = $patient_info->vip;
				$discharge_request = 0;

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
			} else {
				header('location:../index.php');
				exit;
			}



			///////////////////// CHECK ADMISSION STATUS
			$isOnAdmission = false;
			$admission_info = null;
			$admission_app_no = null;
			$adm_status = null;
			$adm_id = null;
			$stmt = $db->prepare("SELECT * FROM admission as adm  
WHERE (adm_status=3 OR adm_status=0) AND hospital_no = ? order by sn DESC LIMIT 1");
			$stmt->execute(array($hospital_no));
			if ($stmt->rowCount() > 0) {

				$adm_checkin = $stmt->rowCount();
				$emergent_prompt = 0;

				$isOnAdmission = true;
				$admission_info = json_decode(json_encode($stmt->fetch(PDO::FETCH_ASSOC)));
				$appointment_number = $admission_info->app_no;
				$admission_app_no = $appointment_number;
				$adm_id = $admission_info->sn;
				$adm_status = intval($admission_info->adm_status);
				$date_admit = $admission_info->date_admit;
				$discharge_note = $admission_info->discharge_note;
				$doc_incharge = $admission_info->doc_incharge;
				$room_bed_sn = $admission_info->room_bed_sn;
				$admission_sn = $admission_info->sn;
				$care_giver = $admission_info->care_giver;
				$care_giver_phone = $admission_info->care_giver_phone;
				$room_bed = $admission_info->room_bed;
				$relationship = $admission_info->relationship;
				$reason_adm = $admission_info->reason_adm;
				$accom_gen_date = $admission_info->accom_gen_date;
				$billable_ = $admission_info->billable;
				$admit_type = $admission_info->admit_type;
				$discharge_request = 0;



				if ($admission_info->adm_status == '3') { /// on admission
					$stmt = $db->prepare('SELECT hospital_no FROM discharge_fellowup WHERE hospital_no=?');
					$stmt->execute(array($hospital_no));
					if ($stmt->rowCount() > 0) { /// discharge request sent
						$discharge_request = 1;
					} else {
						$discharge_request = 0;
					}
				}

				$emr = $hospital_no;
				$general_credit_limit =	$_SESSION['credit_limit_status'];
				$items = call_current_balance($db, $emr, $general_credit_limit);
				$current_balance =  $items["current_balance"];
				$credit_limit_bal  = $items['bal_credit_limit'];
			} else {
				$adm_checkin = null;
			}

			$cur_date_time = date('Y-m-d H:i:s');
			$appointment_number = null;
			$patientAppoint = null;
			$patient_access_type = null;
			$appointment_interest = null;
			$patient_insurance = null;
			$queue_center = null;
			$app_no_vital = null;
			/////////////////////// CHECK IF PATIENT HAS OPPENED APPOINTMENT, THEN GET THE ///////////////
			/////////// IF THE LOGGED IN USER IS A SPECIALIST


			$patientOnQueuestmt = $db->prepare("SELECT * FROM apptm  
		WHERE hospital_no = '$hospital_no' AND status != 'cancelled' ORDER BY sn desc LIMIT 1 ");
			$patientOnQueuestmt->execute();

			if ($patientOnQueuestmt->rowCount() > 0) {
				$patientAppoint = $patientOnQueuestmt->fetch();
				$appointment_number = $patientAppoint['appt_no'];
				$app_no_vital = $patientAppoint['appt_no'];
				$vital_lock = $patientAppoint['vital_lock'];
				$ap_type = $patientAppoint['ap_type'];
				$patient_insurance = $patientAppoint['insurance'];
				$patient_access_type = $patientAppoint['ap_type'];
				$services_name = $patientAppoint['services_name'];
				$queue_center = $patientAppoint['queue_center'];
				$cr = $patientAppoint['cr'];
				$appointment_interest = $interest;
			}

			$word = "EMERGENCY";
			if (strpos(strtolower($services_name), strtolower($word)) !== false) {
				if ($adm_checkin == null) {
					$stmt = $db->prepare("SELECT adm_status FROM admission as adm  
			WHERE adm_status=:adm_status AND hospital_no = :hospital_no order by sn DESC LIMIT 1");
					$stmt->execute(array(
						':adm_status' => 4,
						':hospital_no' => $hospital_no
					));
					$emergent_prompt = ($stmt->rowCount() > 0) ? 0 : 1;
				}
			}

			$isOnAppoint = false;

			$stmt = $db->prepare("SELECT pay, paystatus,item_services FROM patient_ap_services  
			WHERE  hospital_no = ? AND app_no = ?  AND (serv_group='Consultation' OR cat_type = 'Dialysis' OR cat_type = 'Procedure' OR cat_type = 'Transplant')  ");
			$stmt->execute([$hospital_no, $appointment_number]);

			if ($stmt->rowCount() > 0 || $cr > 0 || $credit_limit_bal > 0) {
				$row = $stmt->fetch();
				$pay = $row['pay'];
				$paystatus = $row['paystatus'];
				$item_services = $row['item_services'];

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

				if ($visit_status == 'new') {
					$stmt = $db->prepare("SELECT 1 FROM patient_ap_services WHERE hospital_no = ? AND paystatus = 0 AND item_services = 'New File'");
					$stmt->execute([$hospital_no]);
					if ($stmt->fetchColumn() && $cr == 0 && $credit_limit_bal == 0) {
						$isOnAppoint = false;
						$error_status = 1;
						$error_msg = 'Alert : PATIENT HAS NOT PAID FOR NEW FILE';
					}
				}
			}


			$current_tab = 'medical_hx';
			$consultation_note_taken = true;


			include_once('../inc/_patient_post_actions.php');




			//if($accom_gen_date != $setdate and $discharge_request == 0 ){
			///	echo '=================================' .  $accom_gen_date;

			if (isset($_GET['bill_stp'])) {

				$error_status = 2;
				$error_msg = 'Success : Auto Billing Stopped Successfully!';
			}

			if (isset($_GET['stopbill']) && $_SESSION['unit_head'] == '1') {
				$remarks = 'auto_deduct';
				$stmtt = $db->query("SELECT sn,access,date_entry,claim_amt,pay,qty,remarks,app_no 
				FROM patient_ap_services 
			WHERE hospital_no='$hospital_no' and remarks='$remarks' and paystatus='0'");

				if ($stmtt->rowCount() > 0) {
					while ($row_rs = $stmtt->fetch(PDO::FETCH_ASSOC)) {
						$pre_claim_amt = $row_rs['claim_amt'];
						$pre_pay = $row_rs['pay'];
						$pre_qty = $row_rs['qty'];
						$service_access_OLD = $row_rs['access'];
						$date_admit = $row_rs['date_entry'];
						$app_no = $row_rs['app_no'];
						$date_admit_sn = $row_rs['sn'];
						$Current_date = date('Y-m-d H:i:s');
						$setdate = date('Y-m-d H:i:s');

						/// bed calculation
						date_default_timezone_set('Africa/Lagos');
						$date1 = new DateTime($Current_date);
						$date2 = new DateTime($date_admit);
						$diff = $date2->diff($date1);
						$hr = $diff->format('%h');
						$day = $diff->format('%a');

						if ($day > 0) {
						} else {
							$day = 1;
						}

						$_pay = $pre_pay / $pre_qty;
						$_claim = $pre_claim_amt / $pre_qty;

						/// new amounts ///
						$new_pay = $_pay * $day;
						$new_claim = $_claim * $day;

						/// add and remarks != 'auto_deduct'
						$updateSQL = "UPDATE patient_ap_services 
		SET invoice_status='1', qty='$day', drug_status='1', remarks='auto_deduct2', claim_amt='$new_claim', pay='$new_pay' WHERE sn='$date_admit_sn'";
						$sql = $db->prepare($updateSQL);
						$sql->execute();
					}

					$billable = 'no'; /// stop
					$updateSQL = 'UPDATE admission SET billable=:billable WHERE sn=:sn_numb';
					$sql = $db->prepare($updateSQL);
					$sql->bindParam(':billable', $billable, PDO::PARAM_STR);
					$sql->bindParam(':sn_numb', $admission_sn, PDO::PARAM_STR);
					$sql->execute();

					if ($sql->rowCount() > 0) {

						$desc = 'Accommodation Billing Stopped';
						$action_to_take = 'Bill Stopped';
						$sql = $db->prepare("INSERT INTO patient_staff_logs (item_sn,descriptions,staff_name,patient_id,action,date_and_time) 
						VALUES (:item_sn,:descriptions,:staff_name,:patient_id,:action,:date_and_time)");
						$sql->bindParam(':item_sn', $admission_sn, PDO::PARAM_STR);
						$sql->bindParam(':descriptions', $desc, PDO::PARAM_STR);
						$sql->bindParam(':staff_name', $_SESSION['fullname'], PDO::PARAM_STR);
						$sql->bindParam(':patient_id', $hospital_no, PDO::PARAM_STR);
						$sql->bindParam(':action', $action_to_take, PDO::PARAM_STR);
						$sql->bindParam(':date_and_time', $Current_date, PDO::PARAM_STR);
						$sql->execute();

						header("location:patient.php?hosp_no=$hospital_no&bill_stp");
					}
				}
			}


			//Needed in amission_script
			$patient_list_arr = [$hospital_no];

			if (isset($_GET["action"])) {
				$current_tab = 'mgt';
			} elseif (isset($_POST['save-vitals-btn']) || isset($_GET['vitals'])) {
				$current_tab = 'vitals';
				$vital_page = !empty($_GET['vitals']) ? $_GET['vitals'] : 0;
			} elseif (
				isset($_POST['change_adm_date_time']) ||
				isset($_GET['adm']) || isset($_GET['adm_req']) ||
				isset($_POST['add_admit']) || isset($_POST['change_adm'])
			) {
				$current_tab = 'adm';
			} elseif (
				isset($_POST['add_med']) || isset($_POST['add_med_chart']) || isset($_POST['add_feeding']) ||
				isset($_POST['add_intake']) || isset($_POST['fluids_remarks']) || isset($_POST['add_output']) ||
				isset($_GET['dv']) || isset($_GET['cl']) || isset($_GET['fn']) || isset($_GET['fd']) || isset($_GET['st']) ||
				isset($_GET['feeding']) || isset($_GET['med_chart']) || isset($_GET['in_out']) ||
				isset($_GET['fluid']) || isset($_GET['adm_note']) || isset($_GET['oxygen']) ||
				isset($_GET['seizure_chart']) || isset($_GET['ward_icu']) ||
				isset($_GET['blood_sugar_chart']) || isset($_GET['blood_sugar']) || isset($_GET['charts']) ||
				isset($_GET['del_drug']) || isset($_GET['del_drug_time']) || isset($_GET['del_drug_time_taken']) ||
				isset($_GET['resume_drug']) || isset($_GET['nursing_care_plan'])
			) {
				$current_tab = 'charts';
			} elseif (isset($_GET['lab'])) {
				$current_tab = 'lab';
				$lab_page = !empty($_GET['lab']) ? $_GET['lab'] : 0;
			}



			?>
			<script>
				var consultation_note_taken = "<?= $consultation_note_taken; ?>";
			</script>


			<div class="wrapper wrapper-content">

				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<?php if ($vip == 1) {
									echo '<h5 style="color:blue; ">VIP PATIENT</h5>';
								} else {
									echo '<h5>Patient Dashboard  </h5>';
								} ?>

							</div>
							<div class="ibox-content">

								<div class="row">
									<div class="col-sm-8 b-r">
										<?php include_once('_patient_dashboard_profile_light.php'); ?>
									</div>
									<div class="col-sm-4">

										<?php include('../inc/tem_bp_alert.php');  ?>
										<?php include_once('_patient_dashboard_links.php'); ?>


									</div>

								</div>


								<hr>

								<?php
								//	if ((strtoupper($services_name) == 'EMERGENCY' or $isOnAppoint == true) and $adm_status != 3) {
								//	$open_documentation = 'yes';
								///} else
								///echo  $_SESSION['dept_group_name'];
								$keywords = array("Antenatal", "ANC", "O&G", "Obstetric", "Gynaecology");
								$found = false;
								foreach ($keywords as $word) {
									if (stripos($item_services, $word) !== false) {
										$found = true;
										break;
									}
								}

								if (
									$_SESSION['dept_group_name'] == 'O&G' || (strcasecmp($_SESSION['Designation'], 'Midwifery') == 0 ||
										strcasecmp($_SESSION['Designation'], 'Matron') == 0)
									&& $queue_center == 'MF'
								) {
									$open_documentation = 'yes';
								} elseif ($found) {
									$open_documentation = 'yes';
								} else {
									$open_documentation = 'no';
								}

								?>

								<div class="row">
									<div class="col-md-8 col-lg-12">
										<div class="light-card">
											<div class="tabs-container">
												<ul class="nav nav-tabs">
													<li id="medical_hx" class="<?= ($current_tab == 'medical_hx' ? "active" : ""); ?>">
														<a data-toggle="tab" href="#med-hx-tab" style="color: black; font-size:15px;"><i class="fa fa-database"></i> [ Medical History ] </a>
													</li>

													<?php if ($admission_info->adm_status == 0  or $admission_count > 0 or  $adm_status == 3) { ?>






														<li class="<?= ($current_tab == 'adm' ? "active" : ""); ?>" onClick="check_billabe('<?= $billable_ ?>','<?= $room_bed_sn ?>','<?= $hosp_no ?>','<?= $admission_sn ?>','<?= $appointment_number ?>')">
															<a data-toggle="tab" href="#adm-tab" style="color: black; font-size:15px;">
																<?php if ($discharge_request == 1) { ?>
																	<b>[ Discharge Here ]</b>
																<?php } elseif ($adm_status == 3) { ?>
																	<i class="fa fa-bed"></i>Nurse Report & Admission
																<?php } else { ?>
																	<i class="fa fa-bed"></i>Admission Data
																<?php } ?>
															</a>
														</li>
													<?php  } ?>

													<?php if ($adm_status == 3) { ?>
														<li class="<?= ($current_tab == 'adm_rounds' ? "active" : ""); ?>"><a data-toggle="tab" href="#adm-tab_doc" style="color: black; font-size:15px;"><i class="fa fa-google-wallet"></i>Doctor's Ward Rounds</a></li>
													<?php  } ?>


													<li class="<?= ($current_tab == 'vitals' ? "active" : ""); ?>"><a data-toggle="tab" href="#vitals-tab" style="color: black; font-size:15px;"><i class="fa fa-google-wallet"></i>VITALS</a></li>

													<li class="<?= ($current_tab == 'lab' ? "active" : ""); ?>"><a data-toggle="tab" href="#lab-tab" style="color: black; font-size:15px;"><i class="fa fa-flask"></i>LAB</a></li>

													<li class="<?= ($current_tab == 'image' ? "active" : ""); ?>"><a data-toggle="tab" href="#imaging-tab" style="color: black; font-size:15px;"> <i class="fa fa-film"></i>IMAGING </a></li>

													<?php //if (($admission_info != null && $adm_status == 3) or isset($_GET['dly']) or ($open_documentation == 'yes')) {
													?>
													<li class="<?= ($current_tab == 'drug' ? "active" : ""); ?>"><a data-toggle="tab" href="#med-tab" style="color: black; font-size:15px;"><i class="fa fa-plus-square"></i>DRUG/PLAN</a></li>
													<?php // }
													?>

													<li class="<?= ($current_tab == 'charts' ? "active" : ""); ?>">
														<a data-toggle="tab" href="#chart-tab" style="color: black; font-size:15px;"><i class="fa fa-magic"></i>CHART</a>
													</li>
													<?php



													if ($open_documentation == 'yes') {
													?>
														<li id="mgt" class="<?= ($current_tab == 'mgt' ? "active" : ""); ?>">
															<a data-toggle="tab" href="#mgt-tab" style="color: black; font-size:15px;"><i class="fa fa-user-md"></i><span id="note_color" style="color:blue; ">DOCUMENTATION</span></a>
														</li>
													<?php } ?>



												</ul>

												<div class="tab-content">

													<div id="med-hx-tab" class="tab-pane <?= ($current_tab == 'medical_hx' ? "active" : ""); ?>">

														<div id="vitals_summary">
															<?php $vitals_hx_url = '../inc/_ward_round_vitals.php'; ?>
														</div>
														<br>
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
															<strong>&nbsp;View Nursing/Midwifery Reports</strong></button>
														&nbsp;| &nbsp;
														<button type="submit" id="ward_round" class="btn btn-xs btn-primary" onClick="general_view2('ward_round','1')">
															<strong>&nbsp;View Ward Round Reports</strong></button>
														&nbsp;| &nbsp;
														<button type="submit" id="physio" class="btn btn-xs btn-info" onClick="general_view2('physio','1')">
															<strong>&nbsp;Physiotherapy Notes</strong></button>
														&nbsp;| &nbsp;
														<button type="submit" id="switch_hx" class="btn btn-xs btn-success" onClick="general_view('all_notes','1')">
															<strong>&nbsp;All Notes</strong></button>
														<br>
														<br>

														<div class="animated fadeInRight">
															<?php include_once('_medication_hx.php'); ?>
														</div>
													</div>


													<?php if ($admission_info->adm_status == 0  or $admission_count > 0 or  $adm_status == 3) { ?>
														<div id="adm-tab" class="tab-pane <?= ($current_tab == 'adm' ? " active" : ""); ?>">
															<div class="animated fadeInRight">
																<div class="">

																	<?php include('../inc/_admission_nurse.php'); ?>
																</div>
															</div>
														</div>
													<?php } ?>


													<?php if ($adm_status == 3) { ?>
														<div id="adm-tab_doc" class="tab-pane <?= ($current_tab == 'adm_rounds' ? " active" : ""); ?>">
															<div class="animated fadeInRight">
																<div class="">
																	<?php include('../inc/_admission_doctor_rounds.php'); ?>
																</div>
															</div>
														</div>
													<?php  } ?>


													<div id="vitals-tab" class="tab-pane  <?= ($current_tab == 'vitals' ? "active" : ""); ?>">
														<div class="panel-body animated fadeInRight">
															<div class="row">
																<div class="col-md-8 b-r">
																	<div id="vitals_hx__wrap"></div>
																</div>

																<div class="col-md-4">
																	<?php include('../inc/vitals.php');

																	if (isset($_POST['save-vitals-btn'])) {

																		$updateSQL = "UPDATE apptm SET vital_lock=1 WHERE hospital_no=:hospital_no and appt_no=:appt_no";
																		$sql = $db->prepare($updateSQL);
																		$sql->bindParam(':hospital_no', $hospital_no, PDO::PARAM_STR);
																		$sql->bindParam(':appt_no', $app_no_vital, PDO::PARAM_STR);
																		$sql->execute();
																	}

																	?>

																</div>
															</div>
														</div>
													</div>

													<div id="lab-tab" class="tab-pane <?= ($current_tab == 'lab' ? "active" : ""); ?>">
														<div class="panel-body animated fadeInRight">
															<div class="">
																<div class="pull-right">

																	<?php if (isset($_GET['dly'])) {
																		$p = $_GET['p'];
																		$d = $_GET['d'];
																	?>
																		<a href="../doctor/dialysis.php?patient=<?= $hospital_no; ?>&p=<?= $p; ?>&d=<?= $d; ?>" class="btn btn-primary">Return to Dialysis </a>
																		&nbsp;&nbsp; | &nbsp;&nbsp;
																	<?php } ?>

																	<?php if (($admission_info != null && $adm_status == 3) or isset($_GET['dly']) or $open_documentation == 'yes') { ?>
																		<button class="btn btn-primary" onclick="openLabTestModal('<?= $hospital_no; ?>', '<?= $appointment_number; ?>', 'Laboratory')" id="add_lab_id"><i class="fa fa-plus-square"></i>&nbsp;&nbsp;Add <u>New</u> Request</button> |

																	<?php }	?>
																	<button class="btn btn-danger labs-on-queue-btn " arial-hospital-no="<?= $hospital_no; ?>" arial-lab-section="Laboratory">Confirm Requests Status</button>
																</div>

																<div><?php
																		$investigations_lab_hx_url = '../doctor/_investigation_hx.php';
																		include_once('../doctor/_investigation_hx.php'); ?></div>
															</div>

														</div>
													</div>
													<div id="imaging-tab" class="tab-pane <?= ($current_tab == 'image' ? "active" : ""); ?>">
														<div class="panel-body animated fadeInRight">
															<div class="pull-right">

																<?php if (($admission_info != null && $adm_status == 3) or $open_documentation == 'yes') { ?>
																	<button class="btn btn-primary" onclick="openLabTestModal('<?= $hospital_no; ?>', '<?= $appointment_number; ?>', 'Radiology')" id="add_rad_id"><i class="fa fa-plus-square"></i>&nbsp;&nbsp;Add <u>New</u> Request</button> |
																<?php }	?>
																<button class="btn btn-danger labs-on-queue-btn " arial-hospital-no="<?= $hospital_no; ?>" arial-lab-section="Radiology">Confirm Requests Status</button>
															</div>
															<div class="">
																<div><?php
																		$investigations_image_hx_url = '../doctor/_imaging_hx.php';
																		include_once('../doctor/_imaging_hx.php'); ?></div>
															</div>

														</div>
													</div>

													<?php ///if (($admission_info != null && $adm_status == 3) or isset($_GET['dly']) or $open_documentation == 'yes') {
													?>

													<div id="med-tab" class="tab-pane <?= ($current_tab == 'drug' ? "active" : ""); ?>">
														<div class="animated fadeInRight"><br>
															<?php if ($isOnAppoint || $admission_info != null) {
																$plan_edit_mode = 1;
															} else {
																$plan_edit_mode = 0;
															}
															///	echo $pla
															?>
															<?php if (isset($_GET['dly'])) {
																$p = $_GET['p'];
																$d = $_GET['d'];
															?>
																<a href="../doctor/dialysis.php?patient=<?= $hospital_no; ?>&p=<?= $p; ?>&d=<?= $d; ?>" class="btn btn-primary btn-sm">Return to Dialysis </a>
																&nbsp;&nbsp; | &nbsp;&nbsp;
															<?php } ?>

															<input type="button" name="edit_users" value="View More Patient's Medication History " data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-sm btn-info view_more_medication_hx" />
															&nbsp; | &nbsp;
															<input type="button" name="edit_users" value="Doctor's Medication Plan" data-target="#modal" id="<?php echo $hospital_no; ?>" class="btn btn-sm btn-success add_view_plan" />






															<div class="well well-sm">

																<div class="">
																	<?php
																	$med_fetch_url = '../doctor/fetch_set.php';
																	$save_drug_url = '../doctor/controllers/_saveMedication.php';
																	$save_investigation_url = '../doctor/controllers/_saveInvestigation.php';
																	include_once("../doctor/components/_medications.php");

																	?>
																</div>


																<div style="max-height:600px; overflow:auto">
																	<?php $drug_plan_hx_url = '_drug_hx.php';
																	include_once('_drug_hx.php'); ?>
																</div>
															</div>

														</div>
													</div>
													<?php /// }
													?>

													<div id="chart-tab" class="tab-pane  <?= ($current_tab == 'charts' ? "active" : ""); ?>">
														<div class="animated fadeInRight"><br>
															<div class="well well-sm">
																<?php ///$drug_plan_hx_url = '_drug_hx.php';

																include_once('../inc/_charts_main.php');
																?>
															</div>

														</div>
													</div>


													<?php if ($open_documentation == 'yes') { ?>
														<div id="mgt-tab" class="tab-pane <?= ($current_tab == 'mgt' ? " active" : ""); ?>">
															<div class="animated fadeInRight">
																<div class="">
																	<div class="light-card">
																		<?php include_once('_appointment_mgt_2.php'); ?>
																	</div>
																</div>
															</div>
														</div>
													<?php } ?>






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


			<div class="modal inmodal fade" id="emergency_alert" tabindex="-1" role="dialog" aria-hidden="true" data-keyboard="false" data-backdrop="static">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h4 class="modal-title" id="">Emergency</h4>
						</div>

						<div class="modal-body" align="center">

							<form action="" method="post">

								<img src="../img/em.png" height="150px" width="150px">
								<h2>This Patient is Not Currently On Admission (Emergency)</h2>

								<hr>
								<h3>Do you want to Send this Patient to Observation? </h3>
								<input type="hidden" name="reason_adm" class="form-control" value="Observation" required>
								<br>

								<button class="btn btn-danger btn" type="submit" name="add_accomodation"
									onclick="return confirm('Are you sure you want to send Admission Request?')">
									<i class="fa fa-arrow"></i>&nbsp;Send Patient to Observation Request</button>

								<input type="hidden" name="created_by_name" value="<?= $_SESSION['fullname']; ?>">
								<input type="hidden" name="hosp_no" value="<?= $hosp_no; ?>">
								<input type="hidden" name="created_by" value="<?= $_SESSION['id']; ?>">
								<input type="hidden" name="appointment_number" value="<?= $appointment_number; ?>">


							</form>

						</div>
					</div>
				</div>
			</div>
			<?php include("../inc/patient_alert.php"); ?>
			<?php include("../inc/footer.php"); ?>
		</div>
	</div>




	<?php

	if ($emergent_prompt == 1 && $_SESSION['h_code'] != 'RHO') { ?>
		<script>
			$(document).ready(function() {
				$("#emergency_alert").modal('show');
			});
		</script>
	<?php  } ?>




	<?php include("../inc/footer_scripts.php"); ?>

	<script src="../js/plugins/chosen/chosen.jquery.js"></script>

	<script src="../js/plugins/datapicker/bootstrap-datepicker.js"></script>
	<script src="../js/plugins/datapicker/select2.full.min.js"></script>
	<script src="../js/jquery-ui.js"></script>


	<script src="../js/typeahead.jquery.min.js"></script>
	<script src="../js/vendors/editor/dist/trumbowyg.js"></script>
	<script src="../js/vendors/editor/plugins/fontsize/trumbowyg.fontsize.js"></script>
	<script src="../js/vendors/editor/plugins/colors/trumbowyg.colors.js"></script>

	<?php if (isset($_GET['vaccine'])) { ?>
		<script>
			$(document).ready(function() {
				$('#immunization-button').click();
			});
		</script>
	<?php } ?>

	<script>
		$(document).on('click', '#see-a-specialist-notes-btn', function() {
			$('#seeSpecialistNotesModal').modal('show');
		});


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

		});
	</script>
	<script>
		function countWords(mgt_notes) {
			var wordsArray = mgt_notes.trim().split(/\s+/);
			return wordsArray.length;
		}


		$(document).on('click', '#save-mgt-button', function() {

			let mgt_notes = $("#mgt_notes").html();

			var wordCount = countWords(mgt_notes);
			///	alert(wordCount);

			if (wordCount <= 5) {
				toastr.error('Please enter at least more three words!', 'Error', {
					timeOut: 1000
				})
				exit;
			}

			var msg_type = document.getElementById("msg_type").value;
			var tag = document.getElementById("tag").value;
			var visit_status = document.getElementById("visit_status").value;
			var doctor_id = document.getElementById("doctor_id").value;
			var doctor_name = document.getElementById("doctor_name").value;


			if (mgt_notes != '') {

				///alert(wordCount);
				/* 	toastr.info('Please wait...', 'Saving', {
						timeOut: 1000
					}) */

				$.ajax({
					url: "_appointment_mgt_2.php",
					method: "POST",
					data: {
						saveMgt: true,
						mgt_notes,
						app_no: window.appointment_number,
						hospital_no: window.hospital_no,
						visit_status,
						doctor_id,
						msg_type,
						tag,
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


		<?php if (isset($_GET['mess'])) { ?>
			toastr.info('<?= $_GET['mess']; ?>', 'Attention', {
				timeOut: 1000
			})
		<?php } ?>

		<?php if (isset($_GET['adm'])) { ?>
			///chk_plan(appointment_number, hospital_no);
		<?php } ?>




		$("#floor").change(function() {

			var floor_id = $(this).val();
			if (floor_id != "") {
				$.ajax({
					url: "get-beds.php",
					data: {
						floor_id: floor_id
					},
					type: 'POST',
					success: function(response) {
						var resp = $.trim(response);
						$("#bed_avail").html(resp);
					}
				});
			} else {
				$("#bed_avail").html("<option value=''>------- Select --------</option>");
			}
		});


		$("#floor2").change(function() {

			var floor_id = $(this).val();
			if (floor_id != "") {
				$.ajax({
					url: "get-beds.php",
					data: {
						floor_id: floor_id
					},
					type: 'POST',
					success: function(response) {
						var resp = $.trim(response);
						$("#room_bed2").html(resp);
					}
				});
			} else {
				$("#room_bed2").html("<option value=''>------- Select --------</option>");
			}
		});




		function chk_plan(appointment_number, hospital_no) {
			$.ajax({
				url: "text_editor2.php",
				method: "POST",
				data: {
					check_plan: true,
					hospital_no: hospital_no
				},
				success: function(data) {
					var jsonn = JSON.parse(data);

					/// alert(data);

					if (jsonn["status"] == 1) {
						$.ajax({
							url: "text_editor2.php",
							method: "POST",
							data: {
								check_plan2: true,
								hospital_no: hospital_no
							},
							success: function(data) {

								/// $('.modal-title').text("DOCTOR PLAN AND TREATMENT"); 
								$('#checkplan_mdl').modal('show');
								$('#checkplan_body').html(data);
							}
						});

					}
				}
			});
		}
	</script>

	<script>
		$(document).on('click', '.previous_adm_reason', function() {

			var prv_adm_hx = $(this).attr("id");
			$.ajax({
				url: "../doctor/_ward_review.php",
				method: "POST",
				data: {
					prv_adm_hx: prv_adm_hx
				},
				success: function(data) {

					$('.modal-title').text("Previous Admission History ... ");
					$('#previous_adm_reason_mdl').modal('show');
					$('#previous_adm_reason_body').html(data);
				}
			});
		});








		$(document).on('click', '#new-allergy-btn', function() {
			$('#newPatientAllergiesModal').modal('show');
		});



		function edit_note(sn) {

			$.ajax({
				url: "../inc/plan_add_view.php",
				method: "POST",
				data: {
					edit_number_prgress: sn
				},
				success: function(data) {
					//// alert(data);

					/// messageData = $('#summernote').summernote('code');
					///document.getElementById("myContent").innerHTML = $("#myeditor").summernote("code");

					var json = JSON.parse(data);
					///document.getElementById("mgt_notes").html = json['notes']; 
					//document.getElementById("mgt_notes").code(json['notes'].template); 

					$('.mgt_notes').code(data.results[0].template);
					document.getElementById("notes_sn").value = json['notes_sn'];
					document.getElementById("mode_typpe").value = 'progress_edit_mode';

					/// window.location.reload(true);
					//	
				}
			});
		}

		function check_billabe(billable, room_bed_sn, hosp_no, admission_sn, appointment_number) {
			/// 
			if (billable == 'no') {
				$.ajax({
					url: "../inc/billable_room.php",
					method: "POST",
					data: {
						hosp_no: hosp_no,
						room_bed_sn: room_bed_sn,
						admission_sn: admission_sn,
						appointment_number: appointment_number
					},
					success: function(data) {

						$("#billable_room_form").html(data);
						$('#billable_room').modal('show');
						toastr.clear();
					}
				});
			}
		}



		function load_template() {

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

					$("#mgt_notes").html(data);
					toastr.clear();
				}
			});
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


		show_ward_notes(1);


		function show_ward_notes(page_num) {

			var appointment_number = document.getElementById('appointment_number').value;
			///alert(appointment_number);
			$.ajax({
				url: "../inc/_ward_round_notes_hx.php",
				method: "POST",
				data: {
					patient_ward_rounds: window.hospital_no,
					appointment_number: appointment_number,
					page_num: page_num
				},
				success: function(data) {
					document.getElementById('data_displayed_ward_round').innerHTML = data;
				}
			});
		}




		$(document).on('click', '#save-patient-alert_2', function() {

			var alert_note = $('#patient-alert-notes').val();
			var hospital_no = $('#patient_alert_hospital_no').val();
			$.ajax({
				url: "../inc/set_alert_patients.php",
				method: "POST",
				data: {
					alert: alert_note,
					hospital_no: hospital_no,
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


		function display_alert(hosp_no) {
			$.ajax({
				url: "../inc/set_alert_patients.php",
				method: "POST",
				data: {
					check_alert: hosp_no
				},
				success: function(data) {
					document.getElementById("patient-alert-tbody").innerHTML = data;
				}
			});
		}

		function openModal_fx(id) {
			$('#' + id).modal('show');
		}


		$(document).on('click', '.discharge_patient', function() {

			var discharge_patient_id = $(this).attr("id");
			if (discharge_patient_id != '') {
				$.ajax({
					url: "discharge.php",
					method: "POST",
					data: {
						discharge_patient_id: discharge_patient_id
					},
					success: function(data) {


						$('#discharge_patient_body').html(data);
						$('#discharge_patient_mdl').modal('show');
					}
				});
			}
		});

		$(document).on('click', '.chart_me', function() {

			var hosp_number = $(this).attr("id");
			$.ajax({
				url: "_drug_charts.php",
				method: "POST",
				data: {
					medication_hx_hosp: hosp_number
				},
				success: function(data) {

					$('.modal-title').text("Medication Chart");
					$('#chart_me_mdl').modal('show');
					$('#chart_me_body').html(data);
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
			var appointment_number = '00';
			var hosp_number = $(this).attr("id");

			$.ajax({
				url: "../inc/plan_add_view.php",
				method: "POST",
				data: {
					patient_note_preview: hosp_number,
					appointment_number: appointment_number
				},
				success: function(data) {

					/// alert(data);

					$('.modal-title').text("View Doctor's Medication Plans");
					$('#add_view_plan_mdl').modal('show');
					$('#add_view_plan_body').html(data);
				}
			});
		});

		$(document).on('click', '.view_more_medication_hx', function() {

			var hosp_number = $(this).attr("id");
			$.ajax({
				url: "../inc/plan_add_view.php",
				method: "POST",
				data: {
					medication_hx_hosp: hosp_number
				},
				success: function(data) {

					$('.modal-title').text("Medications History Administered to Patient");
					$('#med_hx_mdl').modal('show');
					$('#med_hx_body').html(data);
				}
			});
		});




		$(document).on('click', '#save-progress-note', function() {

			document.getElementById('save-progress-note').disabled = true;
			document.getElementById('save-progress-note').innerHTML = 'Wait...';

			var doctor_id = document.getElementById('doctor_id').value;
			var doctor_name = document.getElementById('doctor_name').value;
			var hospital_no = document.getElementById('hospital_no').value;
			var date_entry = document.getElementById('date_entry').value;
			var date_entry2 = document.getElementById('date_entry2').value;

			let mgt_notes = $("#mgt_notes_nurse").html()
			toastr.info('Please wait...', 'Saving', {
				timeOut: 1000
			})
			var mode = document.getElementById('mode').value;

			if (mode == 'edit') {
				var notes_sn = document.getElementById('notes_sn').value;
				$.ajax({
					url: "../doctor/_ward_review.php",
					method: "POST",
					data: {
						review_note: mgt_notes,
						edit_notes_update: notes_sn,
						doctor_id: doctor_id,
						date_entry: date_entry,
						date_entry2: date_entry2,
						doctor_name: doctor_name
					},
					success: function(data) {


						var json = JSON.parse(data);
						if (json["status"] == 0) {
							$("#mgt_notes_nurse").html(" ");
							toastr.success(json["message"], 'Updated', {
								timeOut: 5000
							});
							//	window.location.href = 'patient.php?hosp_no=' + hospital_no + '&adm';

							load_nurse_progress('<?= $appointment_number; ?>', 1);
							document.getElementById('save-progress-note').disabled = false;
							document.getElementById('save-progress-note').innerHTML = 'Save Note';


						} else {
							toastr.error(json["message"], 'Error', {
								timeOut: 5000
							});

							document.getElementById('save-progress-note').disabled = false;
							document.getElementById('save-progress-note').innerHTML = 'Save Note';
						}

					}
				});

			} else {

				$.ajax({
					url: "../inc/_patient_post_actions.php",
					method: "POST",
					data: {
						saveMgt: true,
						mgt_notes: mgt_notes,
						app_no: window.appointment_number,
						hospital_no: window.hospital_no,
						doctor_id: doctor_id,
						doctor_name: doctor_name
					},
					success: function(response) {
						toastr.success(response, 'Attention', {
							timeOut: 1000
						})
						$("#mgt_notes_nurse").html(" ")
						///window.location.href = 'patient.php?hosp_no=' + hospital_no + '&adm';
						load_nurse_progress('<?= $appointment_number; ?>', 1);
						document.getElementById('save-progress-note').disabled = false;
						document.getElementById('save-progress-note').innerHTML = 'Save Note';
					}
				});


				////window.location.href = 'patient.php?hosp_no=' + hospital_no + '&adm';	
			}
		})

		function med_hx_refresh() {

			$.ajax({
				url: "_medication_hx.php",
				method: "POST",
				data: {
					loadMedicalHx: true,
					hospital_no: "<?php echo $hospital_no; ?>"
				},
				success: function(response) {
					$("#medical-history-content-area").html(response);

				},
				error: function(err) {
					console.log(err)
				}
			});

		}

		load_nurse_progress('<?= $appointment_number; ?>', 1);

		function load_nurse_progress(appointment_number, page_num) {

			$("#_progress_notes_hx").html('<h3 style="color:red; ">Please Wait .... </h3>');

			$.ajax({
				url: "../inc/_progress_notes_hx.php",
				method: "POST",
				data: {
					loadProgressNote: true,
					page_num: page_num,
					appointment_number: appointment_number,
					hospital_no: "<?php echo $hospital_no; ?>"
				},
				success: function(response) {

					$("#_progress_notes_hx").html(response);

					$('html, body').animate({
						scrollTop: $('#_progress_notes_hx').offset().top - 100
					}, 500);

				},
				error: function(err) {
					console.log(err)
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



		function edit_progress_note(sn) {

			$.ajax({
				url: "../doctor/_ward_review.php",
				method: "POST",
				data: {
					edit_sn: sn
				},
				success: function(data) {

					var json = JSON.parse(data);
					$("#mgt_notes_nurse").html(json["notes"]);
					$("#mode").html(json["notes"]);
					document.getElementById('mode').value = 'edit';
					document.getElementById('notes_sn').value = sn;
					document.getElementById('date_entry').value = json["date_entry"];
					document.getElementById('date_entry2').value = json["date_entry2"];
					document.getElementById('edit__mode').innerHTML = '<strong>[ Edit Note Below ]</strong>';


				}
			});

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
	</script>

	<?php
	$investigations_controller_url = '../doctor/controllers/_investigations.php';
	$medication_controller_url = '../doctor/controllers/_medication.php';

	$investigations_hx_url = '../doctor/_investigation_hx.php';
	include_once('../doctor/_medication_investigation_modal.php');
	include('../doctor/_medical_hx_scripts.php');
	include('_drug_plan_scripts.php');
	include('_vitals_script.php');
	include_once('_admit_patient_modal.php');
	include('../inc/_other_modals.php');
	include('../modal_lock.php');
	include_once('_immunization_modal.php');


	?>
	<script src="../inc/save_vital_script.js"></script>

	<script>
		<?php

		if (isset($_GET['svr_nt'])) { ?>
			toastr.error('Nursing Services Not Selected!', 'Nursing Services Error', {
				timeOut: 2000
			})
		<?php }

		if ($error_status == 1 || isset($_GET['error'])) { ?>
			<?php if (isset($_GET['error'])) {
				$error_msg = $_GET['error'];
			} ?>
			toastr.error('<?php echo addslashes($error_msg); ?>', 'Error', {
				timeOut: 5000
			});

		<?php } elseif ($error_status == 2 || isset($_GET['save'])) {
			if (isset($_GET['save'])) {
				$error_msg = $_GET['save'];
			}
		?>
			toastr.success(' <?php echo $error_msg ?> ', 'Success', {
				timeOut: 5000
			})
		<?php

		}
		?>


		$(document).on('click', '#send-admission-request-btn', function() {
			//alert();
			$('#adm_request').modal('show');
		});



		$(document).on('click', '.labour_summary', function() {

			var labour_summary = $(this).attr("id");
			if (labour_summary != '') {
				$.ajax({
					url: "fetch_set.php",
					method: "POST",
					data: {
						labour_summary: labour_summary
					},
					success: function(data) {
						$('.modal-title').text('Labour Summary');
						$('#bio_data_body').html(data);
						$('#bio_data_modal').modal('show');
					}
				});
			}
		});





		$(document).on('click', '.bio_data_link', function() {
			var bio_data_id = $(this).attr("id");
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

		<?php if ($billable_ == 'yes') { ?>
			process_accomodation_invoice();
			process_accomodation_invoice2();
			process_accomodation_invoice3();
		<?php } ?>


		function process_accomodation_invoice() {

			$.ajax({
				url: "../auth_drop_adm_invoice_tag_only.php",
				method: "POST",
				data: {
					generate_bed_invoice: true,
					hospital_no: "<?php echo $emr; ?>"
				},
				success: function(data) {
					///alert(data);
				}
			});
		}

		function process_accomodation_invoice2() {
			$.ajax({
				url: "../auth_drop_adm_invoice_bed_only.php",
				method: "POST",
				data: {
					generate_bed_invoice: true,
					hospital_no: "<?php echo $hospital_no; ?>"
				},
				success: function(data) {
					///alert(data);
				}
			});

		}

		function process_accomodation_invoice3() {
			$.ajax({
				url: "../auth_drop_adm_invoice_others_only.php",
				method: "POST",
				data: {
					generate_bed_invoice: true,
					hospital_no: "<?php echo $hospital_no; ?>"
				},
				success: function(data) {
					///alert(data);
				}
			});


		}
	</script>


	<script src="../js/idle.js"></script>



</body>

</html>