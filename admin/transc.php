<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
?>
<!DOCTYPE html>
<html>

<?php include("../inc/header.php"); ?>
<?php
$payment_domain = "(p.serv_group='Laboratory' or p.serv_group='Radiology')";
$setdate = date("Y-m-d");

$responsible = $_SESSION['fullname'];
$setdate = date("Y-m-d");
$year = date("Y");
$patient_acct_status = 1;
?>

</head>

<body class="fixed-navigation">

	<div id="wrapper">
		<?php include("../inc/nav_admin_side_bar.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">
			<?php include("../inc/nav_header.php"); ?>

			<?php include("../inc/billing_side_bar.php"); ?>

			<div class="wrapper wrapper-content">



				<div class="row">

					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Transaction Manager</h5>
							</div>

							<div class="ibox-content">
								<div class="row">

									<form method="POST" action="transc.php">
										<div class="col-sm-5 b-r">
											<h3 class="m-t-none m-b"></h3>


											<!--                <form method="POST" action="index.php?transc">
                                    -->
											<div class="form_sep">
												<label for="reg_input_no" class="req">Select Preferred Report</label>
												<select name="Referred_select" id="Referred_select" class="form-control">
													<option selected="selected" value="">Select...</option>
													<option value="receptionist">General Transaction (CASH/POS/TR)</option>
													<option value="Services">Services Category/Sub-Service Category</option>
													<option value="Department">Reports by Departments</option>
													<option value="Requester">Requesters/Staff</option>
													<option value="Buz">External & Internal Patients</option>
													<option value="Credit">Credit Reports</option>
													<option value="Writeoff">Write-off Transaction Reports</option>
													<option value="reverse">Reversed Transaction Reports</option>
													<option value="bill_account">Account Billed to</option>
												</select>

											</div>


											<div class="form_sep" id="dept">
												<?php
												$query_rstSelect = $db->query("SELECT * FROM department WHERE department_type='Radiology' or department_type='Laboratory' or department_type='medical services' or department_type='Pharmacy' order by department");
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
												$query_rstSelect = $db->query("SELECT DISTINCT prepared_by FROM patient_ap_services where prepared_by!='' order by prepared_by");
												$query_rstSelect->execute();
												?>

												<label for="reg_input_no" class="">Select Requesters</label>
												<select name="request_by" id="request_by" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>
													<option value="All">All Requesters</option>

													<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstdepartment['prepared_by']; ?>"><?php echo $row_rstdepartment["prepared_by"]; ?></option>
													<?php } ?>
												</select>
											</div>


											<div class="form_sep" id="services_type">



												<?php
												$query_rstSelect = $db->query("SELECT DISTINCT serv_group FROM patient_ap_services where serv_group!='' order by serv_group");
												$query_rstSelect->execute();
												?>
												<div class="form_sep">
													<label for="reg_input_no" class="req">Services Type</label>
													<select name="services_by" id="services_by" class="form-control" data-required="true">
														<option selected="selected" value="">Select ...</option>
														<option value="All">All Services</option>

														<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $row_rstdepartment['serv_group']; ?>"><?php echo $row_rstdepartment["serv_group"]; ?></option>
														<?php } ?>
													</select>
												</div>


												<?php
												$query_rstSelect = $db->query("SELECT DISTINCT cat_type FROM patient_ap_services where cat_type!='' order by cat_type");
												$query_rstSelect->execute();
												?>
												<div class="form_sep">
													<label for="reg_input_no" class="">Sub-Services Type <strong style="color: red;"> (skip/select)</strong></label>
													<select name="cat_type" id="cat_type" class="form-control" data-required="true">
														<option selected="selected" value="">Select ...</option>
														<?php while ($row_rstdepartment = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $row_rstdepartment['cat_type']; ?>"><?php echo $row_rstdepartment["cat_type"]; ?></option>
														<?php } ?>
													</select>
												</div>

												<div class="form_sep">
													<label for="reg_input_no" class="">Narrow search by Typing Any word <strong style="color: red;"> (skip/select)</strong> </label>
													<input type="text" name="any_service_name" class="form-control" />
												</div>

												<div class="form_sep">
													<label for="reg_select" class="">Transaction Type <strong style="color: red;"> (skip/select)</strong></label>
													<select name="transaction_type" id="transaction_type" class="form-control">
														<option selected="selected" value="">Select...</option>
														<option value="cash">Paid Transaction</option>
														<option value="claim">HMO/Corporate Claims </option>
													</select>
												</div>


											</div>





											<div class="form_sep" id="recep">
												<?php
												//  $query_rstSelect=$db->query("SELECT username, fullname FROM admin_users WHERE rights='RE' order by fullname");
												$query_rstSelect = $db->query("SELECT distinct prepared_by FROM chart_ledger order by prepared_by");
												$query_rstSelect->execute();
												?>

												<div class="form_sep">
													<label for="reg_input_no" class="req">Select Biller/Cashier</label>
													<select name="receptionist" id="receptionist" class="form-control" data-required="true">
														<option selected="selected" value="">Select ...</option>
														<option value="All">All Staff (Recievers)</option>

														<?php while ($rwcep = $query_rstSelect->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $rwcep['prepared_by']; ?>"><?php echo $rwcep["prepared_by"]; ?></option>
														<?php } ?>
													</select>
												</div>


												<div class="form_sep">
													<label for="reg_select" class="">Payment Method</label>
													<select name="payment_method" id="payment_method" class="form-control">
														<option selected="selected" value="">Select...</option>
														<option value="cash">Cash</option>
														<option value="POS">POS </option>
														<option value="transfer">Transfer</option>
													</select>
												</div>


												<div class="form_sep">
													<label for="reg_input_no" class="">Recieving Bank Name</label>
													<select name="bank_name" id="bank_name" class="form-control">
														<option value=''>Select...</option>
														<?php
														$stmt_bnk = $db->query("SELECT distinct bank_name FROM chart_ledger where bank_name!='' order by bank_name");
														while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)) { ?>
															<option value="<?php echo $row_rstbank["bank_name"]; ?>"><?php echo $row_rstbank["bank_name"]; ?></option>
														<?php } ?>
													</select>
												</div>




											</div>

											<div class="form_sep" id="patientType">
												<label for="reg_input_no" class="">Select Patient Type</label>
												<select name="patient_type" id="patient_type" class="form-control" data-required="true">
													<option selected="selected" value="">Select ...</option>
													<option value="IN">Hospital Patients</option>
													<option value="EX">External Patients</option>
												</select>
											</div>


											<div class="form_sep" id="bill_account"><br>
												<label for="reg_input_no" class=""><strong style="color: #F00">Select Account Billed Holder</strong></label>
												<select name="auth_staff" id="auth_staff" class="form-control">
													<option value=''>Select...</option>
													<?php
													$stmt_bnk = $db->query("SELECT * FROM admin_users where bill_account_status=1");
													while ($row_rstbank = $stmt_bnk->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $row_rstbank["EmployeeCode"]; ?>"><?php echo $row_rstbank["fullname"]; ?></option>
													<?php } ?>
												</select>


											</div>


										</div>



										<!--                            ////////////////////////////// division  /////////////////////////////////////-->

										<div class="col-sm-7">
											<h4></h4>

											<div id="other_parameter">

												<div class="form_sep" id="">
													<label class="font-noraml"><strong>Select Dates Report </strong></label>
													<div class="input-daterange input-group" id="" required>
														<input type="date" class="form-control" name="start" value="<?php echo date("Y-m-d"); ?>" />
														<span class="input-group-addon">to</span>
														<input type="date" class="form-control" name="end" value="<?php echo date("Y-m-d"); ?>" />
													</div>
												</div>

												<div class="form_sep">
													<label for="reg_input_no" class="req">Select Report View</label>
													<select name="report_type" id="report_type" class="form-control" required>
														<option selected="selected" value="">Select...</option>
														<option value="Summary Report">Summary Report</option>
														<option value="Detail Report">Detail Report</option>
														<option value="Claim Report">Claim Report</option>

													</select>
												</div>


												<div class="form_sep">
													<button class="btn btn-primary btn" type="submit" name="apply_report_btn">Apply</button>

												</div>

											</div>

											<div class="pull-right">
												<a href="transc.php" class="btn btn-default btn"> <i class="fa fa-refresh"></i>&nbsp; Refresh & Search again</a>
											</div>


										</div>

									</form>


								</div>
							</div>




						</div>
					</div>


				</div>


				<div class="row">


					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Report Panel</h5>
							</div>

							<div class="ibox-content" id="content">

								<?php


								if (isset($_POST["apply_report_btn"])) {
									$start = $_POST["start"];
									$end = $_POST["end"];
									$operator = $_SESSION["fullname"];

									$stmt_del = $db->prepare('DELETE FROM transc_rpt WHERE operator = :operator');
									$stmt_del->bindParam(':operator', $operator);
									$stmt_del->execute();

									$stmt_del = $db->prepare('DELETE FROM invsti_transc_rpt WHERE operator = :operator');
									$stmt_del->bindParam(':operator', $operator);
									$stmt_del->execute();

									$get_report = "";
									$error = 0;
									$msg_lock = 0;


									if ($_POST['Referred_select'] == 'Department' and $_POST['Department'] == '') {
										header("location:transc.php?er=Select Departments from list");
									} elseif ($_POST['Referred_select'] == 'Requester' and $_POST['request_by'] == '') {
										header("location:transc.php?er=Select Requester from list");
									} elseif ($_POST['Referred_select'] == 'Services' and $_POST['services_by'] == '') {
										header("location:transc.php?er=Select Services from list");
									} elseif ($_POST['Referred_select'] == 'Buz' and $_POST['patient_type'] == '') {
										header("location:transc.php?er=Select Type Patient from list");
									} elseif ($_POST['Referred_select'] == 'bill_account' and $_POST['auth_staff'] == '') {
										header("location:transc.php?er=Select Type Bill Account Holder from list");
									} else {
										$err = '';
										$msg_lock = 1;
									}



									/// department /////  -------------------------------------------------                  

									if (isset($_POST['Department']) and $_POST['Referred_select'] == 'Department') {


										$sql = "SELECT * FROM department";
										$stmt = $db->prepare($sql);
										$stmt->execute();

										$departments = array();
										while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
											$departments[$row['sn']] = $row;
										}

										$rpt_type = "Department";
										$search_all = "patient_ap_services order by dept_id";
										$distinct = "DISTINCT dept_id";

										if ($_POST["Department"] != 'All') {
											$post_selection = "One";
											$part = explode("/", $_POST["Department"]);
											$dept_id = $part[0];
											$report_title_part = $part[1];
											$searchTag_part = "dept_id='$dept_id'";
										} else {
											$post_selection = "All_Selection";
										}
									}

									/// Requester /////  -------------------------------------------------   

									if (isset($_POST['request_by']) and $_POST['Referred_select'] == 'Requester' and $_POST['report_type'] != '' and $_POST['request_by'] != '') {
										$rpt_type = "Requester";
										$search_all = "patient_ap_services where prepared_by!='' order by prepared_by";
										$distinct = "DISTINCT prepared_by";

										if ($_POST["request_by"] != 'All') {
											$post_selection = "One";
											$request_by = $_POST["request_by"];
											$report_title_part = $_POST["request_by"];
											$searchTag_part = "prepared_by='$request_by'";
										}
									}


									/// Services /////  -------------------------------------------------   

									if (isset($_POST['services_by']) and $_POST['Referred_select'] == 'Services') {

										$rpt_type = "Services";
										$search_all = "patient_ap_services order by serv_group";
										$distinct = "DISTINCT serv_group";

										if ($_POST["services_by"] != 'All') {
											$post_selection = "One";
											$services_by = $_POST["services_by"];
											$report_title_part = $_POST["services_by"];


											if ($_POST["cat_type"] != '') {
												$cat_type = $_POST["cat_type"];
												$cat_type_search = "and cat_type='$cat_type'";
											} else {
												$cat_type_search = "";
											}

											if ($_POST["any_service_name"] != '') {
												$any_service_name = $_POST["any_service_name"];
												$anyword_search = "and item_services like '%$any_service_name%'";
											} else {
												$anyword_search = "";
											}


											$searchTag_part = "serv_group='$services_by' $cat_type_search $anyword_search ";
										}

										///echo $searchTag_part;

									}


									/// receptionist /////  -------------------------------------------------   


									if (isset($_POST['receptionist']) and $_POST['Referred_select'] == 'receptionist') {
										$rpt_type = "Cash Recieved & POS & Bank Transfer";
										$search_all = "chart_ledger order by prepared_by";
										$distinct = "*";

										if ($_POST["receptionist"] != 'All') {
											$post_selection = "One";
											$receptionist = $_POST["receptionist"];
											$report_title_part = $_POST["receptionist"];
											$searchTag_part = "prepared_by='$receptionist'";
										}
									}


									/// business_service_center /////  -------------------------------------------------   

									if (isset($_POST['patient_type']) and $_POST['Referred_select'] == 'Buz') {
										$rpt_type = "Buz";
										$search_all = "patient_ap_services order by sn";
										//$distinct="DISTINCT *";

										if ($_POST["staff"] != 'All') {
											$post_selection = "One";
											$patient_type = $_POST["patient_type"];
											if ($_POST["patient_type"] == 'IN') {
												$report_title_part = "Internal Patients";
												$searchTag_part = "serv_group!='EX'";
											} else {
												$report_title_part = "External Patients";
												$searchTag_part = "serv_group='EX'";
											}
										}
									}


									/// credit start ============================================

									if (isset($_POST['patient_type']) and $_POST['Referred_select'] == 'Credit') {
										$rpt_type = "credit";
										$credit = 'credit';
										$post_selection = "One";
										$report_title_part = "Credit";
									}


									if (isset($_POST['patient_type']) and $_POST['Referred_select'] == 'Writeoff') {
										$rpt_type = "Writeoff";
										$credit = 'Writeoff';
										$post_selection = "One";
										$report_title_part = "Writeoff";
									}

									if (isset($_POST['patient_type']) and $_POST['Referred_select'] == 'reverse') {
										$rpt_type = "reverse";
										$credit = 'reverse';
										$post_selection = "One";
										$report_title_part = "Reversed";
									}
									/////////////// credit END



									if (isset($_POST['patient_type']) and $_POST['Referred_select'] == 'bill_account') {
										$rpt_type = "bill_account";
										//	$credit='bill_account';
										$post_selection = "One";
										$report_title_part = "Service Billed to Account";
										$auth_staff = $_POST['auth_staff'];
										$searchTag_part = "acct_billed_staff='$auth_staff'";
										//		

									}
								?>

									<?php

									if ($_POST['Referred_select'] == 'receptionist') {
										$tableName = "chart_ledger";
									} else {
										$tableName = "patient_ap_services";
									}


									if (isset($_POST["payment_method"]) and $_POST["payment_method"] != '') {
										$ref_value = $_POST["payment_method"];
										$ref_value_search = " and ref_value ='$ref_value'";
									} else {
										$ref_value_search = '';
									}

									if (isset($_POST["bank_name"]) and $_POST["bank_name"] != '') {
										$bank_name = $_POST["bank_name"];
										$bank_name_search = " and bank_name ='$bank_name'";
									} else {
										$bank_name_search = '';
									}


									if ($tableName == 'chart_ledger' and $post_selection != "One") {


										$receptionist = 'All Receptionist';
										//$searchTag = " WHERE prepared_by='$receptionist' AND date(date_entry2) BETWEEN '$start' AND '$end'";
										///$searchTag2 = "prepared_by='$receptionist' AND date(date_entry2) BETWEEN '$start' AND '$end'";

										$searchTag = " WHERE transc_type='debit' and date(date_entry2) BETWEEN '$start' AND '$end' $ref_value_search $bank_name_search ";
										///$searchTag = " WHERE transc_type='debit' and hospital_no is not null and (insurance_no is null or insurance_no='') and date(date_entry2) BETWEEN '$start' AND '$end' $ref_value_search $bank_name_search ";
										$searchTag2 = "transc_type='debit' and date(date_entry2) BETWEEN '$start' AND '$end' $ref_value_search $bank_name_search";
										//$searchTag2 = "transc_type='debit' and hospital_no is not null and (insurance_no is null or insurance_no='') and  date(date_entry2) BETWEEN '$start' AND '$end' $ref_value_search $bank_name_search";
										$viewer = 'billing';


										$stmt = build_query($tableName, $searchTag, $viewer);
										$stmt->execute();


										$report_title = $report_title_part . " Reports : <br>Between " .  date("d M Y", strtotime("$start")) .
											" - " . date("d M Y", strtotime("$end"));

										if ($stmt->rowCount() > 0) {
											$total_test_requested = $stmt->rowCount();

											$report_type = $_POST["report_type"];
											if ($viewer == 'billing') {
												include("recep.php");
											}
										} else {
											echo '<strong>No Records Found.</strong>';
										}
									} elseif ($post_selection == 'One') {


										if ($_POST['Referred_select'] == 'receptionist') {
											$receptionist = $_POST['receptionist'];
											$searchTag = " WHERE prepared_by='$receptionist' AND date(date_entry2) BETWEEN '$start' AND '$end'";
											$searchTag2 = "prepared_by='$receptionist' AND date(date_entry2) BETWEEN '$start' AND '$end'";
											$viewer = 'billing';
										} elseif ($_POST['Referred_select'] == 'investigation') {
											$searchTag = "WHERE $searchTag_part AND date(A.transact_date) BETWEEN '$start' AND '$end' order by A.sn";
											$viewer = 'invest';
										} elseif ($_POST['Referred_select'] == 'Credit') {
											$searchTag = "WHERE paystatus='0' AND cr='1' AND date(date_entry) BETWEEN '$start' AND '$end' order by sn";
											$viewer = 'ap_services';
										} elseif ($_POST['Referred_select'] == 'Writeoff') {
											$searchTag = "WHERE paystatus='1' and (pay_mode ='writeoff' or wallet_debt_bill_to_acct ='WRF') AND date(date_entry) BETWEEN '$start' AND '$end' order by sn";
											$viewer = 'ap_services';
										} elseif ($_POST['Referred_select'] == 'reverse') {
											$searchTag = "WHERE (paystatus='3' or pay_mode ='reverse') AND date(date_entry) BETWEEN '$start' AND '$end' order by sn";
											$viewer = 'ap_services';
											///$transaction_type ="and pay_mode='writeofff'";

										} elseif ($_POST['Referred_select'] == 'bill_account') {
											$searchTag = "WHERE $searchTag_part AND cr='2' AND date(transact_date) BETWEEN '$start' AND '$end' order by hospital_no";
											$viewer = 'bill_account';
										} else {

											$transaction_type = $_POST['transaction_type'];

											if ($transaction_type != '') {
												$transaction_type = "and pay_mode='$transaction_type'";
											} else {
												$transaction_type = "and pay_mode='cash'";
											}

											$searchTag = "WHERE $searchTag_part AND paystatus=1 and cr=0 $transaction_type and date(transact_date) BETWEEN '$start' AND '$end' order by sn";
											$viewer = 'ap_services';
										}


										$stmt = build_query($tableName, $searchTag, $viewer);
										$stmt->execute();


										$report_title = $report_title_part . " Reports <br> Between " .  date("d M Y", strtotime("$start")) .
											" - " . date("d M Y", strtotime("$end"));

										if ($stmt->rowCount() > 0) {
											$total_test_requested = $stmt->rowCount();

											$report_type = $_POST["report_type"];

											if ($viewer == 'billing') {
												include("recep.php");
											}

											if ($viewer == 'invest') {
												$query_type = 'one';
												$stmt_del = $db->prepare('DELETE FROM invsti_count_rpt WHERE operator = :operator');
												$stmt_del->bindParam(':operator', $_SESSION["fullname"]);
												$stmt_del->execute();
												include("invest.php");
											}

											///	echo $report_type;


											if ($report_type == 'Summary Report' and $viewer == 'ap_services') {
												include("summary.php");
											}

											if ($report_type == 'Detail Report' and $viewer == 'ap_services') {
												include("details.php");
											}

											if (($report_type == 'Detail Report' or $report_type == 'Summary Report') and $viewer == 'bill_account') {
												include("details_bill_account.php");
											}


											$print_button_status = 1;
											if ($report_type == 'Summary Report' and $viewer == 'ap_services') {
												include("summary_print.php");
											}
										} else {
											"<H4><strong>No Records To Display</strong><BR><BR>" . $report_title . '</H4>';
											$print_button_status = 0;
										}
									} else {
										// ALL
										$stmt_del = $db->prepare('DELETE FROM transc_rpt WHERE operator = :operator');
										$stmt_del->bindParam(':operator', $operator);
										$stmt_del->execute();
										$get_report = "";

										///echo $distinct;

										$stmt_master = $db->query("SELECT $distinct FROM $search_all");
										if ($stmt_master->rowCount() > 0) {


											while ($row_dept = $stmt_master->fetch(PDO::FETCH_ASSOC)) {
												if ($rpt_type == "Department") {
													$dept_id = $row_dept['dept_id'];

													if (isset($departments[$dept_id])) {
														$report_title = $departments[$dept_id]['department'];
													}
													///$report_title = $row_dept['dept_id'];
													$searchTag_part = "dept_id='$dept_id'";
												} elseif ($rpt_type == "Requester") {
													$report_title = $row_dept['prepared_by'];
													$searchTag_part = "prepared_by='$report_title'";
												} elseif ($rpt_type == "Services") {


													$transaction_type = $_POST['transaction_type'];


													if ($transaction_type != '') {
														$transaction_type = "and pay_mode='$transaction_type' ";
													} else {
														$transaction_type = "and pay_mode='cash'";;
													}

													echo $report_title = $row_dept['serv_group'];
													echo '<br>';




													if ($_POST["cat_type"] != '') {
														$cat_type = $_POST["cat_type"];
														$cat_type_search = "and cat_type='$cat_type'";
													} else {
														$cat_type_search = "";
													}

													if ($_POST["any_service_name"] != '') {
														$any_service_name = $_POST["any_service_name"];
														$anyword_search = "and item_services like '%$any_service_name%'";
													} else {
														$anyword_search = "";
													}


													$searchTag_part = "serv_group='$services_by' $cat_type_search $anyword_search ";





													//////------------------------																					
												} elseif ($rpt_type == "Buz") {

													if ($_POST["patient_type"] == 'IN') {
														$report_title_part = "Internal Patients";
														$searchTag_part = "serv_group!='EX'";
													} else {
														$report_title_part = "External Patients";
														$searchTag_part = "serv_group='EX'";
													}


													///$report_title=$row_dept['business_service_center'];
													///$searchTag_part = "L.business_service_center='$report_title'";
												}

												//	$viewer='lab_manage';				
												$searchTag = "WHERE $searchTag_part AND date(transact_date) BETWEEN '$start' AND '$end' order by sn";
												$stmt = build_query($tableName, $searchTag, $viewer);
												$stmt->execute();

												if ($stmt->rowCount() > 0) {

													$report_type = $_POST["report_type"];

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
											'<strong>No Records to Display</strong>';
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

										$stmt = $db->query("SELECT * FROM $tableName $searchTag");
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




				<?php include("../inc/footer.php"); ?>

			</div>
		</div>

		<?php include('../modal_lock.php'); ?>
		<?php include("../inc/footer_scripts.php"); ?>

		<!-- Data Tables -->
		<script src="../js/jquery-3.1.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
		<script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

		<script src="../js/plugins/dataTables/datatables.min.js"></script>

		<!-- Custom and plugin javascript -->
		<script src="../js/inspinia.js"></script>
		<script src="../js/plugins/pace/pace.min.js"></script>

		<!-- Page-Level Scripts -->
		<script>
			$(document).ready(function() {
				$('.dataTables-example').DataTable({
					pageLength: 25,
					responsive: true,
					dom: '<"html5buttons"B>lTfgitp',
					buttons: [{
							extend: 'copy'
						},
						{
							extend: 'csv'
						},
						{
							extend: 'excel',
							title: 'ExampleFile'
						},
						{
							extend: 'pdf',
							title: 'ExampleFile'
						},

						{
							extend: 'print',
							customize: function(win) {
								$(win.document.body).addClass('white-bg');
								$(win.document.body).css('font-size', '10px');

								$(win.document.body).find('table')
									.addClass('compact')
									.css('font-size', 'inherit');
							}
						}
					]

				});

			});
		</script>

		<script>
			$(document).ready(function() {
				$("#dept").hide();
				$("#requester").hide();
				$("#services_type").hide();
				$("#approvers").hide();
				$("#referrals").hide();
				$("#patientType").hide();
				$("#recep").hide();
				$("#invest").hide();
				$("#other_parameter").hide()
				$("#phy").hide()
				$("#bill_account").hide()

				$('#Referred_select').on('change', function() {


					if (this.value == 'Department') {
						$("#dept").show();
						$("#requester").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#patientType").hide();
						$("#approvers").hide();
						$("#recep").hide();
						$("#invest").hide();
						$("#other_parameter").show()
						$("#phy").hide()
						$("#bill_account").hide()
					}

					if (this.value == 'Requester') {
						$("#requester").show();
						$("#dept").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#patientType").hide();
						$("#approvers").hide();
						$("#recep").hide();
						$("#invest").hide();
						$("#other_parameter").show()
						$("#phy").hide()
						$("#bill_account").hide()
					}

					if (this.value == 'Buz') {
						$("#requester").hide();
						$("#dept").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#approvers").hide();
						$("#patientType").show();
						$("#recep").hide();
						$("#invest").hide();
						$("#phy").hide()
						$("#bill_account").hide()
						$("#other_parameter").show()
					}



					if (this.value == 'Services') {
						$("#requester").hide();
						$("#dept").hide();
						$("#services_type").show();
						$("#referrals").hide();
						$("#approvers").hide();
						$("#patientType").hide();
						$("#recep").hide();
						$("#invest").hide();
						$("#phy").hide()
						$("#bill_account").hide()
						$("#other_parameter").show()
					}




					if (this.value == 'Credit' || this.value == 'Writeoff' || this.value == 'reverse') {
						$("#requester").hide();
						$("#dept").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#patientType").hide();
						$("#approvers").hide();
						$("#recep").hide();
						$("#invest").hide();
						$("#phy").hide()
						$("#bill_account").hide()
						$("#other_parameter").show()
					}



					if (this.value == 'receptionist') {
						$("#requester").hide();
						$("#dept").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#patientType").hide();
						$("#approvers").hide();
						$("#invest").hide();
						$("#phy").hide()
						$("#bill_account").hide()
						$("#recep").show();
						$("#other_parameter").show()
					}

					if (this.value == 'investigation') {
						$("#requester").hide();
						$("#dept").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#patientType").hide();
						$("#approvers").hide();
						$("#recep").hide();
						$("#invest").show();
						$("#phy").hide()
						$("#bill_account").hide()
						$("#other_parameter").show()
					}

					if (this.value == 'bill_account') {
						$("#requester").hide();
						$("#dept").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#patientType").hide();
						$("#approvers").hide();
						$("#recep").hide();
						$("#invest").hide();
						$("#phy").hide()
						$("#other_parameter").show()
						$("#bill_account").show()
					}


					if (this.value == '') {
						$("#dept").hide();
						$("#requester").hide();
						$("#services_type").hide();
						$("#referrals").hide();
						$("#patientType").hide();
						$("#approvers").hide();
						$("#invest").hide();
						$("#phy").hide()
						$("#other_parameter").hide()
						$("#bill_account").hide()
					}

				});
			});
		</script>

		<script src="../js/idle.js"></script>
</body>

</html>