<?php
session_start();
// Database configuration 
require_once('../Connections/Conn.php');

if (isset($_POST['download_data_button'])) {

    $download_type = $_POST['download_type'];
    $typeoftable = $_POST['typeoftable'];

    // CSV file name for download
    if ($download_type == 'hmo') {
        // HMO / CORPORATE
        $download_hmo = $_POST['download_hmo'];
        $pp = explode("__", $download_hmo);
        $download_hmo = $pp[0];
        $insurance_name = $pp[1];

        $fileName = "hmo-bed-data-" . $typeoftable . "(" . $download_hmo . ")_" . date('Y-m-d') . ".csv";

        // SQL Query to fetch all items from bed_mgt
        $sql = "SELECT 
                    bed_mgt.sn AS stock_sn,
                    bed_mgt.room_name,
                    bed_mgt.hosp_price
                FROM bed_mgt";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $bedMgtRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare CSV file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');

        // Define the CSV header
        $header = ["sn", "stock_sn", "hmo", "item", "insurance_name", "price", "delete(YES or NO)"];
        fputcsv($output, $header);

        foreach ($bedMgtRows as $bedMgtItem) {
            $stock_sn = $bedMgtItem['stock_sn'];

            // Check for HMO price for each item
            $sql = "SELECT 
                        hmo_bed_tariff.sn AS the_sn,
                        hmo_bed_tariff.stock_sn,
                        hmo_bed_tariff.hmo,
                        hmo_bed_tariff.price AS hmo_price,
                        bed_mgt.room_name,
                        insurance_tbl.insurance_name
                    FROM hmo_bed_tariff 
                    INNER JOIN bed_mgt ON hmo_bed_tariff.stock_sn = bed_mgt.sn 
                    INNER JOIN insurance_tbl ON insurance_tbl.insurance_no = hmo_bed_tariff.hmo
                    WHERE hmo_bed_tariff.hmo = :download_hmo AND bed_mgt.sn = :stock_sn";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':download_hmo', $download_hmo);
            $stmt->bindParam(':stock_sn', $stock_sn);
            $stmt->execute();
            $hmoRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($hmoRow) {
                // Use HMO price if available
                fputcsv($output, [$hmoRow['the_sn'], $hmoRow['stock_sn'], $hmoRow['hmo'], $hmoRow['room_name'], $hmoRow['insurance_name'], $hmoRow['hmo_price'], 'NO']);
            } else {
                // Use generic price from bed_mgt
                fputcsv($output, ['', $stock_sn, $download_hmo, $bedMgtItem['room_name'], $insurance_name, $bedMgtItem['hosp_price'], 'NO']);
            }
        }

        fclose($output);
        exit();
    } else {

        $fileName = "full-stock-data-" . $typeoftable . "_" . date('Y-m-d') . ".csv";

        $sql = "SELECT * FROM bed_mgt";
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        $output = fopen('php://output', 'w');
        $header = ["sn", "room_name", "bed_no", "nhis_price", "hosp_price", "ext_price"];
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
