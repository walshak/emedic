<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

$account_payable = 2124; //account code for account payable group, we will use it to get the balance of each supplier from our ledger
$patient_account_payable = 2121; //account code for patient account payable group, we will use it to get the balance of each patient from our ledger

if (isset($_GET['start']) && isset($_GET['end'])) {
	$the_stetment = 1;

	$start = $_GET['start'];
	$end = $_GET['end'];
	$include_anonymous = isset($_GET['include_anonymous']) ? $_GET['include_anonymous'] : 0;

	// Get supplier payables - base query with optional anonymous filter
	$supplier_query = "SELECT 
        COALESCE(s.name, 'Anonymous') as supplier_name,
        c.insurance_no,
		c.lg_ref_no,
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :account_payable THEN c.cr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :account_payable2 THEN c.dr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        stock_company s ON c.insurance_no = s.sn
    WHERE 
        c.account_no = :account_payable3 AND
		(hospital_no = '' or hospital_no IS NULL) AND
        DATE(c.date_entry) BETWEEN :start AND :end";

	// Add anonymous filter if checkbox is unchecked
	if (!$include_anonymous) {
		$supplier_query .= " AND s.name IS NOT NULL";
	}


	// Filter by invoice if specified
	$params = array(
		':account_payable' => $account_payable,
		':account_payable2' => $account_payable,
		':account_payable3' => $account_payable,
		':start' => $start,
		':end' => $end
	);



	if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
		$invoice_no = $_GET['invoice_no'];
		$supplier_query .= " AND (c.invoice_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";

		$params[':invoice_no'] = $invoice_no;
		$params[':lg_ref_no'] = $invoice_no;
	}

	// Add group by and having clauses
	$supplier_query .= " GROUP BY c.insurance_no HAVING balance > 0";

	// Execute supplier query
	$supplier_stmt = $db->prepare($supplier_query);
	foreach ($params as $param_name => $param_value) {
		$supplier_stmt->bindValue($param_name, $param_value);
	}
	$supplier_stmt->execute();
	$supplier_payables = $supplier_stmt->fetchAll(PDO::FETCH_ASSOC);

	// Define patient categories
	// external patients
	$external_patients_query = "SELECT
		COALESCE(pe.cust_name, 'Anonymous') as patient_name,
			c.hospital_no,
			c.lg_ref_no,
			c.insurance_no,
			'External' as patient_type,
			SUM(c.cr_amt) as total_credits,
			SUM(c.dr_amt) as total_debits,
			SUM(c.cr_amt) - SUM(c.dr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			pharm_ext pe ON c.hospital_no = pe.transc_code
		WHERE
			c.account_no = :patient_account_payable AND
			c.insurance_no = 'EX0001' AND
			DATE(c.date_entry) BETWEEN :start AND :end";

	// Add anonymous filter for external patients if checkbox is unchecked
	if (!$include_anonymous) {
		$external_patients_query .= " AND pe.cust_name IS NOT NULL";
	}

	$external_patients_query .= " GROUP BY c.hospital_no HAVING balance > 0";

	//private patients
	$private_patients_query = "SELECT
	COALESCE(CONCAT(e.fname, ' ', e.surname), 'Anonymous') as patient_name,
		c.hospital_no,
		c.lg_ref_no,
		c.insurance_no,
		'Private' as patient_type,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) - SUM(c.dr_amt) as balance
	FROM
		chart_ledger c
	LEFT JOIN
		enrollee e ON c.hospital_no = e.hospital_no
	WHERE
		c.account_no = :patient_account_payable AND
		(c.insurance_no = '1000' OR c.insurance_no = 'private') AND
		DATE(c.date_entry) BETWEEN :start AND :end";

	// Add anonymous filter for private patients if checkbox is unchecked
	if (!$include_anonymous) {
		$private_patients_query .= " AND (e.fname IS NOT NULL AND e.surname IS NOT NULL)";
	}

	$private_patients_query .= " GROUP BY c.hospital_no HAVING balance > 0";

	$family_patients_query = "SELECT
		COALESCE(i.insurance_name, 'Anonymous') as patient_name,
		'' as hospital_no,
		c.lg_ref_no,
		c.insurance_no,
		'Family' as patient_type,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) - SUM(c.dr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			insurance_tbl i ON c.insurance_no = i.insurance_no
		WHERE
			c.account_no = :patient_account_payable AND
			c.insurance_no NOT IN ('EX0001', '1000', 'private', '') AND
			c.insurance_no IS NOT NULL AND
		DATE(c.date_entry) BETWEEN :start AND :end";

	// Add anonymous filter for family patients if checkbox is unchecked
	if (!$include_anonymous) {
		$family_patients_query .= " AND i.insurance_name IS NOT NULL";
	}

	$family_patients_query .= " GROUP BY c.insurance_no HAVING balance > 0";

	// Prepare common parameters
	$patient_params = array(
		':patient_account_payable' => $patient_account_payable,
		':start' => $start,
		':end' => $end
	);

	// Add invoice filter if needed
	if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
		$invoice_no = $_GET['invoice_no'];
		$external_patients_query .= " AND (c.hospital_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
		$private_patients_query .= " AND (c.hospital_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
		$family_patients_query .= " AND (c.insurance_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
		$patient_params[':invoice_no'] = $invoice_no;
		$patient_params[':lg_ref_no'] = $invoice_no;
	}

	// Execute queries
	$external_stmt = $db->prepare($external_patients_query);
	$private_stmt = $db->prepare($private_patients_query);
	$family_stmt = $db->prepare($family_patients_query);

	foreach ($patient_params as $param_name => $param_value) {
		$external_stmt->bindValue($param_name, $param_value);
		$private_stmt->bindValue($param_name, $param_value);
		$family_stmt->bindValue($param_name, $param_value);
	}

	$external_stmt->execute();
	$private_stmt->execute();
	$family_stmt->execute();

	$external_payables = $external_stmt->fetchAll(PDO::FETCH_ASSOC);
	$private_payables = $private_stmt->fetchAll(PDO::FETCH_ASSOC);
	$family_payables = $family_stmt->fetchAll(PDO::FETCH_ASSOC);

	// Combine all patient payables
	$patient_payables = array_merge($external_payables, $private_payables, $family_payables);

	// Calculate total patient payable
	$total_external_payable = 0;
	$total_private_payable = 0;
	$total_family_payable = 0;

	foreach ($external_payables as $patient) {
		$total_external_payable += $patient['balance'];
	}

	foreach ($private_payables as $patient) {
		$total_private_payable += $patient['balance'];
	}

	foreach ($family_payables as $patient) {
		$total_family_payable += $patient['balance'];
	}

	$total_patient_payable = $total_external_payable + $total_private_payable + $total_family_payable;

	// Calculate totals
	$total_supplier_payable = 0;
	foreach ($supplier_payables as $supplier) {
		$total_supplier_payable += $supplier['balance'];
	}


	$grand_total = $total_supplier_payable + $total_patient_payable;
}

