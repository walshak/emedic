<?php
session_start();
include '../inc/header.php';
include_once("../Connections/Conn.php");

if (isset($_GET['start']) && isset($_GET['end'])) {
	$the_stetment = 1;
	$start = $_GET['start'];
	$end = $_GET['end'];

	$prep = $_GET['prepared_by'];
	$gl_acct = $_GET['account'] ?? '';
	$selected_accounts = $_GET['account'] ?? ''; // Store original array
	$gl_acct = ($selected_accounts) ? implode(',', $selected_accounts) : ''; // Create comma-separated string for IN clause

	$stmt_bnk_33 = $db->prepare("SELECT class_id FROM chart_accounts as c 
	INNER JOIN chart_groups as g ON g.id=c.account_group
	WHERE account_code IN (:accounts)");
	$stmt_bnk_33->bindParam(':accounts', $gl_acct);
	$stmt_bnk_33->execute();
	$rwxc = $stmt_bnk_33->fetch(PDO::FETCH_ASSOC);
	if ($rwxc['class_id'] == '2' or $rwxc['class_id'] == '4') {
		$bal_type = 'Credit';
	} else {
		$bal_type = 'Debit';
	}


	$DR_CR = $_GET['DR_CR'];
	$account_payable = $_GET['account_payable'];
	$account_recievables = $_GET['account_recievables'];
	$patient_deposit = $_GET['patient_deposit'];
	$hmo_dues = $_GET['hmo_dues'];
}
include 'inc/functions.php';

$fiscal_year = get_active_year();

$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');
$classes = $classes->fetchAll(PDO::FETCH_ASSOC);
$emty = '';

if (isset($_POST['account'])) {
	try {
		$accounts = $_POST['account'];
		$amount_dr = $_POST['amount_dr'];
		$amount_cr = $_POST['amount_cr'];
		$narrations = $_POST['narration'];
		$date = $_POST['date'];
		$line_ids = $_POST['line_ids'];

		// Validate: date must fall within the active fiscal year
		if ($date < $fiscal_year['begin'] || $date > $fiscal_year['end']) {
			set_flash_message('<h2>FAILED: Event date must be within the active fiscal year (' . date('M d, Y', strtotime($fiscal_year['begin'])) . ' to ' . date('M d, Y', strtotime($fiscal_year['end'])) . ').</h2>', 'danger');
			header('Location:' . $_SERVER['REQUEST_URI']);
			exit;
		}

		$err = 0;
		$db->beginTransaction();
		for ($i = 0; $i < count($accounts); ++$i) {
			$account = $accounts[$i];
			$amt_dr = $amount_dr[$i];
			$amt_cr = $amount_cr[$i];
			$type = (($amount_dr[$i] > 0) ? 'DEBIT' : 'CREDIT');
			$narration = $narrations[$i];

			$entry = $db->prepare('UPDATE chart_ledger SET account_no = ?, transc_type = ?, dr_amt = ?, cr_amt = ?, date_entry2 = ?, item_services  = ?
				WHERE sn = ?
			');

			if ($entry->execute([$account, $type, $amt_dr, $amt_cr, $date, $narration, $line_ids[$i]])) {
				continue;
			} else {
				++$err;
			}
		}
		$db->commit();

		set_flash_message('Entry Updated');
	} catch (\Exception $e) {
		error_log($e->getMessage());
		$db->rollBack();
		set_flash_message('Failed to update entry', 'danger');
	}
}

if (isset($_GET['del']) && $_GET['del'] != '') {
	try {
		$ref = $_GET['del'];
		$i = $db->prepare("DELETE FROM chart_ledger WHERE lg_ref_no = ? ");
		$i->execute([$ref]);
		set_flash_message('Entry Deleted');
	} catch (\Exception $e) {
		error_log($e->getMessage());
		set_flash_message('Failed to delete entry', 'danger');
	}
}

// Fetch hospital details from the database
$hospital_details_query = 'SELECT * FROM hospital_details LIMIT 1';
$hospital_details_stmt = $db->prepare($hospital_details_query);
$hospital_details_stmt->execute();
$hospital_details = $hospital_details_stmt->fetch(PDO::FETCH_ASSOC);

// Construct the hospital table HTML
$hospital_table = '<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width:100%;">';
$hospital_table .= '<tr><td width="50%" align="left"><img src="../img/logo.png" width="196" height="111"></td>';
$hospital_table .= '<td width="50%" align="right"><div style="font-size:18px; font:Verdana, Geneva, sans-serif"><strong>' . $hospital_details['name'] . '</strong></div>';
$hospital_table .= '<br><div style="font-size:14px">' . $hospital_details['address'] . '<br><br>' . $hospital_details['phones'] . '</div></td></tr>';
$hospital_table .= '</table><br>';

// Define the period text
$period_text = 'PERIOD: [' . date('d M, Y', strtotime($_GET['start'])) . ' - ' . date('d M, Y', strtotime($_GET['end'])) . ']';


?>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php include("nav_side.php"); ?>
		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../inc/nav_header.php'; ?>
			<div class="">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">

								<h5>Accounts Dashboard | List Journals</h5>

							</div>
							<div class="ibox-content">


								<a href="all_journal.php" class="btn btn-default"><i class="fa fa-refresh"></i>&nbsp;REFRESH</a>&nbsp; : &nbsp;
								<a href="index.php" class="btn btn-danger" style="color: black; "><i class="fa fa-close"></i>&nbsp;CLOSE</a>
								&nbsp; : &nbsp;
								<button id="download-full-csv" class="btn btn-success" type="button"><i class="fa fa-download"></i>&nbsp;Download Full CSV (Grouped)</button>
								<button id="download-full-csv-ungrouped" class="btn btn-info" type="button"><i class="fa fa-download"></i>&nbsp;Download Full CSV (Ungrouped)</button>
								<br>
								<br>
								<script>
									// Download Full CSV button handler
									document.addEventListener('DOMContentLoaded', function() {
										document.getElementById('download-full-csv').addEventListener('click', function() {
											// Collect current filter values from the form
											var form = document.querySelector('form.form-inline');
											var params = new URLSearchParams();
											if (form) {
												var formData = new FormData(form);
												// For multi-select (account[]), append all values
												if (formData.getAll('account[]').length > 0) {
													params.append('gl_acct', formData.getAll('account[]').join(','));
												}
												[
													'start', 'end', 'prepared_by', 'DR_CR',
													'account_payable', 'hmo_dues', 'account_recievables', 'patient_deposit'
												].forEach(function(key) {
													var val = formData.get(key);
													if (val) {
														// Map to export script's expected param names
														if (key === 'start') params.append('start_date', val);
														else if (key === 'end') params.append('end_date', val);
														else params.append(key, val);
													}
												});
											}
											var url = 'inc/all_journals_export_csv.php?' + params.toString();
											window.open(url, '_blank');
										});
										document.getElementById('download-full-csv-ungrouped').addEventListener('click', function() {
											var form = document.querySelector('form.form-inline');
											var params = new URLSearchParams();
											if (form) {
												var formData = new FormData(form);
												if (formData.getAll('account[]').length > 0) {
													params.append('gl_acct', formData.getAll('account[]').join(','));
												}
												[
													'start', 'end', 'prepared_by', 'DR_CR',
													'account_payable', 'hmo_dues', 'account_recievables', 'patient_deposit'
												].forEach(function(key) {
													var val = formData.get(key);
													if (val) {
														if (key === 'start') params.append('start_date', val);
														else if (key === 'end') params.append('end_date', val);
														else params.append(key, val);
													}
												});
											}
											var url = 'inc/all_journals_export_csv_unGrouped.php?' + params.toString();
											window.open(url, '_blank');
										});
									});
								</script>


								<form action="" method="get" class="form-inline">

									<table width="100%">
										<tr>
											<td>
												<?php
												$stmt2 = $db->query("SELECT distinct a.account_code, a.account_name
													FROM chart_accounts as a
													INNER JOIN chart_ledger as l ON l.account_no = a.account_code
													WHERE account_no != ''
													ORDER BY account_name
													LIMIT 60");
												?>
												<label for="reg_input_no">Account Name (Optional)</label>
												<select class="chosen-select" id="account" name="account[]" multiple data-placeholder="-- Select Accounts --" style="width:350px;">
													<?php
													$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');
													$classes = $classes->fetchAll(PDO::FETCH_ASSOC);
													?>
													<?php foreach ($classes as $class) { ?>
														<optgroup label="<?php echo $class['class_name']; ?>">
															<?php
															$cid = $class['cid'];
															$groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ? AND inactive = ?');
															$groups->execute([$cid, 0]);
															$groups = $groups->fetchAll(PDO::FETCH_ASSOC);
															?>
															<?php foreach ($groups as $group) { ?>
														<optgroup label="<?php echo $group['name']; ?>">
															<?php
																$group_id = $group['id'];
																$accounts = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ? AND inactive = ?');
																$accounts->execute([$group_id, 0]);
																$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
															?>
															<?php foreach ($accounts as $account) { ?>
																<option value="<?php echo $account["account_code"] ?>" <?php
																														// Check if this account is selected
																														if (isset($selected_accounts) && is_array($selected_accounts)) {
																															foreach ($selected_accounts as $selected) {
																																if ($selected == $account["account_code"]) {
																																	echo 'selected';
																																	break;
																																}
																															}
																														}
																														?>>[<?php echo $account['account_code']; ?>] <?php echo $account['account_name']; ?></option>
															<?php } ?>
														</optgroup>
													<?php } ?>
													</optgroup>
												<?php } ?>
												</select>
											</td>

											<!-- Account Payables Dropdown -->
											<td>
												<label for="reg_input_no">Account Payables</label>
												<select data-placeholder="-- Account  --" class="chosen-select" id="account_payable" name="account_payable" style="width:50px;">
													<option selected value="">-- select --</option>
													<?php
													$accounts = $db->prepare('SELECT distinct s.sn, s.name FROM stock_company as s 
													INNER JOIN chart_ledger as c ON c.insurance_no = s.sn
													LIMIT 60');
													$accounts->execute();
													$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
													?>
													<?php foreach ($accounts as $account) { ?>
														<option value="<?php echo $account['sn']; ?>" <?= (($account["sn"] == $account_payable) ? 'selected' : '') ?>>
															<?php echo $account['name']; ?>
														</option>
													<?php } ?>
												</select>
											</td>

											<!-- Account Receivables Dropdown -->
											<td>
												<label for="reg_input_no">Account Receivables</label>
												<select data-placeholder="-- Account  --" class="chosen-select" id="account_recievables" name="account_recievables" style="width:50px;">
													<option selected value="">-- select --</option>
													<?php
													$accounts = $db->prepare("SELECT distinct e.surname, e.hospital_no, e.fname, e.oname  
													FROM enrollee as e 
													INNER JOIN chart_ledger as l ON e.hospital_no = l.hospital_no 
													WHERE l.hospital_no AND account_no = '1114' AND (e.hospital_no IS NOT NULL OR e.hospital_no != '')
													LIMIT 60");
													$accounts->execute();
													$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
													?>
													<?php foreach ($accounts as $account) { ?>
														<option value="<?php echo $account['hospital_no']; ?>" <?= (($account["hospital_no"] == $account_recievables) ? 'selected' : '') ?>>
															<?php echo $account['surname'] . ' ' . $account['fname'] . ' ' . $account['oname']; ?>
														</option>
													<?php } ?>
												</select>
											</td>

											<!-- HMO Dues Dropdown -->
											<td>
												<label for="reg_input_no">HMO DUES</label>
												<select data-placeholder="-- Account  --" class="chosen-select" id="hmo_dues" name="hmo_dues" style="width:50px;">
													<option value="">-- select HMO--</option>
													<?php
													$accounts = $db->prepare("SELECT distinct s.insurance_no, s.insurance_name 
													FROM insurance_tbl as s 
													INNER JOIN chart_ledger as c ON c.insurance_no = s.insurance_no 
													WHERE s.insurance_no != '1000'
													LIMIT 60");
													$accounts->execute();
													$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
													?>
													<?php foreach ($accounts as $account) { ?>
														<option value="<?php echo $account['insurance_no']; ?>">
															<?php echo $account['insurance_name']; ?>
														</option>
													<?php } ?>
												</select>
											</td>

											<!-- Patient Deposit Dropdown -->
											<td>
												<label for="reg_input_no">Patient Deposit</label>
												<select data-placeholder="-- Account  --" class="chosen-select" id="patient_deposit" name="patient_deposit" style="width:50px;">
													<option selected value="">-- select --</option>
													<?php
													$accounts = $db->prepare("SELECT distinct e.surname, e.hospital_no, e.fname, e.oname  
													FROM enrollee as e 
													INNER JOIN chart_ledger as l ON e.hospital_no = l.hospital_no 
													WHERE l.hospital_no AND account_no = '2121' AND (e.hospital_no IS NOT NULL OR e.hospital_no != '')
													LIMIT 60");
													$accounts->execute();
													$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
													?>
													<?php foreach ($accounts as $account) { ?>
														<option value="<?php echo $account['hospital_no']; ?>" <?= (($account["hospital_no"] == $patient_deposit) ? 'selected' : '') ?>>
															<?php echo $account['surname'] . ' ' . $account['fname'] . ' ' . $account['oname']; ?>
														</option>
													<?php } ?>
												</select>
											</td>

										</tr>
										<tr>
											<td colspan="5">&nbsp;</td>
										</tr>
										<tr>
											<td>
												<?php $stmt2 = $db->query("SELECT distinct prepared_by FROM chart_ledger WHERE prepared_by!='' order by prepared_by"); ?>
												<label for="reg_input_no">Staff (Optional)</label>
												<select data-placeholder="-- Staff  --" class="chosen-select" name="prepared_by" style="width:50px;">
													<option selected value="">-- select --</option>
													<?php while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { ?>
														<option value="<?php echo $roww["prepared_by"]; ?>" <?= (($roww["prepared_by"] == $prep) ? 'selected' : '') ?>><?php echo $roww["prepared_by"]; ?></option>
													<?php } ?>
												</select>
											</td>
											<td>
												<label for="reg_input_no">DEBIT/CREDIT</label><br>
												<select name="DR_CR" class="form-control">
													<option selected value="">-- select --</option>
													<option value="CREDIT" <?= (('CREDIT' == $DR_CR) ? 'selected' : '') ?>>CREDIT</option>
													<option value="DEBIT" <?= (('DEBIT' == $DR_CR) ? 'selected' : '') ?>>DEBIT</option>
												</select>
											</td>
											<td colspan="2">
												<label for="" class="req">Start Date</label>
												<input type="date" name="start" max="<?php echo $fiscal_year['end'] ?>" class="form-control" value="<?php echo isset($_GET['start']) ? $_GET['start'] : date('Y-m-d'); ?>" required>
												&nbsp; : &nbsp;
												<label for="" class="req">End Date</label>
												<input type="date" name="end" class="form-control" value="<?php echo isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');; ?>" required>
											</td>
											<td><button type="submit" class="btn btn-primary">Go</button>
											</td>

										</tr>

									</table>
								</form>

								<div>
									<?php echo show_flash_msg(); ?>
									<?php if (isset($_GET['ref'])) { ?>
										<?php
										$rr = $_GET['ref'];
										$gl_lines = $db->prepare('SELECT * FROM chart_ledger WHERE lg_ref_no = ? AND patient_stt_status!=1');
										$gl_lines->execute([$rr]);

										$gl_lines = $gl_lines->fetchAll();
										?>
										<h3>Editing Journal with ref : <?php echo $rr; ?></h3>
										<form action="" method="post" onsubmit="do_submit(event,this)">

											<div class="form_sep" style="width:260px; ">
												<div class="form-group">
													<label for="date"><strong style="color: red; ">Event Date</strong></label>
													<input type="date" id="entry_date" name="date" value="<?php echo $gl_lines[0]['date_entry2']; ?>" class="form-control" style="font-size: 17px;"
														min="<?= date('Y-m-d', strtotime($fiscal_year['begin'])) ?>"
														max="<?= date('Y-m-d', strtotime($fiscal_year['end'])) ?>">
													<small class="text-muted">Active fiscal year: <?= date('M d, Y', strtotime($fiscal_year['begin'])) ?> &ndash; <?= date('M d, Y', strtotime($fiscal_year['end'])) ?></small>
												</div>
											</div>


											<hr>
											<h3 class="text-danger" id="gl_lines_errr"></h3>
											<table class="table">
												<thead>
													<th></th>
													<th>Account</th>
													<th>Amount - Dr</th>
													<th>Amount - Cr</th>
													<th>Narration</th>
													<th><button type="button" class="btn btn-success btn-sm" onclick="addJournalRow()"><i class="fa fa-plus"></i>Add Row</button></th>
												</thead>
												<tbody id="journal_lines" style="font-size: 16px; "> <?php foreach ($gl_lines as $line) { ?>
														<tr>
															<td>
																<button type="button" class="btn btn-danger" onclick="removeJournalRow(this)"><i class="fa fa-times"></i></button>
															</td>
															<td>
																<select name="account[]" class="form-control account select2" required style="width: 100%">
																	<?php foreach ($classes as $class) { ?>
																		<optgroup label="<?php echo $class['class_name']; ?>">
																			<?php
																												$cid = $class['cid'];
																												$groups = $db->prepare('SELECT * FROM chart_groups WHERE class_id = ? AND inactive = ?');
																												$groups->execute([$cid, 0]);
																												$groups = $groups->fetchAll(PDO::FETCH_ASSOC);
																			?>
																			<?php foreach ($groups as $group) { ?>
																		<optgroup label="<?php echo $group['name']; ?>">
																			<?php
																													$group_id = $group['id'];

																													$accounts = $db->prepare('SELECT * FROM chart_accounts WHERE account_group = ? AND inactive = ?');
																													$accounts->execute([$group_id, 0]);
																													$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
																			?>
																			<?php foreach ($accounts as $account) { ?>
																				<option value="<?php echo $account['account_code']; ?>" <?php echo $line['account_no'] == $account['account_code'] ? 'selected' : ''; ?>>[<?php echo $account['account_code']; ?>] <?php echo $account['account_name']; ?></option>
																			<?php } ?>
																		</optgroup>
																	<?php } ?>
																	</optgroup>
																<?php } ?>
																</select>
															</td>
															<td>
																<input type="number" min="0" step="any" value="<?php echo $line['dr_amt']; ?>" onkeyup="get_totals()" class="form-control amount_dr" name="amount_dr[]" required>
															</td>
															<td>
																<input type="number" min="0" step="any" value="<?php echo $line['cr_amt']; ?>" onkeyup="get_totals()" class="form-control amount_cr" name="amount_cr[]" required>
															</td>
															<td>
																<textarea name="narration[]" class="from-control" required style="height: 35px; " cols="50"><?php echo $line['item_services']; ?></textarea>
																<input type="hidden" value="<?php echo $line['sn']; ?>" name="line_ids[]" required>
															</td>

														</tr>
													<?php } ?>
												</tbody>
												<tfoot>
													<th></th>
													<th><b>Total</b></th>
													<td id="total_dr">0</td>

													<td id="total_cr">0</td>
												</tfoot>
											</table>
											<button type="submit" class="btn btn-primary" name="submit_entry"><i class="fa fa-disk"></i>Update entries</button>
										</form>
									<?php } ?>
								</div>
								<hr>
								<?php if (isset($the_stetment)) { ?>
									<div class="table-responsive" id="chart_groups">
										<?php if ($gl_acct == '') { ?><h2>All Journals</h2> <?php } ?>
										<h3><?= $title;  ?></h3>
										<div align="center" style="font-size:20px; font:Verdana, Geneva, sans-serif;">
											<?php echo '<br>PERIOD: [' . date('d M, Y', strtotime($_GET['start'])) . ' - ' . date('d M, Y', strtotime($_GET['end'])) . ']'; ?>
										</div>

										<!-- Loading Overlay -->
										<div id="loading-overlay">
											<div class="spinner" style="
												border: 4px solid #f3f3f3;
												border-top: 4px solid #337ab7;
												border-radius: 50%;
												width: 50px;
												height: 50px;
												animation: spin 1s linear infinite;
												margin-bottom: 15px;
											"></div>
											<h4 style="color: #337ab7; font-weight: bold; margin: 0; font-size: 18px;">
												<i class="fa fa-spinner fa-spin" style="margin-right: 8px;"></i>
												Loading Journal Data...
											</h4>
											<p style="color: #666; margin: 8px 0 0 0; font-size: 14px;">
												Please wait while we fetch your transaction records
											</p>
										</div>
										<!--	<table cellpadding="5" cellspacing="5" border="0" style="font-family: arial; font-size: 13px;text-align:left;width : 100%;">-->
										<table class="table">
											<tr bgcolor="#CCCCAA">
												<th colspan="3" bgcolor="#CCCCAA">Opening balances</th>
												<th bgcolor="#CCCCAA">DR:</th>
												<th id="op_bal_dr" bgcolor="#CCCCAA"></th>
												<th bgcolor="#CCCCAA">CR:</th>
												<th id="op_bal_cr" bgcolor="#CCCCAA"></th>
												<th bgcolor="#CCCCAA">DR_CR Difference:</th>
												<th id="op_bal_bal" colspan="2" bgcolor="#CCCCAA"></th>
											</tr>
										</table>
										<table id="example" class="table table-striped table-bordered table-hover dataTables-example">
											<thead>
												<tr bgcolor="#CCCCCC">
													<th>S/N</th>
													<th>Account</th>
													<th>Description</th>
													<th>Client</th>
													<th>DR</th>
													<th>CR</th>
													<th>DR_CR Difference</th>
													<th>Entry by</th>
													<th>Date</th>
													<th>Action</th>
												</tr>
											</thead>
											<tfoot>
												<tr>
													<th colspan="4">Transaction Totals</th>
													<th id="tot_bal_dr">0.00</th>
													<th id="tot_bal_cr">0.00</th>
													<th id="tot_bal_bal">0.00</th>
													<th></th>
													<th></th>
													<th></th>
												</tr>
											</tfoot>
										</table>
										<table class="table">
											<tr bgcolor="#CCCCAA">
												<th colspan="3" bgcolor="#CCCCAA">Closing balances</th>
												<th bgcolor="#CCCCAA">DR:</th>
												<th id="cl_bal_dr" bgcolor="#CCCCAA"></th>
												<th bgcolor="#CCCCAA">CR:</th>
												<th id="cl_bal_cr" bgcolor="#CCCCAA"></th>
												<th bgcolor="#CCCCAA">DR_CR Difference:</th>
												<th id="cl_bal_bal" colspan="2" bgcolor="#CCCCAA"></th>
											</tr>
										</table>
									</div>


									<button class="btn btn-primary" onclick="printDiv('chart_groups', ['html5buttons', 'dataTables_filter'])"><i class="fa fa-print"></i>&nbsp;Print Report</button>
									<br>
								<?php } ?>


							</div>
						</div>
					</div>
				</div>


				<?php include '../inc/footer.php'; ?>


			</div>
		</div>


		<?php include('../modal_lock.php'); ?>
		<?php include("../inc/footer_scripts.php"); ?>


		<script>
			<?php
			if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
				timeOut: 5000
			})
			<?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
				timeOut: 5000
			})
			<?php } ?>

			$(document).ready(function() {
				$('.select2').select2();
			});
		</script>

		<style>
			@keyframes spin {
				0% {
					transform: rotate(0deg);
				}

				100% {
					transform: rotate(360deg);
				}
			}

			#loading-overlay {
				position: absolute !important;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: rgba(255, 255, 255, 0.8) !important;
				z-index: 9999;
				display: none;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				border-radius: 5px;
			}

			#chart_groups {
				position: relative;
			}

			.dataTables_processing {
				display: none !important;
			}
		</style>
		<!--		
		<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>-->
		<script>
			function printDiv(divId, classesToRemove = []) {
				var content = document.getElementById(divId).innerHTML;
				var popupWindow = window.open('', '_blank', 'width=600,height=600');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>' + document.title + '</title>');

				// Reference the external stylesheet from the main page
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write('</head><body>');

				// Remove specified classes from the body
				classesToRemove.forEach(function(className) {
					popupWindow.document.body.classList.remove(className);
				});

				// Write content to the popup window
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();

				// Wait for the document to fully load, then print
				popupWindow.onload = function() {
					setTimeout(function() {
					    popupWindow.focus();
					    popupWindow.print();
					}, 1000);
				};
			}
		</script>





		<?php include 'inc/journal_scripts.php'; ?>
		<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

		<script src="../js/idle.js"></script>


		<!-- Custom and plugin javascript -->
		<script src="../js/inspinia.js"></script>
		<script src="../js/plugins/pace/pace.min.js"></script>
		<script src="../js/plugins/dataTables/datatables.min.js"></script>
		<script>
			$(document).ready(function() {

				var table = $('#example').DataTable({
					"processing": false,
					"serverSide": true,
					"iDisplayLength": 500,
					"lengthMenu": [
						[10, 25, 50, 100, 500, 1000, 5000],
						[10, 25, 50, 100, 500, 1000, 5000]
					],
					dom: '<"html5buttons"B>lTfgitp',
					buttons: [{
							extend: 'copy'
						},
						{
							extend: 'csv'
						},
						{
							extend: 'excelHtml5',
							title: 'All Transaction Journals',
							customize: function(xlsx) {
								var sheet = xlsx.xl.worksheets['sheet1.xml'];
								var hospitalHeader = `
							<row>
								<c t="inlineStr">
									<is><t><?php echo htmlspecialchars($hospital_table); ?></t></is>
								</c>
							</row>
							<row>
								<c t="inlineStr">
									<is><t><?php echo htmlspecialchars($period_text); ?></t></is>
								</c>
							</row>`;
								$(sheet).find('sheetData').prepend(hospitalHeader);
							}
						},
						{
							extend: 'pdfHtml5',
							title: 'All Transaction Journals',
							customize: function(doc) {
								doc.content.unshift({
									text: `<?php echo $period_text; ?>`,
									style: 'period'
								});
								doc.content.unshift({
									text: `\n`,
									style: 'period'
								});
								doc.content.unshift({
									text: `<?php echo $hospital_details['name']; ?>\n<?php echo $hospital_details['address']; ?>\n<?php echo $hospital_details['phones']; ?>`,
									style: 'header'
								});
								doc.content.unshift({
									margin: [0, 0, 0, 12],
									alignment: 'left',
									image: 'data:image/png;base64,<?php echo base64_encode(file_get_contents("../img/logo.png")); ?>',
									width: 100,
									height: 70
								});
							},
							styles: {
								header: {
									fontSize: 18,
									bold: true,
									alignment: 'right'
								},
								period: {
									fontSize: 20,
									alignment: 'center'
								}
							}
						},
						{
							extend: 'print',
							customize: function(win) {
								$(win.document.body)
									.prepend(`<?php echo $hospital_table; ?><?php echo $period_text; ?>`)
									.css('font-size', '10px');
								$(win.document.body).find('table')
									.addClass('compact')
									.css('font-size', 'inherit');
							}
						}
					],
					"ajax": {
						"url": "inc/all_journals_datatable.php",
						"type": "POST",
						"data": function(d) {
							d.start_date = "<?php echo $_GET['start']; ?>";
							d.end_date = "<?php echo $_GET['end']; ?>";
							d.prep = "<?php echo isset($prep) ? $prep : ''; ?>";
							d.gl_acct = "<?php echo isset($gl_acct) ? $gl_acct : ''; ?>";
							d.DR_CR = "<?php echo isset($DR_CR) ? $DR_CR : ''; ?>";
							d.account_payable = "<?php echo isset($account_payable) ? $account_payable : ''; ?>";
							d.hmo_dues = "<?php echo isset($hmo_dues) ? $hmo_dues : ''; ?>";
							d.account_recievables = "<?php echo isset($account_recievables) ? $account_recievables : ''; ?>";
							d.patient_deposit = "<?php echo isset($patient_deposit) ? $patient_deposit : ''; ?>";
						},
						"dataSrc": function(json) {
							$('#tot_bal_dr').html(json.totals.totalDR);
							$('#tot_bal_cr').html(json.totals.totalCR);
							$('#tot_bal_bal').html(json.totals.totalBalance);
							$('#op_bal_dr').html(json.opening_totals.openingDR);
							$('#op_bal_cr').html(json.opening_totals.openingCR);
							$('#op_bal_bal').html(json.opening_totals.openingBalance);
							$('#cl_bal_dr').html(json.closing_totals.closingDR);
							$('#cl_bal_cr').html(json.closing_totals.closingCR);
							$('#cl_bal_bal').html(json.closing_totals.closingBalance);
							return json.data;
						},
						"beforeSend": function() {
							$('#loading-overlay').show();
						},
						"complete": function() {
							$('#loading-overlay').hide();
						},
						"error": function() {
							$('#loading-overlay').hide();
							toastr.error('Failed to load journal data. Please try again.', 'Error');
						}
					},
					"columns": [{
							"data": "sn"
						},
						{
							"data": "account_no"
						},
						{
							"data": "item_services"
						},
						{
							"data": "client"
						},
						{
							"data": "dr",
							"orderable": false
						},
						{
							"data": "cr",
							"orderable": false
						},
						{
							"data": "balance",
							"orderable": false
						},
						{
							"data": "prepared_by"
						},
						{
							"data": "date_entry2"
						},
						{
							"data": "actions",
							"orderable": false
						},
					],
					rowGroup: {
						dataSrc: 'lg_ref_no',
						startRender: function(rows, group) {
							return '<tr class="table-info"><td colspan="10" style="background:#e6f7ff;"><b>Journal Ref: ' + group + '</b> <span style="color:#888;font-size:12px;">(' + rows.count() + ' entries)</span></td></tr>';
						}
					},
					drawCallback: function(settings) {
						// Optionally, add custom styling for grouped rows
					}
				});

				// Show loading overlay initially
				$('#loading-overlay').show();

				// Handle table redraw events (pagination, sorting, filtering)
				table.on('preXhr.dt', function() {
					$('#loading-overlay').show();
				});

				table.on('xhr.dt', function() {
					$('#loading-overlay').hide();
				});
			});
		</script>


		<script>
			$(document).ready(function() {
				$('.chosen-select').chosen(); // Initialize the chosen-select dropdowns

				// Function to fetch more options via AJAX
				function loadMoreOptions(elementId, endpoint) {
					$.ajax({
						url: endpoint,
						method: 'GET',
						success: function(data) {
							const select = $('#' + elementId);

							// Remove all options after the first 60 if there are more than 60 options
							if (select.children('option').length > 60) {
								select.children('option:gt(59)').remove(); // Remove options with index greater than 59
							}

							// Append new options from the fetched data
							data.forEach(function(item) {
								const option = $('<option></option>').attr('value', item.value).text(item.text);
								select.append(option);
							});

							// Update the chosen-select with new options
							select.trigger("chosen:updated");
						},
						error: function(error) {
							console.error("Error fetching more options: ", error);
						}
					});
				}

				// Event listeners for dropdowns to load more options via AJAX
				$('#account').on('chosen:showing_dropdown', function() {
					loadMoreOptions('account', 'inc/getMoreAccounts.php');
				});

				$('#account_payable').on('chosen:showing_dropdown', function() {
					loadMoreOptions('account_payable', 'inc/getMorePayables.php');
				});

				$('#account_recievables').on('chosen:showing_dropdown', function() {
					loadMoreOptions('account_recievables', 'inc/getMoreRecievables.php');
				});

				$('#hmo_dues').on('chosen:showing_dropdown', function() {
					loadMoreOptions('hmo_dues', 'inc/getMoreHMODues.php');
				});

				$('#patient_deposit').on('chosen:showing_dropdown', function() {
					loadMoreOptions('patient_deposit', 'inc/getMorePatientDeposits.php');
				});
			});
		</script>

</body>

</html>
