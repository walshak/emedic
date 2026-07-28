<?php
include("../Connections/Conn.php");

if (!isset($_GET['rpt_type']) || $_GET['rpt_type'] !== 'ps') {
    exit('Invalid request');
}

$setdate = date('Y-m-d');

$ddset = isset($_GET['ddset']) ? $_GET['ddset'] : 0;
$start = isset($_GET['start']) ? $_GET['start'] : null;
$to = isset($_GET['to']) ? $_GET['to'] : null;
$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : null;
$staff = isset($_GET['staff']) ? $_GET['staff'] : '';

$bindParams = [
    ':drug_status' => 1,
    ':dept_id' => $dept_id,
    ':dept_dispensory_id' => $dept_id
];

$whereClause = "p.drug_status = :drug_status
                AND (p.dept_id = :dept_id OR p.dept_dispensory_id = :dept_dispensory_id)";

$dateFilter = ($ddset == 1)
    ? "AND DATE(p.date_entry) BETWEEN :start AND :to"
    : "AND DATE(p.date_entry) = :setdate";

if ($ddset == 1) {
    $bindParams[':start'] = $start;
    $bindParams[':to'] = $to;
} else {
    $bindParams[':setdate'] = $setdate;
}

$sql = "SELECT p.hospital_no, p.item_services, p.qty, p.claim_amt, p.pay,
               p.serv_group, p.paystatus, p.prepared_by, p.dsp_by, p.date_entry
        FROM patient_ap_services p
        WHERE $whereClause $dateFilter $staff
        ORDER BY p.hospital_no, p.date_entry ASC";

$stmt = $db->prepare($sql);
foreach ($bindParams as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ======================
   CSV DOWNLOAD
   ====================== */
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=patient_sales_' . date('Ymd_His') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Hospital No',
    'Drug Name',
    'Qty',
    'Claim Amount',
    'Amount Paid',
    'Service Group',
    'Pay Status',
    'Prepared By',
    'Dispensed By',
    'Date'
]);

foreach ($data as $row) {
    fputcsv($output, [
        $row['hospital_no'],
        $row['item_services'],
        $row['qty'],
        $row['claim_amt'],
        $row['pay'],
        $row['serv_group'],
        $row['paystatus'],
        $row['prepared_by'],
        $row['dsp_by'],
        date('Y-m-d H:i:s', strtotime($row['date_entry']))
    ]);
}

fclose($output);
exit;
