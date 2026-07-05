<?php
session_start();
require_once('../Connections/Conn.php');

if (isset($_POST['download_data_button'])) {



    $download_type = $_POST['download_type'];
    $typeoftable = $_POST['typeoftable'];
    $category = $_POST['category'];
    $search_string = $category != '' ? "AND category='$category'" : '';


    if ($download_type == 'hmo') {
        // HMO / CORPORATE
        $download_hmo = $_POST['download_hmo'];
        $pp = explode("__", $download_hmo);
        $download_hmo = $pp[0];
        $insurance_name = $pp[1];

        $fileName = "hmo-stock-data-" . $typeoftable . "(" . $download_hmo . ")_" . date('Y-m-d') . ".csv";

        // SQL Query to fetch all items from stock_table
        $sql = "SELECT 
                    stock_table.sn AS stock_sn,
                    stock_table.product_name,
                    stock_table.hosp_price
                FROM stock_table
                WHERE stock_table.stock_table = :typeoftable AND stock_table.status = 'active'";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':typeoftable', $typeoftable);
        $stmt->execute();
        $stockRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare CSV file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');

        // Define the CSV header
        $header = ["sn", "stock_sn", "hmo", "item", "insurance_name", "price", "delete(YES or NO)", "price_markup"];
        fputcsv($output, $header);

        foreach ($stockRows as $stockItem) {
            $stock_sn = $stockItem['stock_sn'];

            // Check for HMO price for each item
            $sql = "SELECT 
                        hmo_stocks_tariff.sn AS the_sn,
                        hmo_stocks_tariff.stock_sn,
                        hmo_stocks_tariff.hmo,
                        hmo_stocks_tariff.price AS hmo_price,
                        hmo_stocks_tariff.price_markup,
                        stock_table.product_name,
                        insurance_tbl.insurance_name
                    FROM hmo_stocks_tariff 
                    INNER JOIN stock_table ON hmo_stocks_tariff.stock_sn = stock_table.sn 
                    INNER JOIN insurance_tbl ON insurance_tbl.insurance_no = hmo_stocks_tariff.hmo
                    WHERE hmo_stocks_tariff.hmo = :download_hmo AND stock_table.sn = :stock_sn";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':download_hmo', $download_hmo);
            $stmt->bindParam(':stock_sn', $stock_sn);
            $stmt->execute();
            $hmoRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($hmoRow) {
                // Use HMO price if available
                fputcsv($output, [$hmoRow['the_sn'], $hmoRow['stock_sn'], $hmoRow['hmo'], $hmoRow['product_name'], $hmoRow['insurance_name'], $hmoRow['hmo_price'], 'NO', $hmoRow['price_markup']]);
            } else {
                // Use generic price from stock_table
                fputcsv($output, ['', $stock_sn, $download_hmo, $stockItem['product_name'], $insurance_name, $stockItem['hosp_price'], 'NO']);
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
            "sn",
            "product_name",
            "generic_name",
            "buying_cost",
            "category",
            "navigation",
            "stock_table",
            "nhis_price",
            "hosp_price",
            "cash_price",
            "stock_total_unit",
            "qty",
            "status",
            "price_markup"
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
}
