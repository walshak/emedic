<?php
session_start();
include('../../Connections/Conn.php');
include('../objects.php');
include('../helpers.php');

if (isset($_POST['search_icdcodes'])) {
    header('Content-Type: application/json');

    $input_text = trim($_POST['input_text']);
    $group_id   = $_POST['group_id'];

    // Build group filter
    if ($group_id == 1) {
        $group_filter = "AND (group_id = 1 OR group_id = 0)";
    } else {
        $group_filter = "AND group_id = :group_id";
    }

    // SQL query with LIKE and group filter
    $sql = "
        SELECT item AS name
        FROM diagnosis
        WHERE item LIKE :search
        $group_filter
        ORDER BY description DESC
        LIMIT 100
    ";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':search', "%$input_text%");

    if ($group_id != 1) {
        $stmt->bindValue(':group_id', $group_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
    exit;
}
