<?php
session_start();
include("../Connections/Conn.php");

if (isset($_GET['v_6474747']) or isset($_POST['hospital_no'])) {
    $HOSPITAL = $_GET['v_6474747'];
    $hospital_no = base64_decode(base64_decode($HOSPITAL));

    if (isset($_POST['hospital_no'])) {
        $hospital_no = $_POST['hospital_no'];
    }

    if ($hospital_no) {

        $stmt_en = $db->prepare("SELECT surname, fname, gender, dob, age FROM enrollee WHERE hospital_no = :hospital_no");
        $stmt_en->execute([':hospital_no' => $hospital_no]);

        if ($stmt_en->rowCount() > 0) {
            $row = $stmt_en->fetch(PDO::FETCH_ASSOC);
            $age = $row['age'];
            $dob = $row['dob'];
            $patient_name = $row['surname'] . ', ' . $row['fname'];
            $gender = $row['gender'];
        }
    }
}


// Default filter and sample patient info
$showRemarks = false;
$showNurses  = false;
$startDate = '';
$endDate = '';
$patientInfo = [
    'Name' => $patient_name,
    'EMR_Number' => $hospital_no,
    'Age' => $age,
    'Gender' => $gender,
];


$showRemarks = isset($_POST['show_remarks']) && $_POST['show_remarks'] === 'on';
$showNurses  = isset($_POST['show_nurses']) && $_POST['show_nurses'] === 'on';
$startDate = $_POST['start_date'];
$endDate = $_POST['end_date'];
// TODO: Here you can fetch patientInfo dynamically from DB based on hosp no


