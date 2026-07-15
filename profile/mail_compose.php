<?php
include("../inc/session.php");
include("../Connections/Conn.php");

function uploadAttachments($db)
{
    $uploadDir = '../uploads/attachments/'; // Directory to store uploaded files

    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $attachments = [];
    $errors = [];

    // Check if files were uploaded
    if (!empty($_FILES['attachments']['name'][0])) {
        $fileCount = count($_FILES['attachments']['name']);
        $maxFileSize = 20 * 1024 * 1024; // Max file size: 20MB

        for ($i = 0; $i < $fileCount; $i++) {
            $fileName = $_FILES['attachments']['name'][$i];
            $fileTmpName = $_FILES['attachments']['tmp_name'][$i];
            $fileError = $_FILES['attachments']['error'][$i];
            $fileSize = $_FILES['attachments']['size'][$i];

            // Check for upload errors
            if ($fileError !== UPLOAD_ERR_OK) {
                $errors[] = "Error uploading file $fileName. Please try again.";
                continue;
            }

            // Validate file size
            if ($fileSize > $maxFileSize) {
                $errors[] = "File $fileName exceeds the maximum allowed size of 20MB.";
                continue;
            }

            // Generate a unique filename to prevent overwriting
            $sanitizedFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
            $uniqueFileName = uniqid() . '_' . $sanitizedFileName;
            $destination = $uploadDir . $uniqueFileName;

            // Move uploaded file to destination
            if (move_uploaded_file($fileTmpName, $destination)) {
                // Store relative path for database
                $attachments[] = 'uploads/attachments/' . $uniqueFileName;
            } else {
                $errors[] = "Failed to move uploaded file: $fileName.";
            }
        }
    }

    // Return semicolon-delimited string for backward compatibility
    return [
        'attachments' => $attachments, 
        'attachments_string' => implode(';', $attachments),
        'errors' => $errors
    ];
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_POST["send"]) || isset($_POST["draft"])) {
    // Generate fresh timestamp for mail send/draft
    $setdate = date("Y-m-d H:i:s");
    
    // Handle file uploads first
    $uploadResult = uploadAttachments($db);
    $attachmentPaths = $uploadResult['attachments'];
    $errors = $uploadResult['errors'];
    $attachmentsStr = $uploadResult['attachments_string']; // Use semicolon-delimited string
    
    // Include forwarded attachments if forwarding
    if (isset($_POST['forward_attachments']) && !empty($_POST['forward_attachments'])) {
        if (!empty($attachmentsStr)) {
            $attachmentsStr .= ';' . $_POST['forward_attachments'];
        } else {
            $attachmentsStr = $_POST['forward_attachments'];
        }
    }

    if ($attachmentsStr == "") {
        $attachment_status = 0;
    } else {
        $attachment_status = 1;
    }

    if (isset($_POST["send"])) {
        $msg = $_POST['msg'];
        $subject = $_POST['subject'];
        $from_username = $_POST['username_sender'];
        $from_name = $_POST['name_sender'];
        
        // Collect all recipients by type
        $to_recipients = isset($_POST['to_list']) ? $_POST['to_list'] : [];
        $additional_recipients = isset($_POST['additional_recipients']) ? $_POST['additional_recipients'] : [];
        $recipient_mode = isset($_POST['recipient_mode']) ? $_POST['recipient_mode'] : 'to';
        
        // Assign additional recipients to CC or BCC based on mode
        $cc_recipients = ($recipient_mode === 'cc') ? $additional_recipients : [];
        $bcc_recipients = ($recipient_mode === 'bcc') ? $additional_recipients : [];
        
        // Get reply/thread information if present
        $thread_id = isset($_POST['thread_id']) ? intval($_POST['thread_id']) : null;
        $parent_mail_id = isset($_POST['parent_mail_id']) ? intval($_POST['parent_mail_id']) : null;
        $reply_type = isset($_POST['reply_type']) ? $_POST['reply_type'] : 'new';
        
        // Validation
        if ((empty($to_recipients) && empty($cc_recipients) && empty($bcc_recipients)) || $msg == "") {
            $errors[] = "Ensure you select at least one recipient and message box is not empty.";
        } else {
            try {
                $db->beginTransaction();
                
                // 1. Create or update thread
                if ($thread_id && $reply_type != 'forward') {
                    // Reply to existing thread - update thread activity
                    $stmt_update_thread = $db->prepare("
                        UPDATE mail_threads 
                        SET last_activity = :last_activity, 
                            message_count = message_count + 1 
                        WHERE thread_id = :thread_id
                    ");
                    $stmt_update_thread->execute([
                        ':last_activity' => $setdate,
                        ':thread_id' => $thread_id
                    ]);
                } else {
                    // New thread
                    $stmt_thread = $db->prepare("
                        INSERT INTO mail_threads (subject, created_by, created_date, last_activity) 
                        VALUES (:subject, :created_by, :created_date, :last_activity)
                    ");
                    $stmt_thread->execute([
                        ':subject' => $subject,
                        ':created_by' => $from_username,
                        ':created_date' => $setdate,
                        ':last_activity' => $setdate
                    ]);
                    $thread_id = $db->lastInsertId();
                    $parent_mail_id = null;
                    $reply_type = 'new';
                }
                
                // 2. Insert main mail record
                $stmt_mail = $db->prepare("
                    INSERT INTO mails (thread_id, parent_mail_id, from_name, from_username, 
                                       msg, subject, mail_status, mail_date, attachments, 
                                       attachment_status, reply_type, on_contact_name, on_contact_username) 
                    VALUES (:thread_id, :parent_mail_id, :from_name, :from_username, :msg, :subject, 
                            'send', :mail_date, :attachments, :attachment_status, :reply_type, '', '')
                ");
                $stmt_mail->execute([
                    ':thread_id' => $thread_id,
                    ':parent_mail_id' => $parent_mail_id,
                    ':from_name' => $from_name,
                    ':from_username' => $from_username,
                    ':msg' => $msg,
                    ':subject' => $subject,
                    ':mail_date' => $setdate,
                    ':attachments' => $attachmentsStr,
                    ':attachment_status' => $attachment_status,
                    ':reply_type' => $reply_type
                ]);
                $mail_id = $db->lastInsertId();
                
                // Update thread with original_mail_id (only for new threads)
                if ($reply_type == 'new') {
                    $stmt_update = $db->prepare("UPDATE mail_threads SET original_mail_id = :mail_id WHERE thread_id = :thread_id");
                    $stmt_update->execute([':mail_id' => $mail_id, ':thread_id' => $thread_id]);
                }
                
                // 3. Insert recipients
                $stmt_recipient = $db->prepare("
                    INSERT INTO mail_recipients (mail_id, thread_id, recipient_username, 
                                                recipient_name, recipient_type) 
                    VALUES (:mail_id, :thread_id, :recipient_username, :recipient_name, :recipient_type)
                ");
                
                // Process TO recipients
                foreach ($to_recipients as $recipient) {
                    if ($recipient != '') {
                        $parts = explode('/', $recipient);
                        $recipient_name = $parts[0];
                        $recipient_username = $parts[1];
                        
                        $stmt_recipient->execute([
                            ':mail_id' => $mail_id,
                            ':thread_id' => $thread_id,
                            ':recipient_username' => $recipient_username,
                            ':recipient_name' => $recipient_name,
                            ':recipient_type' => 'to'
                        ]);
                        
                        // Send notification
                        global_notify_($db, 'user', $recipient_username . '%', 
                                      'New mail: ' . $subject, 
                                      'From: ' . $from_name, 
                                      $from_username);
                    }
                }
                
                // Process CC recipients
                foreach ($cc_recipients as $recipient) {
                    if ($recipient != '') {
                        $parts = explode('/', $recipient);
                        $recipient_name = $parts[0];
                        $recipient_username = $parts[1];
                        
                        $stmt_recipient->execute([
                            ':mail_id' => $mail_id,
                            ':thread_id' => $thread_id,
                            ':recipient_username' => $recipient_username,
                            ':recipient_name' => $recipient_name,
                            ':recipient_type' => 'cc'
                        ]);
                        
                        // Send notification (indicate CC)
                        global_notify_($db, 'user', $recipient_username . '%', 
                                      'New mail (CC): ' . $subject, 
                                      'From: ' . $from_name, 
                                      $from_username);
                    }
                }
                
                // Process BCC recipients
                foreach ($bcc_recipients as $recipient) {
                    if ($recipient != '') {
                        $parts = explode('/', $recipient);
                        $recipient_name = $parts[0];
                        $recipient_username = $parts[1];
                        
                        $stmt_recipient->execute([
                            ':mail_id' => $mail_id,
                            ':thread_id' => $thread_id,
                            ':recipient_username' => $recipient_username,
                            ':recipient_name' => $recipient_name,
                            ':recipient_type' => 'bcc'
                        ]);
                        
                        // Send notification (don't mention BCC)
                        global_notify_($db, 'user', $recipient_username . '%', 
                                      'New mail: ' . $subject, 
                                      'From: ' . $from_name, 
                                      $from_username);
                    }
                }
                
                // Delete draft if this was converting a draft to a sent email
                if ($_POST['compose_type'] == "draft") {
                    $stmt = $db->prepare("DELETE FROM mails WHERE sn = :draft_sn");
                    $stmt->bindParam(':draft_sn', $_POST["draft_sn"], PDO::PARAM_STR);
                    $stmt->execute();
                }
                
                $db->commit();
                header("location:mail_compose.php?send");
                exit;
                
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollback();
                }
                $errors[] = "Error sending message: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST["draft"])) {
        $to_list = "";

        if (!empty($_POST["to_list"])) {
            foreach ($_POST["to_list"] as $Aa) {
                $to_list .= $Aa . ';';
            }

            $to_list = substr($to_list, 0, -1);

            $draft = 'draft';
            $Unknown = "Unknown Draft @ $setdate";

            $sql = $db->prepare("INSERT INTO mails (on_contact_name, on_contact_username, from_name, from_username, msg, subject, mail_status, mail_date, attachments) VALUES (:on_contact_name, :on_contact_username, :from_name, :from_username, :msg, :subject, :mail_status, :mail_date, :attachments)");

            $sql->bindParam(':on_contact_name', $Unknown, PDO::PARAM_STR);
            $sql->bindParam(':on_contact_username', $to_list, PDO::PARAM_STR);
            $sql->bindParam(':from_name', $_POST['name_sender'], PDO::PARAM_STR);
            $sql->bindParam(':from_username', $_POST['username_sender'], PDO::PARAM_STR);
            $sql->bindParam(':msg', $_POST['msg'], PDO::PARAM_STR);
            $sql->bindParam(':subject', $_POST['subject'], PDO::PARAM_STR);
            $sql->bindParam(':mail_status', $draft, PDO::PARAM_STR);
            $sql->bindParam(':mail_date', $setdate, PDO::PARAM_STR);
            $sql->bindParam(':attachments', $attachmentsStr, PDO::PARAM_STR);
            $sql->execute();

            // Delete previous draft if editing an existing draft
            if ($_POST['compose_type'] == "draft") {
                $stmt = $db->prepare("DELETE FROM mails WHERE sn = :draft_sn");
                $stmt->bindParam(':draft_sn', $_POST["draft_sn"], PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }
}

?>


<!DOCTYPE html>
<html>

<head>

    <?php include("../inc/header.php"); ?>
    
    <style>
        .attachment-row {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .attachment-remove {
            margin-left: 10px;
            color: red;
            cursor: pointer;
        }

        #attachments-container {
            max-width: 600px;
        }
        
        /* Additional Recipients Field Styling */
        #additional-recipients-group {
            background: #f9f9f9;
            padding: 15px;
            border-left: 3px solid #1ab394;
            border-radius: 4px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        #additional-recipients-group.bcc-mode {
            background: #fffaf0;
            border-left-color: #f8ac59;
        }
        
        #mode-indicator {
            font-size: 12px;
            border-radius: 4px;
        }
        
        #mode-indicator.cc-mode {
            background-color: #d9edf7;
            border-color: #1ab394;
            color: #31708f;
        }
        
        #mode-indicator.bcc-mode {
            background-color: #fcf8e3;
            border-color: #f8ac59;
            color: #8a6d3b;
        }
        
        .m-t-xs {
            margin-top: 5px;
        }
        
        .m-t-sm {
            margin-top: 10px;
        }
        
        #add-cc-btn, #add-bcc-btn {
            margin-right: 5px;
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
                        <div class="mail-box-header">
                            <div class="pull-right tooltip-demo">
                                <a href="mailbox.php" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Discard email"><i class="fa fa-times"></i> Discard</a>
                            </div>
                            <h2>
                                Compose mail
                            </h2>

                        </div>


                        <?php

                        if (isset($_GET["m"])) {
                            $compose_type = "draft";

                            $sn = $_GET["m"];
                            $stmt3 = $db->prepare("SELECT * FROM mails WHERE sn = :sn");
                            $stmt3->bindParam(':sn', $sn, PDO::PARAM_STR);
                            $stmt3->execute();
                            $row_draft = $stmt3->fetch(PDO::FETCH_ASSOC);
                            $p = explode(";", $row_draft['on_contact_username']);
                        } else {
                            $compose_type = "normal";
                        }

                        // Reply handling
                        $reply_thread_id = isset($_GET['thread']) ? intval($_GET['thread']) : null;
                        $reply_parent_mail_id = null;
                        $reply_type = null;
                        $reply_recipients = [];
                        $reply_cc_recipients = [];
                        
                        if (isset($_GET["reply"])) {
                            $sn = $_GET["reply"];
                            $stmt3 = $db->prepare("SELECT m.*, 
                                (SELECT GROUP_CONCAT(CONCAT(mr.recipient_name, '/', mr.recipient_username, '|', mr.recipient_type) SEPARATOR ';;;')
                                 FROM mail_recipients mr WHERE mr.mail_id = m.sn) as all_recipients
                                FROM mails m WHERE m.sn = :sn");
                            $stmt3->bindParam(':sn', $sn, PDO::PARAM_STR);
                            $stmt3->execute();
                            $row_rpl = $stmt3->fetch(PDO::FETCH_ASSOC);
                            
                            $current_user = $_SESSION['username'];
                            
                            // Reply-all by default (include sender + CC recipients)
                            if ($row_rpl['from_username'] != $current_user) {
                                $reply_recipients[] = $row_rpl['from_name'] . '/' . $row_rpl['from_username'];
                            }
                            
                            // Parse all recipients and add CC (but not BCC) to reply list
                            if (!empty($row_rpl['all_recipients'])) {
                                $all_recs = explode(';;;', $row_rpl['all_recipients']);
                                foreach ($all_recs as $rec) {
                                    $parts = explode('|', $rec);
                                    if (count($parts) == 2) {
                                        list($name_username, $type) = $parts;
                                        list($name, $rec_username) = explode('/', $name_username);
                                        
                                        // Skip current user and BCC recipients
                                        if ($rec_username == $current_user || $type == 'bcc') continue;
                                        
                                        if ($type == 'cc') {
                                            $reply_cc_recipients[] = $name . '/' . $rec_username;
                                        }
                                    }
                                }
                            }
                            
                            $reply_parent_mail_id = $row_rpl['sn'];
                            $reply_type = 'reply';
                            $reply_fwd_msg = ''; // No need to quote - threading view shows conversation
                            $reply_fwd_Subj = (stripos($row_rpl['subject'], 'RE:') === false) ? "RE: " . $row_rpl['subject'] : $row_rpl['subject'];
                            $is_reply_mode = true;
                        }
                        
                        if (isset($_GET["reply_all"])) {
                            $sn = $_GET["reply_all"];
                            $stmt3 = $db->prepare("SELECT m.*, 
                                (SELECT GROUP_CONCAT(CONCAT(mr.recipient_name, '/', mr.recipient_username, '|', mr.recipient_type) SEPARATOR ';;;')
                                 FROM mail_recipients mr WHERE mr.mail_id = m.sn) as all_recipients
                                FROM mails m WHERE m.sn = :sn");
                            $stmt3->bindParam(':sn', $sn, PDO::PARAM_STR);
                            $stmt3->execute();
                            $row_rpl = $stmt3->fetch(PDO::FETCH_ASSOC);
                            
                            $current_user = $_SESSION['username'];
                            
                            // Add original sender to TO
                            if ($row_rpl['from_username'] != $current_user) {
                                $reply_recipients[] = $row_rpl['from_name'] . '/' . $row_rpl['from_username'];
                            }
                            
                            // Parse all recipients and add TO/CC (but not BCC) to reply list
                            if (!empty($row_rpl['all_recipients'])) {
                                $all_recs = explode(';;;', $row_rpl['all_recipients']);
                                foreach ($all_recs as $rec) {
                                    $parts = explode('|', $rec);
                                    if (count($parts) == 2) {
                                        list($name_username, $type) = $parts;
                                        list($name, $rec_username) = explode('/', $name_username);
                                        
                                        // Skip current user and BCC recipients
                                        if ($rec_username == $current_user || $type == 'bcc') continue;
                                        
                                        if ($type == 'to') {
                                            $reply_recipients[] = $name . '/' . $rec_username;
                                        } elseif ($type == 'cc') {
                                            $reply_cc_recipients[] = $name . '/' . $rec_username;
                                        }
                                    }
                                }
                            }
                            
                            $reply_parent_mail_id = $row_rpl['sn'];
                            $reply_type = 'reply_all';
                            $reply_fwd_msg = ''; // No need to quote - threading view shows conversation
                            $reply_fwd_Subj = (stripos($row_rpl['subject'], 'RE:') === false) ? "RE: " . $row_rpl['subject'] : $row_rpl['subject'];
                            $is_reply_mode = true;
                        }

                        // Forward with attachments
                        $forward_attachments = '';
                        if (isset($_GET["forward"])) {
                            $sn = $_GET["forward"];
                            $stmt3 = $db->prepare("SELECT * FROM mails WHERE sn = :sn");
                            $stmt3->bindParam(':sn', $sn, PDO::PARAM_STR);
                            $stmt3->execute();
                            $row_rpl = $stmt3->fetch(PDO::FETCH_ASSOC);
                            
                            // Build attachment list for display in forwarded message
                            $attachment_list = '';
                            if (!empty($row_rpl['attachments'])) {
                                $delimiter = (strpos($row_rpl['attachments'], ';') !== false) ? ';' : ',';
                                $attachments = explode($delimiter, $row_rpl['attachments']);
                                $attachment_list = '<br><strong>Attachments:</strong> ';
                                $attachment_names = [];
                                foreach ($attachments as $att) {
                                    $att = trim($att);
                                    if (!empty($att)) {
                                        $filename = basename($att);
                                        $displayName = preg_replace('/^[a-f0-9]+_/', '', $filename);
                                        $attachment_names[] = $displayName;
                                    }
                                }
                                $attachment_list .= implode(', ', $attachment_names);
                                // Carry forward attachments
                                $forward_attachments = $row_rpl['attachments'];
                            }
                            
                            $reply_fwd_msg = '<br><br><blockquote style="border-left: 3px solid #ccc; padding-left: 10px; color: #666;">' . 
                                '<strong>---------- Forwarded message ----------</strong><br>' . 
                                '<strong>From:</strong> ' . $row_rpl['from_name'] . '<br>' .
                                '<strong>Date:</strong> ' . date('M d, Y \a\t g:i A', strtotime($row_rpl['mail_date'])) . '<br>' .
                                '<strong>Subject:</strong> ' . $row_rpl['subject'] . $attachment_list . '<br><br>' .
                                $row_rpl['msg'] . '</blockquote>';
                            $reply_fwd_Subj = (stripos($row_rpl['subject'], 'FWD:') === false && stripos($row_rpl['subject'], 'FORWARD:') === false) ? "FWD: " . $row_rpl['subject'] : $row_rpl['subject'];
                            
                            // Forward doesn't set parent_mail_id or thread (starts new thread)
                            $reply_type = 'forward';
                        }

                        ?>


                        <form class="form-horizontal" action="<?php echo $editFormAction; ?>" method="POST" id="subjects" name="subjects" enctype="multipart/form-data">

                            <div class="mail-box">


                                <div class="mail-body">

                                    <?php
                                    // Display error messages if any
                                    if (!empty($errors)) {
                                        foreach ($errors as $error) {
                                            echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
                                        }
                                    }

                                    if (isset($_GET["send"])) {
                                        echo "<div class='alert alert-success'>Message Sent! &nbsp;<a href='mailbox.php?send'>Close</a></div>";
                                    } ?>
                                    
                                    <?php if (isset($is_reply_mode) && $is_reply_mode): ?>
                                    <div class="alert alert-info" style="margin: 15px 0;">
                                        <h4><i class="fa fa-reply"></i> Reply Recipients</h4>
                                        <p><strong>Your reply will be sent to:</strong></p>
                                        <ul style="margin-bottom: 10px;">
                                            <?php 
                                            if (!empty($reply_recipients)) {
                                                foreach ($reply_recipients as $recipient) {
                                                    $parts = explode('/', $recipient);
                                                    echo "<li><strong>To:</strong> " . htmlspecialchars($parts[0]) . "</li>";
                                                }
                                            }
                                            if (!empty($reply_cc_recipients)) {
                                                foreach ($reply_cc_recipients as $recipient) {
                                                    $parts = explode('/', $recipient);
                                                    echo "<li><strong>Cc:</strong> " . htmlspecialchars($parts[0]) . "</li>";
                                                }
                                            }
                                            ?>
                                        </ul>
                                        <button type="button" class="btn btn-xs btn-white" id="customize-recipients-btn">
                                            <i class="fa fa-edit"></i> Customize Recipients
                                        </button>
                                        <small class="text-muted" style="display: block; margin-top: 5px;">
                                            <i class="fa fa-info-circle"></i> 
                                            <?php if ($reply_type == 'reply_all'): ?>
                                                You selected <strong>Reply All</strong>. Your reply goes to the sender and all other visible participants (excluding BCC).
                                            <?php else: ?>
                                                You selected <strong>Reply</strong>. Your reply goes strictly to the original sender. Other participants will not receive this.
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="form-group" id="recipient-selector" <?php if (isset($is_reply_mode) && $is_reply_mode) echo 'style="display:none;"'; ?>>
                                        <label class="col-sm-2 control-label">To:</label>

                                        <div class="col-sm-10">
                                            <?php
                                            // Prepare the SQL statement with a placeholder for `status`
                                            $stmt2 = $db->prepare("SELECT admin_users.*, hremp.Designation FROM admin_users 
                                            LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
                                            WHERE admin_users.status = :status");

                                            // Bind the parameter `:status` to the value `1`, treating it as a string
                                            $status = '1';
                                            $stmt2->bindParam(':status', $status, PDO::PARAM_STR);

                                            // Execute the statement
                                            $stmt2->execute();
                                            ?>
                                            <select name="to_list[]" data-placeholder="Select one recipient (use Cc/Bcc for more)..." class="chosen-select" style="width:350px;" tabindex="4" id="users-select">
                                                <?php
                                                //$option=2;
                                                foreach ($p as $Aa) { ?>
                                                    <option value="<?php echo $Aa; ?>" selected><?php $pp = explode("/", $Aa);
                                                                                                echo $pp[0]; ?></option>
                                                <?php } ?>

                                                <?php
                                                while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { 
                                                    $user_value = $roww["fullname"] . '/' . $roww["username"];
                                                    
                                                    // Check if this user should be pre-selected for replies
                                                    $select = "";
                                                    if (isset($replyID) && $replyID == $roww["username"]) {
                                                        $select = "selected";
                                                    }
                                                    if (isset($reply_recipients) && in_array($user_value, $reply_recipients)) {
                                                        $select = "selected";
                                                    }
                                                ?>
                                                    <option value="<?php echo $user_value; ?>" <?php echo $select; ?>><?php echo $roww["fullname"] . ' (' . $roww["Designation"] . ' ' . (($roww["unit_head"] == 1) ? ', Unit Head' : '') . ')'; ?></option>
                                                <?php } ?>


                                            </select>

                                            <div class="m-t-sm">
                                                <label class="text-muted" style="font-weight: normal; font-size: 12px;">
                                                    <i class="fa fa-info-circle text-info"></i> 
                                                    Add more recipients as:
                                                </label>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-white" id="add-cc-btn">
                                                        <i class="fa fa-users text-primary"></i> Cc (Visible to All)
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-white" id="add-bcc-btn">
                                                        <i class="fa fa-eye-slash text-warning"></i> Bcc (Hidden)
                                                    </button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="compose_type" id="compose_type" value="<?php echo $compose_type; ?>">
                                            <input type="hidden" name="draft_sn" id="draft_sn" value="<?php echo $sn; ?>">
                                            <input type="hidden" name="recipient_mode" id="recipient_mode" value="to">
                                            <input type="hidden" name="thread_id" id="thread_id" value="<?php echo $reply_thread_id ?: ''; ?>">
                                            <input type="hidden" name="parent_mail_id" id="parent_mail_id" value="<?php echo $reply_parent_mail_id ?: ''; ?>">
                                            <input type="hidden" name="reply_type" id="reply_type" value="<?php echo $reply_type ?: 'new'; ?>">
                                            <input type="hidden" name="forward_attachments" id="forward_attachments" value="<?php echo isset($forward_attachments) ? htmlspecialchars($forward_attachments) : ''; ?>">
                                            <input type="hidden" name="name_sender" id="name_sender" value="<?php echo $_SESSION['fullname']; ?>">
                                            <input type="hidden" name="username_sender" id="username_sender" value="<?php echo $_SESSION['username']; ?>">

                                            <?php
                                            // sn_code
                                            $stmt_sn = $db->query("SELECT sn FROM mails ORDER BY sn desc limit 1");
                                            if ($stmt_sn->rowCount() > 0) {
                                                $row_sn = $stmt_sn->fetch(PDO::FETCH_ASSOC);
                                            }
                                            ?>
                                            <input type="hidden" name="sn" id="sn" value="<?php echo $row_sn['sn'] + 1; ?>">


                                        </div>

                                    </div>
                                    
                                    <!-- Additional Recipients Field (CC or BCC mode) -->
                                    <div class="form-group" id="additional-recipients-group" style="display:none;">
                                        <label class="col-sm-2 control-label">
                                            <span id="additional-label">Cc:</span>
                                            <i class="fa fa-info-circle" id="additional-icon" data-toggle="tooltip"></i>
                                        </label>
                                        <div class="col-sm-10">
                                            <select name="additional_recipients[]" id="additional-select" data-placeholder="Select additional recipients..." class="chosen-select-additional" multiple style="width:100%;">
                                                <?php
                                                // Reset the statement to get all users again
                                                $stmt2->execute();
                                                while ($roww = $stmt2->fetch(PDO::FETCH_ASSOC)) { 
                                                    $user_value = $roww["fullname"] . '/' . $roww["username"];
                                                    
                                                    // Pre-select CC recipients for reply_all
                                                    $select_cc = "";
                                                    if (isset($reply_cc_recipients) && in_array($user_value, $reply_cc_recipients)) {
                                                        $select_cc = "selected";
                                                    }
                                                ?>
                                                    <option value="<?php echo $user_value; ?>" <?php echo $select_cc; ?>>
                                                        <?php echo $roww["fullname"] . ' (' . $roww["Designation"] . ' ' . (($roww["unit_head"] == 1) ? ', Unit Head' : '') . ')'; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <div class="m-t-xs">
                                                <div id="mode-indicator" class="alert" style="padding: 8px 12px; margin-bottom: 10px;">
                                                    <i class="fa fa-users"></i> 
                                                    <strong>CC Mode:</strong> All recipients can see each other
                                                </div>
                                                <button type="button" class="btn btn-xs btn-white" id="switch-mode-btn">
                                                    <i class="fa fa-exchange"></i> Switch to <span id="switch-mode-text">Bcc</span>
                                                </button>
                                                <a href="#" id="remove-additional-field" class="text-danger" style="margin-left: 10px;">
                                                    <i class="fa fa-times-circle"></i> Remove additional recipients
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group"><label class="col-sm-2 control-label">Subject:</label>

                                        <div class="col-sm-10"><input type="text" class="form-control" name="subject" value="<?php if (isset($_GET["reply"]) or isset($_GET["reply_all"]) or isset($_GET["forward"])) {
                                                                                                                                    echo isset($reply_fwd_Subj) ? htmlspecialchars($reply_fwd_Subj) : '';
                                                                                                                                } ?>"></div>
                                    </div>

                                </div>

                                <div class="mail-text h-200">

                                    <textarea name="msg" id="msg" cols="45" rows="5" maxlength="160" class="summernote" placeholder="Type Your Message Here"><?php if (isset($_GET["reply"]) or isset($_GET["reply_all"]) or isset($_GET["forward"])) {
                                                                                                                                                                    echo isset($reply_fwd_msg) ? $reply_fwd_msg : '';
                                                                                                                                                                } ?></textarea>

                                    <div class="clearfix"></div>
                                </div>
                                <!-- Add file upload input -->
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">Attachments:</label>
                                    <div class="col-sm-10" id="attachments-container">
                                        <!-- Attachment inputs will be dynamically added here -->
                                    </div>
                                    <div class="col-sm-10 col-sm-offset-2">
                                        <button type="button" id="add-attachment" class="btn btn-default">
                                            <i class="fa fa-plus"></i> Add Attachment
                                        </button>
                                    </div>
                                    <div class="col-sm-10 col-sm-offset-2" id="file-list">
                                        <!-- Selected files will be listed here -->
                                    </div>
                                </div>
                                <div class="mail-body text-right tooltip-demo">

                                    <button class="btn btn-sm btn-primary" type="submit" name="send" id="send"><i class="fa fa-reply"></i> Send</button>
                                    <button class="btn btn-white btn-sm" type="submit" name="draft" id="draft"><i class="fa fa-pencil"></i> Draft</button>

                                    <a href="mailbox.php" class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Discard email"><i class="fa fa-times"></i> Discard</a>


                                </div>
                                <div class="clearfix"></div>



                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <?php include("../inc/footer.php") ?>

        </div>
    </div>

    <?php include('../modal_lock.php'); ?>

    <!-- Mainly scripts -->
    <script src="../js/jquery-2.1.1.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="../js/inspinia.js"></script>
    <script src="../js/plugins/pace/pace.min.js"></script>
    <script src="../js/plugins/chosen/chosen.jquery.js"></script>

    <!-- iCheck -->
    <script src="../js/plugins/iCheck/icheck.min.js"></script>

    <!-- SUMMERNOTE -->
    <script src="../js/plugins/summernote/summernote.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });


            $('.summernote').summernote();

            // Auto-show CC field if reply_all has pre-selected CC recipients
            <?php if (isset($reply_cc_recipients) && count($reply_cc_recipients) > 0) { ?>
                setRecipientMode('cc');
                showAdditionalRecipientsField();
                $('.chosen-select-additional').trigger('chosen:updated');
            <?php } ?>

        });


        $(".chosen-select").chosen({
            allow_single_deselect: true,
            enable_search_threshold: 10,
            no_results_text: 'Oops, nothing found!',
            width: "100%"
        });
        $('.chosen-drop').css({
            "width": "100%",
            "white-space": "nowrap"
        })


        var edit = function() {
            $('.click2edit').summernote({
                focus: true
            });
        };
        var save = function() {
            var aHTML = $('.click2edit').code(); //save HTML If you need(aHTML: array).
            $('.click2edit').destroy();
        };

        // JavaScript for "Select All" functionality
        function select_all_u() {
            console.log('clicked');
            $('#users-select option').prop('selected', true);
            $('#users-select').trigger('chosen:updated');
        }

        // JavaScript for "Select All" functionality
        function deselect_all_u() {
            console.log('clicked');
            $('#users-select option').prop('selected', false);
            $('#users-select').trigger('chosen:updated');
        }
        
        // Additional Recipients Management (CC or BCC mode)
        var currentMode = 'cc'; // Default mode
        
        $('#add-cc-btn').on('click', function(e) {
            e.preventDefault();
            setRecipientMode('cc');
            showAdditionalRecipientsField();
        });
        
        $('#add-bcc-btn').on('click', function(e) {
            e.preventDefault();
            setRecipientMode('bcc');
            showAdditionalRecipientsField();
        });
        
        // Handle customize recipients button for reply mode
        $('#customize-recipients-btn').on('click', function(e) {
            e.preventDefault();
            $('#recipient-selector').slideDown(300);
            $(this).closest('.alert-info').slideUp(300);
        });
        
        function showAdditionalRecipientsField() {
            $('#additional-recipients-group').slideDown(300);
            $('#add-cc-btn, #add-bcc-btn').prop('disabled', true).addClass('disabled');
            
            // Initialize Chosen if not already done
            if (!$('#additional-select').hasClass('chosen-processed')) {
                $('.chosen-select-additional').chosen({
                    allow_single_deselect: true,
                    enable_search_threshold: 10,
                    no_results_text: 'Oops, nothing found!',
                    width: "100%",
                    placeholder_text_multiple: "Select recipients..."
                });
                $('#additional-select').addClass('chosen-processed');
            }
        }
        
        function setRecipientMode(mode) {
            currentMode = mode;
            $('#recipient_mode').val(mode);
            
            if (mode === 'cc') {
                $('#additional-label').html('Cc: <i class="fa fa-users text-primary"></i>');
                $('#additional-icon').removeClass('text-warning').addClass('text-info')
                    .attr('title', 'Carbon Copy - All recipients can see each other');
                $('#mode-indicator').removeClass('bcc-mode alert-warning').addClass('cc-mode alert-info')
                    .html('<i class="fa fa-users"></i> <strong>CC Mode:</strong> All recipients can see each other');
                $('#additional-recipients-group').removeClass('bcc-mode');
                $('#switch-mode-text').text('Bcc');
            } else {
                $('#additional-label').html('Bcc: <i class="fa fa-eye-slash text-warning"></i>');
                $('#additional-icon').removeClass('text-info').addClass('text-warning')
                    .attr('title', 'Blind Carbon Copy - Recipients are hidden from each other');
                $('#mode-indicator').removeClass('cc-mode alert-info').addClass('bcc-mode alert-warning')
                    .html('<i class="fa fa-eye-slash"></i> <strong>BCC Mode:</strong> Recipients are hidden from each other');
                $('#additional-recipients-group').addClass('bcc-mode');
                $('#switch-mode-text').text('Cc');
            }
            
            // Reinitialize tooltip
            $('[data-toggle="tooltip"]').tooltip('destroy').tooltip();
        }
        
        $('#switch-mode-btn').on('click', function(e) {
            e.preventDefault();
            var newMode = (currentMode === 'cc') ? 'bcc' : 'cc';
            setRecipientMode(newMode);
        });
        
        $('#remove-additional-field').on('click', function(e) {
            e.preventDefault();
            $('#additional-recipients-group').slideUp(300);
            $('#additional-select').val('').trigger('chosen:updated');
            $('#add-cc-btn, #add-bcc-btn').prop('disabled', false).removeClass('disabled');
            $('#recipient_mode').val('to');
        });
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        // Attachment handling - wrapped in document ready
        $(document).ready(function() {
            const addAttachmentBtn = document.getElementById('add-attachment');
            const attachmentsContainer = document.getElementById('attachments-container');
            const fileListDiv = document.getElementById('file-list');

            if (!addAttachmentBtn || !attachmentsContainer) return;

            // Function to create a new attachment row
            function createAttachmentRow() {
                // Create a new row div
                const row = document.createElement('div');
                row.className = 'attachment-row';

                // Create file input
                const input = document.createElement('input');
                input.type = 'file';
                input.name = `attachments[]`;
                input.className = 'form-control';

                // Create remove button
                const removeBtn = document.createElement('span');
                removeBtn.innerHTML = '&times;';
                removeBtn.className = 'attachment-remove';

                // Add event listener to remove button
                removeBtn.addEventListener('click', function() {
                    attachmentsContainer.removeChild(row);
                    updateFileList();
                });

                // Add event listener to file input
                input.addEventListener('change', updateFileList);

                // Append input and remove button to row
                row.appendChild(input);
                row.appendChild(removeBtn);

                return row;
            }

            // Function to update file list
            function updateFileList() {
                // Collect all file inputs
                const fileInputs = attachmentsContainer.querySelectorAll('input[type="file"]');
                const selectedFiles = [];

                fileInputs.forEach(input => {
                    if (input.files.length > 0) {
                        Array.from(input.files).forEach(file => {
                            selectedFiles.push(file.name);
                        });
                    }
                });

                // Update file list display
                if (selectedFiles.length > 0) {
                    fileListDiv.textContent = 'Selected files: ' + selectedFiles.join(', ');
                } else {
                    fileListDiv.textContent = '';
                }
            }

            // Add initial attachment input
            attachmentsContainer.appendChild(createAttachmentRow());

            // Event listener for add attachment button
            addAttachmentBtn.addEventListener('click', function() {
                // Create and append a new attachment row
                const newRow = createAttachmentRow();
                attachmentsContainer.appendChild(newRow);

                // Trigger file selection
                const fileInput = newRow.querySelector('input[type="file"]');
                fileInput.click();
            });
        }); // End of attachment handling document ready
    </script>
    
    <script src="../js/idle.js"></script>
</body>

</html>