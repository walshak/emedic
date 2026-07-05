<?php
session_start();
include("../Connections/Conn.php");
header("Content-Type: text/html; charset=utf-8");

$filters = [
    'dept_id' => isset($_GET['dept_id']) ? trim($_GET['dept_id']) : '',
    'procedure' => isset($_GET['procedure']) ? trim($_GET['procedure']) : '',
    'insurance_type' => isset($_GET['insurance_type']) ? trim($_GET['insurance_type']) : '',
    'consultant_name' => isset($_GET['consultant_name']) ? trim($_GET['consultant_name']) : '',
    'prepared_by' => isset($_GET['prepared_by']) ? trim($_GET['prepared_by']) : '',
    'created_by' => isset($_GET['created_by']) ? trim($_GET['created_by']) : '',
    'theater' => isset($_GET['theater']) ? trim($_GET['theater']) : '',
    'procedure_time' => isset($_GET['procedure_time']) ? trim($_GET['procedure_time']) : '',
    'pay_mode' => isset($_GET['pay_mode']) ? trim($_GET['pay_mode']) : '',
    'paystatus' => isset($_GET['paystatus']) ? trim($_GET['paystatus']) : '',
    'hospital_no' => isset($_GET['hospital_no']) ? trim($_GET['hospital_no']) : '',
    'start_date' => isset($_GET['start_date']) ? trim($_GET['start_date']) : '',
    'end_date' => isset($_GET['end_date']) ? trim($_GET['end_date']) : ''
];

if (empty($filters['start_date']) || empty($filters['end_date'])) {
    die("<h3 style='color:red;text-align:center;'>Date range is required.</h3>");
}

// ===================
// Build Query
// ===================
$sql = "SELECT DISTINCT 
            p.sn, p.hospital_no, p.app_no, p.name, p.procedures, p.insurance_type, 
            p.primary_diag, p.operation_procedure, p.dept_id, p.anaesthetia_type, 
            p.consultant_name, p.consultant_id, p.sDate, p.cost, p.prepared_by, 
            p.created_by, p.date_entry, p.status, p.pre_opt_notes_id, 
            p.anas_opt_notes_id, p.post_opt_notes_id, p.performed_date, 
            p.post_op_results, p.require_theater, p.theater, p.procedure_time, 
            p.sale_no, p.old_procedure_name, d.department
        FROM procedures p
        LEFT JOIN department d ON p.dept_id = d.sn
        WHERE DATE(p.date_entry) BETWEEN :start_date AND :end_date";

$params = [
    ':start_date' => $filters['start_date'],
    ':end_date' => $filters['end_date']
];

