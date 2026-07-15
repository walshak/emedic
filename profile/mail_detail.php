<?php
include("../inc/session.php");
include("../Connections/Conn.php");

if (isset($_GET['m'])) {
    $sn = intval($_GET['m']);
    
    // Find the thread_id for this message
    $stmt = $db->prepare("SELECT thread_id FROM mails WHERE sn = :sn");
    $stmt->execute([':sn' => $sn]);
    $mail = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($mail && $mail['thread_id']) {
        header("Location: mail_thread_view.php?t=" . $mail['thread_id']);
        exit;
    }
}

// Fallback to inbox if message not found or no thread_id
header("Location: mailbox.php");
exit;
?>