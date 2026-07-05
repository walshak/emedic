<div class="row">

    <form action="index.php?adm" method="POST">
        <div class="col-sm-3">
            <label for="reg_select" class="" style="color: firebrick; ">Sort by Floor</label>
            <select name="floor" class="form-control">
                <option selected="selected" value="">... select ...</option>
                <?php
                $stmt = $db->query("SELECT distinct tips FROM bed_mgt where tips!='' ORDER BY tips ASC");

                while ($row2 = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                    <option value="<?php echo $row2['tips']; ?>"><?php echo $row2['tips']; ?></option>
                <?php } ?>
            </select>
        </div>


        <div class="col-sm-3">

            <div class="form_sep">
                <label for="reg_select" class="">.</label><br>
                <button class="btn btn-info btn btn-sm" type="submit" name="apply" id="apply">Apply</button>
                &nbsp;&nbsp; | &nbsp;&nbsp;

                <a href="index.php?adm" class="btn btn-white btn btn-sm"><i class="fa fa-refresh"></i> &nbsp; Refresh</a>
            </div>

        </div>
    </form>

</div>
<hr>

<?php


$stmt = $db->prepare("SELECT hospital_no FROM discharge_fellowup");
$stmt->execute();

$dischargeHospitalNos = $stmt->fetchAll(PDO::FETCH_COLUMN);
$dischargeLookup = array_flip($dischargeHospitalNos);
$X_search = '';
$params = [];

if (isset($_POST['apply']) && !empty($_POST['floor'])) {
    $X_search = "AND d.floor = ?";
    $params[] = $_POST['floor'];
}

// Main query to fetch all needed admissions
$stmt = $db->prepare("
    SELECT 
        d.hospital_no, 
        d.app_no, 
        d.room_bed, 
        d.room_bed_sn, 
        d.date_admit,
        d.doc_incharge, 
        d.floor, 
        COALESCE(dd.department, 'Admission Pending') AS department,
        d.admit_type
    FROM admission AS d
    LEFT JOIN department AS dd ON dd.sn = d.dept_id
    WHERE d.adm_status in ('0','3') $X_search
    ORDER BY d.date_admit DESC
");

$stmt->execute($params);
$admissions = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Collect hospital numbers and app_nos for bulk fetching
$hospitalNos = array_column($admissions, 'hospital_no');
$appNos = array_column($admissions, 'app_no');

// Bulk fetch patient names
$patients = [];
if (!empty($hospitalNos)) {
    $placeholders = implode(',', array_fill(0, count($hospitalNos), '?'));
    $pstmt = $db->prepare("SELECT hospital_no, surname, fname FROM enrollee WHERE hospital_no IN ($placeholders)");
    $pstmt->execute($hospitalNos);
    while ($row = $pstmt->fetch(PDO::FETCH_ASSOC)) {
        $patients[$row['hospital_no']] = $row['surname'] . ' ' . $row['fname'];
    }
}

// Bulk fetch appointment services
$services = [];
if (!empty($appNos)) {
    $placeholders = implode(',', array_fill(0, count($appNos), '?'));
    $appStmt = $db->prepare("SELECT appt_no, services_name FROM apptm WHERE appt_no IN ($placeholders)");
    $appStmt->execute($appNos);
    while ($row = $appStmt->fetch(PDO::FETCH_ASSOC)) {
        $services[$row['appt_no']] = $row['services_name'];
    }
}

// Automatic discharge for expired observation admissions
$autoDischarge = [];
$currentDateTime = new DateTime();
foreach ($admissions as $row) {
    if ($row['admit_type'] === 'admit_o') {
        $admitDate = new DateTime($row['date_admit']);
        $diffDays = $admitDate->diff($currentDateTime)->days;
        if ($diffDays >= 1) {
            $autoDischarge[] = $row['hospital_no'];
        }
    }
}

if (!empty($autoDischarge)) {
    $placeholders = implode(',', array_fill(0, count($autoDischarge), '?'));
    $updateSQL = "
        UPDATE admission
        SET adm_status = '4',
            date_discharge = NOW(),
            discharge_by_nurse = 'System Discharge'
        WHERE hospital_no IN ($placeholders) AND adm_status = '3'
    ";
    $updateStmt = $db->prepare($updateSQL);
    $updateStmt->execute($autoDischarge);
}
?>

<table class="table table-striped table-bordered table-hover dataTables-example" style="font-size:14px;">
    <thead>
        <tr>
            <th>No</th>
            <th>Hosp. No</th>
            <th>Name</th>
            <th>Adm. By</th>
            <th>Service Type</th>
            <th>Room/Ward</th>
            <th>Floor/Dept</th>
            <th>Adm. Date</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $n = 1;
        foreach ($admissions as $row):
            $hospNo = htmlspecialchars($row['hospital_no']);
            $admitType = $row['admit_type'];
            $appNo = $row['app_no'];
            $admitDateStr = $row['date_admit'];
            $admitDate = new DateTime($admitDateStr);
            $admitDateFormatted = $admitDate->format('d, M h:i a');
            $patientName = htmlspecialchars($patients[$hospNo]);
            $serviceType = htmlspecialchars($services[$appNo]);
            $roomInfo = $admitType === 'admit_o'
                ? '<b style="color:red;">ADMIT TO OBSERVATION</b>'
                : htmlspecialchars($row['room_bed']);
            $adm = "&adm";
            if (isset($dischargeLookup[$hospNo])) {
                $floorDept = htmlspecialchars($row['floor']) . ' (' . htmlspecialchars($row['department']) . ')<br><strong style="color:red;">Discharge Pending</strong>';
                $button_color = 'danger';
            } elseif ($row['department'] == 'Admission Pending') {
                $floorDept = '<strong style="color:green;">' . htmlspecialchars($row['department']) . '</strong>';
                $adm = "";
                $button_color = 'warning';
            } else {
                $floorDept = htmlspecialchars($row['floor']) . '<br><strong>' . htmlspecialchars($row['department']) . '</strong>';
                $button_color = 'primary';
            }
        ?>
            <tr>
                <td><?= $n++ ?></td>
                <td><?= $hospNo ?></td>
                <td><?= $patientName ?></td>
                <td><?= htmlspecialchars($row['doc_incharge']) ?></td>
                <td><?= $serviceType ?></td>
                <td><?= $roomInfo ?></td>
                <td><?= $floorDept ?></td>
                <td><?= $admitDateFormatted ?></td>
                <td align="center">
                    <a href="patient.php?hosp_no=<?= urlencode($hospNo) . $adm ?>" class="btn btn-<?= $button_color; ?> ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>