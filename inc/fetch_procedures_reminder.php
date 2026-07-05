<?php
session_start();
require_once('../Connections/Conn.php');
$consultant_id = $_SESSION['id'];
$what_to = 'procedure';

if (isset($_POST['delete_acknowledge_for_frontdesk'])) {
    $sql = "DELETE FROM acknowledge WHERE staff_id = :staff_id AND what_to = :what_to";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':staff_id' => $consultant_id,
        ':what_to' => $what_to
    ]);
    exit;
}


$sql = "SELECT * FROM acknowledge WHERE staff_id = :staff_id AND what_to = :what_to AND DATE(date_done) = CURDATE()";
$stmt = $db->prepare($sql);
$stmt->execute([
    ':staff_id' => $consultant_id,
    ':what_to' => $what_to
]);

if ($stmt->rowCount() > 0) {
} else {


    $query = "SELECT consultant_id 
          FROM procedures AS p 
          INNER JOIN procedure_resources AS r 
          ON r.prdure_sn = p.sn
          WHERE resource_sn = :resource_sn 
          AND sDate BETWEEN NOW() AND NOW() + INTERVAL 5 DAY";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':resource_sn', $consultant_id, PDO::PARAM_INT); // Assuming $resource_sn is defined
    $stmt->execute();


    if ($stmt->rowCount() > 0) {
        $consultants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $consultant_id =  $consultants[0]['consultant_id']; // Accessing the first result
    } else {
        $consultant_id = $_SESSION['id']; // Ensure this is set and valid
    }

    $rights = $_SESSION['rights'];
    if ($rights == 'NS' or $rights == 'DR') {
        $A = "p.consultant_id = '$consultant_id'";
        $query = "
    SELECT 
        p.hospital_no, 
        p.name, 
        p.procedures, 
        p.consultant_name, 
        p.sDate,
        p.sn,
        GROUP_CONCAT(r.name SEPARATOR ', ') AS resource_persons,
        GROUP_CONCAT(r.role SEPARATOR ', ') AS roles
    FROM 
        procedures AS p
    LEFT JOIN 
        procedure_resources AS r ON r.prdure_sn = p.sn
    WHERE 
        p.consultant_id = '$consultant_id'  
        AND p.sDate BETWEEN NOW() AND NOW() + INTERVAL 5 DAY
    GROUP BY 
        p.sn
";
    } elseif (in_array($rights, ['MD', 'GM', 'RE', 'AC', 'PH'])) {
        $query = "
        SELECT 
            p.hospital_no, 
            p.name, 
            p.procedures, 
            p.consultant_name, 
            p.sDate,
            p.sn,
            GROUP_CONCAT(r.name SEPARATOR ', ') AS resource_persons,
            GROUP_CONCAT(r.role SEPARATOR ', ') AS roles
        FROM 
            procedures AS p
        LEFT JOIN 
            procedure_resources AS r ON r.prdure_sn = p.sn
        WHERE 
            p.sDate BETWEEN NOW() AND NOW() + INTERVAL 500 DAY
        GROUP BY 
            p.sn
    ";
    }

    $stmt = $db->prepare($query);
    /// $stmt->bindParam(':consultant_id', $consultant_id, PDO::PARAM_INT);
    $stmt->execute();
    $procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the row count
    $rowCount = $stmt->rowCount();


    $stmt->closeCursor(); // For PDO, use closeCursor instead of close
    $db = null; // Close the PDO connection

    // Prepare the response data
    $response = [
        'rowCount' => $rowCount,
        'procedures' => $procedures
    ];

    // Return the results as JSON
    echo json_encode($response);
}
