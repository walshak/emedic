<?php
include("../../Connections/Conn.php");

header('Content-Type: application/json');

function getDistinct($db, $sql)
{
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['val'])) {
            $data[] = $row['val'];
        }
    }
    return $data;
}

echo json_encode([
    "insurance_type" => getDistinct($db, "SELECT DISTINCT insurance_type AS val FROM insurance_tbl"),
    "insurance_name" => getDistinct($db, "SELECT DISTINCT insurance_name AS val FROM insurance_tbl"),
    "prepared_by" => getDistinct($db, "SELECT DISTINCT prepared_by AS val FROM patient_ap_services"),
    "dsp_by" => getDistinct($db, "SELECT DISTINCT dsp_by AS val FROM patient_ap_services"),
    "item_services" => getDistinct($db, "SELECT DISTINCT item_services AS val FROM patient_ap_services"),
    "pay_mode" => getDistinct($db, "SELECT DISTINCT pay_mode AS val FROM patient_ap_services"),
    "category" => getDistinct($db, "SELECT DISTINCT category AS val FROM stock_table"),
    "generic_name" => getDistinct($db, "SELECT DISTINCT generic_name AS val FROM stock_table")
]);
