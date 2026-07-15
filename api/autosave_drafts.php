<?php
require_once('../Connections/Conn.php');

header('Content-Type: application/json');

// Helper to generate title
function generateDraftTitle($content) {
    $clean_content = strip_tags($content);
    $words = str_word_count($clean_content, 1);
    $snippet = implode(' ', array_slice($words, 0, 5));
    if (empty(trim($snippet))) {
        $snippet = "Untitled Draft";
    }
    $timestamp = date('M d, g:i A');
    return $snippet . ' - ' . $timestamp;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        $hospital_no = $_POST['hospital_no'] ?? '';
        $app_no = $_POST['app_no'] ?? '';
        $doctor = $_POST['doctor'] ?? '';
        $content = $_POST['content'] ?? '';
        $draft_id = $_POST['draft_id'] ?? ''; // Optional, if we want to overwrite a specific restored draft
        
        $title = generateDraftTitle($content);
        $saved_at = date('Y-m-d H:i:s');
        
        // If draft_id is provided, try to update it directly
        if (!empty($draft_id)) {
            $stmt = $db->prepare("UPDATE autosave SET content = ?, title = ?, saved_at = ? WHERE id = ? AND hospital_no = ? AND doctor = ?");
            $stmt->execute([$content, $title, $saved_at, $draft_id, $hospital_no, $doctor]);
            
            // Enforce max 10 drafts per patient + doctor
            $stmt_check = $db->prepare("SELECT id FROM autosave WHERE hospital_no = ? AND doctor = ? ORDER BY saved_at DESC LIMIT 10, 100");
            $stmt_check->execute([$hospital_no, $doctor]);
            while($row = $stmt_check->fetch(PDO::FETCH_ASSOC)) {
                $db->prepare("DELETE FROM autosave WHERE id = ?")->execute([$row['id']]);
            }
            
            echo json_encode(['status' => 'success', 'draft_id' => $draft_id]);
            exit;
        }

        // Insert new draft if no draft_id was provided
        $insert = $db->prepare("INSERT INTO autosave (hospital_no, app_no, doctor, title, content, saved_at) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->execute([$hospital_no, $app_no, $doctor, $title, $content, $saved_at]);
        $new_id = $db->lastInsertId();
        // Enforce max 10 drafts per patient + doctor
        $stmt_check = $db->prepare("SELECT id FROM autosave WHERE hospital_no = ? AND doctor = ? ORDER BY saved_at DESC LIMIT 10, 100");
        $stmt_check->execute([$hospital_no, $doctor]);
        while($row = $stmt_check->fetch(PDO::FETCH_ASSOC)) {
            $db->prepare("DELETE FROM autosave WHERE id = ?")->execute([$row['id']]);
        }
        
        echo json_encode(['status' => 'success', 'draft_id' => $new_id]);
        exit;
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $db->prepare("DELETE FROM autosave WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($action === 'cleanup') {
        $hospital_no = $_POST['hospital_no'] ?? '';
        $app_no = $_POST['app_no'] ?? '';
        $doctor = $_POST['doctor'] ?? '';
        $draft_id = $_POST['draft_id'] ?? '';
        
        // Delete current app_no draft
        if (!empty($app_no)) {
            $stmt = $db->prepare("DELETE FROM autosave WHERE hospital_no = ? AND app_no = ? AND doctor = ?");
            $stmt->execute([$hospital_no, $app_no, $doctor]);
        }
        // If a specific draft was restored and edited, delete it too
        if (!empty($draft_id)) {
            $stmt2 = $db->prepare("DELETE FROM autosave WHERE id = ? AND hospital_no = ? AND doctor = ?");
            $stmt2->execute([$draft_id, $hospital_no, $doctor]);
        }
        
        echo json_encode(['status' => 'success']);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'list') {
        $hospital_no = $_GET['hospital_no'] ?? '';
        $doctor = $_GET['doctor'] ?? '';
        
        $stmt = $db->prepare("SELECT id, app_no, title, saved_at FROM autosave WHERE hospital_no = ? AND doctor = ? ORDER BY saved_at DESC");
        $stmt->execute([$hospital_no, $doctor]);
        $drafts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $drafts]);
        exit;
    }
    
    if ($action === 'get') {
        $id = $_GET['id'] ?? '';
        $doctor = $_GET['doctor'] ?? '';
        $stmt = $db->prepare("SELECT id, content FROM autosave WHERE id = ? AND doctor = ?");
        $stmt->execute([$id, $doctor]);
        $draft = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $draft]);
        exit;
    }
}
