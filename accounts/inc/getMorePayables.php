<?php
include_once '../../Connections/Conn.php';

// Fetch more account payables, skipping the first 5
$stmt = $db->prepare('SELECT distinct s.sn, s.name 
                      FROM stock_company as s 
                      INNER JOIN chart_ledger as c ON c.insurance_no = s.sn
                      LIMIT 60, 18446744073709551615');  // Skips the first 60 records, selects the rest
$stmt->execute();
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [];
foreach ($accounts as $account) {
    $response[] = [
        'value' => $account['sn'],
        'text' => $account['name']
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
