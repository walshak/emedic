<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

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

// Get parameters
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$fiscal_year_id = isset($_GET['fiscal_year']) && $_GET['fiscal_year'] != '' ? $_GET['fiscal_year'] : null;
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-01-01');
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-12-31');

$account_receivable = 2124; //account code for account receivable group, we will use it to get the balance of each supplier from our ledger
$patient_account_receivable = 2121; //account code for patient account receivable group, we will use it to get the balance of each patient from our ledger
$hmo_account_receivable = 1502; //account code for HMO receivable

if (isset($_GET['start']) && isset($_GET['end'])) {
    $the_stetment = 1;

    $start = $_GET['start'];
    $end = $_GET['end'];
    $include_anonymous = isset($_GET['include_anonymous']) ? $_GET['include_anonymous'] : 0;

    // Get supplier receivables - base query with optional anonymous filter
    $supplier_query = "SELECT 
        COALESCE(s.name, 'Anonymous') as supplier_name,
        c.insurance_no,
		c.lg_ref_no,
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :account_receivable THEN c.dr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :account_receivable2 THEN c.cr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        stock_company s ON c.insurance_no = s.sn
    WHERE 
        c.account_no = :account_receivable3 AND
		(hospital_no = '' or hospital_no IS NULL) AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    // Add anonymous filter if checkbox is unchecked
    if (!$include_anonymous) {
        $supplier_query .= " AND s.name IS NOT NULL";
    }


    // Filter by invoice if specified
    $params = array(
        ':account_receivable' => $account_receivable,
        ':account_receivable2' => $account_receivable,
        ':account_receivable3' => $account_receivable,
        ':start' => $start,
        ':end' => $end
    );



    if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
        $invoice_no = $_GET['invoice_no'];
        $supplier_query .= " AND (c.invoice_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";

        $params[':invoice_no'] = $invoice_no;
        $params[':lg_ref_no'] = $invoice_no;
    }

    // Add group by and having clauses - show only negative balances (overpaid suppliers)
    $supplier_query .= " GROUP BY c.insurance_no HAVING balance > 0";

    // Execute supplier query
    $supplier_stmt = $db->prepare($supplier_query);
    foreach ($params as $param_name => $param_value) {
        $supplier_stmt->bindValue($param_name, $param_value);
    }
    $supplier_stmt->execute();
    $supplier_receivables = $supplier_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Define patient categories
    // external patients
    $external_patients_query = "SELECT
		COALESCE(pe.cust_name, 'Anonymous') as patient_name,
			c.hospital_no,
			c.lg_ref_no,
			c.insurance_no,
			'External' as patient_type,
			SUM(c.dr_amt) as total_debits,
			SUM(c.cr_amt) as total_credits,
			SUM(c.dr_amt) - SUM(c.cr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			pharm_ext pe ON c.hospital_no = pe.transc_code
		WHERE
			c.account_no = :patient_account_receivable AND
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
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) - SUM(c.cr_amt) as balance
	FROM
		chart_ledger c
	LEFT JOIN
		enrollee e ON c.hospital_no = e.hospital_no
	WHERE
		c.account_no = :patient_account_receivable AND
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
		SUM(c.dr_amt) as total_debits,
		SUM(c.cr_amt) as total_credits,
		SUM(c.dr_amt) - SUM(c.cr_amt) as balance
		FROM
			chart_ledger c
		LEFT JOIN
			insurance_tbl i ON c.insurance_no = i.insurance_no
		WHERE
			c.account_no = :patient_account_receivable AND
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
        ':patient_account_receivable' => $patient_account_receivable,
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

    $external_receivables = $external_stmt->fetchAll(PDO::FETCH_ASSOC);
    $private_receivables = $private_stmt->fetchAll(PDO::FETCH_ASSOC);
    $family_receivables = $family_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine all patient receivables
    $patient_receivables = array_merge($external_receivables, $private_receivables, $family_receivables);

    // Calculate total patient receivable
    $total_external_receivable = 0;
    $total_private_receivable = 0;
    $total_family_receivable = 0;

    foreach ($external_receivables as $patient) {
        $total_external_receivable += abs($patient['balance']);
    }

    foreach ($private_receivables as $patient) {
        $total_private_receivable += abs($patient['balance']);
    }

    foreach ($family_receivables as $patient) {
        $total_family_receivable += abs($patient['balance']);
    }

    $total_patient_receivable = $total_external_receivable + $total_private_receivable + $total_family_receivable;

    // Calculate totals
    $total_supplier_receivable = 0;
    foreach ($supplier_receivables as $supplier) {
        $total_supplier_receivable += abs($supplier['balance']);
    }

    // Get HMO receivables
    $hmo_query = "SELECT 
        COALESCE(i.insurance_name, 'Anonymous') as hmo_name,
        c.insurance_no,
        c.lg_ref_no,
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :hmo_account THEN c.dr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :hmo_account2 THEN c.cr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        insurance_tbl i ON c.insurance_no = i.insurance_no
    WHERE 
        c.account_no = :hmo_account3 AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    if (!$include_anonymous) {
        $hmo_query .= " AND i.insurance_name IS NOT NULL";
    }

    $hmo_params = array(
        ':hmo_account' => $hmo_account_receivable,
        ':hmo_account2' => $hmo_account_receivable,
        ':hmo_account3' => $hmo_account_receivable,
        ':start' => $start,
        ':end' => $end
    );

    if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
        $invoice_no = $_GET['invoice_no'];
        $hmo_query .= " AND (c.insurance_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $hmo_params[':invoice_no'] = $invoice_no;
        $hmo_params[':lg_ref_no'] = $invoice_no;
    }

    $hmo_query .= " GROUP BY c.insurance_no HAVING balance > 0";

    $hmo_stmt = $db->prepare($hmo_query);
    foreach ($hmo_params as $param_name => $param_value) {
        $hmo_stmt->bindValue($param_name, $param_value);
    }
    $hmo_stmt->execute();
    $hmo_receivables = $hmo_stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_hmo_receivable = 0;
    foreach ($hmo_receivables as $hmo) {
        $total_hmo_receivable += abs($hmo['balance']);
    }


    $grand_total = $total_supplier_receivable + $total_patient_receivable + $total_hmo_receivable;
}

