<?php
include_once '../../Connections/Conn.php';

// Fetch more accounts, skipping the first 5
$stmt = $db->query("SELECT distinct a.account_code, a.account_name 
                    FROM chart_accounts as a
                    INNER JOIN chart_ledger as l ON l.account_no = a.account_code
                    WHERE account_no != ''
                    ORDER BY account_name
                    LIMIT 60, 18446744073709551615");  // Skips the first 60 records, selects the rest by setting a large number as the edge of the limit

$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [];
foreach ($accounts as $account) {
    $response[] = [
        'value' => $account['account_code'] . '____' . $account['account_name'],
        'text' => $account['account_name']
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
