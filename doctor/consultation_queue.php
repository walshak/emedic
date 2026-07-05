<?php
require_once('../Connections/Conn.php');

session_start();


if (isset($_POST['check_on_going'])) {

    // Force Africa/Lagos timezone
    $tz = new DateTimeZone('Africa/Lagos');

    $hosp_no   = $_POST['hospital_no'];
    $doctor_no = $_POST['doctor_no'];

    header('Content-Type: application/json; charset=utf-8');

    // Get latest on-going consultation
    $stmt = $db->prepare("
        SELECT *
        FROM consultations
        WHERE status = 'On-going'
          AND hosp_no = ?
        ORDER BY start_time DESC
        LIMIT 1
    ");
    $stmt->execute([$hosp_no]);

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "status"     => 0,
            "start_time" => ""
        ]);
        exit;
    }

    $existing   = $stmt->fetch(PDO::FETCH_ASSOC);
    $start_time = $existing['start_time']; // YYYY-MM-DD HH:MM:SS

    // Create DateTime objects in Lagos timezone
    $now     = new DateTime('now', $tz);
    $startDT = new DateTime($start_time, $tz);

    // Difference in seconds (NO abs — future times are ignored)
    $diffSeconds = $now->getTimestamp() - $startDT->getTimestamp();

    // 🔥 RESET IF EXCEEDS 1 HOUR 🔥
    if ($diffSeconds > 3600) {

        // Reset start time to now
        $startDT = clone $now;

        $updateStmt = $db->prepare("
            UPDATE consultations
            SET start_time = ?
            WHERE doctor_id = ?
              AND hosp_no = ?
              AND status = 'On-going'
        ");

        $updateStmt->execute([
            $startDT->format('Y-m-d H:i:s'),
            $doctor_no,
            $hosp_no
        ]);
    }

    // Return ISO 8601 for JS (includes +01:00)
    echo json_encode([
        "status"     => 1,
        "start_time" => $startDT->format(DateTime::ATOM)
    ]);

    exit;
}



if (isset($_POST['mgt_tab'])) {

    $hosp_no      = $_POST['hospital_no'];
    $patient_name = $_POST['patient_name'];
    $doctor_no    = $_POST['doctor_no'];
    $doctor_name  = $_POST['doctor_name'];
    $room = isset($_SESSION['Consult_Room']) ? $_SESSION['Consult_Room'] : 'N/A';


    try {
        // Start Transaction
        $db->beginTransaction();

        // Check if doctor already has an ongoing consultation
        $check = $db->prepare("
                SELECT id, hosp_no 
                FROM consultations 
                WHERE doctor_id = ? 
                AND status = 'On-going'
                LIMIT 1
            ");
        $check->execute([$doctor_no]);

        if ($check->rowCount() > 0) {
            $existing = $check->fetch(PDO::FETCH_ASSOC);

            if ($existing['hosp_no'] != $hosp_no) {
                // Close the existing ongoing consultation
                $stmt = $db->prepare("
                        UPDATE consultations
                        SET end_time = NOW(),
                            duration = TIMESTAMPDIFF(MINUTE, start_time, NOW()),
                            status = 'Closed'
                        WHERE doctor_id = ?
                        AND hosp_no = ?
                        AND status = 'On-going'
                    ");
                $stmt->execute([$doctor_no, $existing['hosp_no']]);
            }
        }

        // Check if current patient already has an ongoing consultation with this doctor
        $check = $db->prepare("
                SELECT id 
                FROM consultations 
                WHERE hosp_no = ? 
                AND doctor_id = ? 
                AND status = 'On-going'
                LIMIT 1
            ");
        $check->execute([$hosp_no, $doctor_no]);

        if ($check->rowCount() == 0) {
            // Insert new consultation
            $stmt = $db->prepare("
                    INSERT INTO consultations 
                    (hosp_no, patient_name, doctor_id, doctor_name, start_time, status, room) 
                    VALUES (?, ?, ?, ?, NOW(), 'On-going', ?)
                ");

            $stmt->execute([$hosp_no, $patient_name, $doctor_no, $doctor_name, $room]);
        }

        // Commit transaction
        $db->commit();

        echo json_encode([
            "status"  => 1,
            "message" => "Consultation started successfully.",
            "d_status" => ($check->rowCount() == 0 ? "created" : "existing")
        ]);
    } catch (Exception $e) {
        $db->rollBack();

        echo json_encode([
            "status" => 0,
            "error" => $e->getMessage(),
            "line" => $e->getLine()
        ]);
        exit;
    }
}
