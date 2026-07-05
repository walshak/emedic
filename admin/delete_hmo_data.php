<?php
include("../Connections/Conn.php");

if (!empty($_POST['insurance_no']) && !empty($_POST['table_type'])) {
    $insurance_no = $_POST['insurance_no'];
    $tableType = $_POST['table_type'];

    // Map dropdown values to actual table names
    $tableMap = array(
        'Pharmacy'            => 'hmo_stocks_tariff',
        'Store'               => 'hmo_stocks_tariff',
        'Nursing Consumable'  => 'hmo_stocks_tariff',
        'Bed'                 => 'hmo_bed_tariff',
        'Consultation'        => 'hmo_medical_tariff',
        'Medical Services'    => 'hmo_medical_tariff',
        'Nursing Services'    => 'hmo_medical_tariff',
        'Investigations'      => 'hmo_investigation_tariff',
        'Other Services'      => 'hmo_medical_tariff' // Assuming it’s medical tariff
    );

    if (!isset($tableMap[$tableType])) {
        exit("Invalid table type selected.");
    }

    $tableName = $tableMap[$tableType];

    try {
        $stmt = $db->prepare("DELETE FROM $tableName WHERE hmo = ?");
        $stmt->execute([$insurance_no]);

        if ($stmt->rowCount() > 0) {
            echo "✅ Successfully deleted all tariff records for HMO [$insurance_no] in [$tableType].";
        } else {
            echo "⚠️ No records found for this HMO in the selected table for HMO [$insurance_no] in [$tableType].";
        }
    } catch (Exception $e) {
        echo "❌ Error deleting records: " . $e->getMessage();
    }
} else {
    echo "Please select both an HMO and a Table.";
}
