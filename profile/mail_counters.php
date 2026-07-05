<?php
// Calculate unread counts for mail sidebar
$username = $_SESSION['username'];

// Count unread inbox messages (messages in threads where user is recipient with unread status)
$stmt_inbox_count = $db->prepare("
    SELECT COUNT(DISTINCT mr.thread_id) as unread_threads
    FROM mail_recipients mr
    WHERE mr.recipient_username = :username
    AND mr.read_status = '0'
    AND mr.deleted_status = '0'
");
$stmt_inbox_count->execute([':username' => $username]);
$inbox_result = $stmt_inbox_count->fetch(PDO::FETCH_ASSOC);
$inbox = $inbox_result['unread_threads'];

// Count draft messages
$stmt_draft_count = $db->prepare("
    SELECT COUNT(*) as draft_count
    FROM mails
    WHERE from_username = :username
    AND mail_status = 'draft'
");
$stmt_draft_count->execute([':username' => $username]);
$draft_result = $stmt_draft_count->fetch(PDO::FETCH_ASSOC);
$count_draft = $draft_result['draft_count'];
?>
