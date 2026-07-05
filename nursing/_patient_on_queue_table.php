<?php
include("../Connections/Conn.php");
include('../doctor/objects.php');
include('../doctor/helpers.php');
session_start();

// Set date
$setdate = isset($_POST['vital_date']) ? $_POST['vital_date'] : date("Y-m-d");
$currentDateTime = date('Y-m-d H:i:s');

// Define filter based on input
$filter = isset($_POST['vital_date'])
    ? "AND a.vital_lock = 1 AND a.queue_lock = 1"
    : "AND a.vital_lock = 0 AND (a.queue_lock = 0 OR a.re_queue_lock = 0)";

// Handle department filter
$deptFilter = "";
if (!empty($_SESSION['dispensory']) && $_SESSION['dispensory'] == 1) {
    $dept_id = $_SESSION['dept_id'];
    $deptFilter = "AND dept = :dept_id";
}

// Prepare main patient queue query
$currentDateTime = date('Y-m-d H:i:s');

$sql = "
    SELECT a.*, 
           p.serv_group, 
           p.paystatus, 
           p.item_services, 
           p.app_no AS p_app_no, 
           u.fullname AS doctor_fullname
    FROM apptm a
    INNER JOIN patient_ap_services p ON a.appt_no = p.app_no
    LEFT JOIN admin_users u ON a.app_by = u.username
    WHERE p.serv_group = 'Consultation'
      AND DATE(a.app_expiration_date) >= :cur_time
      AND p.item_services != 'New File'
      AND a.queue_time_stamp BETWEEN DATE_SUB(:now_time, INTERVAL 6 HOUR) AND :now_time2
      $filter
      $deptFilter
    ORDER BY a.sn DESC
";

$stmt = $db->prepare($sql);

// Bind required parameters
$stmt->bindParam(':cur_time', $currentDateTime);
$stmt->bindParam(':now_time', $currentDateTime);
$stmt->bindParam(':now_time2', $currentDateTime);

// Bind optional department filter
if (!empty($deptFilter)) {
    $stmt->bindParam(':dept_id', $dept_id);
}

// Execute
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<table width="100%">
    <tr>
        <td width="50%">
            <strong style="color: red;">Patient(s) On-Queue</strong>
            <a onClick="queue_list()"><strong>&nbsp;[ Refresh List ]</strong></a>
        </td>
        <td width="50%">
            <div align="right">
                <strong style="color: red;">Vital Taken Report: </strong>Specify Date
                <input type="date" name="vital_date" id="vital_date" value="<?= $setdate ?>">
                <button type="submit" class="btn btn-success btn-sm" onClick="queue_list_display()">Display</button>
            </div>
        </td>
    </tr>
</table><br>

<table class='table table-striped table-bordered table-hover dataTables-example'>
    <thead>
        <tr>
            <th>No</th>
            <th>Hospital No.</th>
            <th>Patient Name</th>
            <th>Service Name</th>
            <th>Contact</th>
            <th>Date</th>
            <th>Waiting</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $index = 1;
        foreach ($patients as $row) {
            $patient = $Patient->get(['hospital_no' => $row['hospital_no']]);
            $isPaid = ($row['paystatus'] == 1 || $row['cr'] > 0);
            $disabled = $isPaid ? '' : 'disabled';
            $title = $isPaid ? '' : ($row['cr'] > 0 ? 'On-Credit' : 'Un-Paid');

            $vitalStatus = ($row['vital_lock'] == 0) ? 'Take Vitals' : 'View Patient';
            $serviceName = strtoupper($row['services_name']);
            $doctorName = ($row['app_by'] === 'anydoctor' || empty($row['doctor_fullname'])) ? 'Any Doctor' : $row['doctor_fullname'];

            $appointmentDate = date('d M, y h:i:s a', strtotime($row['ap_date_time']));
            $waitingTime = dateDifference_format($row['ap_date_time']) . ' ago';

            echo "<tr>
                    <td>{$index}</td>
                    <td>{$patient->hospital_no}</td>
                    <td>{$row['patient_name']}</td>
                    <td>{$row['services_name']}</td>
                    <td>{$doctorName}</td>
                    <td>{$appointmentDate}</td>
                    <td>{$waitingTime}</td>
                    <td class='text-center'>";

            if (in_array($serviceName, ['VACCINATION', 'IMMUNIZATION', 'VACCINE'])) {
                echo "<a href='patient.php?hosp_no={$patient->hospital_no}&vaccine' class='btn btn-primary' {$disabled}>{$title} Immunization</a>";
            } else {
                $btnClass = ($row['queue_center'] === 'MF') ? 'primary' : 'success';
                $label = ($row['queue_center'] === 'MF') ? 'Antenatal' : $vitalStatus;
                echo "<a href='patient.php?hosp_no={$patient->hospital_no}&vitals' class='btn btn-{$btnClass}' {$disabled}>{$title} {$label}</a>";
            }

            echo "</td></tr>";
            $index++;
        }
        ?>
    </tbody>
</table>

<?php
// Count patients with vitals taken
$countStmt = $db->prepare("SELECT COUNT(*) FROM apptm WHERE vital_lock = 1 AND DATE(queue_time_stamp) = :setdate");
$countStmt->bindParam(':setdate', $setdate);
$countStmt->execute();
$vitalTakenCount = $countStmt->fetchColumn();
?>

<hr>
<h4>TOTAL PATIENT(S) VITALS TAKEN COUNT: <?= $vitalTakenCount; ?></h4>