try {
    // Create PDO connection

    // Get distinct fluid types for intake
    $sqlIntakeTypes = "
        SELECT DISTINCT fluid_type 
        FROM fluidchart 
        WHERE chart = 'in' AND hospital_no = :hospital_no
    ";
    $stmt = $db->prepare($sqlIntakeTypes);
    $stmt->execute([':hospital_no' => $hospital_no]);
    $intakeFluidTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get distinct fluid types for output
    $sqlOutputTypes = "
        SELECT DISTINCT fluid_type 
        FROM fluidchart 
        WHERE chart = 'out' AND hospital_no = :hospital_no
    ";
    $stmt = $db->prepare($sqlOutputTypes);
    $stmt->execute([':hospital_no' => $hospital_no]);
    $outputFluidTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get all entries grouped by entry_date and entry_time, fluid_type, chart
    $sqlEntries = "
        SELECT entry_date, entry_time, fluid_type, chart, amount, remark, nurse 
        FROM fluidchart 
        WHERE hospital_no = :hospital_no
        AND entry_date BETWEEN :start_date AND :end_date
        ORDER BY entry_date, entry_time
    ";
    $stmt = $db->prepare($sqlEntries);
    $stmt->execute([':hospital_no' => $hospital_no, ':start_date' => $startDate, ':end_date' => $endDate]);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data structure: group by date, then time; store amount, remarks, nurses lists
    $data = [];
    foreach ($entries as $entry) {
        $date = $entry['entry_date'];
        $time = substr($entry['entry_time'], 0, 5);
        $chart = $entry['chart'];
        $fluid = $entry['fluid_type'];

        $amount_str = strtolower($entry['amount']);
        $amount_str = str_replace(['mls', 'ml', ' '], '', $amount_str);
        $amount = is_numeric($amount_str) ? (float)$amount_str : 0;

        $remark = trim($entry['remark']);
        $nurse = trim($entry['nurse']);

        if (!isset($data[$date])) {
            $data[$date] = [];
        }
        if (!isset($data[$date][$time])) {
            $data[$date][$time] = ['in' => [], 'out' => []];
        }
        if (!isset($data[$date][$time][$chart][$fluid])) {
            $data[$date][$time][$chart][$fluid] = [
                'amount' => 0,
                'remarks' => [],
                'nurses' => [],
            ];
        }
        $data[$date][$time][$chart][$fluid]['amount'] += $amount;
        if ($remark !== '') {
            $data[$date][$time][$chart][$fluid]['remarks'][] = $remark;
        }
        if ($nurse !== '') {
            $data[$date][$time][$chart][$fluid]['nurses'][] = $nurse;
        }
    }
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$grandTotals = [
    'in' => array_fill_keys((isset($intakeFluidTypes) ? $intakeFluidTypes : []), 0),
    'out' => array_fill_keys((isset($outputFluidTypes) ? $outputFluidTypes : []), 0),
    'totalIn' => 0,
    'totalOut' => 0,
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Patient Monitoring Chart for Nurse</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f9f9f9;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 10px;
        }

        .logo img {
            max-height: 70px;
        }

        .title {
            font-weight: bold;
            font-size: 1.8rem;
            color: #007BFF;
        }

        .patient-info {
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .patient-info span {
            display: inline-block;
            margin-right: 20px;
            font-weight: bold;
        }

        form {
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            margin-right: 10px;
        }

        input[type="text"],
        input[type="date"] {
            padding: 6px 10px;
            font-size: 1rem;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button,
        #printBtn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 7px 15px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 3px;
            margin-left: 5px;
        }

        button:hover,
        #printBtn:hover {
            background-color: #0056b3;
        }

        h2 {
            color: #004a99;
            margin-top: 40px;
            margin-bottom: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            background: #fff;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 10px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #007BFF;
            color: white;
            white-space: nowrap;
        }

        tbody tr:nth-child(odd) {
            background-color: #fefefe;
        }

        tbody tr:hover {
            background-color: #f1f9ff;
        }

        tfoot td {
            font-weight: bold;
            background-color: #def0ff;
        }

        .time-col {
            font-weight: bold;
            background-color: #e6f0ff;
            color: #004a99;
            white-space: nowrap;
        }

        .balance-cell {
            font-weight: bold;
            background-color: #cce6ff;
            color: #003366;
        }

        .grand-total-table {
            font-size: 1rem;
            font-weight: bold;
            margin-top: 40px;
        }

        .grand-total-table th,
        .grand-total-table td {
            border: 2px solid #007BFF;
            background: #cce6ff;
            color: #003366;
        }

        .remark,
        .nurse {
            font-size: 0.85em;
            display: block;
            margin-top: 2px;
            font-style: italic;
        }

        .remark {
            color: #555;
        }

        .nurse {
            color: #2a3f54;
            font-weight: bold;
            font-style: normal;
        }

        thead tr.group-header th {
            background-color: #0056b3;
            font-size: 1.1rem;
            padding: 10px 6px;
            border-bottom: 2px solid white;
        }

        @media print {

            #printBtn,
            form {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>

<body>

    <div class="header">
        <div class="logo">
            <!-- Replace src with actual hospital logo path -->
            <img src="../img/logo.jpg" alt="Hospital Logo" />
        </div>
        <div class="title">PATIENT MONITORING CHART FOR NURSE</div>
    </div>

    <div class="patient-info">
        <span>Name: <?= htmlspecialchars($patientInfo['Name']) ?></span>
        <span>EMR Number: <?= htmlspecialchars($patientInfo['EMR_Number']) ?></span>
        <span>Age: <?= htmlspecialchars($patientInfo['Age']) ?></span>
        <span>Gender: <?= htmlspecialchars($patientInfo['Gender']) ?></span>
    </div>

    <form method="post" action="">
        <input type="hidden" id="hospital_no" name="hospital_no" value="<?= htmlspecialchars($hospital_no) ?>" required />

        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required />

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required />

        <label style="margin-left:20px;">
            <input type="checkbox" name="show_remarks" <?= $showRemarks ? 'checked' : '' ?> />
            Show Remarks
        </label>
        <label style="margin-left:20px;">
            <input type="checkbox" name="show_nurses" <?= $showNurses ? 'checked' : '' ?> />
            Show Nurse(s)
        </label>
        <button type="submit">Submit</button>
        &nbsp;&nbsp; : &nbsp;&nbsp;
        <button type="button" id="printBtn" onclick="printPage()">Print</button>
        &nbsp;&nbsp; : &nbsp;&nbsp;
        <a href="patient.php?hosp_no=<?= $hospital_no; ?>">Close</a>
    </form>

    <?php if (empty($data)): ?>
        <p>No data found for Hospital Number: <?= htmlspecialchars($hospital_no) ?> within the selected date range.</p>
    <?php else: ?>
        <?php foreach ($data as $date => $times): ?>
            <h2><?= date('d M Y', strtotime($date)) ?></h2>
            <table>
                <thead>
                    <tr class="group-header">
                        <th rowspan="2">Time</th>
                        <th colspan="<?= count($intakeFluidTypes) + 1 ?>">IN</th>
                        <th colspan="<?= count($outputFluidTypes) + 1 ?>">OUT</th>
                        <th rowspan="2">Balance (mL)</th>
                    </tr>
                    <tr>
                        <?php foreach ($intakeFluidTypes as $ft): ?>
                            <th><?= htmlspecialchars($ft) ?></th>
                        <?php endforeach; ?>
                        <th>Total Intake (mL)</th>
                        <?php foreach ($outputFluidTypes as $ft): ?>
                            <th><?= htmlspecialchars($ft) ?></th>
                        <?php endforeach; ?>
                        <th>Total Output (mL)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dailyTotals = ['in' => array_fill_keys($intakeFluidTypes, 0), 'out' => array_fill_keys($outputFluidTypes, 0)];
                    $dailyTotalIn = 0;
                    $dailyTotalOut = 0;

                    foreach ($times as $time => $vals):
                        $rowTotalIn = 0;
                        $rowTotalOut = 0;
                        $formattedTime = date('h:i A', strtotime($time)); // 12-hour format
                    ?>
                        <tr>
                            <td class="time-col"><?= htmlspecialchars($formattedTime) ?></td>
                            <?php
                            // Intake columns
                            foreach ($intakeFluidTypes as $ft) {
                                if (isset($vals['in'][$ft])) {
                                    $item = $vals['in'][$ft];
                                    $amount = $item['amount'];
                                    $remarksArr = $item['remarks'];
                                    $nursesArr = $item['nurses'];
                                } else {
                                    $amount = 0;
                                    $remarksArr = [];
                                    $nursesArr = [];
                                }
                                $amountDisplay = ($amount > 0) ? number_format($amount) : '';
                                if ($showRemarks && !empty($remarksArr)) {
                                    $remarkText = htmlspecialchars(implode('; ', array_unique($remarksArr)));
                                    $amountDisplay .= '<span class="remark">(' . $remarkText . ')</span>';
                                }
                                if ($showNurses && !empty($nursesArr)) {
                                    $nurseText = htmlspecialchars(implode('; ', array_unique($nursesArr)));
                                    $amountDisplay .= '<span class="nurse">[' . $nurseText . ']</span>';
                                }
                                echo '<td>' . $amountDisplay . '</td>';
                                $rowTotalIn += $amount;
                                $dailyTotals['in'][$ft] += $amount;
                                $grandTotals['in'][$ft] += $amount;
                            }
                            ?>
                            <td><strong><?= number_format($rowTotalIn) ?></strong></td>
                            <?php
                            // Output columns
                            foreach ($outputFluidTypes as $ft) {
                                if (isset($vals['out'][$ft])) {
                                    $item = $vals['out'][$ft];
                                    $amount = $item['amount'];
                                    $remarksArr = $item['remarks'];
                                    $nursesArr = $item['nurses'];
                                } else {
                                    $amount = 0;
                                    $remarksArr = [];
                                    $nursesArr = [];
                                }
                                $amountDisplay = ($amount > 0) ? number_format($amount) : '';
                                if ($showRemarks && !empty($remarksArr)) {
                                    $remarkText = htmlspecialchars(implode('; ', array_unique($remarksArr)));
                                    $amountDisplay .= '<span class="remark">(' . $remarkText . ')</span>';
                                }
                                if ($showNurses && !empty($nursesArr)) {
                                    $nurseText = htmlspecialchars(implode('; ', array_unique($nursesArr)));
                                    $amountDisplay .= '<span class="nurse">[' . $nurseText . ']</span>';
                                }
                                echo '<td>' . $amountDisplay . '</td>';
                                $rowTotalOut += $amount;
                                $dailyTotals['out'][$ft] += $amount;
                                $grandTotals['out'][$ft] += $amount;
                            }
                            ?>
                            <td><strong><?= number_format($rowTotalOut) ?></strong></td>
                            <?php
                            $balance = $rowTotalIn - $rowTotalOut;
                            echo '<td class="balance-cell">' . number_format($balance) . '</td>';
                            $dailyTotalIn += $rowTotalIn;
                            $dailyTotalOut += $rowTotalOut;
                            $grandTotals['totalIn'] += $dailyTotalIn;
                            $grandTotals['totalOut'] += $dailyTotalOut;
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td><strong>Daily Total</strong></td>
                        <?php
                        foreach ($intakeFluidTypes as $ft) {
                            echo '<td><strong>' . number_format($dailyTotals['in'][$ft]) . '</strong></td>';
                        }
                        ?>
                        <td><strong><?= number_format($dailyTotalIn) ?></strong></td>
                        <?php
                        foreach ($outputFluidTypes as $ft) {
                            echo '<td><strong>' . number_format($dailyTotals['out'][$ft]) . '</strong></td>';
                        }
                        ?>
                        <td><strong><?= number_format($dailyTotalOut) ?></strong></td>
                        <td class="balance-cell"><strong><?= number_format($dailyTotalIn - $dailyTotalOut) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        <?php endforeach; ?>

        <h2>Grand Total Summary</h2>
        <table class="grand-total-table">
            <thead>
                <tr class="group-header">
                    <th rowspan="2">Total</th>
                    <th colspan="<?= count($intakeFluidTypes) + 1 ?>">IN</th>
                    <th colspan="<?= count($outputFluidTypes) + 1 ?>">OUT</th>
                    <th rowspan="2">Balance</th>
                </tr>
                <tr>
                    <?php foreach ($intakeFluidTypes as $ft): ?>
                        <th><?= htmlspecialchars($ft) ?></th>
                    <?php endforeach; ?>
                    <th>Total Intake (mL)</th>
                    <?php foreach ($outputFluidTypes as $ft): ?>
                        <th><?= htmlspecialchars($ft) ?></th>
                    <?php endforeach; ?>
                    <th>Total Output (mL)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Grand Total</strong></td>
                    <?php
                    foreach ($intakeFluidTypes as $ft) {
                        echo '<td>' . number_format($grandTotals['in'][$ft]) . '</td>';
                    }
                    ?>
                    <td><?= number_format($grandTotals['totalIn']) ?></td>
                    <?php
                    foreach ($outputFluidTypes as $ft) {
                        echo '<td>' . number_format($grandTotals['out'][$ft]) . '</td>';
                    }
                    ?>
                    <td><?= number_format($grandTotals['totalOut']) ?></td>
                    <td class="balance-cell"><?= number_format($grandTotals['totalIn'] - $grandTotals['totalOut']) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>