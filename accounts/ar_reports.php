<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

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

                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="include_anonymous" value="1" <?= (isset($_GET['include_anonymous']) && $_GET['include_anonymous']) ? 'checked' : '' ?>>
                                                Include Anonymous (transactions where supplier/patient name cannot be found)
                                            </label>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Display</button>
                                    <a href="ar_reports.php" class="btn btn-secondary">Clear</a>
                                </form>
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
                                    <button class="btn btn-success" onclick="printDiv('chart_groups')"><i class="fa fa-print">&nbsp; Print Receivable Report</i></button>
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
                var popupWindow = window.open('', '_blank', 'width=600,height=600');
                popupWindow.document.open();
                popupWindow.document.write('<html><head><title>Accounts Receivable Report</title>');
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