include 'inc/functions.php';
redirect_to_active_year();

// Get suppliers with receivable balances
$invoices = $db->prepare("SELECT DISTINCT c.invoice_no, s.sn, s.name 
    FROM chart_ledger AS c 
    INNER JOIN stock_company AS s ON c.insurance_no = s.sn 
    WHERE c.account_no = :account_receivable 
    AND (c.transc_type = 'CREDIT' OR c.transc_type = 'DEBIT')
    AND (c.dr_amt - c.cr_amt) <> 0");
$invoices->bindValue(':account_receivable', $account_receivable, PDO::PARAM_INT);
$invoices->execute();
$invoices = $invoices->fetchAll(PDO::FETCH_ASSOC);

// Get patients with receivable balances
$invoices_p = $db->prepare("SELECT DISTINCT c.hospital_no AS invoice_no, e.hospital_no AS sn, CONCAT(e.fname, ' ', e.surname) AS name 
    FROM chart_ledger AS c 
    INNER JOIN enrollee AS e ON c.hospital_no = e.hospital_no 
    WHERE c.account_no = :patient_account_receivable 
    AND (c.transc_type = 'CREDIT' OR c.transc_type = 'DEBIT')
    AND (c.dr_amt - c.cr_amt) <> 0 
    ORDER BY c.hospital_no");
$invoices_p->bindValue(':patient_account_receivable', $patient_account_receivable, PDO::PARAM_INT);
$invoices_p->execute();
$invoices_p = $invoices_p->fetchAll(PDO::FETCH_ASSOC);

// Get HMOs with receivable balances
$invoices_hmo = $db->prepare("SELECT DISTINCT c.insurance_no AS invoice_no, i.insurance_no AS sn, i.insurance_name AS name 
    FROM chart_ledger AS c 
    INNER JOIN insurance_tbl AS i ON c.insurance_no = i.insurance_no 
    WHERE c.account_no = :hmo_account_receivable 
    AND (c.transc_type = 'CREDIT' OR c.transc_type = 'DEBIT')
    AND (c.dr_amt - c.cr_amt) <> 0 
    ORDER BY i.insurance_name");
$invoices_hmo->bindValue(':hmo_account_receivable', $hmo_account_receivable, PDO::PARAM_INT);
$invoices_hmo->execute();
$invoices_hmo = $invoices_hmo->fetchAll(PDO::FETCH_ASSOC);
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
									<!-- Filter Form -->
									<div class="row">
										<div class="col-md-12">
											<form method="GET" class="well" style="background: #f8f9fa; border: 1px solid #e9ecef; padding: 20px; border-radius: 8px;">

												<!-- Date Selection Row -->
												<div class="row mb-3">
					<?php
						$active_fy = get_active_year();
					?>
					<?php if ($active_fy): ?>
					<div class="col-md-12" style="margin-bottom: 10px;">
						<div class="alert alert-info" style="padding: 8px 14px; margin-bottom: 0;">
							<i class="fa fa-info-circle"></i>
							<strong>Active Fiscal Year:</strong>
							<?= date('M d, Y', strtotime($active_fy['begin'])) ?> &ndash; <?= date('M d, Y', strtotime($active_fy['end'])) ?>
							&nbsp;&mdash;&nbsp;<small class="text-muted">Select a fiscal year below to scope this report to that accounting period.</small>
						</div>
					</div>
					<?php endif; ?>

					<div class="col-md-3">
						<?php echo render_fiscal_year_filter($fiscal_year_id); ?>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="start" class="control-label"><strong>Start Date</strong></label>
							<input type="date" id="start" name="start" class="form-control" value="<?php echo $start_date; ?>" required>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="end" class="control-label"><strong>End Date</strong></label>
							<input type="date" id="end" name="end" class="form-control" value="<?php echo $end_date; ?>" required>
						</div>
					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label for="invoice_no" class="control-label"><strong>Invoice/Reference</strong></label>
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

								<optgroup label="HMOs">
									<?php foreach ($invoices_hmo as $iv) : ?>
										<?php if ($iv['invoice_no']) : ?>
											<option value="<?= $iv['invoice_no'] ?>" <?= (isset($_GET['invoice_no']) && $_GET['invoice_no'] == $iv['invoice_no']) ? 'selected' : '' ?>>
												<?= $iv['invoice_no'] ?> - <?= $iv['name'] ?>
											</option>
										<?php endif; ?>
									<?php endforeach; ?>
								</optgroup>
							</select>
						</div>
					</div>

												</div>

												<div class="row mb-3">
													<div class="col-md-12">
														<div class="form-group">
															<div class="checkbox">
																<label>
																	<input type="checkbox" name="include_anonymous" id="include_anonymous" value="1" <?= (isset($_GET['include_anonymous']) && $_GET['include_anonymous']) ? 'checked' : '' ?>>
																	<strong>Include Anonymous</strong> (transactions where supplier/patient name cannot be found)
																</label>
															</div>
														</div>
													</div>
												</div>

												<div class="row mb-3">
													<div class="col-md-12">
														<div class="form-group" style="text-align: right;">
															<label class="control-label">&nbsp;</label>
															<div style="margin-top: 8px;">
																<button type="submit" class="btn btn-primary" title="Click to fetch data">
																	<i class="fa fa-search"></i> <strong>DISPLAY</strong>
																</button>
                                                                <a href="ar_reports.php" class="btn btn-secondary">Clear</a>
																<button type="button" class="btn btn-success" onclick="exportToCSV()" title="Export to CSV">
																	<i class="fa fa-download"></i> Export CSV
																</button>
																<button type="button" class="btn btn-primary" onclick="printDiv('chart_groups')" title="Print Report">
																	<i class="fa fa-print"></i> Print
																</button>
															</div>
														</div>
													</div>
												</div>

												<!-- Quick Date Shortcuts -->
												<div class="row">
													<div class="col-md-12">
														<div class="form-group" style="margin-bottom: 0;">
															<label class="control-label"><strong>Quick Date Shortcuts</strong></label>
															<div style="margin-top: 5px;">
																<button type="button" class="btn btn-xs btn-info" onclick="setCurrentMonth()">
																	<i class="fa fa-calendar"></i> Current Month
																</button>
																<button type="button" class="btn btn-xs btn-info" onclick="setCurrentQuarter()">
																	<i class="fa fa-calendar-alt"></i> Current Quarter
																</button>
																<button type="button" class="btn btn-xs btn-info" onclick="setCurrentYear()">
																	<i class="fa fa-calendar-year"></i> Current Year
																</button>
																<button type="button" class="btn btn-xs btn-warning" onclick="setLastMonth()">
																	<i class="fa fa-backward"></i> Last Month
																</button>
																<button type="button" class="btn btn-xs btn-warning" onclick="setLastQuarter()">
																	<i class="fa fa-step-backward"></i> Last Quarter
																</button>
															</div>
														</div>
													</div>
												</div>

											</form>
										</div>
									</div>
                                <hr>
                                <?php if (isset($the_stetment)) { ?>
                                    <div id="chart_groups">
                                        <h1>Accounts Receivable Reports</h1>
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
                                                                    <td><strong>Total Supplier Receivables:</strong></td>
                                                                    <td class="text-right">₦<?= number_format($total_supplier_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Patient Receivables Breakdown:</strong></td>
                                                                    <td></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="pl-4">&nbsp;&nbsp;&nbsp;&nbsp;External Patients:</td>
                                                                    <td class="text-right">₦<?= number_format($total_external_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="pl-4">&nbsp;&nbsp;&nbsp;&nbsp;Private Patients:</td>
                                                                    <td class="text-right">₦<?= number_format($total_private_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="pl-4">&nbsp;&nbsp;&nbsp;&nbsp;Family Patients:</td>
                                                                    <td class="text-right">₦<?= number_format($total_family_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Total Patient Receivables:</strong></td>
                                                                    <td class="text-right">₦<?= number_format($total_patient_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Total HMO Receivables:</strong></td>
                                                                    <td class="text-right">₦<?= number_format($total_hmo_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Grand Total Receivables:</strong></td>
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
                                                <h3>Supplier Receivables</h3>
                                                <?php if (empty($supplier_receivables)) : ?>
                                                    <i>No supplier receivables found in the selected period</i>
                                                <?php else: ?>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover dataTables-supplier">
                                                            <thead>
                                                                <tr>
                                                                    <th>Supplier Name</th>
                                                                    <th>Insurance ID</th>
                                                                    <th class="text-right">Balance Receivable (₦)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($supplier_receivables as $supplier) : ?>
                                                                    <tr>
                                                                        <td><?= $supplier['supplier_name'] ?></td>
                                                                        <td><?= $supplier['insurance_no'] ?: 'N/A' ?></td>
                                                                        <td class="text-right"><?= number_format(abs($supplier['balance']), 2) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>


                                            </div>
                                            <div class="col-md-12">
                                                <h3>HMO Receivables</h3>
                                                <?php if (empty($hmo_receivables)) : ?>
                                                    <i>No HMO receivables found in the selected period</i>
                                                <?php else: ?>

                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover dataTables-hmo">
                                                            <thead>
                                                                <tr>
                                                                    <th>HMO Name</th>
                                                                    <th>Insurance ID</th>
                                                                    <th class="text-right">Balance Receivable (₦)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($hmo_receivables as $hmo) : ?>
                                                                    <tr>
                                                                        <td><?= $hmo['hmo_name'] ?></td>
                                                                        <td><?= $hmo['insurance_no'] ?: 'N/A' ?></td>
                                                                        <td class="text-right"><?= number_format(abs($hmo['balance']), 2) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>


                                            </div>
                                            <div class="col-md-12">
                                                <h3>Patient Receivables</h3>
                                                <?php if (empty($patient_receivables)) : ?>
                                                    <i>No patient receivables found in the selected period</i>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover dataTables-patient">
                                                            <thead>
                                                                <tr>
                                                                    <th>Patient Name</th>
                                                                    <th>Hospital No/Insurance ID</th>
                                                                    <th>Patient Type</th>
                                                                    <th class="text-right">Total Debits (₦)</th>
                                                                    <th class="text-right">Total Credits (₦)</th>
                                                                    <th class="text-right">Balance Receivable (₦)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($patient_receivables as $patient) : ?>
                                                                    <tr>
                                                                        <td><?= $patient['patient_name'] ?></td>
                                                                        <td><?= $patient['hospital_no'] ?: $patient['insurance_no'] ?></td>
                                                                        <td><?= $patient['patient_type'] ?></td>
                                                                        <td class="text-right"><?= number_format($patient['total_debits'], 2) ?></td>
                                                                        <td class="text-right"><?= number_format($patient['total_credits'], 2) ?></td>
                                                                        <td class="text-right"><?= number_format(abs($patient['balance']), 2) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
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
			// Hospital header HTML for printouts
			var hospitalHeader = `<?php echo addslashes($hospital_table); ?>`;

			// Auto-update date range when year changes
			document.getElementById('year_select').addEventListener('change', function() {
				const year = this.value;
				document.getElementById('start').value = `${year}-01-01`;
				document.getElementById('end').value = `${year}-12-31`;
			});

			// Quick date shortcut functions
			function setCurrentMonth() {
				const now = new Date();
				const year = now.getFullYear();
				const month = String(now.getMonth() + 1).padStart(2, '0');
				document.getElementById('start').value = `${year}-${month}-01`;
				const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
				document.getElementById('end').value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year_select').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function setCurrentQuarter() {
				const now = new Date();
				const year = now.getFullYear();
				const quarter = Math.floor(now.getMonth() / 3);
				const startMonth = String(quarter * 3 + 1).padStart(2, '0');
				const endMonth = String(quarter * 3 + 3).padStart(2, '0');
				document.getElementById('start').value = `${year}-${startMonth}-01`;
				const lastDay = new Date(year, quarter * 3 + 3, 0).getDate();
				document.getElementById('end').value = `${year}-${endMonth}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year_select').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function setCurrentYear() {
				const year = new Date().getFullYear();
				document.getElementById('start').value = `${year}-01-01`;
				document.getElementById('end').value = `${year}-12-31`;
				document.getElementById('year_select').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function setLastMonth() {
				const now = new Date();
				const lastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
				const year = lastMonth.getFullYear();
				const month = String(lastMonth.getMonth() + 1).padStart(2, '0');
				document.getElementById('start').value = `${year}-${month}-01`;
				const lastDay = new Date(year, lastMonth.getMonth() + 1, 0).getDate();
				document.getElementById('end').value = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year_select').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function setLastQuarter() {
				const now = new Date();
				const currentQuarter = Math.floor(now.getMonth() / 3);
				const lastQuarter = currentQuarter === 0 ? 3 : currentQuarter - 1;
				const year = currentQuarter === 0 ? now.getFullYear() - 1 : now.getFullYear();
				const startMonth = String(lastQuarter * 3 + 1).padStart(2, '0');
				const endMonth = String(lastQuarter * 3 + 3).padStart(2, '0');
				document.getElementById('start').value = `${year}-${startMonth}-01`;
				const lastDay = new Date(year, lastQuarter * 3 + 3, 0).getDate();
				document.getElementById('end').value = `${year}-${endMonth}-${String(lastDay).padStart(2, '0')}`;
				document.getElementById('year_select').value = year;
				document.getElementById('start').closest('form').submit();
			}

			function exportToCSV() {
				var startDate = document.querySelector('input[name="start"]').value;
				var endDate = document.querySelector('input[name="end"]').value;
				var invoiceNo = document.querySelector('select[name="invoice_no"]').value;
				var includeAnon = document.querySelector('input[name="include_anonymous"]').checked ? 1 : 0;

				if (!startDate || !endDate) {
					alert('Please select start and end dates first.');
					return;
				}

				var exportUrl = 'ar_reports_export_csv.php?start=' + encodeURIComponent(startDate) +
					'&end=' + encodeURIComponent(endDate) + '&invoice_no=' + encodeURIComponent(invoiceNo) + '&include_anonymous=' + includeAnon;

				var link = document.createElement('a');
				link.href = exportUrl;
				link.download = 'AR_Reports_' + startDate + '_to_' + endDate + '.csv';
				link.style.display = 'none';

				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			}

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
                        "emptyTable": "No supplier receivables found in the selected period"
                    }
                });

                $('.dataTables-patient').dataTable({
                    responsive: true,
                    "dom": 'T<"clear">lfrtip',
                    "iDisplayLength": 2000,
                    // buttons: ['csv', 'print', 'excel', 'pdf'],
                    "language": {
                        "emptyTable": "No patient receivables found in the selected period"
                    }
                });

                $('.dataTables-hmo').dataTable({
                    responsive: true,
                    "dom": 'T<"clear">lfrtip',
                    "iDisplayLength": 2000,
                    // buttons: ['csv', 'print', 'excel', 'pdf'],
                    "language": {
                        "emptyTable": "No HMO receivables found in the selected period"
                    }
                });
            });

            function printDiv(divId) {
                var content = document.getElementById(divId).innerHTML;
                var startDate = document.getElementById('start') ? document.getElementById('start').value : '';
                var endDate = document.getElementById('end') ? document.getElementById('end').value : '';
                
                var filterSummary = '<div style="text-align:center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; font-family: arial;">' +
                    '<h2 style="margin:0; padding:0; color: #333;">Accounts Receivable Report</h2>' +
                    '<p style="margin:5px 0 0 0; font-size: 14px; color: #555;">' +
                    '<strong>Date Range:</strong> ' + startDate + ' to ' + endDate +
                    '</p></div>';

                var popupWindow = window.open('', '_blank', 'width=900,height=900');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>Accounts Receivable Report</title>');
                popupWindow.document.write('<link rel="stylesheet" type="text/css" href="../../css/bootstrap.min.css">');
                popupWindow.document.write(`
                    <style>
                        body { font-size: 14px; color: #222; background: #fff; }
                        .table { width: 100%; border-collapse: collapse !important; margin-bottom: 20px;}
                        .table td, .table th { padding: 5px; border: 1px solid #ddd; }
                        a { text-decoration: none !important; color: inherit !important; }
						.panel { border: 1px solid #ddd; margin-bottom: 20px; }
						.panel-heading { background-color: #f5f5f5; padding: 10px; border-bottom: 1px solid #ddd; }
						.panel-title { margin: 0; font-size: 16px; }
						.panel-body { padding: 15px; }
                        .dataTables_filter, .dataTables_paginate, .dataTables_info, .dataTables_length { display: none; }
                                                /* Hide original headings in the print popup to prevent duplication */
                        body > h1, body > h3, .text-center.mb-4 { display: none !important; }
                        @media print {
                            a[href]:after { content: none !important; }
                        }
                    </style>
                `);
                popupWindow.document.write('</head><body>');
                popupWindow.document.write(hospitalHeader);
                popupWindow.document.write(filterSummary);
                popupWindow.document.write(content);
                popupWindow.document.write('</body></html>');
                popupWindow.document.close();
                setTimeout(function() {
                    popupWindow.focus();
                    popupWindow.print();
                }, 1000);
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