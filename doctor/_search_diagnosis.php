<?php
session_start();
include("../Connections/Conn.php");

header('Content-Type: application/json');

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$results = [];

if ($search !== '') {

    $stmt = $db->prepare("
        SELECT id, item 
        FROM diagnosis 
        WHERE item LIKE :search 
        ORDER BY item ASC 
        LIMIT 50
    ");

    $stmt->execute([
        ':search' => "%$search%"
    ]);
} else {
    // Prevent heavy loading when no search term
    echo json_encode([]);
    exit;
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $results[] = [
        'id'   => $row['id'],
        'text' => $row['item']
    ];
}

echo json_encode($results);
exit;
