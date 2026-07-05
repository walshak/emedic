<?php
include("../inc/session.php");
include("../Connections/Conn.php");

$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];

// Get thread ID
$thread_id = isset($_GET['t']) ? intval($_GET['t']) : 0;

if ($thread_id == 0) {
    header("Location: mailbox.php");
    exit;
}

// Get thread info
$stmt_thread = $db->prepare("SELECT * FROM mail_threads WHERE thread_id = :thread_id");
$stmt_thread->execute([':thread_id' => $thread_id]);
$thread = $stmt_thread->fetch(PDO::FETCH_ASSOC);

if (!$thread) {
    header("Location: mailbox.php");
    exit;
}

// Check if user has access to this thread
$stmt_access = $db->prepare("
    SELECT COUNT(*) as has_access, 
           MAX(deleted_status) as is_deleted
    FROM mail_recipients 
    WHERE thread_id = :thread_id 
    AND recipient_username = :username
");
$stmt_access->execute([':thread_id' => $thread_id, ':username' => $username]);
$access_check = $stmt_access->fetch(PDO::FETCH_ASSOC);

// Also check if user is the creator
$is_creator = ($thread['created_by'] == $username);

if ($access_check['has_access'] == 0 && !$is_creator) {
    header("Location: mailbox.php");
    exit;
}

// Redirect to trash if thread is deleted
if ($access_check['is_deleted'] == '1') {
    header("Location: mailbox.php?trash");
    exit;
}

// Get all messages in thread that user can see
$stmt_messages = $db->prepare("
    SELECT DISTINCT
        m.*,
        (SELECT GROUP_CONCAT(DISTINCT CONCAT(mr.recipient_name, '|', mr.recipient_type) SEPARATOR ';;;')
         FROM mail_recipients mr
         WHERE mr.mail_id = m.sn) as all_recipients,
        (SELECT mr2.deleted_status 
         FROM mail_recipients mr2 
         WHERE mr2.mail_id = m.sn AND mr2.recipient_username = :username3) as is_deleted_by_me
    FROM mails m
    INNER JOIN mail_recipients mr ON mr.mail_id = m.sn
    WHERE m.thread_id = :thread_id
    AND (mr.recipient_username = :username OR m.from_username = :username2)
    ORDER BY m.mail_date ASC
");
$stmt_messages->execute([
    ':thread_id' => $thread_id, 
    ':username' => $username,
    ':username2' => $username,
    ':username3' => $username
]);
$messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

// Mark messages as read
$stmt_mark_read = $db->prepare("
    UPDATE mail_recipients 
    SET read_status = '1' 
    WHERE thread_id = :thread_id 
    AND recipient_username = :username
");
$stmt_mark_read->execute([':thread_id' => $thread_id, ':username' => $username]);

// Helper function to get visible recipients for current user
function getVisibleRecipients($all_recipients_str, $current_user, $message_sender) {
    if (empty($all_recipients_str)) return [];
    
    $recipients = explode(';;;', $all_recipients_str);
    $visible = [];
    
    foreach ($recipients as $rec) {
        $parts = explode('|', $rec);
        if (count($parts) != 2) continue;
        
        list($name, $type) = $parts;
        
        // TO and CC are always visible to everyone
        if ($type == 'to' || $type == 'cc') {
            $visible[] = ['name' => $name, 'type' => $type];
        }
        // BCC only visible to sender and that specific BCC recipient
        elseif ($type == 'bcc') {
            if ($current_user == $message_sender) {
                $visible[] = ['name' => $name, 'type' => 'bcc'];
            } elseif (strpos($name, '(') !== false) {
                // Extract username from "Full Name (username)" format
                preg_match('/\(([^)]+)\)/', $name, $matches);
                $recipient_username = isset($matches[1]) ? $matches[1] : '';
                if ($recipient_username == $current_user) {
                    // Show only "You" for BCC to the BCC recipient
                    $visible[] = ['name' => 'You', 'type' => 'bcc'];
                }
            }
        }
    }
    
    return $visible;
}

?>

<!DOCTYPE html>
<html>

<head>
    <?php include("../inc/header.php"); ?>
    <style>
        .message-card {
            background: #fff;
            border: 1px solid #e7eaec;
            border-radius: 4px;
            margin-bottom: 20px;
            padding: 20px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .message-card.unread {
            border-left: 4px solid #1ab394;
            background: #f9fcfb;
        }
        
        .message-header {
            border-bottom: 1px solid #e7eaec;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .sender-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #1ab394;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 12px;
            font-size: 16px;
        }
        
        .sender-details {
            flex: 1;
        }
        
        .sender-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .message-date {
            color: #999;
            font-size: 12px;
        }
        
        .recipient-list {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .recipient-type-label {
            font-weight: 600;
            margin-right: 5px;
        }
        
        .recipient-chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            margin-right: 5px;
            font-size: 11px;
        }
        
        .recipient-to {
            background: #e8f4fd;
            color: #1c84c6;
        }
        
        .recipient-cc {
            background: #d9edf7;
            color: #31708f;
        }
        
        .recipient-bcc {
            background: #fcf8e3;
            color: #8a6d3b;
        }
        
        .message-body {
            line-height: 1.6;
            color: #333;
            padding: 10px 0;
        }
        
        .message-body img {
            max-width: 100%;
            height: auto;
        }
        
        .message-attachments {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e7eaec;
        }
        
        .attachment-item {
            display: inline-block;
            padding: 8px 12px;
            background: #f3f3f4;
            border: 1px solid #e7eaec;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
        }
        
        .attachment-item:hover {
            background: #1ab394;
            color: #fff;
            border-color: #1ab394;
            text-decoration: none;
        }
        
        .attachment-item i {
            margin-right: 5px;
        }
        
        .attachment-item small {
            margin-left: 5px;
            opacity: 0.8;
        }
        
        .reply-section {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .thread-header {
            background: #fff;
            padding: 20px;
            border-bottom: 1px solid #e7eaec;
            margin-bottom: 20px;
        }
        
        .thread-subject {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .thread-meta {
            color: #999;
            font-size: 13px;
        }
        
        .collapsed-message {
            background: #fafafa;
            border: 1px solid #e7eaec;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .collapsed-message:hover {
            background: #f5f5f5;
            border-color: #d0d0d0;
        }
        
        .collapsed-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .collapsed-sender {
            font-weight: 600;
            color: #555;
        }
        
        .collapsed-snippet {
            color: #999;
            font-size: 12px;
            margin-left: 15px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body>

    <div id="wrapper">
        <?php include("../profile/nav_side.php"); ?>

        <div id="page-wrapper" class="gray-bg">
            <?php include("../profile/nav_header.php"); ?>

            <div class="wrapper wrapper-content">
                <div class="row">
                    <?php include("mail_counters.php"); ?>
                    <?php include("mail_icons.php"); ?>

                    <div class="col-lg-9 animated fadeInRight">
                        
                        <div class="mail-box">
                            
                            <!-- Thread Header -->
                            <div class="thread-header">
                                <a href="mailbox.php" class="btn btn-sm btn-white">
                                    <i class="fa fa-arrow-left"></i> Back to Inbox
                                </a>
                                
                                <div class="thread-subject">
                                    <i class="fa fa-comments text-muted"></i> 
                                    <?php echo htmlspecialchars($thread['subject']); ?>
                                </div>
                                
                                <div class="thread-meta">
                                    <i class="fa fa-envelope"></i> <?php echo $thread['message_count']; ?> message(s) 
                                    &nbsp;&nbsp;
                                    <i class="fa fa-clock-o"></i> Started <?php include_once("../inc/dd.php"); echo getTheDay3($thread['created_date']); ?>
                                </div>
                            </div>

                            <!-- Messages -->
                            <?php 
                            $message_count = count($messages);
                            $show_collapsed = ($message_count > 2); // Collapse older messages if more than 2
                            
                            foreach ($messages as $index => $msg) { 
                                $is_last_message = ($index == $message_count - 1);
                                $is_first_message = ($index == 0);
                                
                                // Show first message expanded, middle messages collapsed, last message expanded
                                $show_expanded = $is_first_message || $is_last_message || !$show_collapsed;
                                
                                // Get visible recipients for this message
                                $visible_recipients = getVisibleRecipients($msg['all_recipients'], $username, $msg['from_username']);
                                
                                // Get sender details
                                $stmt_sender = $db->prepare("SELECT admin_users.*, hremp.Designation 
                                    FROM admin_users 
                                    LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
                                    WHERE admin_users.username = :username");
                                $stmt_sender->execute([':username' => $msg['from_username']]);
                                $sender = $stmt_sender->fetch(PDO::FETCH_ASSOC);
                                
                                $sender_designation = isset($sender["Designation"]) ? $sender["Designation"] : '';
                                $sender_unit_head = (isset($sender["unit_head"]) && $sender["unit_head"] == 1) ? ', Unit Head' : '';
                                $sender_full_title = $msg['from_name'] . ' (' . $sender_designation . $sender_unit_head . ')';
                                
                                // Get initials for avatar
                                $name_parts = explode(' ', $msg['from_name']);
                                $initials = '';
                                foreach ($name_parts as $part) {
                                    if (!empty($part)) {
                                        $initials .= strtoupper($part[0]);
                                        if (strlen($initials) >= 2) break;
                                    }
                                }
                                
                                if (!$show_expanded) {
                                    // Collapsed view for middle messages
                                    $snippet = strip_tags($msg['msg']);
                                    $snippet = substr($snippet, 0, 80);
                                ?>
                                    <div class="collapsed-message" onclick="$(this).next('.message-card').slideToggle(200); $(this).hide();">
                                        <div class="collapsed-content">
                                            <span class="collapsed-sender"><?php echo htmlspecialchars($msg['from_name']); ?></span>
                                            <span class="collapsed-snippet"><?php echo htmlspecialchars($snippet); ?>...</span>
                                            <span class="message-date"><?php echo getTheDay3($msg['mail_date']); ?></span>
                                            <i class="fa fa-chevron-down"></i>
                                        </div>
                                    </div>
                                <?php } ?>
                                
                                <div class="message-card" style="<?php echo !$show_expanded ? 'display:none;' : ''; ?>">
                                    <div class="message-header">
                                        <div class="sender-info">
                                            <div class="sender-avatar">
                                                <?php echo $initials; ?>
                                            </div>
                                            <div class="sender-details">
                                                <div class="sender-name">
                                                    <?php echo htmlspecialchars($sender_full_title); ?>
                                                    <?php if ($msg['from_username'] == $username) { ?>
                                                        <span class="label label-info">You</span>
                                                    <?php } ?>
                                                </div>
                                                <div class="message-date">
                                                    <?php echo getTheDay3($msg['mail_date']); ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Recipients -->
                                        <div class="recipient-list">
                                            <?php 
                                            $to_recipients = array_filter($visible_recipients, function($r) { return $r['type'] == 'to'; });
                                            $cc_recipients = array_filter($visible_recipients, function($r) { return $r['type'] == 'cc'; });
                                            $bcc_recipients = array_filter($visible_recipients, function($r) { return $r['type'] == 'bcc'; });
                                            
                                            if (count($to_recipients) > 0) {
                                                echo '<span class="recipient-type-label">To:</span> ';
                                                foreach ($to_recipients as $rec) {
                                                    echo '<span class="recipient-chip recipient-to">' . htmlspecialchars($rec['name']) . '</span> ';
                                                }
                                                echo '<br>';
                                            }
                                            
                                            if (count($cc_recipients) > 0) {
                                                echo '<span class="recipient-type-label"><i class="fa fa-users"></i> Cc:</span> ';
                                                foreach ($cc_recipients as $rec) {
                                                    echo '<span class="recipient-chip recipient-cc">' . htmlspecialchars($rec['name']) . '</span> ';
                                                }
                                                echo '<br>';
                                            }
                                            
                                            if (count($bcc_recipients) > 0) {
                                                echo '<span class="recipient-type-label"><i class="fa fa-eye-slash"></i> Bcc:</span> ';
                                                foreach ($bcc_recipients as $rec) {
                                                    echo '<span class="recipient-chip recipient-bcc">' . htmlspecialchars($rec['name']) . '</span> ';
                                                }
                                                echo '<small class="text-muted">(hidden from other recipients)</small>';
                                            }
                                            ?>
                                        </div>
                                        
                                        <!-- Message Actions -->
                                        <div class="message-actions" style="margin-top: 10px;">
                                            <?php if (isset($msg['is_deleted_by_me']) && $msg['is_deleted_by_me'] == '1'): ?>
                                                <span class="label label-warning"><i class="fa fa-trash-o"></i> Deleted</span>
                                                <a href="mailbox.php?restore_single_message=<?php echo $msg['sn']; ?>" 
                                                   class="btn btn-xs btn-success restore-message-btn" 
                                                   data-mail-id="<?php echo $msg['sn']; ?>"
                                                   title="Restore this message">
                                                    <i class="fa fa-undo"></i> Restore
                                                </a>
                                            <?php else: ?>
                                                <a href="mailbox.php?trash_single_message=<?php echo $msg['sn']; ?>" 
                                                   class="btn btn-xs btn-white trash-message-btn" 
                                                   data-mail-id="<?php echo $msg['sn']; ?>"
                                                   title="Delete this message">
                                                    <i class="fa fa-trash-o text-danger"></i> Delete
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="message-body">
                                        <?php echo $msg['msg']; ?>
                                    </div>
                                    
                                    <?php if ($msg['attachment_status'] == "1" && !empty($msg['attachments'])) { ?>
                                    <div class="message-attachments">
                                        <strong><i class="fa fa-paperclip"></i> Attachments:</strong><br>
                                        <?php 
                                        // Helper function to get file icon based on extension
                                        function getFileIcon($filename) {
                                            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                            $icons = [
                                                'pdf' => 'fa-file-pdf-o',
                                                'doc' => 'fa-file-word-o',
                                                'docx' => 'fa-file-word-o',
                                                'xls' => 'fa-file-excel-o',
                                                'xlsx' => 'fa-file-excel-o',
                                                'ppt' => 'fa-file-powerpoint-o',
                                                'pptx' => 'fa-file-powerpoint-o',
                                                'jpg' => 'fa-file-image-o',
                                                'jpeg' => 'fa-file-image-o',
                                                'png' => 'fa-file-image-o',
                                                'gif' => 'fa-file-image-o',
                                                'zip' => 'fa-file-archive-o',
                                                'rar' => 'fa-file-archive-o',
                                                'txt' => 'fa-file-text-o',
                                                'csv' => 'fa-file-excel-o',
                                            ];
                                            return $icons[$ext] ?? 'fa-file-o';
                                        }
                                        
                                        // Helper function to format file size
                                        function formatFileSize($filepath) {
                                            if (file_exists($filepath)) {
                                                $bytes = filesize($filepath);
                                                if ($bytes >= 1073741824) {
                                                    return number_format($bytes / 1073741824, 2) . ' GB';
                                                } elseif ($bytes >= 1048576) {
                                                    return number_format($bytes / 1048576, 2) . ' MB';
                                                } elseif ($bytes >= 1024) {
                                                    return number_format($bytes / 1024, 2) . ' KB';
                                                } else {
                                                    return $bytes . ' bytes';
                                                }
                                            }
                                            return '';
                                        }
                                        
                                        // Support both semicolon (new) and comma (old) delimiters for backward compatibility
                                        $delimiter = (strpos($msg['attachments'], ';') !== false) ? ';' : ',';
                                        $attachments = explode($delimiter, $msg['attachments']);
                                        foreach ($attachments as $attachment) {
                                            $attachment = trim($attachment);
                                            if (!empty($attachment)) {
                                                $filepath = '../' . $attachment;
                                                $filename = basename($attachment);
                                                // Remove unique prefix (uniqid_) from display name
                                                $displayName = preg_replace('/^[a-f0-9]+_/', '', $filename);
                                                $fileIcon = getFileIcon($filename);
                                                $fileSize = formatFileSize($filepath);
                                        ?>
                                            <a href="../<?php echo htmlspecialchars($attachment); ?>" target="_blank" class="attachment-item" title="<?php echo $fileSize; ?>">
                                                <i class="fa <?php echo $fileIcon; ?>"></i> 
                                                <?php echo htmlspecialchars($displayName); ?>
                                                <?php if ($fileSize): ?>
                                                    <small class="text-muted">(<?php echo $fileSize; ?>)</small>
                                                <?php endif; ?>
                                            </a>
                                        <?php 
                                            }
                                        }
                                        ?>
                                    </div>
                                    <?php } ?>
                                    
                                    <?php if (!$show_expanded) { ?>
                                    <div class="text-center" style="margin-top: 15px;">
                                        <a href="#" onclick="$(this).closest('.message-card').slideUp(200); $(this).closest('.message-card').prev('.collapsed-message').show(); return false;" class="text-muted">
                                            <i class="fa fa-chevron-up"></i> Collapse
                                        </a>
                                    </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            
                            <!-- Reply Section -->
                            <div class="reply-section">
                                <h4><i class="fa fa-reply"></i> Reply to this conversation</h4>
                                <div class="btn-group m-t-sm">
                                    <a href="mail_compose.php?reply=<?php echo $messages[count($messages)-1]['sn']; ?>&thread=<?php echo $thread_id; ?>" class="btn btn-primary">
                                        <i class="fa fa-reply"></i> Reply
                                    </a>
                                    <a href="mail_compose.php?reply_all=<?php echo $messages[count($messages)-1]['sn']; ?>&thread=<?php echo $thread_id; ?>" class="btn btn-info">
                                        <i class="fa fa-reply-all"></i> Reply All
                                    </a>
                                    <a href="mail_compose.php?forward=<?php echo $messages[count($messages)-1]['sn']; ?>" class="btn btn-white">
                                        <i class="fa fa-mail-forward"></i> Forward
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <?php include("../inc/footer.php") ?>

        </div>
    </div>

    <?php include('../modal_lock.php'); ?>
    
    <script src="../js/jquery-2.1.1.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
    <script src="../js/inspinia.js"></script>
    <script src="../js/plugins/pace/pace.min.js"></script>
    <script src="../js/idle.js"></script>
    
    <script>
        $(document).ready(function() {
            // Handle individual message trash with confirmation
            $('.trash-message-btn').on('click', function(e) {
                e.preventDefault();
                var mailId = $(this).data('mail-id');
                
                if(confirm('Delete this message? It will be moved to trash.')) {
                    window.location.href = 'mailbox.php?trash_single_message=' + mailId;
                }
            });
            
            // Handle individual message restore with confirmation
            $('.restore-message-btn').on('click', function(e) {
                e.preventDefault();
                var mailId = $(this).data('mail-id');
                
                if(confirm('Restore this message?')) {
                    window.location.href = 'mailbox.php?restore_single_message=' + mailId;
                }
            });
            
            // Show success messages
            <?php if (isset($_GET['msg_deleted'])): ?>
                alert('Message deleted successfully');
            <?php endif; ?>
            
            <?php if (isset($_GET['msg_restored'])): ?>
                alert('Message restored successfully');
            <?php endif; ?>
        });
    </script>
</body>

</html>
