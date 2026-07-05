<?php
session_start();
include("../Connections/Conn.php");


$search = $_GET['q'];
$search = trim($search);

$sql = "
    SELECT sn, product_name
    FROM stock_table
    WHERE category = 'Nursing Consumables'
      AND status = 'active'
      AND product_name LIKE :search
    ORDER BY product_name
    LIMIT 50
";

$stmt = $db->prepare($sql);
$stmt->execute([
    ':search' => "%$search%"
]);

$results = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $results[] = [
        'id'   => $row['sn'],
        'text' => $row['product_name']
    ];
}

echo json_encode($results);
