<?php
header('Content-Type: application/json');
include("../Connections/Conn.php");

// Determine mode: external, internal, or all
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

// Return early if no valid search
if (strlen($search) < 2 || empty($mode)) {
    echo json_encode([]);
    exit;
}

$results = array();
$term = '%' . $search . '%';

try {
    if ($mode === 'external') {
        $query = "
            SELECT transc_code, cust_name, referral
            FROM pharm_ext
            WHERE cust_name LIKE :term OR transc_code LIKE :term2
            ORDER BY transc_code
            LIMIT 50
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':term', $term, PDO::PARAM_STR);
        $stmt->bindParam(':term2', $term, PDO::PARAM_STR);
    } elseif ($mode === 'internal') {
        $query = "
            SELECT hospital_no, surname, fname, oname
            FROM enrollee
            WHERE surname LIKE :term OR fname LIKE :term2 OR hospital_no LIKE :term3
            ORDER BY hospital_no
            LIMIT 50
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':term', $term, PDO::PARAM_STR);
        $stmt->bindParam(':term2', $term, PDO::PARAM_STR);
        $stmt->bindParam(':term3', $term, PDO::PARAM_STR);
    } elseif ($mode === 'all_patients') {
        $query = "
            SELECT DISTINCT patient, patient_name
            FROM lab_manage WHERE patient LIKE :term OR patient_name LIKE :term2
            ORDER BY patient
            LIMIT 20
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':term', $term, PDO::PARAM_STR);
        $stmt->bindParam(':term2', $term, PDO::PARAM_STR);
    } else {
        echo json_encode([]);
        exit;
    }

    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($mode === 'external') {
            $results[] = array(
                'id'   => $row['transc_code'] . '__' . $row['cust_name'] . '__' . $row['referral'],
                'text' => $row['transc_code'] . ' - ' . $row['cust_name']
            );
        } elseif ($mode === 'internal') {
            $results[] = array(
                'id'   => $row['hospital_no'],
                'text' => $row['hospital_no'] . ' - ' . $row['surname'] . ' ' . $row['fname'] . ' ' . $row['oname']
            );
        } elseif ($mode === 'all_patients') {
            $is_vip = isset($row["vip"]) && $row["vip"] == 1;
            $name = $is_vip ? 'VIP: ' . $row["patient"] : $row["patient"] . ' - ' . $row["patient_name"];
            $results[] = array(
                'id'   => $row['patient'],
                'text' => $name
            );
        }
    }

    echo json_encode($results);
} catch (PDOException $e) {
    // Log or handle errors as needed
    // error_log($e->getMessage());
    echo json_encode([]);
}
