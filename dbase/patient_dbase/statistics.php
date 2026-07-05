<?php
include("../../Connections/Conn.php");
session_start();
// Fetch distinct values for dropdowns

// Fetch distinct values for dropdowns
$prepared_by_query = $db->query("SELECT DISTINCT prepared_by FROM patient_ap_services WHERE serv_group LIKE 'pharmacy' ORDER BY prepared_by");
$dsp_by_query = $db->query("SELECT DISTINCT dsp_by FROM patient_ap_services WHERE serv_group LIKE 'pharmacy' ORDER BY dsp_by");
$pay_mode_query = $db->query("SELECT DISTINCT pay_mode FROM patient_ap_services WHERE serv_group LIKE 'pharmacy' ORDER BY pay_mode");
$department_query = $db->query("SELECT sn, department FROM department ORDER BY department");
$item_services_query = $db->query("SELECT DISTINCT drug_sn, item_services FROM patient_ap_services WHERE serv_group LIKE 'pharmacy' ORDER BY item_services");

$results = [];
$summary_medications_results = [];
$summary_dispensed_results = [];
$total_dispensed_by = 0;
$statistics_results = [];

function outputCsv($filename, $headers, $rows)
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $headers);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // PHP 5 compatible variable assignments
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $prepared_by = isset($_POST['prepared_by']) ? $_POST['prepared_by'] : '';
    $dsp_by = isset($_POST['dsp_by']) ? $_POST['dsp_by'] : '';
    $pay_mode = isset($_POST['pay_mode']) ? $_POST['pay_mode'] : '';
    $dept_id = isset($_POST['dept_id']) ? $_POST['dept_id'] : '';
    $item_services = isset($_POST['item_services']) ? $_POST['item_services'] : '';
    $hospital_no = isset($_POST['hospital_no']) ? trim($_POST['hospital_no']) : '';
    $download_csv = isset($_POST['download_csv']) ? $_POST['download_csv'] : '';

    // Validate dates
    if (!$start_date || !$end_date) {
        die("Please provide a valid date range.");
    }

    // Build filters for SQL
    $filterSql = " AND date_entry BETWEEN :start_date AND :end_date";
    if (!empty($prepared_by)) {
        $filterSql .= " AND prepared_by = :prepared_by";
    }
    if (!empty($dsp_by)) {
        $filterSql .= " AND dsp_by = :dsp_by";
    }
    if (!empty($pay_mode)) {
        $filterSql .= " AND pay_mode = :pay_mode";
    }
    if (!empty($dept_id)) {
        $filterSql .= " AND dept_id = :dept_id";
    }
    if (!empty($item_services)) {
        $filterSql .= " AND drug_sn  = :item_services";
    }

    if (!empty($hospital_no)) {
        $filterSql .= " AND pas.hospital_no = :hospital_no"; // Change 'pas' to 'e'
    }
    // 1. Main pharmacy services query
    $sql = "SELECT pas.hospital_no, e.surname, e.fname, pas.item_services, pas.prepared_by, pas.dsp_by,
            pas.date_entry, d.department, pas.hosp_price, pas.claim_amt, pas.pay, pas.pay_mode, pas.drug_sn, i.insertion_date_time, i.enter_by
            FROM patient_ap_services pas
            JOIN enrollee e ON pas.hospital_no = e.hospital_no
            LEFT JOIN department d ON pas.dept_id = d.sn
            LEFT JOIN stock_table_inven i ON i.sale_sn = pas.sn
            WHERE pas.serv_group LIKE 'pharmacy' AND paystatus=1 AND drug_status=1 $filterSql
            ORDER BY pas.date_entry DESC";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    if (!empty($prepared_by)) {
        $stmt->bindParam(':prepared_by', $prepared_by);
    }
    if (!empty($dsp_by)) {
        $stmt->bindParam(':dsp_by', $dsp_by);
    }
    if (!empty($pay_mode)) {
        $stmt->bindParam(':pay_mode', $pay_mode);
    }
    if (!empty($dept_id)) {
        $stmt->bindParam(':dept_id', $dept_id);
    }
    if (!empty($item_services)) {
        $stmt->bindParam(':item_services', $item_services);
    }

    if (!empty($hospital_no)) {
        $stmt->bindParam(':hospital_no', $hospital_no);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);


    //echo 'sdsdsds';
    ///exit;


    // 2. Summary by prepared_by: total medications prescribed
    $summary_medications_sql = "SELECT prepared_by, COUNT(*) AS total_medications
                               FROM patient_ap_services as pas WHERE serv_group LIKE 'pharmacy' AND paystatus=1 AND drug_status=1 $filterSql
                               GROUP BY prepared_by ORDER BY prepared_by";

    $stmt_medications = $db->prepare($summary_medications_sql);
    $stmt_medications->bindParam(':start_date', $start_date);
    $stmt_medications->bindParam(':end_date', $end_date);
    if (!empty($prepared_by)) {
        $stmt_medications->bindParam(':prepared_by', $prepared_by);
    }
    if (!empty($dsp_by)) {
        $stmt_medications->bindParam(':dsp_by', $dsp_by);
    }
    if (!empty($pay_mode)) {
        $stmt_medications->bindParam(':pay_mode', $pay_mode);
    }
    if (!empty($dept_id)) {
        $stmt_medications->bindParam(':dept_id', $dept_id);
    }
    if (!empty($item_services)) {
        $stmt_medications->bindParam(':item_services', $item_services);
    }

    if (!empty($hospital_no)) {
        $stmt_medications->bindParam(':hospital_no', $hospital_no);
    }
    $stmt_medications->execute();
    $summary_medications_results = $stmt_medications->fetchAll(PDO::FETCH_ASSOC);

    // 3. Summary by dsp_by: total medications dispensed (drug_status=1)
    $summary_dispensed_sql = "SELECT dsp_by, COUNT(*) AS total_dispensed
                              FROM patient_ap_services as pas
                              WHERE serv_group LIKE 'pharmacy' AND drug_status = 1 AND paystatus=1 $filterSql
                              GROUP BY dsp_by ORDER BY dsp_by";

    $stmt_dispensed = $db->prepare($summary_dispensed_sql);
    $stmt_dispensed->bindParam(':start_date', $start_date);
    $stmt_dispensed->bindParam(':end_date', $end_date);
    if (!empty($prepared_by)) {
        $stmt_dispensed->bindParam(':prepared_by', $prepared_by);
    }
    if (!empty($dsp_by)) {
        $stmt_dispensed->bindParam(':dsp_by', $dsp_by);
    }
    if (!empty($pay_mode)) {
        $stmt_dispensed->bindParam(':pay_mode', $pay_mode);
    }
    if (!empty($dept_id)) {
        $stmt_dispensed->bindParam(':dept_id', $dept_id);
    }
    if (!empty($item_services)) {
        $stmt_dispensed->bindParam(':item_services', $item_services);
    }

    if (!empty($hospital_no)) {
        $stmt_dispensed->bindParam(':hospital_no', $hospital_no);
    }
    $stmt_dispensed->execute();
    $summary_dispensed_results = $stmt_dispensed->fetchAll(PDO::FETCH_ASSOC);

    // Total distinct dsp_by
    $summary_dispensed_count_sql = "SELECT COUNT(DISTINCT dsp_by) AS total_dispensed_by
                                   FROM patient_ap_services as pas
                                   WHERE serv_group LIKE 'pharmacy' AND drug_status = 1 AND paystatus=1  $filterSql";

    $stmt_dispensed_count = $db->prepare($summary_dispensed_count_sql);
    $stmt_dispensed_count->bindParam(':start_date', $start_date);
    $stmt_dispensed_count->bindParam(':end_date', $end_date);
    if (!empty($prepared_by)) {
        $stmt_dispensed_count->bindParam(':prepared_by', $prepared_by);
    }
    if (!empty($dsp_by)) {
        $stmt_dispensed_count->bindParam(':dsp_by', $dsp_by);
    }
    if (!empty($pay_mode)) {
        $stmt_dispensed_count->bindParam(':pay_mode', $pay_mode);
    }
    if (!empty($dept_id)) {
        $stmt_dispensed_count->bindParam(':dept_id', $dept_id);
    }
    if (!empty($item_services)) {
        $stmt_dispensed_count->bindParam(':item_services', $item_services);
    }

    if (!empty($hospital_no)) {
        $stmt_dispensed_count->bindParam(':hospital_no', $hospital_no);
    }
    $stmt_dispensed_count->execute();
    $summary_dispensed_count_result = $stmt_dispensed_count->fetch(PDO::FETCH_ASSOC);
    $total_dispensed_by = isset($summary_dispensed_count_result['total_dispensed_by']) ? $summary_dispensed_count_result['total_dispensed_by'] : 0;

    // Maps for quick lookup
    $summary_medications_map = array();
    foreach ($summary_medications_results as $item) {
        $summary_medications_map[$item['prepared_by']] = $item['total_medications'];
    }
    $summary_dispensed_map = array();
    foreach ($summary_dispensed_results as $item) {
        $summary_dispensed_map[$item['dsp_by']] = $item['total_dispensed'];
    }

    // 4. Statistics table: drug statistics
    $statistics_sql = "SELECT item_services, COUNT(DISTINCT hospital_no) AS total_patients,
                       SUM(pay) AS total_amount, SUM(claim_amt) AS total_claim_amt
                       FROM patient_ap_services as pas
                       WHERE serv_group LIKE 'pharmacy' AND drug_status = 1 AND paystatus=1 $filterSql
                       GROUP BY item_services ORDER BY item_services";

    $statistics_stmt = $db->prepare($statistics_sql);
    $statistics_stmt->bindParam(':start_date', $start_date);
    $statistics_stmt->bindParam(':end_date', $end_date);
    if (!empty($prepared_by)) {
        $statistics_stmt->bindParam(':prepared_by', $prepared_by);
    }
    if (!empty($dsp_by)) {
        $statistics_stmt->bindParam(':dsp_by', $dsp_by);
    }
    if (!empty($pay_mode)) {
        $statistics_stmt->bindParam(':pay_mode', $pay_mode);
    }
    if (!empty($dept_id)) {
        $statistics_stmt->bindParam(':dept_id', $dept_id);
    }
    if (!empty($item_services)) {
        $statistics_stmt->bindParam(':item_services', $item_services);
    }

    if (!empty($hospital_no)) {
        $statistics_stmt->bindParam(':hospital_no', $hospital_no);
    }
    $statistics_stmt->execute();
    $statistics_results = $statistics_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Handle CSV download requests
    if ($download_csv) {
        if ($download_csv === 'pharmacy_services') {
            $header = ['Hospital No', 'Surname', 'First Name', 'Drug (Item Services)', 'Prepared By', 'Dispensed By', 'Date Entry', 'Department', 'Hospital Price', 'Claim Amount', 'Pay', 'Pay Mode'];
            $rows = array();
            foreach ($results as $r) {
                $rows[] = [
                    $r['hospital_no'],
                    $r['surname'],
                    $r['fname'],
                    $r['item_services'],
                    $r['prepared_by'],
                    $r['dsp_by'],
                    $r['date_entry'],
                    $r['department'] !== null ? $r['department'] : 'N/A',
                    $r['hosp_price'],
                    $r['claim_amt'],
                    $r['pay'],
                    $r['pay_mode']
                ];
            }
            outputCsv('pharmacy_services_report.csv', $header, $rows);
        } elseif ($download_csv === 'summary') {
            $header = ['Prepared By', 'Total Medications Prescribed', 'Dispensed By', 'Total Medications Dispensed'];
            $all_prepared_by = array_keys($summary_medications_map);
            $all_dsp_by = array_keys($summary_dispensed_map);
            $max_rows = max(count($all_prepared_by), count($all_dsp_by));
            $rows = array();
            for ($i = 0; $i < $max_rows; $i++) {
                $pb = isset($all_prepared_by[$i]) ? $all_prepared_by[$i] : '';
                $med = $pb ? $summary_medications_map[$pb] : '';
                $db = isset($all_dsp_by[$i]) ? $all_dsp_by[$i] : '';
                $disp = $db ? $summary_dispensed_map[$db] : '';
                $rows[] = [$pb, $med, $db, $disp];
            }
            outputCsv('summary_report.csv', $header, $rows);
        } elseif ($download_csv === 'statistics') {
            $header = ['Drug Name', 'Total Patients', 'Total Amount', 'Total Claim Amount'];
            $rows = array();
            foreach ($statistics_results as $stat) {
                $rows[] = [
                    $stat['item_services'],
                    $stat['total_patients'],
                    $stat['total_amount'],
                    $stat['total_claim_amt']
                ];
            }
            outputCsv('statistics_report.csv', $header, $rows);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Pharmacy Services Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 15px;
            background-color: #f9f9f9;
        }

        h2 {
            color: #2c3e50;
        }

        form {
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 6px #ccc;
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        label {
            font-weight: bold;
            margin-right: 5px;
            line-height: 30px;
        }

        input[type="date"],
        select {
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            min-width: 140px;
        }

        input[type="submit"],
        button {
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 18px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
            height: 34px;
            align-self: center;
            margin-left: auto;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #219150;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        th,
        td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background-color: #e1f5fe;
        }

        .serial-number {
            width: 50px;
            text-align: center;
        }

        .actions {
            margin-bottom: 15px;
        }

        .actions button,
        .actions form {
            display: inline-block;
            margin-right: 10px;
        }
    </style>
    <script>
        function printReport() {
            window.print();
        }

        function downloadCSV(tableName) {
            var form = document.getElementById('filterForm');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'download_csv';
            input.value = tableName;
            form.appendChild(input);
            form.submit();
        }
    </script>
</head>

<body>
    <h1>Pharmacy Services Report</h1>
    <form method="POST" id="filterForm">
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars(isset($_POST['start_date']) ? $_POST['start_date'] : '') ?>" required />
        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars(isset($_POST['end_date']) ? $_POST['end_date'] : '') ?>" required />

        <label for="hospital_no">Hospital No:</label>
        <input type="text" name="hospital_no" value="<?= htmlspecialchars(isset($_POST['hospital_no']) ? $_POST['hospital_no'] : '') ?>" placeholder="Optional" />
        <label for="prepared_by">Prepared By:</label>
        <select name="prepared_by">
            <option value="">-- All --</option>
            <?php foreach ($prepared_by_query as $row): ?>
                <option value="<?= htmlspecialchars($row['prepared_by']) ?>" <?= (isset($_POST['prepared_by']) && $_POST['prepared_by'] === $row['prepared_by']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['prepared_by']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="dsp_by">Dispensed By:</label>
        <select name="dsp_by">
            <option value="">-- All --</option>
            <?php foreach ($dsp_by_query as $row): ?>
                <option value="<?= htmlspecialchars($row['dsp_by']) ?>" <?= (isset($_POST['dsp_by']) && $_POST['dsp_by'] === $row['dsp_by']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['dsp_by']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="pay_mode">Payment Mode:</label>
        <select name="pay_mode">
            <option value="">-- All --</option>
            <?php foreach ($pay_mode_query as $row): ?>
                <option value="<?= htmlspecialchars($row['pay_mode']) ?>" <?= (isset($_POST['pay_mode']) && $_POST['pay_mode'] === $row['pay_mode']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['pay_mode']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="dept_id">Department:</label>
        <select name="dept_id">
            <option value="">-- All --</option>
            <?php foreach ($department_query as $row): ?>
                <option value="<?= htmlspecialchars($row['sn']) ?>" <?= (isset($_POST['dept_id']) && $_POST['dept_id'] == $row['sn']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['department']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="item_services">Drug (Item Services):</label>
        <select name="item_services">
            <option value="">-- All --</option>
            <?php foreach ($item_services_query as $row): ?>
                <option value="<?= htmlspecialchars($row['drug_sn']) ?>" <?= (isset($_POST['item_services']) && $_POST['item_services'] === $row['drug_sn']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['item_services']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Filter" />
        &nbsp;&nbsp;&nbsp; :
        <a href="statistics.php" style="font-size: 16px;;">Reset</a>
        <a href="index.php?rpt" style="font-size: 16px;;">Close</a>

    </form>

    <?php if (!empty($results)): ?>
        <div class="actions">
            <button onclick="printReport()">Print Report</button>
            <button onclick="downloadCSV('pharmacy_services')">Download Pharmacy Services CSV</button>
            <button onclick="downloadCSV('summary')">Download Summary CSV</button>
            <button onclick="downloadCSV('statistics')">Download Statistics CSV</button>
        </div>

        <h2>Pharmacy Services</h2>
        <table>
            <thead>
                <tr>
                    <th class="serial-number">#</th>
                    <th>Hospital No</th>
                    <th>Surname</th>
                    <th>First Name</th>
                    <th>Drug (Item Services)</th>
                    <th>Prepared By</th>
                    <th>Date Entry</th>
                    <th>Dispensed By</th>
                    <th>Dispensed Date</th>

                    <th>Department</th>
                    <th>Hospital Price</th>
                    <th>Claim Amount</th>
                    <th>Pay</th>
                    <th>Pay Mode</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serial = 1;
                foreach ($results as $row): ?>
                    <tr>
                        <td class="serial-number"><?= $serial++ ?></td>
                        <td><?= htmlspecialchars($row['hospital_no']) ?></td>
                        <td><?= htmlspecialchars($row['surname']) ?></td>
                        <td><?= htmlspecialchars($row['fname']) ?></td>
                        <td><?= htmlspecialchars($row['item_services']) ?></td>
                        <td><?= htmlspecialchars($row['prepared_by']) ?></td>

                        <td>
                            <?php
                            $dateTime = new DateTime($row['date_entry']);
                            echo htmlspecialchars($dateTime->format('d/M/Y h:i A')); // Format: dd mm YYYY hh:mm AM/PM
                            ?>
                        </td>

                        <td><?= htmlspecialchars($row['enter_by']) ?></td>
                        <td>
                            <?php
                            $dateTime = new DateTime($row['insertion_date_time']);
                            echo htmlspecialchars($dateTime->format('d/M/Y h:i A')); // Format: dd mm YYYY hh:mm AM/PM
                            ?>
                        </td>


                        <td><?= htmlspecialchars($row['department'] !== null ? $row['department'] : 'N/A') ?></td>
                        <td><?= htmlspecialchars(number_format($row['hosp_price'], 2)) ?></td>
                        <td><?= htmlspecialchars(number_format($row['claim_amt'], 2)) ?></td>
                        <td><?= htmlspecialchars(number_format($row['pay'], 2)) ?></td>
                        <td><?= htmlspecialchars($row['pay_mode']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Summary by Prepared By (Total Medications Prescribed) and Dispensed By (Total Medications Dispensed)</h2>
        <table>
            <thead>
                <tr>
                    <th class="serial-number">#</th>
                    <th>Prepared By</th>
                    <th>Total Medications Prescribed</th>
                    <th class="serial-number">#</th>
                    <th>Dispensed By</th>
                    <th>Total Medications Dispensed</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $all_prepared_by = array_keys($summary_medications_map);
                $all_dsp_by = array_keys($summary_dispensed_map);
                $max_rows = max(count($all_prepared_by), count($all_dsp_by));
                for ($i = 0; $i < $max_rows; $i++):
                    $prepared_by_key = isset($all_prepared_by[$i]) ? $all_prepared_by[$i] : '';
                    $dispensed_by_key = isset($all_dsp_by[$i]) ? $all_dsp_by[$i] : '';
                    $med_count = $prepared_by_key ? $summary_medications_map[$prepared_by_key] : '';
                    $dispensed_count = $dispensed_by_key ? $summary_dispensed_map[$dispensed_by_key] : '';
                ?>
                    <tr>
                        <td class="serial-number"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($prepared_by_key) ?></td>
                        <td><?= htmlspecialchars($med_count) ?></td>
                        <td class="serial-number"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($dispensed_by_key) ?></td>
                        <td><?= htmlspecialchars($dispensed_count) ?></td>
                    </tr>
                <?php endfor; ?>
                <tr style="font-weight:bold; background-color:#ecf0f1;">
                    <td colspan="2">Total Distinct Prepared By</td>
                    <td><?= count($all_prepared_by) ?></td>
                    <td colspan="1">Total Distinct Dispensed By</td>
                    <td><?= $total_dispensed_by ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <h2>Statistics of Drugs</h2>
        <table>
            <thead>
                <tr>
                    <th class="serial-number">#</th>
                    <th>Drug Name</th>
                    <th>Total Patients</th>
                    <th>Total Amount</th>
                    <th>Total Claim Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serial = 1;
                foreach ($statistics_results as $stat): ?>
                    <tr>
                        <td class="serial-number"><?= $serial++ ?></td>
                        <td><?= htmlspecialchars($stat['item_services']) ?></td>
                        <td><?= htmlspecialchars($stat['total_patients']) ?></td>
                        <td><?= htmlspecialchars(number_format($stat['total_amount'], 2)) ?></td>
                        <td><?= htmlspecialchars(number_format($stat['total_claim_amt'], 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <p>No records found for the selected filters.</p>
    <?php endif; ?>

    <script>
        function printReport() {
            window.print();
        }

        function downloadCSV(tableName) {
            var form = document.getElementById('filterForm');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'download_csv';
            input.value = tableName;
            form.appendChild(input);
            form.submit();
        }
    </script>

</body>

</html>