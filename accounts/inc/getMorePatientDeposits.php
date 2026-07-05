<?php
include_once '../../Connections/Conn.php';
// Fetch more patient deposits, skipping the first 5
$stmt = $db->prepare("SELECT distinct e.surname, e.hospital_no, e.fname, e.oname  
                      FROM enrollee as e 
                      INNER JOIN chart_ledger as l ON e.hospital_no = l.hospital_no 
                      WHERE l.hospital_no AND account_no = '2121' AND (e.hospital_no IS NOT NULL OR e.hospital_no != '')
                      LIMIT 60, 18446744073709551615");  // Skips the first 60 records, selects the rest
$stmt->execute();
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [];
foreach ($accounts as $account) {
    $response[] = [
        'value' => $account['hospital_no'],
        'text' => $account['surname'] . ' ' . $account['fname'] . ' ' . $account['oname']
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
