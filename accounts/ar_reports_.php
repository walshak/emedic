<?php include("../Connections/Conn.php"); ?>

<?php
session_start();
include('../inc/header.php');

// Account codes
$account_payable = 2124; // For suppliers that we have overpaid
$patient_account_payable = 2121; // For patients that have overdrawn accounts
$account_receivable = 1512; // For transactions posted to General account receivable
$patient_account_receivable = 1502; // For transactions posted to patient accounts receivable

if (isset($_GET['start']) && isset($_GET['end'])) {
    $the_statement = 1;

    $start = $_GET['start'];
    $end = $_GET['end'];

    // Get suppliers that we have overpaid (negative balance in AP becomes AR)
    $supplier_query = "SELECT 
        COALESCE(s.name, 'Anonymous') as supplier_name,
        c.insurance_no,
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :account_payable THEN c.cr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :account_payable2 THEN c.dr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        stock_company s ON c.insurance_no = s.sn
    WHERE 
        c.account_no = :account_payable3 AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    // Get patients that have overdrawn accounts (negative balance in AP becomes AR)
    $patient_query = "SELECT 
        COALESCE(CONCAT(e.fname, ' ', e.surname), 'Anonymous') as patient_name,
        c.hospital_no,
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :patient_account_payable THEN c.cr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :patient_account_payable2 THEN c.dr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        enrollee e ON c.hospital_no = e.sn
    WHERE 
        c.account_no = :patient_account_payable3 AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    // Get transactions posted to General accounts receivable
    $general_ar_query = "SELECT 
        c.lg_ref_no,
        c.item_services,
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :account_receivable THEN c.cr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :account_receivable2 THEN c.dr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    WHERE 
        c.account_no = :account_receivable3 AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    // Get transactions posted to patient accounts receivable
    $patient_ar_query = "SELECT 
        COALESCE(CONCAT(e.fname, ' ', e.surname), 'Anonymous') as patient_name,
        c.hospital_no,
        c.lg_ref_no,
        SUM(CASE WHEN c.transc_type = 'CREDIT' AND c.account_no = :patient_account_receivable THEN c.cr_amt ELSE 0 END) - 
        SUM(CASE WHEN c.transc_type = 'DEBIT' AND c.account_no = :patient_account_receivable2 THEN c.dr_amt ELSE 0 END) as balance
    FROM 
        chart_ledger c
    LEFT JOIN 
        enrollee e ON c.hospital_no = e.sn
    WHERE 
        c.account_no = :patient_account_receivable3 AND
        DATE(c.date_entry) BETWEEN :start AND :end";

    // Prepare parameters
    $params = array(
        ':account_payable' => $account_payable,
        ':account_payable2' => $account_payable,
        ':account_payable3' => $account_payable,
        ':start' => $start,
        ':end' => $end
    );

    $patient_params = array(
        ':patient_account_payable' => $patient_account_payable,
        ':patient_account_payable2' => $patient_account_payable,
        ':patient_account_payable3' => $patient_account_payable,
        ':start' => $start,
        ':end' => $end
    );

    $general_ar_params = array(
        ':account_receivable' => $account_receivable,
        ':account_receivable2' => $account_receivable,
        ':account_receivable3' => $account_receivable,
        ':start' => $start,
        ':end' => $end
    );

    $patient_ar_params = array(
        ':patient_account_receivable' => $patient_account_receivable,
        ':patient_account_receivable2' => $patient_account_receivable,
        ':patient_account_receivable3' => $patient_account_receivable,
        ':start' => $start,
        ':end' => $end
    );

    // Filter by invoice if specified
    if (isset($_GET['invoice_no']) && !empty($_GET['invoice_no'])) {
        $invoice_no = $_GET['invoice_no'];
        $supplier_query .= " AND (c.invoice_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $patient_query .= " AND (c.hospital_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $general_ar_query .= " AND (c.invoice_no = :invoice_no OR c.lg_ref_no = :lg_ref_no)";
        $patient_ar_query .= " AND (c.invoice_no = :invoice_no OR c.hospital_no = :invoice_no2 OR c.lg_ref_no = :lg_ref_no)";

        $params[':invoice_no'] = $invoice_no;
        $params[':lg_ref_no'] = $invoice_no;

        $patient_params[':invoice_no'] = $invoice_no;
        $patient_params[':lg_ref_no'] = $invoice_no;

        $general_ar_params[':invoice_no'] = $invoice_no;
        $general_ar_params[':lg_ref_no'] = $invoice_no;

        $patient_ar_params[':invoice_no'] = $invoice_no;
        $patient_ar_params[':invoice_no2'] = $invoice_no;
        $patient_ar_params[':lg_ref_no'] = $invoice_no;
    }

    // Add group by and having clauses - Note: for overpaid suppliers we want negative balance
    $supplier_query .= " GROUP BY c.insurance_no HAVING balance < 0";
    $patient_query .= " GROUP BY c.hospital_no HAVING balance < 0";

    $general_ar_query .= " GROUP BY c.lg_ref_no HAVING balance > 0";
    $patient_ar_query .= " GROUP BY c.hospital_no HAVING balance > 0";

    // Execute supplier query
    $supplier_stmt = $db->prepare($supplier_query);
    foreach ($params as $param_name => $param_value) {
        $supplier_stmt->bindValue($param_name, $param_value);
    }
    $supplier_stmt->execute();
    $supplier_receivables = $supplier_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert negative balances to positive for display (they are receivables now)
    foreach ($supplier_receivables as &$supplier) {
        $supplier['balance'] = abs($supplier['balance']);
    }

    // Execute patient query
    $patient_stmt = $db->prepare($patient_query);
    foreach ($patient_params as $param_name => $param_value) {
        $patient_stmt->bindValue($param_name, $param_value);
    }
    $patient_stmt->execute();
    $patient_receivables = $patient_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert negative balances to positive for display (they are receivables now)
    foreach ($patient_receivables as &$patient) {
        $patient['balance'] = abs($patient['balance']);
    }

    // Execute general AR transactions query
    $general_ar_stmt = $db->prepare($general_ar_query);
    foreach ($general_ar_params as $param_name => $param_value) {
        $general_ar_stmt->bindValue($param_name, $param_value);
    }
    $general_ar_stmt->execute();
    $general_ar_transactions = $general_ar_stmt->fetchAll(PDO::FETCH_ASSOC);


    // Execute patient AR transactions query
    $patient_ar_stmt = $db->prepare($patient_ar_query);
    foreach ($patient_ar_params as $param_name => $param_value) {
        $patient_ar_stmt->bindValue($param_name, $param_value);
    }
    $patient_ar_stmt->execute();
    $patient_ar_transactions = $patient_ar_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $total_supplier_receivable = 0;
    foreach ($supplier_receivables as $supplier) {
        $total_supplier_receivable += $supplier['balance'];
    }

    $total_patient_receivable = 0;
    foreach ($patient_receivables as $patient) {
        $total_patient_receivable += $patient['balance'];
    }

    $total_general_ar = 0;
    foreach ($general_ar_transactions as $general_ar) {
        $total_general_ar += $general_ar['balance'];
    }

    $total_patient_ar = 0;
    foreach ($patient_ar_transactions as $patient_ar) {
        $total_patient_ar += $patient_ar['balance'];
    }
    $grand_total = $total_supplier_receivable + $total_patient_receivable + $total_general_ar + $total_patient_ar;
}

include 'inc/functions.php';
redirect_to_active_year();

// Get suppliers for dropdown
$invoices = $db->prepare("SELECT DISTINCT c.invoice_no, s.sn, s.name 
    FROM chart_ledger AS c 
    INNER JOIN stock_company AS s ON c.insurance_no = s.sn 
    WHERE (c.account_no = :account_payable OR c.account_no = :account_receivable)
    AND (c.transc_type = 'CREDIT' OR c.transc_type = 'DEBIT')");
$invoices->bindValue(':account_payable', $account_payable, PDO::PARAM_INT);
$invoices->bindValue(':account_receivable', $account_receivable, PDO::PARAM_INT);
$invoices->execute();
$invoices = $invoices->fetchAll(PDO::FETCH_ASSOC);

// Get patients for dropdown
$invoices_p = $db->prepare("SELECT DISTINCT c.hospital_no AS invoice_no, e.sn, CONCAT(e.fname, ' ', e.surname) AS name 
    FROM chart_ledger AS c 
    INNER JOIN enrollee AS e ON c.hospital_no = e.sn 
    WHERE (c.account_no = :patient_account_payable OR c.account_no = :patient_account_receivable)
    AND (c.transc_type = 'CREDIT' OR c.transc_type = 'DEBIT')
    ORDER BY c.hospital_no");
$invoices_p->bindValue(':patient_account_payable', $patient_account_payable, PDO::PARAM_INT);
$invoices_p->bindValue(':patient_account_receivable', $patient_account_receivable, PDO::PARAM_INT);
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
                                        <input type="date" id="start" name="start" class="form-control" value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="end">End Date</label>
                                        <input type="date" id="end" name="end" class="form-control" value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="invoice_no">Invoice/Reference</label>
                                        <select name="invoice_no" id="invoice_no" class="form-control chosen-select">
                                            <option value="">--All--</option>

                                            <optgroup label="Suppliers">
                                                <?php foreach ($invoices as $iv) : ?>
                                                    <?php if (!empty($iv['invoice_no'])) : ?>
                                                        <option value="<?= $iv['invoice_no'] ?>" <?= (isset($_GET['invoice_no']) && $_GET['invoice_no'] == $iv['invoice_no']) ? 'selected' : '' ?>>
                                                            <?= $iv['invoice_no'] ?> - <?= $iv['name'] ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </optgroup>

                                            <optgroup label="Patients">
                                                <?php foreach ($invoices_p as $iv) : ?>
                                                    <?php if (!empty($iv['invoice_no'])) : ?>
                                                        <option value="<?= $iv['invoice_no'] ?>" <?= (isset($_GET['invoice_no']) && $_GET['invoice_no'] == $iv['invoice_no']) ? 'selected' : '' ?>>
                                                            <?= $iv['invoice_no'] ?> - <?= $iv['name'] ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Display</button>
                                    <a href="ar_reports.php" class="btn btn-secondary">Clear</a>
                                </form>
                                <hr>
                                <?php if (isset($the_statement)) { ?>
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
                                                                    <td><strong>Supplier Overpayments (Receivable):</strong></td>
                                                                    <td class="text-right">₦<?= number_format($total_supplier_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Patient Overdrawn (Receivable):</strong></td>
                                                                    <td class="text-right">₦<?= number_format($total_patient_receivable, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>General A/R Balance:</strong></td>
                                                                    <td class="text-right">₦<?= number_format($total_general_ar, 2) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Patient A/R Balance:</strong></td>
                                                                    <td class="text-right">₦<?= number_format($total_patient_ar, 2) ?></td>
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
                                                <h3>Supplier Overpayments (Receivable)</h3>
                                                <?php if (empty($supplier_receivables)) : ?>
                                                    <i>No supplier overpayments found in the selected period</i>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover dataTables-supplier">
                                                            <thead>
                                                                <tr>
                                                                    <th>Supplier Name</th>
                                                                    <th>ID</th>
                                                                    <th class="text-right">Balance Receivable (₦)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($supplier_receivables as $supplier) : ?>
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
                                        </div>

                                        <div class="row" style="margin-top: 20px;">
                                            <div class="col-md-12">
                                                <h3>Patient Overdrawn Accounts (Receivable)</h3>
                                                <?php if (empty($patient_receivables)) : ?>
                                                    <i>No patient overdrawn accounts found in the selected period</i>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover dataTables-patient">
                                                            <thead>
                                                                <tr>
                                                                    <th>Patient Name</th>
                                                                    <th>Hospital No</th>
                                                                    <th class="text-right">Balance Receivable (₦)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($patient_receivables as $patient) : ?>
                                                                    <tr>
                                                                        <td><?= $patient['patient_name'] ?></td>
                                                                        <td><?= $patient['hospital_no'] ?: 'N/A' ?></td>
                                                                        <td class="text-right"><?= number_format($patient['balance'], 2) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="row" style="margin-top: 20px;">
                                            <div class="col-md-12">
                                                <h3>Transactions Posted to General Accounts Receivable</h3>
                                                <?php if (empty($general_ar_transactions)) : ?>
                                                    <i>No general accounts receivable transactions found in the selected period</i>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover dataTables-general-ar">
                                                            <thead>
                                                                <tr>
                                                                    <th>Reference/Invoice</th>
                                                                    <th>Description</th>
                                                                    <th class="text-right">Balance</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($general_ar_transactions as $transaction) : ?>
                                                                    <tr>

                                                                        <td><?= $transaction['lg_ref_no'] ?: $transaction['lg_ref_no'] ?: 'N/A' ?></td>
                                                                        <td><?= $transaction['item_services'] ?></td>
                                                                        <td class="text-right"><?= number_format($transaction['balance'], 2) ?></td>
                                                                    </tr>
                                                            </tbody>
                                                        <?php endforeach; ?>

                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="row" style="margin-top: 20px;">
                                            <div class="col-md-12">
                                                <h3>Transactions Posted to Patient Accounts Receivable</h3>
                                                <?php if (empty($patient_ar_transactions)) : ?>
                                                    <i>No patient accounts receivable transactions found in the selected period</i>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-bordered table-hover dataTables-patient-ar">
                                                            <thead>
                                                                <tr>
                                                                    <th>Patient</th>
                                                                    <th>Hospital No</th>
                                                                    <th>Reference/Invoice</th>
                                                                    <th class="text-right">Balance (₦)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($patient_ar_transactions as $transaction) : ?>
                                                                    <tr>
                                                                        <td><?= $transaction['patient_name'] ?></td>

                                                                        <td><?= $transaction['hospital_no'] ?: 'N/A' ?></td>
                                                                        <td><?= $transaction['lg_ref_no'] ?: $transaction['lg_ref_no'] ?: 'N/A' ?></td>
                                                                        <td class="text-right"><?= number_format($transaction['balance'], 2) ?></td>
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
                    "language": {
                        "emptyTable": "No supplier overpayments found in the selected period"
                    }
                });

                $('.dataTables-patient').dataTable({
                    responsive: true,
                    "dom": 'T<"clear">lfrtip',
                    "iDisplayLength": 2000,
                    "language": {
                        "emptyTable": "No patient overdrawn accounts found in the selected period"
                    }
                });

                $('.dataTables-general-ar').dataTable({
                    responsive: true,
                    "dom": 'T<"clear">lfrtip',
                    "iDisplayLength": 2000,
                    "language": {
                        "emptyTable": "No general accounts receivable transactions found in the selected period"
                    }
                });

                $('.dataTables-patient-ar').dataTable({
                    responsive: true,
                    "dom": 'T<"clear">lfrtip',
                    "iDisplayLength": 2000,
                    "language": {
                        "emptyTable": "No patient accounts receivable transactions found in the selected period"
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