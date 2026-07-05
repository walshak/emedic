<?php include("../Connections/Conn.php"); ?>
<?php

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

if ($category === 'all') {
    $stmt = $db->prepare("SELECT sn, item_service, ext_price FROM `prices_table` WHERE hosp_price > 0 AND ext_price > 0 AND is_sugical_procedure = 1 ORDER BY item_service ASC");
} else {
    $stmt = $db->prepare("SELECT sn, item_service, ext_price FROM `prices_table` WHERE category = :category AND hosp_price > 0 AND ext_price > 0 AND is_sugical_procedure = 1 ORDER BY item_service ASC");
    $stmt->bindParam(':category', $category);
}

$stmt->execute();
$procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($procedures);
?>