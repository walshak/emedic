<?php

if (isset($_GET['export']) && $_GET['export'] === 'csv') {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=investigation_report_' . date('Ymd') . '.csv');

    $output = fopen('php://output', 'w');

    // CSV Header
    fputcsv($output, [
        'SN',
        'Hospital No',
        'Investigation',
        'Pay',
        'Claim Amount',
        'Date',
        'Insurance'
    ]);

    $sn = 1;
    foreach ($results as $row) {
        fputcsv($output, [
            $sn++,
            $row['hospital_no'],
            $row['item_services'],
            number_format($row['pay'], 2),
            number_format($row['claim_amt'], 2),
            date('d/m/Y', strtotime($row['transact_date'])),
            $row['insurance_name']
        ]);
    }

    // Optional summary in CSV
    fputcsv($output, []);
    fputcsv($output, ['SUMMARY']);

    foreach ($summary as $insurance => $group) {
        fputcsv($output, [$insurance]);
        fputcsv($output, ['Investigation', 'Count', 'Total Paid', 'Claim Amount']);

        foreach ($group['services'] as $test => $values) {
            fputcsv($output, [
                $test,
                $values['count'],
                number_format($values['pay'], 2),
                number_format($values['claim'], 2)
            ]);
        }

        fputcsv($output, [
            'SUBTOTAL',
            '',
            number_format($group['sub_total_pay'], 2),
            number_format($group['sub_total_claim'], 2)
        ]);

        fputcsv($output, []);
    }

    // Grand total
    fputcsv($output, ['GRAND TOTAL', '', number_format($grandPay, 2), number_format($grandClaim, 2)]);

    fclose($output);
    exit;
}



if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
    $the_stetment = 1;

    $currentDate = date('Y-m-d');
    // Check if 'from_date' and 'to_date' are set in the GET parameters
    $start_date = !empty($_GET['from_date']) ? $_GET['from_date'] : $currentDate;
    $end_date = !empty($_GET['to_date']) ? $_GET['to_date'] : $currentDate;
    $rpt_type = $_GET['rpt_type'];

    if ($rpt_type == 'ps') {
        $sql = "
        SELECT 
            patient,
            patient_name,
            COUNT(*) AS total_tests,
            SUM(CASE WHEN data_capture_status = 'specimen' THEN 1 ELSE 0 END) AS specimen,
            SUM(CASE WHEN data_capture_status = 'result' THEN 1 ELSE 0 END) AS result,
            SUM(CASE WHEN data_capture_status = 'queue' THEN 1 ELSE 0 END) AS queue,
            SUM(CASE WHEN data_capture_status = 'approve' THEN 1 ELSE 0 END) AS approve
        FROM lab_manage
        WHERE request_date2 BETWEEN :start_date AND :end_date
        GROUP BY patient, patient_name
        ORDER BY patient_name ASC
    ";

        $stmt = $db->prepare($sql);
        $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
        $results = $stmt->fetchAll();
    }
}

