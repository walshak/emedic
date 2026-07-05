<?php
include_once '../../Connections/Conn.php';
// Fetch more HMO dues, skipping the first 5
$stmt = $db->prepare("SELECT distinct s.insurance_no, s.insurance_name 
                      FROM insurance_tbl as s 
                      INNER JOIN chart_ledger as c ON c.insurance_no = s.insurance_no 
                      WHERE s.insurance_no != '1000'
                      LIMIT 60, 18446744073709551615");  // Skips the first 60 records, selects the rest
$stmt->execute();
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [];
foreach ($accounts as $account) {
    $response[] = [
        'value' => $account['insurance_no'],
        'text' => $account['insurance_name']
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