include 'inc/functions.php';
redirect_to_active_year();

// Get suppliers with payable balances
$invoices = $db->prepare("SELECT DISTINCT c.invoice_no, s.sn, s.name 
    FROM chart_ledger AS c 
    INNER JOIN stock_company AS s ON c.insurance_no = s.sn 
    WHERE c.account_no = :account_payable 
    AND (c.transc_type = 'CREDIT' OR c.transc_type = 'DEBIT')
    AND (c.cr_amt - c.dr_amt) <> 0");
$invoices->bindValue(':account_payable', $account_payable, PDO::PARAM_INT);
$invoices->execute();
$invoices = $invoices->fetchAll(PDO::FETCH_ASSOC);

// Get patients with payable balances
$invoices_p = $db->prepare("SELECT DISTINCT c.hospital_no AS invoice_no, e.hospital_no AS sn, CONCAT(e.fname, ' ', e.surname) AS name 
    FROM chart_ledger AS c 
    INNER JOIN enrollee AS e ON c.hospital_no = e.hospital_no 
    WHERE c.account_no = :patient_account_payable 
    AND (c.transc_type = 'CREDIT' OR c.transc_type = 'DEBIT')
    AND (c.cr_amt - c.dr_amt) <> 0 
    ORDER BY c.hospital_no");
$invoices_p->bindValue(':patient_account_payable', $patient_account_payable, PDO::PARAM_INT);
$invoices_p->execute();
$invoices_p = $invoices_p->fetchAll(PDO::FETCH_ASSOC);
?>

<body class="fixed-navigation">
	<div id="wrapper">
		<?php include("nav_side.php"); ?>

		<div id="page-wrapper" class="gray-bg sidebar-content">

			<?php include '../../inc/nav_header.php'; ?>
			<div class="wrapper wrapper-content">
				<div class="row">
					<div class="col-lg-12">
						<div class="ibox float-e-margins">
							<div class="ibox-title">
								<h5>Accounting Dashboard</h5>
							</div>
							<div class="ibox-content">
								<form action="" method="get">
									<div class="form-group">
										<label for="start">Start Date</label>
										<input type="date" name="start" id="start" class="form-control" value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>" required>
									</div>

									<div class="form-group">
										<label for="end">End Date</label>
										<input type="date" name="end" id="end" class="form-control" value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>" required>
									</div>

									<div class="form-group">
										<label for="invoice_no">Invoice/Reference</label>
										<select name="invoice_no" id="invoice_no" class="form-control chosen-select">
											<option value="">--All--</option>

											<optgroup label="Suppliers">
												<?php foreach ($invoices as $iv) : ?>
													<?php if ($iv['invoice_no']) : ?>
														<option value="<?= $iv['invoice_no'] ?>" <?= (isset($_GET['invoice_no']) && $_GET['invoice_no'] == $iv['invoice_no']) ? 'selected' : '' ?>>
															<?= $iv['invoice_no'] ?> - <?= $iv['name'] ?>
														</option>
													<?php endif; ?>
												<?php endforeach; ?>
											</optgroup>

											<optgroup label="Patients">
												<?php foreach ($invoices_p as $iv) : ?>
													<?php if ($iv['invoice_no']) : ?>
														<option value="<?= $iv['invoice_no'] ?>" <?= (isset($_GET['invoice_no']) && $_GET['invoice_no'] == $iv['invoice_no']) ? 'selected' : '' ?>>
															<?= $iv['invoice_no'] ?> - <?= $iv['name'] ?>
														</option>
													<?php endif; ?>
												<?php endforeach; ?>
											</optgroup>
										</select>
									</div>

									<div class="form-group">
										<div class="checkbox">
											<label>
												<input type="checkbox" name="include_anonymous" value="1" <?= (isset($_GET['include_anonymous']) && $_GET['include_anonymous']) ? 'checked' : '' ?>>
												Include Anonymous (transactions where supplier/patient name cannot be found)
											</label>
										</div>
									</div>

									<button type="submit" class="btn btn-primary">Display</button>
									<a href="ap_reports.php" class="btn btn-secondary">Clear</a>
								</form>
								<hr>
								<?php if (isset($the_stetment)) { ?>
									<div id="chart_groups">
										<h1>Accounts Payable Reports</h1>
										<h3>For the period starting <?php echo date('d M Y', strtotime($_GET['start'])); ?> and ending
											<?php echo date('d M Y', strtotime($_GET['end'])); ?></h3>

										<div class="row" style="margin-top: 20px;">
											<div class="col-md-12">
												<div class="panel panel-default">
													<div class="panel-heading">
														<h3 class="panel-title">Summary</h3>
													</div>
													<div class="panel-body">
														<table class="table table-bordered">
															<tbody>
																<tr>
																	<td><strong>Total Supplier Payables:</strong></td>
																	<td class="text-right">₦<?= number_format($total_supplier_payable, 2) ?></td>
																</tr>
																<tr>
																	<td><strong>Patient Payables Breakdown:</strong></td>
																	<td></td>
																</tr>
																<tr>
																	<td class="pl-4">&nbsp;&nbsp;&nbsp;&nbsp;External Patients:</td>
																	<td class="text-right">₦<?= number_format($total_external_payable, 2) ?></td>
																</tr>
																<tr>
																	<td class="pl-4">&nbsp;&nbsp;&nbsp;&nbsp;Private Patients:</td>
																	<td class="text-right">₦<?= number_format($total_private_payable, 2) ?></td>
																</tr>
																<tr>
																	<td class="pl-4">&nbsp;&nbsp;&nbsp;&nbsp;Family Patients:</td>
																	<td class="text-right">₦<?= number_format($total_family_payable, 2) ?></td>
																</tr>
																<tr>
																	<td><strong>Total Patient Payables:</strong></td>
																	<td class="text-right">₦<?= number_format($total_patient_payable, 2) ?></td>
																</tr>
																<tr>
																	<td><strong>Grand Total Payables:</strong></td>
																	<td class="text-right"><strong>₦<?= number_format($grand_total, 2) ?></strong></td>
																</tr>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12">
												<h3>Supplier Payables</h3>
												<?php if (empty($supplier_payables)) : ?>
													<i>No supplier payables found in the selected period</i>
												<?php else: ?>

													<div class="table-responsive">
														<table class="table table-striped table-bordered table-hover dataTables-supplier">
															<thead>
																<tr>
																	<th>Supplier Name</th>
																	<th>Insurance ID</th>
																	<th class="text-right">Balance Payable (₦)</th>
																</tr>
															</thead>
															<tbody>
																<?php foreach ($supplier_payables as $supplier) : ?>
																	<tr>
																		<td><?= $supplier['supplier_name'] ?></td>
																		<td><?= $supplier['insurance_no'] ?: 'N/A' ?></td>
																		<td class="text-right"><?= number_format($supplier['balance'], 2) ?></td>
																	</tr>
																<?php endforeach; ?>
															</tbody>
														</table>
													</div>
												<?php endif; ?>


											</div>
											<div class="col-md-12">
												<h3>Patient Payables</h3>
												<?php if (empty($patient_payables)) : ?>
													<i>No patient payables found in the selected period</i>
												<?php else: ?>
													<div class="table-responsive">
														<table class="table table-striped table-bordered table-hover dataTables-patient">
															<thead>
																<tr>
																	<th>Patient Name</th>
																	<th>Hospital No/Insurance ID</th>
																	<th>Patient Type</th>
																	<th class="text-right">Total Credits (₦)</th>
																	<th class="text-right">Total Debits (₦)</th>
																	<th class="text-right">Balance Payable (₦)</th>
																</tr>
															</thead>
															<tbody>
																<?php foreach ($patient_payables as $patient) : ?>
																	<tr>
																		<td><?= $patient['patient_name'] ?></td>
																		<td><?= $patient['hospital_no'] ?: $patient['insurance_no'] ?></td>
																		<td><?= $patient['patient_type'] ?></td>
																		<td class="text-right"><?= number_format($patient['total_credits'], 2) ?></td>
																		<td class="text-right"><?= number_format($patient['total_debits'], 2) ?></td>
																		<td class="text-right"><?= number_format($patient['balance'], 2) ?></td>
																	</tr>
																<?php endforeach; ?>
															</tbody>
														</table>
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
									<button class="btn btn-success" onclick="printDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Payable Report</i></button>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>

				<?php include '../../inc/footer.php'; ?>
			</div>
		</div>

		<?php include('../modal_lock.php'); ?>
		<?php include("../inc/footer_scripts.php"); ?>
		<!-- <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script> -->
		<script src="../js/plugins/chosen/chosen.jquery.js"></script>


		<script>
			$('.chosen-select').chosen({
				width: "100%"
			});
			<?php
			if (isset($error_status) && $error_status == 1) { ?>
				toastr.error('<?php echo isset($error_msg) ? $error_msg : "An error occurred"; ?>', 'Error', {
					timeOut: 5000
				});
			<?php } elseif (isset($error_status) && $error_status == 2) { ?>
				toastr.success('<?php echo isset($error_msg) ? $error_msg : "Operation successful"; ?>', 'Success', {
					timeOut: 5000
				});
			<?php } ?>

			$(document).ready(function() {

				$('.dataTables-supplier').dataTable({
					responsive: true,
					"dom": 'T<"clear">lfrtip',
					"iDisplayLength": 2000,
					// buttons: ['csv', 'print', 'excel', 'pdf'],
					"language": {
						"emptyTable": "No supplier payables found in the selected period"
					}
				});

				$('.dataTables-patient').dataTable({
					responsive: true,
					"dom": 'T<"clear">lfrtip',
					"iDisplayLength": 2000,
					// buttons: ['csv', 'print', 'excel', 'pdf'],
					"language": {
						"emptyTable": "No patient payables found in the selected period"
					}
				});
			});

			function printDiv(divId) {
				var content = document.getElementById(divId).innerHTML;
				var popupWindow = window.open('', '_blank', 'width=600,height=600');
				popupWindow.document.open();
				popupWindow.document.write('<html><head><title>Accounts Payable Report</title>');
				popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
				popupWindow.document.write('<style>body { font-size: 12px; } .dataTables_filter, .dataTables_paginate, .dataTables_info, .dataTables_length { display: none; }</style>');
				popupWindow.document.write('</head><body>');
				popupWindow.document.write(content);
				popupWindow.document.write('</body></html>');
				popupWindow.document.close();
				popupWindow.print();
			}
		</script>

		<script src="../../js/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.responsive.js"></script>
		<script src="../../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
		<script src="../js/idle.js"></script>
	</div>
</body>

</html>