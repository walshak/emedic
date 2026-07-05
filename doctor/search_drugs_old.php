<?php session_start();
$dispensory = $_SESSION['dispensory'];
include('../Connections/Conn.php');
function cleanInput($data)
{
    $data = htmlspecialchars($data);
    $data = stripslashes($data);
    $data = trim($data);
    return $data;
}


if (isset($_POST['drug_search_typeahead'])) {
    header('Content-Type: application/json');
    $input_text = cleanInput($_POST['input_text']);

    if ($dispensory == 1) {

        $sql = "SELECT  m.sn,m.product_name, CONCAT(m.product_name, '') AS name, m.sn id, m.product_name, d.hosp_price, d.nhis_price,d.cash_price,d.expire_date,m.qty FROM stock_table m
        INNER JOIN stock_table_dispensory d ON d.stock_table_id = m.sn
          WHERE m.status = 'active' AND d.hosp_price > 0 AND m.product_name  LIKE '%$input_text%' ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $drug_stocks_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($drug_stocks_list);
        exit;
    } else {


        $sql = "SELECT  sn,product_name, CONCAT(product_name, '  Qty: [',qty,']') AS name, sn id, product_name, hosp_price, nhis_price,cash_price,expire_date,qty FROM stock_table 
          WHERE status = 'active' AND hosp_price > 0 AND product_name  LIKE '%$input_text%' ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $drug_stocks_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($drug_stocks_list);
        exit;
    }
}
