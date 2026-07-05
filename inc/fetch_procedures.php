<?php
session_start();
include("../Connections/Conn.php");
$show_ext_price = true;

if (in_array(strtolower($_SESSION['h_code']), array('360', 'mluth'))) {
    $show_ext_price = false;
}

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

if ($category === 'all') {
    $stmt = $db->prepare("
        SELECT sn, item_service, ext_price 
        FROM prices_table 
        WHERE hosp_price > 0 AND is_sugical_procedure = 1 
        ORDER BY item_service ASC
    ");
} else {
    $stmt = $db->prepare("
        SELECT sn, item_service, ext_price 
        FROM prices_table 
        WHERE category = :category 
          AND hosp_price > 0 
          AND is_sugical_procedure = 1 
        ORDER BY item_service ASC
    ");
    $stmt->bindParam(':category', $category);
}

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Remove price if hospital doesn't want to show price
if (!$show_ext_price) {
    foreach ($data as &$row) {
        unset($row['ext_price']);
    }
}

echo json_encode($data);
