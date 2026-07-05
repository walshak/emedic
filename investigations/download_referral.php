<?php

session_start();
include("../Connections/Conn.php");
if (isset($_GET['all'])) {


    try {

        $stmt = $db->query("SELECT `sn`, `transc_code`, `cust_name`, `description`, `gender`, `dob`, `phone`, `address`, `email_address`, `referral`, `total_bill`, `amt_paid`, `bal`, `date_ap`, `status`, `captured_by`, `discount_set` FROM `pharm_ext`");

        // set headers for download
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=pharm_ext_" . date("Y-m-d") . ".csv");

        $output = fopen("php://output", "w");

        // fetch header
        $firstRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($firstRow) {
            fputcsv($output, array_keys($firstRow)); // column headers
            fputcsv($output, $firstRow);             // first row data

            // rest of rows
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} elseif (isset($_GET['id'])) {


    $id = intval($_GET['id']);
    $stmt = $db->prepare("SELECT * FROM pharm_ext WHERE referral = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=referral_{$id}.csv");

        $output = fopen("php://output", "w");
        fputcsv($output, array_keys($row)); // header row
        fputcsv($output, $row);
        fclose($output);
        exit;
    } else {
        echo "Referral not found.";
    }
}
