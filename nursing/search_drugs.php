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
        $sql = "SELECT 
            m.sn,
            m.product_name,
            CONCAT(
                m.product_name,
                ' Qty: [', COALESCE(m.qty, '0'), ']',
                CASE
                    WHEN d.expire_date IS NULL OR CAST(d.expire_date AS CHAR) = '' OR CAST(d.expire_date AS CHAR) = '0000-00-00' 
                    THEN ''
                    WHEN DATE(d.expire_date) <= DATE_ADD(CURRENT_DATE, INTERVAL 6 MONTH)
                    THEN CONCAT(' **exp in ', DATEDIFF(d.expire_date, CURRENT_DATE), ' days**')
                    ELSE ''
                END
            ) AS name,
            m.sn AS id,
            COALESCE(d.hosp_price, 0) as hosp_price,
            COALESCE(d.nhis_price, 0) as nhis_price,
            COALESCE(d.cash_price, 0) as cash_price,
            d.expire_date,
            COALESCE(m.qty, 0) as qty,
            CASE
                WHEN d.expire_date IS NULL OR CAST(d.expire_date AS CHAR) = '' OR CAST(d.expire_date AS CHAR) = '0000-00-00' 
                THEN 0
                WHEN DATE(d.expire_date) <= DATE_ADD(CURRENT_DATE, INTERVAL 6 MONTH)
                THEN 1
                ELSE 0
            END AS expires_soon
        FROM stock_table m
        INNER JOIN stock_table_dispensory d ON d.stock_table_id = m.sn
        WHERE m.status = 'active' 
        AND d.hosp_price > 0 
        AND (m.product_name LIKE :input_text OR m.generic_name LIKE :input_text)
        LIMIT 50";
    } else {
        $sql = "SELECT 
            sn,
            product_name,
            CONCAT(
                product_name,
                ' Qty: [', COALESCE(qty, '0'), ']',
                CASE
                    WHEN expire_date IS NULL OR CAST(expire_date AS CHAR) = '' OR CAST(expire_date AS CHAR) = '0000-00-00' 
                    THEN ''
                    WHEN DATE(expire_date) <= DATE_ADD(CURRENT_DATE, INTERVAL 6 MONTH)
                    THEN CONCAT(' **exp in ', DATEDIFF(expire_date, CURRENT_DATE), ' days**')
                    ELSE ''
                END
            ) AS name,
            sn AS id,
            COALESCE(hosp_price, 0) as hosp_price,
            COALESCE(nhis_price, 0) as nhis_price,
            COALESCE(cash_price, 0) as cash_price,
            expire_date,
            COALESCE(qty, 0) as qty,
            CASE
                WHEN expire_date IS NULL OR CAST(expire_date AS CHAR) = '' OR CAST(expire_date AS CHAR) = '0000-00-00' 
                THEN 0
                WHEN DATE(expire_date) <= DATE_ADD(CURRENT_DATE, INTERVAL 6 MONTH)
                THEN 1
                ELSE 0
            END AS expires_soon
        FROM stock_table
        WHERE status = 'active' 
        AND hosp_price > 0 
        AND (product_name LIKE :input_text OR generic_name LIKE :input_text2)
        LIMIT 50";
    }

    try {
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':input_text', '%' . $input_text . '%', PDO::PARAM_STR);
        $stmt->bindValue(':input_text2', '%' . $input_text . '%', PDO::PARAM_STR);
        $stmt->execute();
        $drug_stocks_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($drug_stocks_list);
    } catch (PDOException $e) {
        // Log error and return empty result
        error_log("Drug search error: " . $e->getMessage());
        echo json_encode([]);
    }
    exit;
}
