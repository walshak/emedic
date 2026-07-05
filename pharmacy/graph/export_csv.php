<?php
include("../../Connections/Conn.php");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=pharmacy_report.csv');

$out = fopen("php://output", "w");

$metric = isset($_GET['metric_type']) ? $_GET['metric_type'] : 'claim';

if ($metric == 'combined') {
    fputcsv($out, array('Label', 'Count', 'Claim', 'Cash(Paid)', 'Total'));
} else {
    fputcsv($out, array('Label', 'Count', 'Claim', 'Cash(Paid)'));
}

$where = array();
$params = array();

if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $where[] = "DATE(p.transact_date) BETWEEN :from AND :to";
    $params[':from'] = $_GET['from'];
    $params[':to'] = $_GET['to'];
} else {
    exit;
}

if (!empty($_GET['hospital_no'])) {
    $where[] = "p.hospital_no = :hospital_no";
    $params[':hospital_no'] = $_GET['hospital_no'];
}

$where[] = "p.serv_group='pharmacy'";
$where[] = "p.paystatus=1";

$where_sql = implode(" AND ", $where);

$group = isset($_GET['chart_reporting_type']) ? $_GET['chart_reporting_type'] : 'category';

$map = array(
    'insurance_type' => 'i.insurance_type',
    'insurance_name' => 'i.insurance_name',
    'prepared_by' => 'p.prepared_by',
    'dsp_by' => 'p.dsp_by',
    'item_services' => 'p.item_services',
    'pay_mode' => 'p.pay_mode',
    'category' => 's.category',
    'generic_name' => 's.generic_name'
);

$groupField = isset($map[$group]) ? $map[$group] : 's.category';

$sql = "
SELECT 
    $groupField AS label,
    COUNT(*) AS total_count,
    SUM(p.claim_amt) AS total_claim,
    SUM(p.pay) AS total_pay,
    SUM(p.claim_amt + p.pay) AS total_value
FROM patient_ap_services p
INNER JOIN stock_table s ON s.sn = p.drug_sn
INNER JOIN enrollee e ON e.hospital_no = p.hospital_no
INNER JOIN insurance_tbl i ON i.insurance_no = e.hmo_no
WHERE $where_sql
GROUP BY $groupField
ORDER BY total_value DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $label = $row['label'] ? $row['label'] : 'N/A';

    if ($metric == 'combined') {
        fputcsv($out, array($label, $row['total_count'], $row['total_claim'], $row['total_pay'], $row['total_value']));
    } else {
        fputcsv($out, array($label, $row['total_count'], $row['total_claim'], $row['total_pay']));
    }
}

fclose($out);
exit;
