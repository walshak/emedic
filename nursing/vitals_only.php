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

// Default values
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

$showPreparedBy = isset($_POST['show_prepared_by']) && $_POST['show_prepared_by'] === 'on';



// Prepare SQL query
$sql = "SELECT pulse_read, bp, temp, resp_rate, height, weight, bmi, others, date_ap, prepared_by, GTT, muac_read, spo2, fbs, rbs, ppbs, FHR, comments 
        FROM vital_sign 
        WHERE hospital_no='$hospital_no' AND date(date_ap) BETWEEN :start_date AND :end_date 
        ORDER BY date_ap";

$stmt = $db->prepare($sql);
$stmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);

$results = $stmt->fetchAll();

// Group readings by date and hour
$readings_by_date = [];

foreach ($results as $row) {
    $date = date('jS F, Y', strtotime($row['date_ap']));
    $dt = new DateTime($row['date_ap']);
    $hour = (int)$dt->format('G'); // 24-hour format hour (0-23)

    $readings_by_date[$date][$hour][] = [
        'temp' => $row['temp'],
        'bp' => $row['bp'],
        'pulse' => $row['pulse_read'],
        'resp' => $row['resp_rate'],
        'height' => $row['height'],
        'weight' => $row['weight'],
        'bmi' => $row['bmi'],
        'others' => $row['others'],
        'prepared_by' => $row['prepared_by'],
        'GTT' => $row['GTT'],
        'muac_read' => $row['muac_read'],
        'spo2' => $row['spo2'],
        'fbs' => $row['fbs'],
        'rbs' => $row['rbs'],
        'ppbs' => $row['ppbs'],
        'FHR' => $row['FHR'],
        'comments' => $row['comments'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Vital Signs</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background: #f9fafb;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .logo img {
            max-height: 70px;
        }

        .title {
            font-weight: bold;
            font-size: 1.8rem;
            color: #007bff;
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

        h2 {
            background: #4a90e2;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 40px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            background: white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        th,
        td {
            text-align: center;
            padding: 8px 6px;
            border-bottom: 1px solid #ddd;
            font-weight: 500;
            font-size: 14px;
            vertical-align: middle;
        }

        th {
            background-color: #007bff;
            color: white;
            letter-spacing: 0.05em;
        }

        tr:hover {
            background-color: #f1f5f9;
        }

        td:first-child,
        th:first-child {
            text-align: left;
            padding-left: 15px;
            font-weight: 600;
            background: #f0f4fb;
            width: 90px;
        }

        /* Fixed column widths */
        td:nth-child(n+2),
        th:nth-child(n+2) {
            width: 80px;
        }

        td:nth-child(19),
        th:nth-child(19) {
            width: 120px;
        }

        td:nth-last-child(2),
        th:nth-last-child(2),
        td:last-child,
        th:last-child {
            width: 100px;
        }

        /* Time right column */

        td .multi-entry {
            margin-bottom: 6px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }

        #printBtn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 7px 15px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 3px;
            margin-top: 20px;
        }

        #printBtn:hover {
            background-color: #0056b3;
        }

        @media print {
            #printBtn {
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
        <div class="title">PATIENT VITALS</div>
    </div>
    <div class="patient-info">
        <span>Name: <?= $patient_name; ?></span>
        <span>EMR Number: <?= $hospital_no; ?></span>
        <span>Age: <?= $age; ?></span>
        <span>Gender: <?= $gender; ?></span>
    </div>

    <form method="post" action="">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" required />

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" required />

        <label style="margin-left:20px;">
            <input type="checkbox" name="show_prepared_by" <?= $showPreparedBy ? 'checked' : '' ?> />
            Show Who Captured
        </label>

        <button type="submit">Submit</button>
        &nbsp;&nbsp; : &nbsp;&nbsp;
        <button type="button" id="printBtn" onclick="printPage()">Print</button>
        &nbsp;&nbsp;

        <a href="patient.php?hosp_no=<?= $hospital_no; ?>">Close</a>
        <input type="hidden" name="hospital_no" value="<?= $hospital_no; ?>">
    </form>

    <?php
    if (empty($readings_by_date)) {
        echo "<p>No records found in the specified date range.</p>";
    } else {
        foreach ($readings_by_date as $date => $hours_entries) {
            echo "<h3>{$date} Reading</h3>";
            echo "<table>";
            echo "<thead><tr>
            <th>Time</th>
            <th>TEMP</th>
            <th>BP</th>
            <th>PULSE</th>
            <th>RESP</th>
            <th>SP02</th>
            <th>FBS</th>
            <th>RBS</th>
            <th>Height</th>
            <th>Weight</th>
            <th>BMI</th>
            <th>GTT</th>
            <th>MUAC</th>
            <th>PPBS</th>
            <th>FHR</th>
            <th>Comments</th>
            <th>Others</th>";
            if ($showPreparedBy) {
                echo "<th>Prepared By</th>";
            }
            echo "</tr></thead><tbody>";

            // Sort hours ascending for consistent display
            ksort($hours_entries);

            $format = function ($val) {
                return ($val !== null && $val !== '') ? htmlspecialchars($val) : '-';
            };

            foreach ($hours_entries as $hour_val => $entries) {
                // Format hour label in 12h format with AM/PM
                $hour_label = date('g A', strtotime("$hour_val:00"));

                // Collect all column values for this row to check if non-empty
                $temp_col = [];
                $bp_col = [];
                $pulse_col = [];
                $resp_col = [];
                $spo2_col = [];
                $fbs_col = [];
                $rbs_col = [];
                $height_col = [];
                $weight_col = [];
                $bmi_col = [];
                $gtt_col = [];
                $muac_col = [];
                $ppbs_col = [];
                $fhr_col = [];
                $comments_col = [];
                $others_col = [];
                $prepared_by_col = [];

                foreach ($entries as $e) {
                    $temp_col[] = $format($e['temp']);
                    $bp_col[] = $format($e['bp']);
                    $pulse_col[] = $format($e['pulse']);
                    $resp_col[] = $format($e['resp']);
                    $spo2_col[] = $format($e['spo2']);
                    $fbs_col[] = $format($e['fbs']);
                    $rbs_col[] = $format($e['rbs']);
                    $height_col[] = $format($e['height']);
                    $weight_col[] = $format($e['weight']);
                    $bmi_col[] = $format($e['bmi']);
                    $gtt_col[] = $format($e['GTT']);
                    $muac_col[] = $format($e['muac_read']);
                    $ppbs_col[] = $format($e['ppbs']);
                    $fhr_col[] = $format($e['FHR']);
                    $comments_col[] = $format($e['comments']);
                    $others_col[] = $format($e['others']);
                    if ($showPreparedBy) {
                        $prepared_by_col[] = $format($e['prepared_by']);
                    }
                }

                $all_values = array_merge(
                    $temp_col,
                    $bp_col,
                    $pulse_col,
                    $resp_col,
                    $spo2_col,
                    $fbs_col,
                    $rbs_col,
                    $height_col,
                    $weight_col,
                    $bmi_col,
                    $gtt_col,
                    $muac_col,
                    $ppbs_col,
                    $fhr_col,
                    $comments_col,
                    $others_col
                );
                if ($showPreparedBy) {
                    $all_values = array_merge($all_values, $prepared_by_col);
                }

                // Filter out only entries that are not '-' or empty
                $non_empty = array_filter($all_values, function ($v) {
                    return $v !== '-' && $v !== '';
                });

                if (empty($non_empty)) {
                    // Skip empty row
                    continue;
                }

                // Prepare html for each column
                $temp_html = '';
                foreach ($temp_col as $val) {
                    // Color coding for Temperature
                    if ($val !== '-') {
                        $temp_value = (float)$val;
                        if ($temp_value < 36.1) {
                            $temp_html .= "<div class='multi-entry' style='color: red;'>{$val}°C</div>"; // Hypothermia
                        } elseif ($temp_value <= 37.2) {
                            $temp_html .= "<div class='multi-entry' style='color: green;'>{$val}°C</div>"; // Normal
                        } elseif ($temp_value <= 38.0) {
                            $temp_html .= "<div class='multi-entry' style='color: yellow;'>{$val}°C</div>"; // Mild Fever
                        } elseif ($temp_value <= 39.0) {
                            $temp_html .= "<div class='multi-entry' style='color: orange;'>{$val}°C</div>"; // Moderate Fever
                        } else {
                            $temp_html .= "<div class='multi-entry' style='color: red;'>{$val}°C</div>"; // High Fever
                        }
                    } else {
                        $temp_html .= "<div class='multi-entry'>{$val}</div>"; // Invalid Temp format
                    }
                }

                // Color coding for BP
                $bp_html = '';
                foreach ($bp_col as $val) {
                    $bp_value = explode('/', $val);
                    if (count($bp_value) == 2) {
                        $systolic = (int)$bp_value[0];
                        $diastolic = (int)$bp_value[1];
                        if ($systolic < 90 || $diastolic < 60) {
                            $bp_html .= "<div class='multi-entry' style='color: red;'>{$val}</div>"; // Low BP
                        } elseif ($systolic <= 120 && $diastolic <= 80) {
                            $bp_html .= "<div class='multi-entry' style='color: green;'>{$val}</div>"; // Normal BP
                        } elseif ($systolic <= 129 && $diastolic <= 80) {
                            $bp_html .= "<div class='multi-entry' style='color: yellow;'>{$val}</div>"; // Elevated BP
                        } elseif ($systolic <= 139 || $diastolic <= 89) {
                            $bp_html .= "<div class='multi-entry' style='color: orange;'>{$val}</div>"; // Hypertension Stage 1
                        } else {
                            $bp_html .= "<div class='multi-entry' style='color: red;'>{$val}</div>"; // Hypertension Stage 2
                        }
                    } else {
                        $bp_html .= "<div class='multi-entry'>{$val}</div>"; // Invalid BP format
                    }
                }

                $pulse_html = '';
                foreach ($pulse_col as $val) {
                    $pulse_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $resp_html = '';
                foreach ($resp_col as $val) {
                    $resp_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $spo2_html = '';
                foreach ($spo2_col as $val) {
                    $spo2_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $fbs_html = '';
                foreach ($fbs_col as $val) {
                    $fbs_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $rbs_html = '';
                foreach ($rbs_col as $val) {
                    $rbs_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $height_html = '';
                foreach ($height_col as $val) {
                    $height_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $weight_html = '';
                foreach ($weight_col as $val) {
                    $weight_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $bmi_html = '';
                foreach ($bmi_col as $val) {
                    $bmi_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $gtt_html = '';
                foreach ($gtt_col as $val) {
                    $gtt_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $muac_html = '';
                foreach ($muac_col as $val) {
                    $muac_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $ppbs_html = '';
                foreach ($ppbs_col as $val) {
                    $ppbs_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $fhr_html = '';
                foreach ($fhr_col as $val) {
                    $fhr_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $comments_html = '';
                foreach ($comments_col as $val) {
                    $comments_html .= "<div class='multi-entry'>{$val}</div>";
                }
                $others_html = '';
                foreach ($others_col as $val) {
                    $others_html .= "<div class='multi-entry'>{$val}</div>";
                }
                if ($showPreparedBy) {
                    $prepared_by_html = '';
                    foreach ($prepared_by_col as $val) {
                        $prepared_by_html .= "<div class='multi-entry'>{$val}</div>";
                    }
                }

                // Output the row
                echo "<tr>
            <td>{$hour_label}</td>
            <td>{$temp_html}</td>
            <td>{$bp_html}</td>
            <td>{$pulse_html}</td>
            <td>{$resp_html}</td>
            <td>{$spo2_html}</td>
            <td>{$fbs_html}</td>
            <td>{$rbs_html}</td>
            <td>{$height_html}</td>
            <td>{$weight_html}</td>
            <td>{$bmi_html}</td>
            <td>{$gtt_html}</td>
            <td>{$muac_html}</td>
            <td>{$muac_html}</td>
            <td>{$ppbs_html}</td>
            <td>{$fhr_html}</td>
            <td>{$comments_html}</td>
            <td>{$others_html}</td>";
                if ($showPreparedBy) {
                    echo "<td>{$prepared_by_html}</td>";
                }
                echo "</tr>";
            }

            echo "</tbody></table>";
        }
    }
    ?>

</body>

</html>