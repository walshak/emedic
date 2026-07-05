<?php
include("../../Connections/Conn.php");

$where = array();
$params = array();

/* DATE */
if (!empty($_POST['from']) && !empty($_POST['to'])) {
    $where[] = "DATE(p.transact_date) BETWEEN :from AND :to";
    $params[':from'] = $_POST['from'];
    $params[':to'] = $_POST['to'];
} else {
    echo json_encode(array("error" => "Date required"));
    exit;
}

/* HOSPITAL */
if (!empty($_POST['hospital_no'])) {
    $where[] = "p.hospital_no = :hospital_no";
    $params[':hospital_no'] = $_POST['hospital_no'];
}

$where[] = "p.serv_group='pharmacy'";
$where[] = "p.paystatus=1";

$where_sql = implode(" AND ", $where);

/* GROUP */
$group = isset($_POST['chart_reporting_type']) ? $_POST['chart_reporting_type'] : 'category';

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

/* METRIC */
$metric = isset($_POST['metric_type']) ? $_POST['metric_type'] : 'claim';

$metricField = "SUM(p.claim_amt)";
if ($metric == 'pay') $metricField = "SUM(p.pay)";
if ($metric == 'combined') $metricField = "SUM(p.claim_amt + p.pay)";

/* QUERY */
$sql = "
SELECT 
    $groupField AS label,
    COUNT(*) AS total_count,
    SUM(p.claim_amt) AS total_claim,
    SUM(p.pay) AS total_pay,
    $metricField AS total_value
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
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = array();
$values = array();

foreach ($data as $row) {
    $labels[] = $row['label'] ? $row['label'] : 'N/A';
    $values[] = (float)$row['total_value'];
}

echo json_encode(array(
    "labels" => $labels,
    "values" => $values,  /// put comma in values for js to parse
    "table" => $data
));
