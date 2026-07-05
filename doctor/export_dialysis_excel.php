<?php
include("../Connections/Conn.php");


header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=dialysis_completed_" . date("Ymd_His") . ".xls");

$where = ["completed = 'yes'"];
$params = [];

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $where[] = "DATE(request_date) BETWEEN :start AND :end";
    $params[':start'] = $_GET['start_date'];
    $params[':end'] = $_GET['end_date'];
}

if (!empty($_GET['request_type'])) {
    $where[] = "request_type = :type";
    $params[':type'] = $_GET['request_type'];
}

if (!empty($_GET['search'])) {
    $where[] = "(hospital_no LIKE :search OR patient_name LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$where_clause = implode(" AND ", $where);
$sql = "SELECT * FROM dialysis WHERE $where_clause AND status = '1' ORDER BY id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<table border="1">
    <thead>
        <tr>
            <th>SN</th>
            <th>Patient Name</th>
            <th>Hospital No</th>
            <th>Request Type</th>
            <th>Number of Session</th>
            <th>Request Date</th>
        </tr>
    </thead>
    <tbody>
        <?php $sn = 1;
        foreach ($data as $row): ?>
            <tr>
                <td><?= $sn++ ?></td>
                <td><?= $row['patient_name'] ?></td>
                <td><?= $row['hospital_no'] ?></td>
                <td><?= $row['request_type'] ?></td>
                <td><?= $row['number_of_session'] ?></td>
                <td><?= date('d-M-Y h:i A', strtotime($row['request_date'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>