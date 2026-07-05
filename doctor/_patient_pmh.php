<?php

# Save PMH
if (isset($_POST['savePMH'])) {
    session_start();
    include("../Connections/Conn.php");
    include('helpers.php');
    header('Content-Type: application/json');

    $pmh_sn = intval($_POST['pmh_sn']);
    $diagnosis_id = intval($_POST['diagnosis_id']);
    $diagnosis_text = cleanInput($_POST['diagnosis_text']);
    $notes = cleanInput($_POST['pmh_notes']);
    $hospital_no = cleanInput($_POST['hospital_no']);
    $app_no = cleanInput($_POST['app_no']);
    $now = date('Y-m-d H:i:s');

    // Combine for display, but keep diagnosis_id separate
    $display_text = trim($diagnosis_text . "<br>" . $notes);

    if ($pmh_sn > 0) {
        $stmt = $db->prepare("UPDATE c_d_remarks
SET complain=?, updated_at=?, updated_by=?, diagnosis_id=?
WHERE sn=?");
        $save = $stmt->execute([$display_text, $now, $_SESSION['id'], $diagnosis_id, $pmh_sn]);
    } else {
        $stmt = $db->prepare("INSERT INTO c_d_remarks
(complain, app_no, hospital_no, cat_type, prepared_by, created_by)
VALUES (?, ?, ?, 'pmh', ?, ?)");
        $save = $stmt->execute([$display_text, $app_no, $hospital_no, $_SESSION['fullname'], $_SESSION['id']]);
    }

    if ($save) {
        echo json_encode([
            'message' => "Past Medical History Saved",
            'pmh_html' => '<p>' . $display_text . '</p><br>Captured By: ' . $_SESSION["fullname"] . ' <br>Captured On: ' . date('d M,Y')
        ]);
    } else {
        echo json_encode(['message' => "Error saving"]);
    }
    exit;
}



if (isset($_POST['loadPMH'])) {
    session_start();
    include("../Connections/Conn.php");
    include("helpers.php"); // make sure this file exists

    $hospital_no = cleanInput($_POST['hospital_no']);
    $app_no = cleanInput($_POST['app_no']);

    $stmt = $db->prepare("SELECT * FROM c_d_remarks 
                          WHERE hospital_no=? AND cat_type='pmh' AND status='1' 
                          ORDER BY sn DESC");
    $stmt->execute([$hospital_no]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalRows = count($rows);
    $currentUser = $_SESSION['fullname'];   // adjust to your session variable
    $userRights = $_SESSION['rights'];      // MD, Nurse, etc.

    $html = "<table class='table table-bordered table-sm'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Diagnosis / Notes</th>
                        <th>Captured By</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";

    if ($rows) {
        $i = 1;
        foreach ($rows as $row) {
            // Check if delete is allowed
            $canDelete = false;
            if ($userRights == 'MD') {
                $canDelete = true;
            } elseif ($row['prepared_by'] == $currentUser && $totalRows < 2) {
                $canDelete = true;
            }

            $deleteBtn = $canDelete
                ? "<button class='btn btn-sm btn-danger' onclick='deletePMH({$row['sn']})'>Delete</button>"
                : "<span class='text-muted'>-</span>";

            $html .= "<tr>
                        <td>{$i}</td>
                        <td>" . htmlspecialchars($row['complain']) . "</td>
                        <td>" . htmlspecialchars($row['prepared_by']) . "</td>
                        <td>" . date('d M, Y', strtotime($row['date_entry'])) . "</td>
                        <td>{$deleteBtn}</td>
                      </tr>";
            $i++;
        }
    } else {
        $html .= "<tr><td colspan='5' class='text-center text-muted'>No past medical history found</td></tr>";
    }

    $html .= "</tbody></table>";

    echo $html;
    exit;
}


if (isset($_POST['deletePMH'])) {
    session_start();
    include("../Connections/Conn.php");

    $sn = intval($_POST['sn']);
    $currentUser = $_SESSION['fullname'];
    $userRights = $_SESSION['rights'];

    // Get the row
    $stmt = $db->prepare("SELECT * FROM c_d_remarks WHERE sn=? AND cat_type='pmh' AND status='1'");
    $stmt->execute([$sn]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Count how many PMH entries for this patient
        $countStmt = $db->prepare("SELECT COUNT(*) FROM c_d_remarks WHERE hospital_no=? AND cat_type='pmh' AND status='1'");
        $countStmt->execute([$row['hospital_no']]);
        $totalRows = $countStmt->fetchColumn();

        // Permission check
        if ($userRights == 'MD' || ($row['prepared_by'] == $currentUser && $totalRows < 2)) {
            // Soft delete (update status=0) instead of delete
            $update = $db->prepare("UPDATE c_d_remarks SET status='0' WHERE sn=?");
            $update->execute([$sn]);
            echo "success";
        } else {
            http_response_code(403);
            echo "Not allowed";
        }
    } else {
        http_response_code(404);
        echo "Not found";
    }
    exit;
}
