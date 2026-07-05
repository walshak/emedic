<?php
session_start();
require_once('../Connections/Conn.php');

if (isset($_POST['download_data_button'])) {

    $download_type = $_POST['download_type'];
    $typeoftable = $_POST['typeoftable'];
    $category = $_POST['category'];
    $stock_table = $_POST['stock_table'];

    $search_string = $category != '' ? "AND category='$category'" : '';

    try {
        // Create a new PDO instance
        $db = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($download_type == 'hmo') {
            // HMO / COPERATE
            $download_hmo = $_POST['download_hmo'];
            $pp = explode("__", $download_hmo);
            $download_hmo = $pp[0];
            $insurance_name = $pp[1];

            $fileName = "hmo-stock-data-" . $typeoftable . "(" . $download_hmo . ")_" . date('Y-m-d') . ".csv";

            $sql = "SELECT hmo_stocks_tariff.sn as the_sn, hmo_stocks_tariff.stock_sn, hmo_stocks_tariff.hmo,
                    hmo_stocks_tariff.price, stock_table.product_name, insurance_tbl.insurance_name
                    FROM hmo_stocks_tariff 
                    INNER JOIN stock_table ON hmo_stocks_tariff.stock_sn = stock_table.sn 
                    INNER JOIN insurance_tbl ON insurance_tbl.insurance_no = hmo_stocks_tariff.hmo
                    WHERE stock_table.stock_table = :typeoftable AND hmo_stocks_tariff.hmo = :download_hmo";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':typeoftable', $typeoftable);
            $stmt->bindParam(':download_hmo', $download_hmo);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            $output = fopen('php://output', 'w');

            if (count($rows) > 0) {
                $header = ["sn", "stock_sn", "hmo", "product_name", "insurance_name", "price", "delete(YES or NO)"];
                fputcsv($output, $header);

                foreach ($rows as $item) {
                    fputcsv($output, [$item['the_sn'], $item['stock_sn'], $item['hmo'], $item['product_name'], $item['insurance_name'], $item['price'], 'NO']);
                }
            } else {
                $sql = "SELECT * FROM stock_table WHERE stock_table = :typeoftable AND status = 'active' $search_string";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':typeoftable', $typeoftable);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $header = ["sn", "stock_sn", "hmo", "product_name", "insurance_name", "price", "delete(YES or NO)"];
                fputcsv($output, $header);

                foreach ($rows as $item) {
                    fputcsv($output, ["", $item['sn'], $download_hmo, $item['product_name'], $insurance_name, $item['hosp_price'], 'NO']);
                }
            }

            fclose($output);
            exit();
        } else {
            $fileName = "full-stock-data-" . $typeoftable . "_" . date('Y-m-d') . ".csv";

            $sql = "SELECT * FROM stock_table WHERE stock_table = :typeoftable $search_string";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':typeoftable', $typeoftable);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            $output = fopen('php://output', 'w');

            $header = [
                "sn", "product_name", "category", "navigation", "stock_table", "coverage", "insurance_type", "generic_name", "dosage", "strength", "presentation", "buying_cost",
                "nhis_price", "hosp_price", "cash_price", "stock_total_unit", "qty", "main_qty", "reorder_level", "mfg_date",
                "expire_date", "status", "date_captured", "entry_mode", "supplier_id", "date_last_update", "ml_1sheet"
            ];
            fputcsv($output, $header);

            foreach ($rows as $item) {
                $filteredItem = [];
                foreach ($header as $key) {
                    if (isset($item[$key])) {
                        $filteredItem[$key] = $item[$key];
                    } else {
                        $filteredItem[$key] = '';
                    }
                }
                fputcsv($output, $filteredItem);
            }

            fclose($output);
            exit();
        }
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
    }
}
