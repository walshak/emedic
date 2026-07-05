<?php
include("../Connections/Conn.php");

$conditions = [];

// Build the search conditions based on selected values
if (!empty($_POST['serv_group'])) {
    $conditions[] = "serv_group = :serv_group";
}
if (!empty($_POST['cat_type'])) {
    $conditions[] = "cat_type = :cat_type";
}
if (!empty($_POST['prepared_by'])) {
    $conditions[] = "prepared_by = :prepared_by";
}
if (!empty($_POST['invoice_by'])) {
    $conditions[] = "invoice_by = :invoice_by";
}
if (!empty($_POST['dept_id'])) {
    $conditions[] = "dept_id = :dept_id";
}
if (!empty($_POST['hospital_no'])) {
    $conditions[] = "hospital_no = :hospital_no";
}
if (!empty($_POST['item_services'])) {
    $conditions[] = "item_services = :item_services";
}
if (!empty($_POST['created_by'])) {
    $conditions[] = "created_by = :created_by";
}
if (!empty($_POST['dsp_by'])) {
    $conditions[] = "dsp_by = :dsp_by";
}
if (!empty($_POST['pay_mode'])) {
    $conditions[] = "pay_mode = :pay_mode";
}

// Construct the SQL query
$sql = "SELECT * FROM `patient_ap_services`";
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

// Prepare the statement
$stmt = $db->prepare($sql);

// Bind parameters
if (!empty($_POST['serv_group'])) {
    $stmt->bindValue(':serv_group', $_POST['serv_group']);
}
if (!empty($_POST['cat_type'])) {
    $stmt->bindValue(':cat_type', $_POST['cat_type']);
}
if (!empty($_POST['prepared_by'])) {
    $stmt->bindValue(':prepared_by', $_POST['prepared_by']);
}
if (!empty($_POST['invoice_by'])) {
    $stmt->bindValue(':invoice_by', $_POST['invoice_by']);
}
if (!empty($_POST['dept_id'])) {
    $stmt->bindValue(':dept_id', $_POST['dept_id']);
}
if (!empty($_POST['hospital_no'])) {
    $stmt->bindValue(':hospital_no', $_POST['hospital_no']);
}
if (!empty($_POST['item_services'])) {
    $stmt->bindValue(':item_services', $_POST['item_services']);
}
if (!empty($_POST['created_by'])) {
    $stmt->bindValue(':created_by', $_POST['created_by']);
}
if (!empty($_POST['dsp_by'])) {
    $stmt->bindValue(':dsp_by', $_POST['dsp_by']);
}
if (!empty($_POST['pay_mode'])) {
    $stmt->bindValue(':pay_mode', $_POST['pay_mode']);
}

// Execute the query
$stmt->execute();

// Fetch results and process for CSV download
if (isset($_POST['download'])) {

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="search_results.csv"');
    $output = fopen('php://output', 'w');

    // Fetch and write column headers
    $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($headers)) {
        fputcsv($output, array_keys($headers[0])); // Write headers
    }

    // Fetch and write each row
    foreach ($headers as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

// Close connection (optional, as PHP will close it automatically at the end of the script)
$db = null;
