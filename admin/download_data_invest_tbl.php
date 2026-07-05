<?php
session_start();
// Database configuration 
require_once('../Connections/Conn.php');

if (isset($_POST['download_data_button'])) {

    $download_type = $_POST['download_type'];
    $typeoftable = $_POST['typeoftable'];
    $category = $_POST['category'];
    $search_string = $category != '' ? " WHERE ptbl.category='$category'" : '';

    if ($download_type == 'hmo') {
        // HMO / CORPORATE
        $download_hmo = $_POST['download_hmo'];
        $pp = explode("__", $download_hmo);
        $download_hmo = $pp[0];
        $insurance_name = $pp[1];

        $fileName = "hmo-lab-scan-data-" . $typeoftable . "(" . $download_hmo . ")_" . date('Y-m-d') . ".csv";

        // SQL Query to fetch all items from lab_scan
        $sql = "SELECT 
                    lab_scan.sn AS stock_sn,
                    lab_scan.test,
                    lab_scan.hosp_price
                FROM lab_scan";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $labScanRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare CSV file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');

        // Define the CSV header
        $header = ["sn", "stock_sn", "hmo", "item", "insurance_name", "price", "delete(YES or NO)"];
        fputcsv($output, $header);

        foreach ($labScanRows as $labScanItem) {
            $stock_sn = $labScanItem['stock_sn'];

            // Check for HMO price for each item
            $sql = "SELECT 
                        hmo_investigation_tariff.sn AS the_sn,
                        hmo_investigation_tariff.stock_sn,
                        hmo_investigation_tariff.hmo,
                        hmo_investigation_tariff.price AS hmo_price,
                        lab_scan.test,
                        insurance_tbl.insurance_name
                    FROM hmo_investigation_tariff 
                    INNER JOIN lab_scan ON hmo_investigation_tariff.stock_sn = lab_scan.sn 
                    INNER JOIN insurance_tbl ON insurance_tbl.insurance_no = hmo_investigation_tariff.hmo
                    WHERE hmo_investigation_tariff.hmo = :download_hmo AND lab_scan.sn = :stock_sn";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':download_hmo', $download_hmo);
            $stmt->bindParam(':stock_sn', $stock_sn);
            $stmt->execute();
            $hmoRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($hmoRow) {
                // Use HMO price if available
                fputcsv($output, [$hmoRow['the_sn'], $hmoRow['stock_sn'], $hmoRow['hmo'], $hmoRow['test'], $hmoRow['insurance_name'], $hmoRow['hmo_price'], 'NO']);
            } else {
                // Use generic price from lab_scan
                fputcsv($output, ['', $stock_sn, $download_hmo, $labScanItem['test'], $insurance_name, $labScanItem['hosp_price'], 'NO']);
            }
        }

        fclose($output);
        exit();
    } else {

        $fileName = "full-stock-data-" . $typeoftable . "_" . date('Y-m-d') . ".csv";

        $sql = "SELECT ptbl.*, dept.department 
                FROM lab_scan as ptbl 
                INNER JOIN department as dept ON dept.sn = ptbl.dept $search_string
                ORDER BY ptbl.sn";

        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');

        $header = ["sn", "test", "category", "dept", "hosp_price", "ext_price", "nhis_price", "dept_name", "status"];
        fputcsv($output, $header);

        foreach ($rows as $item) {
            $filteredItem = [];
            foreach ($header as $key) {
                if (isset($item[$key])) {
                    $filteredItem[$key] = $item[$key];
                } else {
                    $filteredItem[$key] = '0';
                }
            }
            fputcsv($output, $filteredItem);
        }

        fclose($output);
        exit();
    }
}
