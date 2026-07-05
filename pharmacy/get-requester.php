<?php

require_once('../Connections/Conn.php');

if (isset($_POST['requester_post'])) {
    $back_date = date('Y-m-d', strtotime('-1 day'));
    $to_date = date('Y-m-d');

    $stmt = $db->prepare("
        SELECT DISTINCT p.prepared_by, p.hospital_no, e.surname, e.fname 
        FROM patient_ap_services AS p
        INNER JOIN enrollee AS e ON e.hospital_no = p.hospital_no
        WHERE p.serv_group = 'Pharmacy' 
          AND p.invoice_status = 0 
          AND DATE(p.date_entry) BETWEEN :back_date AND :to_date
        ORDER BY p.prepared_by, p.date_entry DESC
    ");

    $stmt->execute([
        ':back_date' => $back_date,
        ':to_date' => $to_date
    ]);

    echo "<option value=''>-- Select --</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $encoded_val = base64_encode($row['hospital_no'] . '__' . $row['prepared_by']);
        $full_name = htmlspecialchars($row['fname'] . ' ' . $row['surname']);
        $prepared_by = htmlspecialchars($row['prepared_by']);
        echo "<option value='$encoded_val'>$prepared_by ($full_name)</option>";
    }
}
?>

?>