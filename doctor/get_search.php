<?php
session_start();

header('Content-Type: application/json');
include("../Connections/Conn.php");

// Determine mode: external, internal, or all
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$hosp_number = isset($_POST['hosp_number']) ? trim($_POST['hosp_number']) : '';

// Return early if no valid search
if (strlen($search) < 2 || empty($mode)) {
    echo json_encode([]);
    exit;
}

$results = array();
$term = '%' . $search . '%';

try {
    if ($mode === 'doctor_names') {
        $query = "
            SELECT distinct prepared_by, created_by
            FROM notes
            WHERE hospital_no LIKE :term AND prepared_by LIKE :term2
            ORDER BY prepared_by
            LIMIT 50
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':term', $hosp_number, PDO::PARAM_STR);
        $stmt->bindParam(':term2', $term, PDO::PARAM_STR);
    } elseif ($mode === 'search_anything') {
        $query = "
            SELECT notes,sn,date_entry,prepared_by
            FROM notes
            WHERE hospital_no LIKE :hospital_no AND notes LIKE :term2 AND status=1
            ORDER BY notes_type
            LIMIT 50
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hospital_no', $hosp_number, PDO::PARAM_STR);
        $stmt->bindParam(':term2', $term, PDO::PARAM_STR);
    } elseif ($mode === 'all_patients') {

        $stmt = $db->prepare("SELECT distinct hospital_no,patient_name from dialysis order by patient_name");
        $query = "
            SELECT DISTINCT hospital_no,patient_name
            FROM dialysis ORDER BY patient_name
            LIMIT 50
        ";
        $stmt = $db->prepare($query);
    } elseif ($mode === 'all_app_service') {

        if ($_SESSION['dispensory'] == 1) {
            $dept_id = $_SESSION['dept_id'];
            $x_search = " AND dept=:dept_id";
        } else {
            $x_search = "";
        }

        $query = "
        SELECT item_service, sn, hosp_price 
        FROM prices_table  
        WHERE price_table='Medical Services' 
        AND category != 'dialysis'
        AND item_service LIKE :term
        $x_search
        ORDER BY item_service
        LIMIT 50    ";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':term', $term, PDO::PARAM_STR);

        if ($_SESSION['dispensory'] == 1) {
            $stmt->bindParam(':dept_id', $dept_id, PDO::PARAM_STR);
        }
    } else {
        echo json_encode([]);
        exit;
    }

    $stmt->execute();


    if ($mode === 'doctor_names') {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = array(
                'id'   => $row['created_by'],
                'text' => $row['prepared_by']
            );
        }
    }

    if ($mode === 'search_anything') {
        $count = $stmt->rowCount();
        $results[] = array(
            'id'   => $term,
            'text' => 'Search found (' . $count . ') '  . $search . ' => Click to View'
        );
    }

    if ($mode === 'all_patients') {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = array(
                'id'   => $row['hospital_no'],
                'text' => $row['hospital_no'] . ' : ' . $row['patient_name']
            );
        }
    }

    /*     if ($mode === 'all_app_service') {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = array(
                'id'   => $row["sn"] . '||' . $row["item_service"],
                'text' => $row["item_service"] . ' [ ' . number_format($row["hosp_price"]) . ' ]'
            );
        }
    } */


    if ($mode === 'all_app_service') {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $text = $row["item_service"];

            // Show price only for allowed hospitals
            if ($show_ext_price) {
                $text .= ' [ ' . number_format($row["hosp_price"]) . ' ]';
            }

            $results[] = array(
                'id'   => $row["sn"] . '||' . $row["item_service"],
                'text' => $text
            );
        }
    }






    echo json_encode($results);
} catch (PDOException $e) {
    // Log or handle errors as needed
    // error_log($e->getMessage());
    echo json_encode([]);
}
