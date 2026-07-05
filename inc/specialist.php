<?php session_start();
include('../Connections/Conn.php');

if (isset($_POST['search_specialist'])) {
    header('Content-Type: application/json');

    $input_text = $_POST['input_text'];

    $stmt = $db->query("SELECT fullname AS name, id, username  FROM admin_users WHERE (fullname LIKE '%$input_text%' OR username LIKE '%$input_text%') AND (rights = 'MD' or rights = 'DR') AND status = 1 ");
    $spcialists =  $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($spcialists);
    exit;
}


if (isset($_POST['search_specialist_nurses'])) {
    header('Content-Type: application/json');

    $input_text = $_POST['input_text'];

    $stmt = $db->query("SELECT fullname AS name, id, username  FROM admin_users WHERE (fullname LIKE '%$input_text%' OR username LIKE '%$input_text%') AND rights = 'NS' AND status = 1 ");
    $spcialists =  $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($spcialists);
    exit;
}
