<?php include("../Connections/Conn.php");


if (isset($_POST["field_no_del"])) {
    // Delete from lab_scan_fields
    $stmt = $db->prepare("DELETE FROM lab_scan_fields WHERE sn = :field_no_del");
    $stmt->bindParam(':field_no_del', $_POST["field_no_del"], PDO::PARAM_STR);
    $stmt->execute();

    // Delete from lab_scan_rlts_opt
    $stmt = $db->prepare("DELETE FROM lab_scan_rlts_opt WHERE field_id_no = :field_no_del");
    $stmt->bindParam(':field_no_del', $_POST["field_no_del"], PDO::PARAM_STR);
    $stmt->execute();
}

if (isset($_POST["option_no_del"])) {
    // Delete from lab_scan_rlts_opt
    $stmt = $db->prepare("DELETE FROM lab_scan_rlts_opt WHERE sn = :option_no_del");
    $stmt->bindParam(':option_no_del', $_POST["option_no_del"], PDO::PARAM_STR);
    $stmt->execute();
}

if (isset($_POST["values_no_del"])) {
    // Delete from lab_scan_rlts_values
    $stmt = $db->prepare("DELETE FROM lab_scan_rlts_values WHERE sn = :values_no_del");
    $stmt->bindParam(':values_no_del', $_POST["values_no_del"], PDO::PARAM_STR);
    $stmt->execute();
}

if (isset($_POST["consumable_id_del"])) {
    // Delete from lab_test_consumble
    $stmt = $db->prepare("DELETE FROM lab_test_consumble WHERE sn = :consumable_id_del");
    $stmt->bindParam(':consumable_id_del', $_POST["consumable_id_del"], PDO::PARAM_STR);
    $stmt->execute();
}

if (isset($_POST["combo_no"])) {
    // Delete from lab_combos_items
    $stmt = $db->prepare("DELETE FROM lab_combos_items WHERE sn = :combo_no");
    $stmt->bindParam(':combo_no', $_POST["combo_no"], PDO::PARAM_STR);
    $stmt->execute();
}

if (isset($_POST["del_combs_name_id"])) {
    // Delete from lab_combos_items by sn
    $stmt = $db->prepare("DELETE FROM lab_combos_items WHERE sn = :del_combs_name_id");
    $stmt->bindParam(':del_combs_name_id', $_POST["del_combs_name_id"], PDO::PARAM_STR);
    $stmt->execute();

    // Delete from lab_combos_items by combos_id
    $stmt = $db->prepare("DELETE FROM lab_combos_items WHERE combos_id = :del_combs_name_id");
    $stmt->bindParam(':del_combs_name_id', $_POST["del_combs_name_id"], PDO::PARAM_STR);
    $stmt->execute();

    // Optionally include or require "refresh.php" and perform any necessary additional operations
    include("refresh.php");
    $sub = combos(); // Assuming this function call updates some data
}

if (isset($_POST["assign_no"])) {
    // Delete from invsti_machine_settings
    $stmt = $db->prepare("DELETE FROM invsti_machine_settings WHERE sn = :assign_no");
    $stmt->bindParam(':assign_no', $_POST["assign_no"], PDO::PARAM_STR);
    $stmt->execute();
}

?>
