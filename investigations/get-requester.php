<?php

require_once('../Connections/Conn.php');

if (isset($_POST['requester_post'])) {
    // Ensure proper indexing on patient and hospital_no columns
    $stmt = $db->query("
        SELECT DISTINCT l.patient, l.patient_name, e.vip 
        FROM lab_manage l 
        LEFT JOIN enrollee e ON l.patient = e.hospital_no 
        ORDER BY l.patient
    ");

    if ($stmt->rowCount() > 0) {
        echo "<option value=''>--Search and Select Patient--</option>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $name = ($row["vip"] == 1) ? 'VIP: ' . $row["patient"] : $row["patient"] . ' : ' . $row["patient_name"];
?>
            <option value="<?php echo htmlspecialchars($row['patient']); ?>"><?php echo htmlspecialchars($name); ?></option>
<?php
        }
    }
}


?>