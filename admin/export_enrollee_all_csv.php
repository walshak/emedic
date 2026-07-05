<?php
require_once("../Connections/Conn.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cols = isset($_POST['cols']) ? $_POST['cols'] : [];

    if (!$cols) {
        exit('Invalid request');
    }

    // Build SQL select fields with hospital_no as string
    $fieldMap = [
        'name' => "CONCAT(e.surname, ' ', e.fname, ' ', e.oname) AS name",
        'hospital_no' => "CONCAT('', e.hospital_no) AS hospital_no", // Ensures it's treated as string
        'email' => "e.email",
        'phone' => "e.phone",
        'dob' => "e.dob",
        'address' => "e.addr",
        'gender' => "e.gender",
        'blood_group' => "e.blood_g",
        'genotype' => "e.geno_type",
        'hmo_no' => "e.hmo_no",
        'insurance' => "e.insurance",
    ];

    $selectFields = [];
    foreach ($cols as $col) {
        if (isset($fieldMap[$col])) {
            $selectFields[] = $fieldMap[$col];
        }
    }

    if (empty($selectFields)) {
        exit('No valid columns selected');
    }

    $select = implode(", ", $selectFields);

    // Query
    $sql = "SELECT $select
        FROM enrollee e
        ORDER BY e.sn ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    // Output CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="enrollee_list_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');

    // Output column headers
    fputcsv($out, $cols);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $csvRow = [];
        foreach ($cols as $col) {
            $value = $row[$col];

            // Ensure hospital_no stays as string with leading zeros
            if ($col === 'hospital_no') {
                $value = '# ' . (string)$value;
            }

            $csvRow[] = $value;
        }
        fputcsv($out, $csvRow);
    }

    fclose($out);
    exit;
}
