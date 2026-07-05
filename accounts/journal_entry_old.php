<?php
session_start();
include '../inc/header.php';
/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('error_log', 'error_log');*/
$classes = $db->query('SELECT * FROM chart_class WHERE inactive = 0');

$classes = $classes->fetchAll(PDO::FETCH_ASSOC);

include 'inc/functions.php';

if (isset($_POST['account'])) {
	try {
		$db->beginTransaction();

		$accounts = $_POST['account'];

		$account_payable = $_POST['account_payable'];
		$accountNo_hmo =  $_POST['accountNo_hmo'];
		$patient_deposit =  $_POST['patient_deposit'];

		//check for invoice number, only applies to account payable.
		if (isset($_POST['invoice'])) {
			$invoice_no =  $_POST['invoice'];
		} else {
			$invoice_no =  null;
		}


		$amount_dr = $_POST['amount_dr'];
		$amount_cr = $_POST['amount_cr'];
		$narrations = $_POST['narration'];
		$date = $_POST['date'];
		$posted_by = $_SESSION['fullname'];
		$ref_val = $_POST['ref_val'];
		$setdate=date("Y-m-d H:i:s");

		$fical_year = get_active_year()['id'];
		$err = 0;
		$first_narration = '';

		for ($i = 0; $i < count($accounts); ++$i) {
			$account = $accounts[$i];
			$can_delete = 1; //simce it is a direct posting

			if (strpos($account, "account_payable") !== false) {
				$supplier_id = str_replace("account_payable", "", $account);
				$account = $account_payable;
				$hospital_no = null;
			} elseif (strpos($account, "account_hmo") !== false) {
				$supplier_id = str_replace("account_hmo", "", $account);
				$account = $accountNo_hmo;
				$hospital_no = null;
			} elseif (strpos($account, "patient_deposit") !== false) {
				$hospital_no = str_replace("patient_deposit", "", $account);
				$account = 2121;
				$supplier_id = $_POST['hmo'];
			
				///$supplier_id = null;
			} else {
				$hospital_no = $_POST['hospital_no'];
				$supplier_id = $_POST['hmo'];
				///$hospital_no = null;
				///$supplier_id ='';	
			}

			$amt_dr = $amount_dr[$i];
			$amt_cr = $amount_cr[$i];
			$type = (($amount_dr[$i] > 0) ? 'DEBIT' : 'CREDIT');
			$narration = $narrations[$i];

			//copy the first narration for other rows that have none
			if ($i == 0) {
				$first_narration = $narration;
			} elseif ($narration == '') {
				$narration = $first_narration;
			}

			$stmt = $db->query("SELECT * FROM chart_ledger WHERE lg_ref_no='$ref_val' and account_no='$account' 
				and transc_type='$type' and dr_amt='$amt_dr' and cr_amt='$amt_cr'");
			if ($stmt->rowCount() == 0) {
				$entry = $db->prepare('INSERT INTO chart_ledger(hospital_no,insurance_no, account_no, transc_type, dr_amt, cr_amt,date_entry, date_entry2, prepared_by, 
							lg_ref_no, fiscal_year, item_services, invoice_no, can_delete) 
							VALUES(?,?,?,?,?,?,?,?,?,?,?, ?,?, ?)');
				if ($account == '') {
					$err++;
				}
			}
			if ($entry->execute([$hospital_no, $supplier_id, $account, $type, $amt_dr, $amt_cr, $setdate, $date, $posted_by, $ref_val, $fical_year, $narration, $invoice_no, $can_delete])) {
				continue;
			} else {
				$err++;
			}
		}
		$db->commit();

		if ($err > 0) {
			$delete = $db->prepare("DELETE FROM chart_ledger WHERE lg_ref_no = ?");
			$deleted = $delete->execute(array($ref_val));
			set_flash_message('<h2>FAILED TO MAKE ENTRY</h2>', 'danger');
		} else {
			set_flash_message('<h2>ENTRY ADDED SUCCESSFUL!</h2>');
		}
	} catch (\Exception $e) {
		error_log($e->getMessage());
		$db->rollBack();
		set_flash_message('<h2>FAILED TO MAKE ENTRY</h2>', 'danger');
	}
}
$active_yaer = get_active_year();

$invoices = $db->prepare('SELECT DISTINCT c.invoice_no, s.sn, s.name FROM chart_ledger AS c INNER JOIN stock_company AS s ON c.insurance_no = s.sn');
$invoices->execute();
$invoices = $invoices->fetchAll(PDO::FETCH_ASSOC);

// var_dump($invoices);
// die();
?>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php include("nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../inc/nav_header.php'; ?>
			<div>
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Accounts Dashboard | Journal Entry</h5>
							</div>
							<div class="ibox-content">

								<a href="journal_entry.php" class="btn btn-default"><i class="fa fa-refresh"></i>&nbsp;REFRESH</a>&nbsp; : &nbsp;
								<a href="journal_entry.php?payable" class="btn btn-success">ACCOUNT PAYABLE</a>&nbsp; : &nbsp;
								<a href="journal_entry.php?patient_deposit" class="btn btn-primary">PATIENT DEPOSIT</a>&nbsp; : &nbsp;
								<a href="journal_entry.php?hmo" class="btn btn-info">HMO RECIEVABLE</a>&nbsp; : &nbsp;
								<a href="index.php" class="btn btn-danger" style="color: black; "><i class="fa fa-close"></i>&nbsp;CLOSE</a>
								<br>
								<br>


								<?php
								echo show_flash_msg();
								?>
								<form action="" method="post" onsubmit="do_submit(event,this)">
									<div class="form_sep" style="width:260px; ">
										<?php /*?>	 min="<?=date('Y-m-d', strtotime($active_yaer['begin'])) ?>" max="<?=date('Y-m-d', strtotime($active_yaer['end'])) ?>"<?php */ ?>

										<label for="date"><strong style="color: red; ">Event Date</strong></label>
										<input type="date" id="entry_date" name="date" class="form-control" style="font-size: 17px; ">
									</div>
									<br>
									<!-- If we are dealing with account payble, we give anoptio to specify invoice no -->
									<?php if (isset($_GET['payable'])) : ?>
										<div class="">
											<div class="row">
												<div class="form-group col-sm-4">
													<label for="invoiceOption"><strong style="color: red;">Select Invoice Option</strong></label>
													<select id="invoiceOption" class="form-control" style="font-size: 17px;">
														<option value="new">New Invoice</option>
														<option value="existing">Existing Invoice</option>
													</select>
												</div>
												<div class="form-group col-sm-4" id="newInvoiceField">
													<label for="newInvoice"><strong style="color: red;">Event Invoice Number</strong></label>
													<input type="text" id="newInvoice" name="invoice" class="form-control" style="font-size: 17px;" required>
												</div>
												<div class="form-group col-sm-4" id="existingInvoiceField" style="display: none;">
													<label for="existingInvoice"><strong style="color: red;">Event Invoice Number</strong></label>
													<br>
													<select id="existingInvoice" name="invoice" class="form-control" style="font-size: 17px; display:block; min-width:300px;" required disabled>
														<option value="">--select existing invoice--</option>
														<!-- Options will be populated by JavaScript -->
													</select>
												</div>
											</div>
										</div>
									<?php endif ?>


									<hr>
									<h3 class="text-danger" id="gl_lines_errr"></h3>





									<table class="table">
										<thead>
											<th></th>
											<th>Account</th>
											<th>Amount - Dr</th>
											<th>Amount - Cr</th>
											<th>Narration</th>

										</thead>
										<tbody id="journal_lines" style="font-size: 16px; ">
											<tr>
												<td>
													<button type="button" class="btn btn-danger" onclick="removeJournalRow(this)"><i class="fa fa-times"></i></button>
												</td>
												<td width="40%">

													<?php if (isset($_GET['hmo'])) {

														$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_code=1502");
														if ($stmt->rowCount() > 0) {
															$row = $stmt->fetch(PDO::FETCH_ASSOC);
															$accountNo_hmo = $row['account_code'];
														} else {
															$accountNo_hmo = '';
														}
														$account_hmo_receivable = 1;
													?>
														<select name="account[]" class="form-control account2 select2" id="account_checker2" onChange="call_bal2(); " required>
															<option value="">-- select HMO--</option>
															<?php
															$accounts = $db->prepare("SELECT distinct s.insurance_no,s.insurance_name FROM insurance_tbl as s inner join chart_ledger as c on c.insurance_no = s.insurance_no where s.insurance_no!='1000'");
															$accounts->execute();
															$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
															foreach ($accounts as $account) { ?>
																<option value="<?php echo 'account_hmo' . $account['insurance_no']; ?>"> <?php echo $account['insurance_name']; ?></option>
															<?php } ?>
														</select>



													<?php } elseif (isset($_GET['payable'])) {
														$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_code=2124");
														if ($stmt->rowCount() > 0) {
															$row = $stmt->fetch(PDO::FETCH_ASSOC);
															$accountNo_payable = $row['account_code'];
														} else {
															$accountNo_payable = '';
														}
														$account_payable = 1;
													?>
														<small class="text-danger">Ensure you select an account that maches any existing Invoice that you have selected</small>
														<select name="account[]" class="form-control account select2" id="account_checker" onChange="call_bal(); " required>
															<option value="">-- select Vendor--</option>
															<?php
															$accounts = $db->prepare('SELECT distinct s.sn,s.name FROM stock_company as s inner join chart_ledger as c on c.insurance_no = s.sn');
															$accounts->execute();
															$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
															foreach ($accounts as $account) { ?>
																<option value="<?php echo 'account_payable' . $account['sn']; ?>"> <?php echo $account['name']; ?></option>
															<?php } ?>
														</select>


													<?php } elseif (isset($_GET['patient_deposit'])) {

														///////////////// PATIENT DEPOSIT SSSSSSSSSSSSSSSS =============>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>	



														$stmt = $db->query("SELECT account_code FROM chart_accounts WHERE account_code=2124");
														if ($stmt->rowCount() > 0) {
															$row = $stmt->fetch(PDO::FETCH_ASSOC);
															$accountNo_payable = $row['account_code'];
														} else {
															$accountNo_payable = '';
														}
														$patient_deposit = 1;
													?>
														<select name="account[]" class="form-control account select2" id="account_checker_deposit" onChange="call_bal3(); " required>
															<option value="">-- Select Patient--</option>
															<?php
															$accounts = $db->prepare('SELECT distinct s.surname,s.fname,s.oname,s.hospital_no,s.hmo_no FROM enrollee as s inner join chart_ledger as c on c.hospital_no = s.hospital_no where account_no=2121 order by hospital_no ');
															$accounts->execute();
															$accounts = $accounts->fetchAll(PDO::FETCH_ASSOC);
															foreach ($accounts as $account) {
///insurance_no='$insurance_no' and account_no='$wallet_account'																

																$emr = $account['hospital_no'];
																$hmo_no = $account['hmo_no'];
																if($hmo_no == 1000){
																	$my_s ="hospital_no='$emr'";
																}else{
																	$my_s ="insurance_no = '$hmo_no'";
																}
																$stmt = $db->query("SELECT 
																	sum(dr_amt) as TOTAL_DEBITS, 
																	sum(cr_amt) as TOTAL_CREDITS
																FROM chart_ledger WHERE $my_s and account_no='2121'");
																if ($stmt->rowCount() > 0) {
																	$row = $stmt->fetch(PDO::FETCH_ASSOC);
																	$TOTAL_CREDITS = $row['TOTAL_CREDITS'];
																	$TOTAL_DEBITS = $row['TOTAL_DEBITS'];
																	$current_balance = $TOTAL_CREDITS - $TOTAL_DEBITS;
																}
															?>

																<?php if ($current_balance > 0) { ?>
																	<option value="<?php echo 'patient_deposit' . $account['hospital_no']; ?>"> <?php echo $account['hospital_no'] . ' : ' . $account['surname'] . ' ' . $account['fname'] . ' ' . $account['oname'] . 'Bal.'  .  number_format($current_balance); ?></option>
																<?php } ?>

															<?php } ?>
														</select>

													<?php } else {
														$patient_deposit = 0; ?>


														<select name="account[]" onChange="get_me()" id="account_get_acctNO" class="form-control account select2" required>
															<option value="">-- select--</option>
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
																		<option value="<?php echo $account['account_code']; ?>">[<?php echo $account['account_code']; ?>] <?php echo $account['account_name']; ?></option>
																	<?php } ?>
																</optgroup>
															<?php } ?>
															</optgroup>
														<?php } ?>
														</select>


													<?php } ?>


												</td>
												<td width="15%">
													<input type="number" step="any" min="0" value="0" onkeyup="get_totals()" class="form-control amount_dr" name="amount_dr[]" id="amount_dr" required style="font-size: 20px;">
												</td>
												<td width="15%">
													<input type="number" step="any" min="0" value="0" onkeyup="get_totals()" class="form-control amount_cr" name="amount_cr[]" id="amount_cr" required style="font-size: 20px;">
												</td>
												<td width="40%">
													<textarea name="narration[]" class="from-control narrations" required style="height: 35px; " cols="50"></textarea>
												</td>

											</tr>
										</tbody>
										<tfoot>
											<th></th>
											<td id="total_dr" style="font-size: 20px; ">0</td>

											<td id="total_cr" style="font-size: 20px; ">0</td>
											<th></th>
										</tfoot>
									</table>


									<?php

									if (isset($_GET['payable']) and $accountNo_payable == '') {
										$disabled = 'disabled';
										echo '<strong style="color: red;"></strong>';
									} else {
										$disabled = '';
									}

									?>
									<table width="100%">
										<tr>
											<td>
												<button type="button" class="btn btn-success" onclick="addJournalRow()"><i class="fa fa-plus"></i> &nbsp;Add Row</button>
											</td>
											<td>
												<div align="right">

												</div>
												<button type="submit" class="btn btn-primary" name="submit_entry" <?= $disabled; ?>><i class="fa fa-disk"></i>Save entries</button>
											</td>

										</tr>
									</table>

									<input type="hidden" value="<?= time(); ?>" name="ref_val">
									<input type="hidden" value="<?= $accountNo_payable; ?>" name="account_payable" id="account_payable">
									<input type="hidden" value="<?= $accountNo_hmo; ?>" name="accountNo_hmo" id="accountNo_hmo">
									<input type="hidden" value="2121" name="patient_deposit" id="patient_deposit">

									<input type="hidden" value="" name="hospital_no" id="hospital_no">
									<input type="hidden" value="" name="hmo" id="hmo">


									
								</form>

							</div>
						</div>
					</div>
				</div>
				<?php include '../inc/footer.php'; ?>

			</div>
		</div>


		<?php include('../modal_lock.php'); ?>
		<?php include("../inc/footer_scripts.php"); ?>

		<script src="../js/plugins/chosen/chosen.jquery.js"></script>
		<script>
			$('.chosen-select').chosen({
				width: "100%"
			});

			function call_bal() {
				var account_checker = document.getElementById("account_checker").value;
				var account_payable = document.getElementById("account_payable").value;
				$.ajax({
					url: "fetch.php",
					method: "POST",
					data: {
						account_checker: account_checker,
						account_payable: account_payable
					},
					success: function(data) {
						var json = JSON.parse(data);
						document.getElementById("amount_dr").value = json["amount"];
					}
				});
			}


			function call_bal2() {
				var account_checker2 = document.getElementById("account_checker2").value;
				var accountNo_hmo = document.getElementById("accountNo_hmo").value;
				$.ajax({
					url: "fetch.php",
					method: "POST",
					data: {
						account_checker2: account_checker2,
						accountNo_hmo: accountNo_hmo
					},
					success: function(data) {
						var json = JSON.parse(data);
						document.getElementById("amount_cr").value = json["amount"];
					}
				});
			}


			function get_me() {
				var account_get_acctNO = document.getElementById("account_get_acctNO").value;

				$.ajax({
					url: "fetch.php",
					method: "POST",
					data: {
						account_get_acctNO: account_get_acctNO
					},
					success: function(data) {
						var json = JSON.parse(data);

						if (json["code"] == 'DR') {
							document.getElementById("amount_cr").value = json["amount"];

						} else {
							document.getElementById("amount_dr").value = json["amount"];
						}


					}
				});
			}



			function call_bal3() {
				var account_checker_deposit = document.getElementById("account_checker_deposit").value;

				///alert(account_checker_deposit);

				$.ajax({
					url: "fetch.php",
					method: "POST",
					data: {
						account_checker_deposit: account_checker_deposit
					},
					success: function(data) {

						var json = JSON.parse(data);
						document.getElementById("amount_dr").value = json["amount"];
						document.getElementById("hospital_no").value = json["hospital_no"];
						document.getElementById("hmo").value = json["hmo"];
					}
				});
			}


		

			<?php if (isset($_GET['msg'])) { ?>
				<?php $error_status = $_GET['msg'];
				if ($error_status == 1) { ?>toastr.error('<?php echo $error_msg; ?>', 'Error', {
					timeOut: 5000
				})
				<?php } elseif ($error_status == 2) { ?>toastr.success(' <?php echo $error_msg; ?> ', 'Success', {
					timeOut: 5000
				})
			<?php } ?>
			<?php } ?>
		</script>
		<script>
			$(document).ready(function() {
				$('.select2').select2();
			});

			document.addEventListener("DOMContentLoaded", function() {
				const dateInput = document.getElementById("entry_date");

				// Get the current date
				const currentDate = new Date();

				// Convert the date to the required format (YYYY-MM-DD)
				const year = currentDate.getFullYear();
				const month = String(currentDate.getMonth() + 1).padStart(2, "0");
				const day = String(currentDate.getDate()).padStart(2, "0");

				const formattedDate = `${year}-${month}-${day}`;

				// Set the default value of the date input
				dateInput.value = formattedDate;
			});
		</script>
		<script>
			//invoices fetched from the database
			var invoices = <?php echo json_encode($invoices); ?>;

			$(document).ready(function() {
				$('#invoiceOption').change(function() {
					if ($(this).val() == 'new') {
						$('#newInvoiceField').show().find('input').prop('disabled', false);
						$('#existingInvoiceField').hide().find('select').prop('disabled', true).select2('destroy');
					} else {
						$('#newInvoiceField').hide().find('input').prop('disabled', true);
						$('#existingInvoiceField').show().find('select').prop('disabled', false);

						$('#existingInvoice').addClass('select2'); // Add class before initializing
						$('#existingInvoice').select2(); // Initialize select2
					}
				});

				// Populate the existing invoices dropdown
				invoices.forEach(function(invoice) {
					if (invoice.invoice_no != '' && invoice.invoice_no != null) {
						$('#existingInvoice').append($('<option>', {
							value: invoice.invoice_no,
							text: invoice.invoice_no + ' - ' + invoice.name
						}));
					}
				});
			});
		</script>

		<?php include 'inc/journal_scripts.php'; ?>
		<script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>

		<script src="../js/idle.js"></script>
</body>

</html>