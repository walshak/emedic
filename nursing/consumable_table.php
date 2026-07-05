<?php

include('../Connections/Conn.php');

if (isset($_POST['consumable_table'])) {

  $search = $_POST['input_text'] ?? '';

  $sql = "
    SELECT product_name, sn
    FROM stock_table
    WHERE status = 'active'
      AND product_name LIKE :search
      AND (
            category IN ('Consumables', 'Consumable', 'Nursing Consumables')
            OR navigation = 'nursing'
          )
    ORDER BY product_name ASC
    LIMIT 20
";


  $stmt = $db->prepare($sql);
  $stmt->execute(['search' => "%$search%"]);

  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}