// Apply additional filters dynamically
foreach ($filters as $key => $val) {
    if (!empty($val) && !in_array($key, ['start_date', 'end_date', 'paystatus'])) {
        $col = '';
        switch ($key) {
            case 'procedure':
                $col = 'p.procedures';
                break;
            case 'dept_id':
                $col = 'p.dept_id';
                break;
            case 'insurance_type':
                $col = 'p.insurance_type';
                break;
            case 'consultant_name':
                $col = 'p.consultant_name';
                break;
            case 'prepared_by':
                $col = 'p.prepared_by';
                break;
            case 'created_by':
                $col = 'p.created_by';
                break;
            case 'theater':
                $col = 'p.theater';
                break;
            case 'procedure_time':
                $col = 'p.procedure_time';
                break;
            case 'hospital_no':
                $col = 'p.hospital_no';
                break;
        }
        if ($col != '') {
            $sql .= " AND $col = :$key";
            $params[":$key"] = $val;
        }
    }
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===================
// Filter rows with valid procedure (visible rows)
// ===================
$displayRows = [];
$uniqueSNs = [];
foreach ($rows as $r) {
    if (!empty($r['procedures']) && !in_array($r['sn'], $uniqueSNs)) {
        $displayRows[] = $r;
        $uniqueSNs[] = $r['sn'];
    }
}

// ===================
// Related Data
// ===================
$saleNos = array_filter(array_column($displayRows, 'sale_no'));
$paymentData = [];
if ($saleNos) {
    $in = implode(',', array_fill(0, count($saleNos), '?'));
    $payStmt = $db->prepare("SELECT sn, invoice_date, transact_date, pay, pay_mode, paystatus 
                             FROM patient_ap_services WHERE sn IN ($in)");
    $payStmt->execute($saleNos);
    foreach ($payStmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $paymentData[$p['sn']] = $p;
    }
}

$procSns = array_column($displayRows, 'sn');
$resourcePersons = [];
if ($procSns) {
    $in2 = implode(',', array_fill(0, count($procSns), '?'));
    $resStmt = $db->prepare("SELECT name, role, prdure_sn FROM procedure_resources WHERE prdure_sn IN ($in2)");
    $resStmt->execute($procSns);
    foreach ($resStmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $sn = $r['prdure_sn'];
        if (!isset($resourcePersons[$sn])) $resourcePersons[$sn] = [];
        $resourcePersons[$sn][] = $r['name'] . " (role: " . $r['role'] . ")";
    }
}

// ===================
// Filter by paystatus if provided
// ===================
if (!empty($filters['paystatus'])) {
    $filterStatus = intval($filters['paystatus']); // 1 = Paid, 0 = Unpaid
    $displayRows = array_filter($displayRows, function ($r) use ($paymentData, $filterStatus) {
        $pay = $paymentData[$r['sale_no']] ?? null;
        if (!$pay) return $filterStatus === 0; // treat missing payment as unpaid
        return intval($pay['paystatus']) === $filterStatus;
    });
}

// Update total procedures after filtering
$totalProcedures = count($displayRows);

// ===================
// CSV Export
// ===================
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="procedure_report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Hospital No', 'Name', 'Procedure', 'Insurance', 'Department', 'Consultant', 'Performed Date', 'Cost', 'Payment Status', 'Pay Mode', 'Resources']);
    foreach ($displayRows as $r) {
        $pay = $paymentData[$r['sale_no']] ?? [];
        $resTxt = isset($resourcePersons[$r['sn']]) ? implode(", ", $resourcePersons[$r['sn']]) : '';
        $payStatus = isset($pay['paystatus']) ? ($pay['paystatus'] == 1 ? 'Paid' : 'Unpaid') : 'Unpaid';
        $performDate = !empty($r['performed_date']) ? date("Y-m-d", strtotime($r['performed_date'])) : '';
        fputcsv($out, [$r['hospital_no'], $r['name'], $r['procedures'], $r['insurance_type'], $r['department'], $r['consultant_name'], $performDate, $r['cost'], $payStatus, $pay['pay_mode'] ?? '', $resTxt]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Detailed Procedure Report</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body {
            background: #fff;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: red;
        }

        .totals-table,
        .dept-breakdown {
            width: 70%;
            margin: auto;
            border: 2px solid #555;
            font-weight: bold;
            background: #f9f9f9;
            margin-top: 20px;
        }

        .totals-table th {
            background: #eee;
            width: 60%;
        }

        .summary-section {
            margin-top: 20px;
        }

        @media print {

            .no-print,
            .btn {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="no-print text-center mt-3">
            <button onclick="window.print();" class="btn btn-primary btn-sm">Print</button>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" class="btn btn-success btn-sm">Download CSV</a>
        </div>

        <h4 class="text-center mt-3">Detailed Procedure Report</h4>
        <p class="text-center"><strong>Date Range:</strong>
            <?php echo date("d M Y", strtotime($filters['start_date'])) . " - " . date("d M Y", strtotime($filters['end_date'])); ?>
        </p>

        <?php if (!$displayRows) { ?>
            <p class="no-data">No data found for the selected filters.</p>
        <?php } else {
            $i = 1;
            $totalCost = 0;
            $totalPaid = 0;
            $paidCount = 0;
            $unpaidCount = 0;
            $deptTotals = [];
        ?>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Hospital No</th>
                        <th>Name</th>
                        <th>Procedure</th>
                        <th>Insurance</th>
                        <th>Department</th>
                        <th>Consultant</th>
                        <th>Performed Date</th>
                        <th>Cost</th>
                        <th>Payment</th>
                        <th>Mode</th>
                        <th>Resources</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($displayRows as $r):
                        $sn = $r['sn'];
                        $pay = $paymentData[$r['sale_no']] ?? [];
                        $resTxt = $resourcePersons[$sn] ?? [];
                        $resTxt = implode("<br>", $resTxt);
                        $cost = is_numeric($r['cost']) ? floatval($r['cost']) : 0;
                        $totalCost += $cost;

                        $dept = $r['department'] ?: 'Unknown';
                        if (!isset($deptTotals[$dept])) $deptTotals[$dept] = ['count' => 0, 'cost' => 0];
                        $deptTotals[$dept]['count']++;
                        $deptTotals[$dept]['cost'] += $cost;

                        if (isset($pay['paystatus'])) {
                            if ($pay['paystatus'] == 1) {
                                $paidCount++;
                                $totalPaid += floatval($pay['pay'] ?? 0);
                            } else $unpaidCount++;
                        } else {
                            $unpaidCount++;
                        }

                        $payStatus = isset($pay['paystatus']) ? ($pay['paystatus'] == 1 ? '<span class="text-success">Paid</span>' : '<span class="text-danger">Unpaid</span>') : '<span class="text-danger">Unpaid</span>';
                        $performDate = !empty($r['performed_date']) ? date("d M Y", strtotime($r['performed_date'])) : '';
                    ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $r['hospital_no']; ?></td>
                            <td><?php echo $r['name']; ?></td>
                            <td><?php echo $r['procedures']; ?></td>
                            <td><?php echo $r['insurance_type']; ?></td>
                            <td><?php echo $r['department']; ?></td>
                            <td><?php echo $r['consultant_name']; ?></td>
                            <td><?php echo $performDate; ?></td>
                            <td align="right"><?php echo number_format($cost, 2); ?></td>
                            <td><?php echo $payStatus; ?></td>
                            <td><?php echo $pay['pay_mode'] ?? ''; ?></td>
                            <td><?php echo $resTxt; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="summary-section no-print">
                <table class="totals-table table table-bordered table-sm mb-3">
                    <tr>
                        <th>Total Procedures</th>
                        <td><?php echo $totalProcedures; ?></td>
                    </tr>
                    <tr>
                        <th>Total Cost</th>
                        <td><?php echo number_format($totalCost, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Total Paid Procedures</th>
                        <td><?php echo $paidCount; ?></td>
                    </tr>
                    <tr>
                        <th>Total Unpaid Procedures</th>
                        <td><?php echo $unpaidCount; ?></td>
                    </tr>
                    <tr>
                        <th>Total Amount Paid</th>
                        <td><?php echo number_format($totalPaid, 2); ?></td>
                    </tr>
                </table>

                <table class="dept-breakdown table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Procedures</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deptTotals as $dept => $val): ?>
                            <tr>
                                <td><?php echo $dept; ?></td>
                                <td><?php echo $val['count']; ?></td>
                                <td><?php echo number_format($val['cost'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php } ?>
    </div>
</body>

</html>