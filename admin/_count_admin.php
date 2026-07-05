<?php
session_start();
include("../Connections/Conn.php");
$setdate = date("Y-m-d");

if (!empty($_POST['Accknl_booking'])) {
    $sn = (int) $_POST['Accknl_booking']; // Typecast to avoid SQL injection


    if ($sn === 1) {
        // Acknowledge all
        $stmt = $db->prepare("UPDATE apptm_fellowup SET status = 1 WHERE status = 0 ");
        // $stmt->bindParam(':setdate', $setdate, PDO::PARAM_STR);
    } else {
        // Acknowledge specific recor
        $stmt = $db->prepare("UPDATE apptm_fellowup SET status = 1 WHERE sn = :sn");
        $stmt->bindParam(':sn', $sn, PDO::PARAM_INT);
    }

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            echo "Update successful.";
        } else {
            echo "No records were updated. (Possibly already set)";
        }
    } else {
        echo "Update failed. Please try again.";
    }
}



if (isset($_POST['consults_status'])) {
    // Close consultations older than 60 minutes
    closeOldConsultations($db);

    // Get today's ongoing consultations
    $allAppointments = getTodaysOngoingConsultations($db);

    displayConsultationTable($allAppointments);
}

/**
 * Close consultations that have been ongoing for more than 60 minutes
 */
