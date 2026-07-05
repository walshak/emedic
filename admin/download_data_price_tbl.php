<?php
session_start();
// Database configuration 
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

        // SQL Query to fetch all items from prices_table
        $sql = "SELECT 
                    prices_table.sn AS stock_sn,
                    prices_table.item_service,
                    prices_table.hosp_price
                FROM prices_table
                WHERE prices_table.price_table = :typeoftable $search_string";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':typeoftable', $typeoftable);
        $stmt->execute();
        $pricesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare CSV file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');

        // Define the CSV header
        $header = ["sn", "stock_sn", "hmo", "item", "insurance_name", "price", "delete(YES or NO)"];
        fputcsv($output, $header);

        foreach ($pricesRows as $priceItem) {
            $stock_sn = $priceItem['stock_sn'];

            // Check for HMO price for each item
            $sql = "SELECT 
                        hmo_medical_tariff.sn AS the_sn,
                        hmo_medical_tariff.stock_sn,
                        hmo_medical_tariff.hmo,
                        hmo_medical_tariff.price AS hmo_price,
                        prices_table.item_service,
                        insurance_tbl.insurance_name
                    FROM hmo_medical_tariff 
                    INNER JOIN prices_table ON hmo_medical_tariff.stock_sn = prices_table.sn 
                    INNER JOIN insurance_tbl ON insurance_tbl.insurance_no = hmo_medical_tariff.hmo
                    WHERE hmo_medical_tariff.hmo = :download_hmo AND prices_table.sn = :stock_sn";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':download_hmo', $download_hmo);
            $stmt->bindParam(':stock_sn', $stock_sn);
            $stmt->execute();
            $hmoRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($hmoRow) {
                // Use HMO price if available
                fputcsv($output, [$hmoRow['the_sn'], $hmoRow['stock_sn'], $hmoRow['hmo'], $hmoRow['item_service'], $hmoRow['insurance_name'], $hmoRow['hmo_price'], 'NO']);
            } else {
                // Use generic price from prices_table
                fputcsv($output, ['', $stock_sn, $download_hmo, $priceItem['item_service'], $insurance_name, $priceItem['hosp_price'], 'NO']);
            }
        }

        fclose($output);
        exit();
    } else {

        $fileName = "full-stock-data-" . $typeoftable . "_" . date('Y-m-d') . ".csv";

        $sql = "SELECT ptbl.*, dept.department 
                FROM prices_table AS ptbl 
                INNER JOIN department AS dept ON dept.sn = ptbl.dept 
                WHERE ptbl.price_table = :typeoftable $search_string 
                ORDER BY ptbl.sn";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':typeoftable', $typeoftable);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');

        $header = [
            "sn",
            "item_service",
            "category",
            "dept",
            "price_table",
            "hosp_price",
            "ext_price",
            "nhis_price",
            "status",
            "deptname"
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
