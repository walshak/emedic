<?php
include("Connections/Conn.php");
session_start();

if (isset($_GET['query'])) {
    header('Content-Type: application/json');

    try {
        // Check if the query parameter exists
        if (isset($_GET['query'])) {
            $query = $_GET['query'];

            // Prepare and execute the query
            $stmt = $db->prepare(
                "SELECT cust_name, transc_code, gender, dob
             FROM pharm_ext 
             WHERE cust_name LIKE ? 
                OR phone LIKE ?
                OR transc_code LIKE ?
             LIMIT 5"
            );
            $stmt->execute(['%' . $query . '%', '%' . $query . '%', '%' . $query . '%']);

            $patients = $stmt->fetchAll();

            // Return the results
            if ($patients) {
                echo json_encode(["status" => "success", "data" => $patients]);
            } else {
                echo json_encode(["status" => "success", "data" => []]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Query parameter missing"]);
        }
    } catch (PDOException $e) {
        // Handle database connection or query errors
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}


// Handle POST requests to get notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['get_notes'])) {
    $hospital_no = $_POST['hospital_no'];
    $appointment_no = $_POST['appointment_no'];

    if ($hospital_no && $appointment_no) {
        $stmt = $db->prepare("SELECT * FROM notes WHERE hospital_no = ? AND app_no = ? AND status = '1' ORDER BY date_entry DESC LIMIT 50");
        $stmt->execute([$hospital_no, $appointment_no]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $notes]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
    }
    exit;
}

// Handle GET requests to get pharmacy drugs
if (isset($_GET['get_pharmacy_drugs'])) {
    $hospital_no = $_GET['hospital_no'];
    $appointment_no = $_GET['appointment_no'];

    if ($hospital_no && $appointment_no) {
        $stmt = $db->prepare("SELECT * FROM patient_ap_services 
                              WHERE hospital_no = ? AND serv_group IN ('Pharmacy') ORDER BY date_entry DESC LIMIT 50");
        $stmt->execute([$hospital_no,]);
        $drugs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($drugs);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
    }
    exit;
}

// Handle GET requests to get lab results
if (isset($_GET['get_lab_results'])) {
    $hospital_no = $_GET['hospital_no'];
    $appointment_no = $_GET['appointment_no'];

    if ($hospital_no && $appointment_no) {
        $stmt = $db->prepare("
            SELECT lr.test_name, lr.field_value, lr.specimen_collected, lr.notes, lr.result_date, lr.lab_sci_name, pas.*
            FROM patient_ap_services pas
            LEFT JOIN lab_result lr ON pas.drug_sn = lr.lab_no
            WHERE pas.hospital_no = ? AND pas.serv_group IN ('Laboratory', 'Radiology', 'External Services') ORDER BY pas.date_entry DESC LIMIT 50
        ");
        $stmt->execute([$hospital_no,]);
        $lab_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($lab_results);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
    }
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['post_notes'])) {
    $mode = $_POST['mode'];
    $review_note = htmlspecialchars(trim($_POST['review_note']));
    $hospital_no = htmlspecialchars(trim($_POST['hospital_no']));
    $appointment_no = htmlspecialchars(trim($_POST['appointment_number']));
    $dept_id = htmlspecialchars(trim($_POST['dept_id']));
    $created_by = $_SESSION['fullname'];
    $prepared_by = $_SESSION['fullname'];
    $date_entry = date('Y-m-d H:i:s');

    if ($mode === 'new') {
        $stmt = $db->prepare("INSERT INTO notes (app_no, hospital_no, notes, tag, notes_type, prepared_by, created_by, date_entry, status) 
                              VALUES (?, ?, ?, 'PH', 'Pharm', ?, ?, ?, '1')");
        $success = $stmt->execute([$appointment_no, $hospital_no, $review_note, $prepared_by, $created_by, $date_entry]);
    } else {
        $sn = $_POST['notes_sn'];
        $stmt = $db->prepare("UPDATE notes SET notes = ? WHERE sn = ?");
        $success = $stmt->execute([$review_note, $sn]);
    }

    echo json_encode(['status' => $success ? 1 : 0]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['delete_post'])) {
    $sn = $_POST['delete_notes'];

    $stmt = $db->prepare("UPDATE notes SET status = '0' WHERE sn = ?");
    $success = $stmt->execute([$sn]);

    echo json_encode(['status' => $success ? '0' : '1']);
    exit;
}