function closeOldConsultations($db)
{
    $cutoffTime = date('Y-m-d H:i:s', strtotime('-60 minutes'));

    $stmt = $db->prepare("
        UPDATE consultations 
        SET status = 'Closed' 
        WHERE status = 'On-going' 
        AND start_time <= :cutoffTime
    ");
    $stmt->bindParam(':cutoffTime', $cutoffTime);
    $stmt->execute();
}

/**
 * Get all ongoing consultations for today
 */
function getTodaysOngoingConsultations($db)
{
    $setdate = date("Y-m-d");

    $stmt = $db->prepare("
        SELECT * FROM consultations
        WHERE DATE(start_time) = :setdate 
        AND status = 'On-going'
    ");
    $stmt->bindParam(':setdate', $setdate);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Display the consultation table or "no results" message
 */
function displayConsultationTable($appointments)
{
    if (count($appointments) > 0): ?>
        <a class="btn btn-warning btn-rounded" href="index.php" onclick="consults_status()">
            Close:
        </a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>EMR/Name</th>
                    <th>Doctor</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Consult Room</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $row):
                    $startTime = new DateTime($row['start_time']);
                    $duration = calculateDuration($startTime);
                ?>
                    <tr>
                        <td><?= htmlspecialchars("{$row['hosp_no']}/{$row['patient_name']}") ?></td>
                        <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                        <td><?= $startTime->format('h:ia') ?></td>
                        <td><?= $duration ?> (Min.)</td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['room']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <h3>No Current On-going Consultation!</h3>
        <a href="index.php">Close</a>
    <?php endif;
}

/**
 * Calculate duration in minutes from start time to now
 */
function calculateDuration(DateTime $startTime)
{
    $currentTime = new DateTime();
    $interval = $currentTime->diff($startTime);

    return ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
}



if (isset($_POST['display_queue_table'])) {
    $stmt = $db->prepare("
    SELECT 
        a.hospital_no,
        a.patient_name,
        a.status,
        a.ap_date_time,
        p.serv_group,
        p.cat_type
    FROM apptm a
    JOIN patient_ap_services p ON p.app_no = a.appt_no
    WHERE a.date_ap = :setdate
      AND a.queue_lock = 0
      AND a.status = 'checkin'
      AND (p.serv_group = 'Consultation' OR p.cat_type = 'Dialysis')
    GROUP BY 
        a.appt_no, 
        a.hospital_no, 
        a.patient_name, 
        a.status, 
        a.ap_date_time, 
        p.serv_group, 
        p.cat_type
");
    $stmt->bindParam(':setdate', $setdate);
    $stmt->execute();
    $allAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group in PHP
    $consultationQueue = [];
    $dialysisQueue = [];

    foreach ($allAppointments as $row) {
        if ($row['serv_group'] === 'Consultation') {
            $consultationQueue[] = $row;
        }
        if ($row['cat_type'] === 'Dialysis') {
            $dialysisQueue[] = $row;
        }
    }

    ?>


    <?php if (count($consultationQueue) > 0): ?>

        <hr>
        <table width="100%">
            <tr>
                <td align="">
                    <h3 style="color:#900">
                        <?php
                        $setdate = date("Y-m-d");
                        // Prepare the query to count ongoing consultations
                        $stmt = $db->prepare("SELECT COUNT(*) FROM consultations
                        WHERE DATE(start_time) = :setdate 
                            AND status = 'On-going'");
                        $stmt->bindParam(':setdate', $setdate);
                        $stmt->execute();
                        $ongoingCount = $stmt->fetchColumn(); // Fetch the count directly

                        if ($ongoingCount > 0): ?>
                            <a href="#" class="btn btn-danger btn-rounded" onclick="consults_status()">
                                <i class="fa fa-sign-out"></i> View Consults Status
                            </a>
                            &nbsp;:&nbsp;
                        <?php endif; ?>

                        CANCEL/CHANGE & NHIS/PHIS Auth. Code <a href="index.php?equiry" class="btn btn-success btn-rounded">
                            <i class="fa fa-sign-out"></i>Click Here
                        </a>
                    </h3>
                </td>
            </tr>
        </table>

        <!-- Consultation Section -->



        <a class="btn btn-primary btn-rounded" href="">
            Queue List:
            <strong style="font-size: 14px;"><?= count($consultationQueue) ?></strong>
        </a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="50%">EMR/Name</th>
                    <th width="10%">Status</th>
                    <th>Time</th>
                    <th>.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultationQueue as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['hospital_no']) ?>/<?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <?php
                            $date = new DateTime($row['ap_date_time']);
                            echo $date->format('d M') . '/ ' . $date->format('h:ia');
                            ?>
                        </td>
                        <td><a href="index.php?ptm=all/<?= htmlspecialchars($row['hospital_no']) ?>">View Patient</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Dialysis Section -->
    <?php if ($_SESSION['dialysis_visible'] == 1 && count($dialysisQueue) > 0): ?>

        <a class="btn btn-primary btn-rounded" href="">Dialysis List:
            <strong style="font-size: 14px;"><?= count($dialysisQueue) ?></strong>
        </a>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="50%">EMR/Name</th>
                    <th width="10%">Status</th>
                    <th>Time</th>
                    <th>.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dialysisQueue as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['hospital_no']) ?>/<?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <?php
                            $date = new DateTime($row['ap_date_time']);
                            echo $date->format('d M') . '/ ' . $date->format('h:ia');
                            ?>
                        </td>
                        <td><a href="index.php?ptm=all/<?= htmlspecialchars($row['hospital_no']) ?>">View Patient</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<?php endif;
}

if (isset($_POST['get_dashboard_counts'])) {
    //  header('Content-Type: application/json');

    $setdate = date('Y-m-d');

    $stmt = $db->prepare("
    SELECT 
        SUM(CASE WHEN p.serv_group = 'Consultation' THEN 1 END) AS consultation_count,
        SUM(CASE WHEN p.cat_type = 'Dialysis' THEN 1 END) AS dialysis_count
    FROM apptm a
    JOIN patient_ap_services p ON p.app_no = a.appt_no
    WHERE a.date_ap = :setdate
      AND a.queue_lock = 0
      AND a.status = 'checkin'
");

    $stmt->bindParam(':setdate', $setdate);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $TO_QUEUE = (int)$result['consultation_count'];
    $TO_QUEUE_dialysis = (int)$result['dialysis_count'];





    try {
        $stmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN adm_status = '3' THEN 1 ELSE 0 END) AS adm_count,
            SUM(CASE WHEN adm_status = '4' THEN 1 ELSE 0 END) AS discharge_count
        FROM admission
        WHERE DATE(date_admit) = :setdate OR DATE(date_discharge) = :setdate
    ");

        // bind as string explicitly
        $stmt->bindValue(':setdate', $setdate, PDO::PARAM_STR);

        $ok = $stmt->execute();
        if (! $ok) {
            $err = $stmt->errorInfo();
            $today_adm = 0;
            $today_discharge = 0;
        } else {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row === false) {
                // no rows returned (rare for aggregate but safe)
                $today_adm = 0;
                $today_discharge = 0;
            } else {
                $today_adm = (int) ($row['adm_count']);
                $today_discharge = (int) ($row['discharge_count']);
            }
        }
    } catch (PDOException $ex) {
        $today_adm = 0;
        $today_discharge = 0;
    }




    if ($report_mgr == 1) {
        $title = "This Month <a href='discount.php' style='color:white;'> [ View ]</a>";
        $start_month = date('Y-m-01');
        $end_month = date('Y-m-t');
        $d_c_query = "AND p.transact_date BETWEEN :start_month AND :end_month";
    } else {
        $title = "As At Today";
        $d_c_query = "";
    }






    $sql = "SELECT SUM(p.discount) AS discount 
            FROM patient_ap_services AS p 
            WHERE p.discount > 0 $d_c_query";
    $stmt5 = $db->prepare($sql);
    if ($report_mgr == 1) {
        $stmt5->bindParam(':start_month', $start_month);
        $stmt5->bindParam(':end_month', $end_month);
    }
    $stmt5->execute();
    $dsc_rwx = $stmt5->fetch(PDO::FETCH_ASSOC);
    $discount_report = number_format((float) ($dsc_rwx['discount']));

    $sql = "SELECT SUM(p.add_charge) AS add_charge
            FROM patient_ap_services AS p
            INNER JOIN chart_ledger AS b ON b.sale_sn = p.sn
            WHERE p.add_charge > 0 $d_c_query";
    $stmt6 = $db->prepare($sql);
    if ($report_mgr == 1) {
        $stmt6->bindParam(':start_month', $start_month);
        $stmt6->bindParam(':end_month', $end_month);
    }
    $stmt6->execute();
    $rwx = $stmt6->fetch(PDO::FETCH_ASSOC);
    $extra_charge = number_format((float) ($rwx['add_charge']));

    echo json_encode([
        'status' => 200,
        'today_queue' => $TO_QUEUE,
        'TO_QUEUE_dialysis' => $TO_QUEUE_dialysis,
        'today_adm' => $today_adm,
        'today_discharge' => $today_discharge,
        'extra_charge' => $extra_charge,
        'discount_report' => $discount_report
    ]);
    exit;
}