if ($rpt_type == 'dcr') {

    $sql = "
    SELECT 
        c.hospital_no,
        COALESCE(CONCAT(e.fname, ' ', e.surname), 'Anonymous') as patient_name,
        e.insurance,
        c.item_services,
        c.serv_group,
        c.hosp_price,
        c.claim_amt,
        c.qty,
        c.invoice_date,
        c.invoice_by,
        c.prepared_by,
        c.created_by,
        c.dsp_by,
        c.date_entry,
        c.pay,
        c.paystatus,
        l.entered_by
    FROM patient_ap_services c
    LEFT JOIN enrollee e ON c.hospital_no = e.hospital_no
    LEFT JOIN lab_manage l ON l.labrequest_no = c.drug_sn
    WHERE (c.serv_group LIKE '%Laboratory%' OR c.serv_group LIKE '%Radiology%')
        AND c.drug_status = 1
        AND c.cr = 1
        AND c.paystatus = 0
    ORDER BY c.hospital_no, c.sn DESC
";

    $stmt = $db->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<h1>Investigation Reports</h1>
<h3>For the period starting <?php echo date('d M Y', strtotime($start_date)); ?> and ending
    <?php echo date('d M Y', strtotime($end_date)); ?></h3>


<div class="row">

    <div class="col-md-12">
        <?php
        if ($rpt_type == 'ps') {

            $total_tests = $total_specimen = $total_result = $total_queue = $total_approve = 0;

            if (count($results) > 0): ?>
                <table class="table table-striped table-bordered table-hover dataTables-example">

                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>Total Tests</th>
                            <th>Queue</th>
                            <th>Specimen</th>
                            <th>Result</th>

                            <th>Approve</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row):

                            $total_tests += $row['total_tests'];
                            $total_specimen += $row['specimen'];
                            $total_result += $row['result'];
                            $total_queue += $row['queue'];
                            $total_approve += $row['approve'];

                        ?>

                            <tr>
                                <td><?= htmlspecialchars($row['patient']) ?></td>
                                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                <td><?= $row['total_tests'] ?></td>
                                <td><?= $row['queue'] ?></td>
                                <td><?= $row['specimen'] ?></td>
                                <td><?= $row['result'] ?></td>

                                <td><?= $row['approve'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Summary Row -->

                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background: #ecf0f1;">
                            <td>Total:</td>
                            <td><?= $rowCount = count($results) . ' Patient(s)'; ?></td>
                            <td><?= $total_tests ?></td>
                            <td><?= $total_queue ?></td>
                            <td><?= $total_specimen ?></td>
                            <td><?= $total_result ?></td>

                            <td><?= $total_approve ?></td>
                        </tr>
                    </tfoot>

                </table>
            <?php else: ?>
                <p>No test records found for the selected date range.</p>
            <?php endif; ?>
        <?php } elseif ($rpt_type == 'dcr') { ?>

            <table class="table table-striped table-bordered table-hover dataTables-example">
                <thead>
                    <tr>
                        <th>Hospital No</th>
                        <th>Patient Name</th>
                        <th>Item Services</th>
                        <th>Category</th>
                        <th>Hosp Price</th>
                        <th>Claim Amt</th>
                        <th>Prepared By</th>
                        <th>Result Entered by</th>
                        <th>Request Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $currentHospitalNo = null;
                    $subtotal = 0;

                    $insuranceTotals = [];
                    $grandPayTotal = 0;
                    $grandClaimTotal = 0;

                    foreach ($results as $row) {
                        // Check if hospital number has changed
                        if ($currentHospitalNo !== null && $row['hospital_no'] !== $currentHospitalNo) {
                            // Output subtotal row for the previous hospital number
                            echo "<tr style='font-weight:bold; background:#e0e0e0;'>
                <td colspan='9'>Subtotal for $currentHospitalNo</td>
                <td>₦" . number_format($subtotal, 2) . "</td> </tr>";
                            $subtotal = 0; // Reset subtotal for next patient
                        }

                        $currentHospitalNo = $row['hospital_no'];

                        echo "<tr><td>{$row['hospital_no']}</td><td>{$row['patient_name']}</td><td>{$row['item_services']}</td><td>{$row['serv_group']}</td><td>{$row['hosp_price']}</td><td>{$row['claim_amt']}</td><td>{$row['prepared_by']}</td><td>{$row['entered_by']}</td><td>" . date('d M Y', strtotime($row['date_entry'])) . "</td><td>{$row['pay']}</td></tr>";

                        $subtotal += $row['pay'];

                        // Totals by insurance
                        $insuranceKey = $row['insurance'];
                        if (!isset($insuranceTotals[$insuranceKey])) {
                            $insuranceTotals[$insuranceKey] = ['pay' => 0, 'claim_amt' => 0];
                        }
                        $insuranceTotals[$insuranceKey]['pay'] += $row['pay'];
                        $insuranceTotals[$insuranceKey]['claim_amt'] += $row['claim_amt'];

                        // Grand totals
                        $grandPayTotal += $row['pay'];
                        $grandClaimTotal += $row['claim_amt'];
                    }

                    // Final subtotal row
                    if ($currentHospitalNo !== null) {
                        echo "<tr style='font-weight:bold; background:#e0e0e0;'>
                                <td colspan='9'>Subtotal for $currentHospitalNo</td>
                                <td>₦" . number_format($subtotal, 2) . "</td>
                            
                            </tr>";
                    }

                    echo "</tbody>
                                        <tfoot>
                                                <tr>
                                            <th>Hospital No</th>
                                            <th>Patient Name</th>
                                            <th>Item Services</th>
                                            <th>Category</th>
                                            <th>Hosp Price</th>
                                            <th>Claim Amt</th>
                                            <th>Prepared By</th>
                                            <th>Result Entered by</th>
                                            <th>Request Date</th>
                                            <th>Amount</th>
                                        </tr>
                                    </tfoot>
                                    </table>";

                    // Display summary by insurance
                    echo '<hr>';
                    echo "<h2>Summary by Insurance</h2>
                                    <table class='table table-striped table-bordered'>
                        <tr><th>Insurance</th><th>Total Pay</th><th>Total Claim Amt</th></tr>";
                    foreach ($insuranceTotals as $insurance => $totals) {
                        echo "<tr>
                            <td>$insurance</td>
                            <td>₦" . number_format($totals['pay'], 2) . "</td>
                            <td>₦" . number_format($totals['claim_amt'], 2) . "</td>
                        </tr>";
                    }
                    echo "</table>";

                    // Grand totals
                    echo "<hr><h3>Grand Totals</h3>
                        <h2>Total Pay: ₦" . number_format($grandPayTotal, 2) . "</h2>
                        <h2>Total Claim Amount: ₦" . number_format($grandClaimTotal, 2) . "</h2>";
                } elseif ($rpt_type == "requester" or $rpt_type == "approver") {

                    if ($rpt_type == "requester") {
                        $q1 = "WHERE request_date2 BETWEEN :start_date AND :end_date AND (data_capture_status='approve' or data_capture_status='result') ORDER BY request_date ASC";
                        $c5 = "Requested by";
                        $c6 = "Requested Date";
                        $title_ = "Requested";
                    } else {
                        $q1 = "WHERE result_date BETWEEN :start_date AND :end_date AND (data_capture_status='approve' or data_capture_status='result') ORDER BY result_date ASC";
                        $c5 = "Approved by";
                        $c6 = "Approved Date";
                        $title_ = "Approved";
                    }
                    ////=========================== REQUESTER ==========================


                    $query = $db->prepare("
                        SELECT sn, patient, patient_name, test_name, request_date, request_by,approved_by,result_date,data_capture_status
                        FROM lab_manage
                        $q1
                    ");
                    $query->execute([':start_date' => $start_date, ':end_date' => $end_date]);
                    $lab_data = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Display table
                    if ($lab_data) {


                        echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
                            <tr>
                                <th>SN</th>
                                <th>Patient</th>
                                <th>Patient Name</th>
                                <th>Test Name</th>
                                <th>$c6</th>
                                <th>$c5</th>
                                <th>Status</th></tr>";
                        $counter = 0;
                        foreach ($lab_data as $row) {

                            if ($rpt_type == "requester") {
                                $c6_d = $row['request_date'];
                                $c5_d = $row['request_by'];
                            } else {
                                $c6_d = $row['result_date'];
                                $c5_d = $row['approved_by'];
                            }
                            echo "<tr>
                                <td>" . (++$counter) . "</td>
                                <td>{$row['patient']}</td>
                                <td>{$row['patient_name']}</td>
                                <td>{$row['test_name']}</td>
                                <td>$c6_d</td>
                                <td>$c5_d</td>
                                <td>{$row['data_capture_status']}</td>
                            </tr>";
                        }
                        echo "</table><br>";
                    } else {
                        echo "No lab requests found in the selected date range.<br><br>";
                    }

                    // Summary: Total tests requested by each staff
                    echo "<h3>Total Tests $title_ by Each Staff</h3>";
                    if ($rpt_type == "requester") {
                        $requester_query = $db->prepare("
                            SELECT request_by, COUNT(*) AS total_requests
                            FROM lab_manage
                            WHERE (data_capture_status='approve' or data_capture_status='result') AND request_date2 BETWEEN :start_date AND :end_date
                            GROUP BY request_by
                        ");
                    } else {
                        $requester_query = $db->prepare("
                            SELECT approved_by, COUNT(*) AS total_approvals
                            FROM lab_manage
                            WHERE (data_capture_status='approve' or data_capture_status='result') AND result_date BETWEEN :start_date AND :end_date
                            GROUP BY approved_by
                        ");
                    }
                    $requester_query->execute([':start_date' => $start_date, ':end_date' => $end_date]);
                    $requesters = $requester_query->fetchAll(PDO::FETCH_ASSOC);

                    if ($requesters) {
                        echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
            <tr><th>SN</th><th>$title_ By</th><th>Total Tests</th></tr>";
                        $counter = 0;
                        foreach ($requesters as $r) {
                            if ($rpt_type == "requester") {
                                echo "  <tr><td>" . (++$counter) . "</td><td>{$r['request_by']}</td><td>{$r['total_requests']}</td></tr>";
                            } else {
                                echo "  <tr><td>" . (++$counter) . "</td><td>{$r['approved_by']}</td><td>{$r['total_approvals']}</td></tr>";
                            }
                        }
                        echo "</table><br>";
                    }

                    // Summary: Total tests per category
                    // Summary: Total tests per category grouped by test_name and request_by
                    echo "<h3>Total Tests per Category with $title_</h3>";
                    if ($rpt_type == "requester") {
                        $category_with_requester_query = $db->prepare("
    SELECT test_name, request_by, COUNT(*) AS test_count
    FROM lab_manage
    WHERE (data_capture_status='approve' or data_capture_status='result') AND request_date2 BETWEEN :start_date AND :end_date
    GROUP BY test_name, request_by
    ORDER BY test_name, request_by
");
                    } else {

                        $category_with_requester_query = $db->prepare("
                    SELECT test_name, approved_by, COUNT(*) AS test_count
                    FROM lab_manage
                    WHERE (data_capture_status='approve' or data_capture_status='result') AND request_date2 BETWEEN :start_date AND :end_date
                    GROUP BY test_name, approved_by
                    ORDER BY test_name, approved_by
                ");
                    }
                    $category_with_requester_query->execute([':start_date' => $start_date, ':end_date' => $end_date]);
                    $category_rows = $category_with_requester_query->fetchAll(PDO::FETCH_ASSOC);

                    // Display table
                    if ($category_rows) {
                        echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
            <tr><th>SN</th><th>Test Name</th><th>$title_ By</th><th>Total Count</th></tr>";
                        $counter = 0;
                        foreach ($category_rows as $row) {

                            if ($rpt_type == "requester") {
                                $c3 = $row['request_by'];
                            } else {
                                $c3 = $row['approved_by'];
                            }
                            echo "<tr>
                 <td>" . (++$counter) . "</td>
                <td>{$row['test_name']}</td>
                <td>$c3</td>
                <td>{$row['test_count']}</td>
              </tr>";
                        }
                        echo "</table><br>";
                    } else {
                        echo "No category data found for the selected date range.";
                    }
                } elseif ($rpt_type == "item_services") {


                    $itemServices = $_GET["item_services"]; // Assuming this is an array like ['Urinalysis', 'FBC/CBC']

                    $currentDate = date('Y-m-d');
                    $start_date = !empty($_GET['from_date']) ? $_GET['from_date'] : $currentDate;
                    $end_date = !empty($_GET['to_date']) ? $_GET['to_date'] : $currentDate;
                    $params = [$start_date, $end_date];

                    if (!empty($_GET['item_services'])) {
                        $itemServices = $_GET['item_services']; // Assuming this comes from a multi-select
                        $itemPlaceholders = implode(',', array_fill(0, count($itemServices), '?'));
                        $params = array_merge($params, $itemServices);
                        $item_servicesSQL = " AND pas.item_services IN ($itemPlaceholders)";
                    }


                    $insuranceSQL = '';

                    if (!empty($_GET['hmo_nhis'])) {

                        // Ensure it's always an array
                        $insuranceNames = (array) $_GET['hmo_nhis'];

                        // CASE 1: "ALL" selected
                        if (in_array('all', $insuranceNames)) {

                            $insuranceSQL = "
            AND i.insurance_type IN ('PHIS', 'Corporate', 'NHIS')
            AND i.status = 'active'
        ";

                            // ⚠️ Do NOT add insurance names to params

                        }
                        // CASE 2: Specific insurance selected
                        else {

                            $insurancePlaceholders = implode(',', array_fill(0, count($insuranceNames), '?'));

                            $insuranceSQL = "
            AND i.insurance_name IN ($insurancePlaceholders)
            AND i.insurance_type IN ('PHIS', 'Corporate', 'NHIS')
            AND i.status = 'active'
        ";

                            $params = array_merge($params, $insuranceNames);
                        }
                    }


                    $sql = "
    SELECT 
        pas.sn, 
        pas.hospital_no, 
        pas.item_services, 
        pas.pay, 
        pas.claim_amt, 
        pas.transact_date,
        
        COALESCE(i.insurance_name, 'External') AS insurance_name,
        COALESCE(e.surname, 'External') AS surname,
        COALESCE(e.fname, 'External') AS fname

    FROM 
        patient_ap_services pas
    LEFT JOIN enrollee e ON pas.hospital_no = e.hospital_no
    LEFT JOIN insurance_tbl i ON e.hmo_no = i.insurance_no
    WHERE 
        DATE(pas.transact_date) BETWEEN ? AND ?
        AND pas.paystatus = 1
        $item_servicesSQL
        AND pas.serv_group IN ('laboratory', 'radiology')
        $insuranceSQL
    ORDER BY 
        i.insurance_name, 
        pas.item_services
";

                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


                    // Display Table
                    echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
                <tr><th>SN</th><th>Hospital No</th><th>Name</th><th>Investigation</th><th>Payy</th><th>Claim</th><th>Date</th><th>Insurance</th></tr>";

                    $summary = [];
                    $grandPay = 0;
                    $grandClaim = 0;
                    $counter = 0;
                    foreach ($results as $row) {
                        echo "<tr>
                    <td>" . (++$counter) . "</td>
                    <td>{$row['hospital_no']}</td>
                    <td>{$row['surname']}, {$row['fname']}</td>
                    <td>{$row['item_services']}</td>
                    <td>" . number_format($row['pay'], 2) . "</td>
                    <td>" . number_format($row['claim_amt'], 2) . "</td>
                    <td>" . date('d/m/Y', strtotime($row['transact_date'])) . "</td>
                    <td>{$row['insurance_name']}</td>
                </tr>";


                        $ins = $row['insurance_name'];
                        $item = $row['item_services'];

                        if (!isset($summary[$ins])) {
                            $summary[$ins] = ['services' => [], 'sub_total_pay' => 0, 'sub_total_claim' => 0];
                        }

                        if (!isset($summary[$ins]['services'][$item])) {
                            $summary[$ins]['services'][$item] = ['count' => 0, 'pay' => 0, 'claim' => 0];
                        }

                        // Update counts
                        $summary[$ins]['services'][$item]['count'] += 1;
                        $summary[$ins]['services'][$item]['pay'] += $row['pay'];
                        $summary[$ins]['services'][$item]['claim'] += $row['claim_amt'];

                        $summary[$ins]['sub_total_pay'] += $row['pay'];
                        $summary[$ins]['sub_total_claim'] += $row['claim_amt'];

                        $grandPay += $row['pay'];
                        $grandClaim += $row['claim_amt'];
                    }

                    echo "</table><br><br>";

                    // Display Summary
                    echo "<h3>Summary</h3>";
                    foreach ($summary as $insurance => $group) {
                        echo "<strong>Insurance Name: $insurance</strong>";
                        echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
                        <tr><th>Investigation</th><th>Total Count</th><th>Total Paid</th><th>Claim Amount</th></tr>";
                        foreach ($group['services'] as $test => $values) {
                            echo "<tr>
                            <td>$test</td>
                            <td>{$values['count']}</td>
                                              <td>" . number_format($values['pay'], 2) . "</td>
<td>" . number_format($values['claim'], 2) . "</td>
                        </tr>";
                        }

                        // Subtotals
                        echo "<tr style='font-weight:bold; background:#f0f0f0;'>
                        <td>SUBTOTAL</td>
                        <td></td>
                   <td>" . number_format($group['sub_total_pay'], 2) . "</td>
<td>" . number_format($group['sub_total_claim'], 2) . "</td>

                    </tr>";
                        echo "</table><br>";
                    }

                    // Grand total
                    echo "<h4 style='margin-top:20px;'>Grand Total</h4>";
                    echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
                    <tr><th>Total Pay</th><th>Total Claim Amount</th></tr>
                    <tr><td>" . number_format($grandPay, 2) . "</td><td>" . number_format($grandClaim, 2) . "</td></tr>
                </table>";


                    ?>


                    <form method="GET" action="export_investigation_csv.php">
                        <?php
                        foreach ($_GET as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $v) {
                                    echo "<input type='hidden' name='{$key}[]' value='" . htmlspecialchars($v) . "'>";
                                }
                            } else {
                                echo "<input type='hidden' name='{$key}' value='" . htmlspecialchars($value) . "'>";
                            }
                        }
                        ?>
                        <button type="submit" class="btn btn-success">
                            Download CSV
                        </button>
                    </form>






                <?php

                } elseif ($rpt_type == "transact_rpt") {

                    $currentDate = date('Y-m-d');
                    $start_date = !empty($_GET['from_date']) ? $_GET['from_date'] : $currentDate;
                    $end_date = !empty($_GET['to_date']) ? $_GET['to_date'] : $currentDate;

                    // Prepare parameters for the SQL query
                    $params = [$start_date, $end_date];

                    // Optional insurance filter
                    $insuranceSQL = '';
                    if (!empty($_GET['hmo_nhis'])) {
                        $insuranceNames = $_GET['hmo_nhis'];
                        $insurancePlaceholders = implode(',', array_fill(0, count($insuranceNames), '?'));
                        $insuranceSQL = "AND i.insurance_name IN ($insurancePlaceholders) AND i.insurance_type IN ('PHIS', 'Corporate', 'NHIS')
    AND i.status='active'";
                        $params = array_merge($params, $insuranceNames);
                    }

                    $sql = "
                        SELECT 
                            pas.sn, pas.hospital_no, pas.item_services, pas.pay, pas.claim_amt, pas.transact_date,
                            COALESCE(i.insurance_name, 'Unknown') AS insurance_name
                        FROM 
                            patient_ap_services pas
                        LEFT JOIN enrollee e ON pas.hospital_no = e.hospital_no
                        LEFT JOIN insurance_tbl i ON e.hmo_no = i.insurance_no
                        WHERE 
                            DATE(pas.transact_date) BETWEEN ? AND ?
                            AND pas.paystatus = 1
                            AND pas.serv_group IN ('laboratory', 'radiology')
                            $insuranceSQL
                        ORDER BY i.insurance_name, pas.item_services
                        ";

                    try {
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        echo "Error: " . htmlspecialchars($e->getMessage());
                        exit;
                    }

                    // Display Table
                    echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
<tr><th>SN</th><th>Hospital No</th><th>Investigation</th><th>Pay</th><th>Claim</th><th>Date</th><th>Insurance</th></tr>";

                    $summary = [];
                    $grandPay = 0;
                    $grandClaim = 0;
                    $counter = 0;
                    foreach ($results as $row) {
                        echo "<tr>
    <td>" . (++$counter) . "</td>
    <td>" . htmlspecialchars($row['hospital_no']) . "</td>
    <td>" . htmlspecialchars($row['item_services']) . "</td>
    <td>" . number_format($row['pay'], 2) . "</td>
    <td>" . number_format($row['claim_amt'], 2) . "</td>
    <td>" . date('d/m/Y', strtotime($row['transact_date'])) . "</td>
    <td>" . htmlspecialchars($row['insurance_name']) . "</td>
    </tr>";

                        $ins = $row['insurance_name'];
                        $item = $row['item_services'];

                        if (!isset($summary[$ins])) {
                            $summary[$ins] = ['services' => [], 'sub_total_pay' => 0, 'sub_total_claim' => 0];
                        }

                        if (!isset($summary[$ins]['services'][$item])) {
                            $summary[$ins]['services'][$item] = ['count' => 0, 'pay' => 0, 'claim' => 0];
                        }

                        // Update counts
                        $summary[$ins]['services'][$item]['count'] += 1;
                        $summary[$ins]['services'][$item]['pay'] += $row['pay'];
                        $summary[$ins]['services'][$item]['claim'] += $row['claim_amt'];

                        $summary[$ins]['sub_total_pay'] += $row['pay'];
                        $summary[$ins]['sub_total_claim'] += $row['claim_amt'];

                        $grandPay += $row['pay'];
                        $grandClaim += $row['claim_amt'];
                    }

                    echo "</table><br><br>";

                    // Display Summary
                    echo "<h3>Summary</h3>";
                    foreach ($summary as $insurance => $group) {
                        echo "<strong>Insurance Name: " . htmlspecialchars($insurance) . "</strong>";
                        echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
        <tr><th>Investigation</th><th>Total Count</th><th>Total Paid</th><th>Claim Amount</th></tr>";
                        foreach ($group['services'] as $test => $values) {
                            echo "<tr>
            <td>" . htmlspecialchars($test) . "</td>
            <td>{$values['count']}</td>
            <td>" . number_format($values['pay'], 2) . "</td>
            <td>" . number_format($values['claim'], 2) . "</td>
        </tr>";
                        }

                        // Subtotals
                        echo "<tr style='font-weight:bold; background:#f0f0f0;'>
        <td>SUBTOTAL</td>
        <td></td>
        <td>" . number_format($group['sub_total_pay'], 2) . "</td>
        <td>" . number_format($group['sub_total_claim'], 2) . "</td>
    </tr>";
                        echo "</table><br>";
                    }

                    // Grand total
                    echo "<h4 style='margin-top:20px;'>Grand Total</h4>";
                    echo "<table class='table table-striped table-bordered table-hover dataTables-example'>
    <tr><th>Total Pay</th><th>Total Claim Amount</th></tr>
    <tr><td>" . number_format($grandPay, 2) . "</td><td>" . number_format($grandClaim, 2) . "</td></tr>
</table>"; ?>

                    <form method="GET" action="export_investigation_csv_part2.php" style="margin-top:15px;">
                        <?php
                        foreach ($_GET as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $v) {
                                    echo "<input type='hidden' name='{$key}[]' value='" . htmlspecialchars($v) . "'>";
                                }
                            } else {
                                echo "<input type='hidden' name='{$key}' value='" . htmlspecialchars($value) . "'>";
                            }
                        }
                        ?>
                        <button type="submit" class="btn btn-success">
                            Download CSV
                        </button>
                    </form>


                <?php                } ?>
    </div>
</div>














<a href="mgt_rpt.php" class="btn btn-danger">
    <i class="fa fa-arrow">&nbsp; Close Report</i>
</a>