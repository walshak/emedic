<?php include("../Connections/Conn.php"); ?>

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
					<h2>Transaction Reports</h2>
					<ol class="breadcrumb">
						<li>
							<a href="index.html">Home</a>
						</li>
						<li class="active">
							<strong>Reports</strong>
						</li>
					</ol>
				</div>
				<div class="col-lg-2">

				</div>
			</div>

			<div class="wrapper wrapper-content animated fadeInRight">
				<div class="row">

					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Auto Generate Reports</h5>
								<div class="ibox-tools">

								</div>
							</div>
							<div class="ibox-content">

								<div class="row">
									<div class="col-sm-5 b-r">
										<h3 class="m-t-none m-b"></h3>

										<form method="POST">
											<div class="form_sep">
												<label for="reg_input_no" class="">Select Preferred Report</label>
												<select name="Referred_select" id="Referred_select" class="form-control">
													<option selected="selected" value="">Select...</option>
													<option value="receptionist">POS/Cash Recieved by Receptionist On Duty</option>
													<option value="Department">Reports by Departments</option>
													<option value="Requester">Requester (Doctors & Self Requests)</option>
													<option value="Physician">Requesting Physician</option>
													<option value="Approvers">Approvers</option>
													<option value="Staff">Staff(Lab. Staff / X-Ray Technicians)</option>
													<option value="investigation">Report by Investigations</option>
													<option value="Referrals">Referrals</option>
													<option value="Buz">External & Internal Patients</option>
													<option value="Credit">Tests Done On-Credit</option>
												</select>
											</div>


											<div class="form_sep" id="dept">
												<?php
												$query_rstSelect = $db->query("SELECT * FROM department WHERE department_type='Radiology' or department_type='Laboratory'");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Department</label>
												<select name="Department" id="Department" class="form-control">
													<option selected="selected" value="">Select ...</option>
													<option value="All">All Department</option>
													<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstdepartment['sn'] . '/' . $row_rstdepartment["department"]; ?>"><?php echo $row_rstdepartment["department"]; ?></option>
													<?php } ?>
												</select>
											</div>


											<div class="form_sep" id="requester">
												<?php
												$query_rstSelect = $db->query("SELECT DISTINCT request_by FROM lab_manage where request_by!='' order by request_by");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Requester</label>
												<select name="request_by" id="request_by" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>
													<option value="All">All Requesters</option>

													<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstdepartment['request_by']; ?>"><?php echo $row_rstdepartment["request_by"]; ?></option>
													<?php } ?>
												</select>
											</div>


											<div class="form_sep" id="phy">
												<?php
												$query_rstSelect = $db->query("SELECT DISTINCT requesting_physician FROM lab_manage where requesting_physician!='' order by requesting_physician");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Requesting Physician</label>
												<select name="physician" id="physician" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>
													<option value="All">All Requesting Physicians</option>

													<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstdepartment['requesting_physician']; ?>"><?php echo $row_rstdepartment["requesting_physician"]; ?></option>
													<?php } ?>
												</select>
											</div>



											<div class="form_sep" id="approvers">
												<?php
												$query_rstSelect = $db->query("SELECT DISTINCT approved_by FROM lab_manage where approved_by!='' order by approved_by");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Approvers</label>
												<select name="approved_by" id="approved_by" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>
													<option value="All">All Approvers</option>

													<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstdepartment['approved_by']; ?>"><?php echo $row_rstdepartment["approved_by"]; ?></option>
													<?php } ?>
												</select>
											</div>


											<div class="form_sep" id="staff">
												<?php
												$query_rstSelect = $db->query("SELECT DISTINCT lab_sci_name FROM lab_manage order by lab_sci_name");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Staff</label>
												<select name="staff" id="staff" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>

													<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstdepartment['lab_sci_name']; ?>"><?php echo $row_rstdepartment["lab_sci_name"]; ?></option>
													<?php } ?>
												</select>
											</div>

											<div class="form_sep" id="recep">
												<?php
												$query_rstSelect = $db->query("SELECT u.username, u.fullname FROM admin_users as u inner join invsti_users as i on u.username=i.username order by fullname");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Receptionist On Duty</label>
												<select name="receptionist" id="receptionist" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>

													<?php while ($rwcep = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $rwcep['fullname']; ?>"><?php echo $rwcep["fullname"]; ?></option>
													<?php } ?>
												</select>
											</div>


											<div class="form_sep" id="invest">
												<?php
												$query_rstSelect = $db->query("SELECT distinct test_name FROM lab_manage order by test_name");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Report by Investigations</label>
												<select name="investigation" id="investigation" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>
													<option value="All">All Investigations</option>


													<?php while ($rwcep = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $rwcep['test_name']; ?>"><?php echo $rwcep["test_name"]; ?></option>
													<?php } ?>
												</select>
											</div>

											<div class="form_sep" id="referrals">
												<?php
												$query_rstSelect = $db->query("SELECT DISTINCT referral FROM lab_manage order by referral");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Referral</label>
												<select name="referral" id="referral" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>

													<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstdepartment['referral']; ?>"><?php echo $row_rstdepartment["referral"]; ?></option>
													<?php } ?>
												</select>
											</div>

											<div class="form_sep" id="patientType">
												<label for="reg_input_no" class="">Select Patient Type</label>
												<select name="patient_type" id="patient_type" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>
													<option value="IN">Hospital Patients</option>
													<option value="EX">External Patients</option>
												</select>
											</div>
									</div>


									<div class="col-sm-7">
										<h4></h4>

										<div id="other_parameter">

											<div class="form_sep" id="data_5">
												<label class="font-noraml"><strong>Select Dates Report </strong></label>
												<div class="input-daterange input-group" id="datepicker">
													<input type="text" class="input-sm form-control" name="start" value="<?php echo date("Y-m-d"); ?>" />
													<span class="input-group-addon">to</span>
													<input type="text" class="input-sm form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
												</div>
											</div>

											<div class="form_sep">
												<label for="reg_input_no" class="">Select Report View</label>
												<select name="report_type" id="report_type" class="form-control" required>
													<option selected="selected" value="">Select...</option>
													<option value="Summary Report">Summary Report</option>
													<option value="Detail Report">Detail Report</option>

												</select>
											</div>

											<div class="form_sep">
												<button class="btn btn-primary btn" type="submit" name="apply_report_btn">Apply</button>
												<div class="pull-right">
													<a href="transc.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh & Search again</a>
												</div>
											</div>

											<br>
											<input type="hidden" name="MM_update" value="adding_cat" />

											</form>

										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-lg-12">
						<div class="ibox ">
							<div class="ibox-title">
								<h5>Data Display</h5>
							</div>
							<div class="ibox-content" id="content">

								<?php




								if (isset($_POST["apply_report_btn"])) {
									$start = $_POST["start"];
									$end = $_POST["end"];
									$operator = $_SESSION["fullname"];



									$stmt_del = $db->prepare("DELETE FROM invsti_transc_rpt WHERE operator = :operator");
									$stmt_del->bindParam(':operator', $operator, PDO::PARAM_STR);
									$stmt_del->execute();

									$get_report = "";
									$error = 0;
									$msg_lock = 0;


									if ($_POST['Referred_select'] == 'Department' and $_POST['Department'] == '') {
										header("location:transc.php?er=Select Departments from list");
									} elseif ($_POST['Referred_select'] == 'Requester' and $_POST['request_by'] == '') {
										header("location:transc.php?er=Select Requester from list");
									} elseif ($_POST['Referred_select'] == 'Staff' and $_POST['staff'] == '') {
										header("location:transc.php?er=Select Staff from list");
									} elseif ($_POST['Referred_select'] == 'Approvers' and $_POST['approved_by'] == '') {
										header("location:transc.php?er=Select Approvers from list");
									} elseif ($_POST['Referred_select'] == 'Referrals' and $_POST['referral'] == '') {
										header("location:transc.php?er=Select Referrals from list");
									} elseif ($_POST['Referred_select'] == 'Physician' and $_POST['physician'] == '') {
										header("location:transc.php?er=Select Requesting Physician from list");
									} elseif ($_POST['Referred_select'] == 'investigation' and $_POST['investigation'] == '') {
										header("location:transc.php?er=Select Investigation from list");
									} elseif ($_POST['Referred_select'] == 'Buz' and $_POST['patient_type'] == '') {
										header("location:transc.php?er=Select Type Patient from list");
									} else {
										$err = '';
										$msg_lock = 1;
									}



									/// department /////  -------------------------------------------------                  

									if (isset($_POST['Department']) and $_POST['Referred_select'] == 'Department') {
										$rpt_type = "Department";
										$search_all = "lab_manage order by lab_cat";
										$distinct = "DISTINCT lab_cat";

										if ($_POST["Department"] != 'All') {
											$post_selection = "One";
											$part = explode("/", $_POST["Department"]);
											$dept_id = $part[0];
											$report_title_part = $part[1];
											$searchTag_part = "L.lab_cat='$dept_id'";
										} else {
											$post_selection = "All_Selection";
										}
									}

									/// Requester /////  -------------------------------------------------   

									if (isset($_POST['request_by']) and $_POST['Referred_select'] == 'Requester' and $_POST['report_type'] != '' and $_POST['request_by'] != '') {
										$rpt_type = "Requester";
										$search_all = "lab_manage where request_by!='' order by request_by";
										$distinct = "DISTINCT request_by";

										if ($_POST["request_by"] != 'All') {
											$post_selection = "One";
											$request_by = $_POST["request_by"];
											$report_title_part = $_POST["request_by"];
											$searchTag_part = "L.request_by='$request_by'";
										}
									}


									/// Requesting Physician /////   -------------------------------------------------   

									if (isset($_POST['physician']) and $_POST['Referred_select'] == 'Physician' and $_POST['report_type'] != '' and $_POST['physician'] != '') {
										$rpt_type = "Physician";
										$search_all = "lab_manage where requesting_physician!='' order by requesting_physician";
										$distinct = "DISTINCT requesting_physician";

										if ($_POST["physician"] != 'All') {
											$post_selection = "One";
											$physician = $_POST["physician"];
											$report_title_part = $_POST["physician"];
											$searchTag_part = "L.requesting_physician='$physician'";
										}
									}

									/// Staff /////  -------------------------------------------------   


									if (isset($_POST['staff']) and $_POST['Referred_select'] == 'Staff') {
										$rpt_type = "Staff";
										$search_all = "lab_manage order by lab_sci_name";
										$distinct = "DISTINCT lab_sci_name";

										if ($_POST["staff"] != 'All') {
											$post_selection = "One";
											$staff = $_POST["staff"];
											$report_title_part = $_POST["staff"];
											$searchTag_part = "L.lab_sci_name='$staff'";
										}
									}


									/// approvers /////  -------------------------------------------------   


									if (isset($_POST['approved_by']) and $_POST['Referred_select'] == 'Approvers') {
										$rpt_type = "Approvers";
										$search_all = "lab_manage  where approved_by!='' order by approved_by";
										$distinct = "DISTINCT approved_by";

										if ($_POST["approved_by"] != 'All') {
											$post_selection = "One";
											$approved_by = $_POST["approved_by"];
											$report_title_part = $_POST["approved_by"];
											$searchTag_part = "L.approved_by='$approved_by'";
										}
									}


									/// investigations /////  -------------------------------------------------   


									if (isset($_POST['investigation']) and $_POST['Referred_select'] == 'investigation') {
										$rpt_type = "Investigations";
										$search_all = "lab_manage  where test_name!='' order by test_name";
										$distinct = "DISTINCT test_name";

										if ($_POST["investigation"] != 'All') {
											$post_selection = "One";
											$investigation = $_POST["investigation"];
											$report_title_part = $_POST["investigation"];
											$searchTag_part = "L.test_name='$investigation'";
										}
									}


									/// receptionist /////  -------------------------------------------------   


									if (isset($_POST['receptionist']) and $_POST['Referred_select'] == 'receptionist') {
										$rpt_type = "Cash Recieved & POS";
										$search_all = "chart_ledger order by prepared_by";
										$distinct = "*";

										if ($_POST["receptionist"] != 'All') {
											$post_selection = "One";
											$receptionist = $_POST["receptionist"];
											$report_title_part = $_POST["receptionist"];
											$searchTag_part = "prepared_by='$receptionist'";
										}
									}


									/// referral /////  -------------------------------------------------   


									if (isset($_POST['referral']) and $_POST['Referred_select'] == 'Referrals') {
										$rpt_type = "Referrals";
										$search_all = "lab_manage where referral!='' order by referral";
										$distinct = "DISTINCT referral";

										if ($_POST["staff"] != 'All') {
											$post_selection = "One";
											$staff = $_POST["referral"];
											$report_title_part = $_POST["referral"];
											$searchTag_part = "L.referral='$staff'";
										}
									}


									/// business_service_center /////  -------------------------------------------------   

									if (isset($_POST['patient_type']) and $_POST['Referred_select'] == 'Buz') {
										$rpt_type = "Buz";
										$search_all = "lab_manage order by sn";
										$distinct = "DISTINCT business_service_center";

										if ($_POST["staff"] != 'All') {
											$post_selection = "One";
											$patient_type = $_POST["patient_type"];
											if ($_POST["patient_type"] == 'IN') {
												$report_title_part = "Internal Patients";
											} else {
												$report_title_part = "External Patients";
											}
											$searchTag_part = "L.business_service_center='$patient_type'";
										}
									}


									/// credit start ============================================

									if (isset($_POST['patient_type']) and $_POST['Referred_select'] == 'Credit') {
										$rpt_type = "credit";
										$credit = 'credit';
										$post_selection = "One";
										$report_title_part = "Credit";
									}
									/////////////// credit END

								?>

									<?php

									if ($_POST['Referred_select'] == 'receptionist') {
										$tableName = "chart_ledger";
									} else {
										$tableName = "lab_manage As L INNER JOIN patient_ap_services as A ON A.drug_sn=L.labrequest_no";
									}



									if ($post_selection == 'One') {



										if ($_POST['Referred_select'] == 'receptionist') {
											$receptionist = $_POST['receptionist'];
											$searchTag = " WHERE prepared_by='$receptionist' AND date_entry2 BETWEEN '$start' AND '$end'";
											$viewer = 'billing';
										} elseif ($_POST['Referred_select'] == 'investigation') {
											$searchTag = "WHERE $searchTag_part AND A.transact_date BETWEEN '$start' AND '$end' order by A.sn";
											$viewer = 'invest';
										} elseif ($_POST['Referred_select'] == 'Credit') {
											$searchTag = "WHERE A.paystatus='0' AND A.cr='1' AND date(A.date_entry) BETWEEN '$start' AND '$end' order by A.sn";
											$viewer = 'lab_manage';
										} else {
											$searchTag = "WHERE $searchTag_part AND A.transact_date BETWEEN '$start' AND '$end' order by A.sn";
											$viewer = 'lab_manage';
										}


										$stmt = build_query($tableName, $searchTag, $viewer);
										$stmt->execute();


										$report_title = $report_title_part . " Reports - Between " .  date("d M Y", strtotime("$start")) .
											" - " . date("d M Y", strtotime("$end"));

										if ($stmt->rowCount() > 0) {
											$total_test_requested = $stmt->rowCount();

											$report_type = $_POST["report_type"];

											if ($viewer == 'billing') {
												include("recep.php");
											}

											if ($viewer == 'invest') {
												$query_type = 'one';
												// Assuming $db is your active PDO connection object
												$stmt_del = $db->prepare("DELETE FROM invsti_count_rpt WHERE operator = :fullname");
												$stmt_del->bindParam(':fullname', $_SESSION["fullname"], PDO::PARAM_STR);
												$stmt_del->execute();

												include("invest.php");
											}

											if ($report_type == 'Summary Report' and $viewer == 'lab_manage') {
												include("summary.php");
											}

											if ($report_type == 'Detail Report' and $viewer == 'lab_manage') {
												include("details.php");
											}


											$print_button_status = 1;
											if ($report_type == 'Summary Report' and $viewer == 'lab_manage') {
												include("summary_print.php");
											}
										} else {
											echo "<H4><strong>No Records To Display</strong><BR><BR>" . $report_title . '</H4>';
											$print_button_status = 0;
										}
									} else {
										// ALL

										// Assuming $db is your active PDO connection object

										// Delete from invsti_transc_rpt table
										$stmt_del1 = $db->prepare("DELETE FROM invsti_transc_rpt WHERE operator = :operator");
										$stmt_del1->bindParam(':operator', $operator, PDO::PARAM_STR);
										$stmt_del1->execute();

										// Delete from invsti_count_rpt table
										$stmt_del2 = $db->prepare("DELETE FROM invsti_count_rpt WHERE operator = :operator");
										$stmt_del2->bindParam(':operator', $operator, PDO::PARAM_STR);
										$stmt_del2->execute();

										$get_report = ""; // Initialize $get_report if needed


										$stmt_master = $db->query("SELECT $distinct FROM $search_all");
										if ($stmt_master->rowCount() > 0) {

											while ($row_dept = $stmt_master->fetch(PDO::FETCH_ASSOC)) {
												if ($rpt_type == "Department") {
													$lab_cat = $row_dept['lab_cat'];
													$report_title = $row_dept['lab_cat'];
													$searchTag_part = "L.lab_cat='$lab_cat'";
												} elseif ($rpt_type == "Requester") {
													$report_title = $row_dept['request_by'];
													$searchTag_part = "L.request_by='$report_title'";
												} elseif ($rpt_type == "Approvers") {
													$report_title = $row_dept['approved_by'];
													$searchTag_part = "L.approved_by='$report_title'";
													//////------------------------																					
												} elseif ($rpt_type == "Physician") {
													$report_title = $row_dept['requesting_physician'];
													$searchTag_part = "L.requesting_physician='$report_title'";
												} elseif ($rpt_type == "Staff") {
													$report_title = $row_dept['lab_sci_name'];
													$searchTag_part = "L.lab_sci_name='$report_title'";
												} elseif ($rpt_type == "Referrals") {
													$report_title = $row_dept['referral'];
													$searchTag_part = "L.referral='$report_title'";
												} elseif ($rpt_type == "Buz") {
													$report_title = $row_dept['business_service_center'];
													$searchTag_part = "L.business_service_center='$report_title'";
												} elseif ($rpt_type == "Investigations") {
													$viewer = 'invest';
													$report_title = $row_dept['test_name'];
													$report_title_part = $row_dept['test_name'];
													$searchTag_part = "L.test_name='$report_title'";
												}

												//	$viewer='lab_manage';				
												$searchTag = "WHERE $searchTag_part AND A.transact_date BETWEEN '$start' AND '$end' order by A.sn";
												$stmt = build_query($tableName, $searchTag, $viewer);
												$stmt->execute();

												if ($stmt->rowCount() > 0) {

													$report_type = $_POST["report_type"];

													if ($viewer == 'invest') {
														$query_type = 'all';
														$total_test_requested = $stmt->rowCount();
														include("invest.php");
													}

													if ($report_type == 'Summary Report' and $viewer != 'invest') {
														include("summary.php");
													}

													if ($report_type == 'Detail Report' and $viewer != 'invest') {
														include("details.php");
													}

													$print_button_status = 1;
												} else {

													$print_button_status = 0;
												}
											}
										} else {
											echo '<strong>No Records to Display</strong>';
										}


										if ($report_type == 'Summary Report') {
											include("summary_print.php");
										}
									}
								}

								if ($msg_lock == 0) {
									if (isset($_GET['er'])) {
										$err = $_GET['er'];
									} ?>
									<strong style="color:#F00"><?php echo $err; ?></strong>

								<?php
								}
								echo "<hr><br>";


								function build_query($tableName, $searchTag, $viewer)
								{
									include("../Connections/Conn.php");
									if ($viewer == 'billing') {
										$stmt = $db->query("SELECT * FROM chart_ledger $searchTag");
										return $stmt;
									} else {
										$stmt = $db->query("SELECT L.*, A.hospital_no,A.claim_amt,A.qty,A.invoice_status,A.pay,A.paystatus,A.cr,A.transact_date FROM $tableName $searchTag");
										return $stmt;
									}
								}
								?>

								<?php if ($print_button_status == 1) { ?>
									<!--	<a href="javascript:Clickheretoprint()" style="font-size:20px;"><button class="btn btn-success btn-sm"><i class="fa fa-print"></i>&nbsp;Print</button></a>
--><?php } ?>

							</div>

						</div>


					</div>
				</div>
			</div>









		</div>
	</div>


	<?php include("search_modal.php"); ?>

	<?php include("../inc/footer_scripts.php"); ?>


	<!-- Data Tables -->
	<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
	<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
	<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
	<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

	<script>
		$(document).ready(function() {
			$("#dept").hide();
			$("#requester").hide();
			$("#staff").hide();
			$("#approvers").hide();
			$("#referrals").hide();
			$("#patientType").hide();
			$("#recep").hide();
			$("#invest").hide();
			$("#other_parameter").hide()
			$("#phy").hide()

			$('#Referred_select').on('change', function() {


				if (this.value == 'Department') {
					$("#dept").show();
					$("#requester").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#recep").hide();
					$("#invest").hide();
					$("#other_parameter").show()
					$("#phy").hide()
				}

				if (this.value == 'Requester') {
					$("#requester").show();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#recep").hide();
					$("#invest").hide();
					$("#other_parameter").show()
					$("#phy").hide()
				}

				if (this.value == 'Buz') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#approvers").hide();
					$("#patientType").show();
					$("#recep").hide();
					$("#invest").hide();
					$("#phy").hide()
					$("#other_parameter").show()
				}

				if (this.value == 'Staff') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").show();
					$("#referrals").hide();
					$("#approvers").hide();
					$("#patientType").hide();
					$("#recep").hide();
					$("#invest").hide();
					$("#phy").hide()
					$("#other_parameter").show()
				}

				if (this.value == 'Referrals') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").show();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#recep").hide();
					$("#invest").hide();
					$("#phy").hide()
					$("#other_parameter").show()
				}


				if (this.value == 'Credit') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#recep").hide();
					$("#invest").hide();
					$("#phy").hide()
					$("#other_parameter").show()
				}

				if (this.value == 'Approvers') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").show();
					$("#invest").hide();
					$("#phy").hide()
					$("#other_parameter").show();
					$("#recep").hide();

				}

				if (this.value == 'receptionist') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#invest").hide();
					$("#phy").hide()
					$("#recep").show();
					$("#other_parameter").show()
				}

				if (this.value == 'investigation') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#recep").hide();
					$("#invest").show();
					$("#phy").hide()
					$("#other_parameter").show()
				}

				if (this.value == 'Physician') {
					$("#requester").hide();
					$("#dept").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#recep").hide();
					$("#invest").hide();
					$("#phy").show()
					$("#other_parameter").show()
				}


				if (this.value == '') {
					$("#dept").hide();
					$("#requester").hide();
					$("#staff").hide();
					$("#referrals").hide();
					$("#patientType").hide();
					$("#approvers").hide();
					$("#invest").hide();
					$("#phy").hide()
					$("#other_parameter").hide()
				}

			});
		});



		var config = {
			'.chosen-select': {},
			'.chosen-select-deselect': {
				allow_single_deselect: true
			},
			'.chosen-select-no-single': {
				disable_search_threshold: 10
			},
			'.chosen-select-no-results': {
				no_results_text: 'Oops, nothing found!'
			},
			'.chosen-select-width': {
				width: "95%"
			}
		}
		for (var selector in config) {
			$(selector).chosen(config[selector]);
		}

		$('#data_5 .input-daterange').datepicker({
			keyboardNavigation: false,
			forceParse: false,
			autoclose: true
		});


		function Clickheretoprint() {
			var disp_setting = "toolbar=yes,location=no,directories=yes,menubar=yes,";
			disp_setting += "scrollbars=yes,width=800, height=400, left=100, top=25";
			var content_vlue = document.getElementById("content").innerHTML;

			var docprint = window.open("", "", disp_setting);
			docprint.document.open();
			docprint.document.write('</head><body onLoad="self.print()" style="width: 800px; font-size: 13px; font-family: arial;">');
			docprint.document.write(content_vlue);
			docprint.document.close();
			docprint.focus();
		}



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
			oTable.$('td').editable('../example_ajax.php', {
				"callback": function(sValue, y) {
					var aPos = oTable.fnGetPosition(this);
					oTable.fnUpdate(sValue, aPos[0], aPos[1]);
				},
				"submitdata": function(value, settings) {
					return {
						"row_id": this.parentNode.getAttribute('id'),
						"column": oTable.fnGetPosition(this)[2]
					};
				},

				"width": "90%",
				"height": "100%"
			});


		});

		function fnClickAddRow() {
			$('#editable').dataTable().fnAddData([
				"Custom row",
				"New row",
				"New row",
				"New row",
				"New row"
			]);

		}
	</script>

</body>

</html>