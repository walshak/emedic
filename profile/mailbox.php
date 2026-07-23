<?php
include("../inc/session.php");
include("../Connections/Conn.php");

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

// Handle bulk trash operation
if (isset($_GET['bulk_trash'])) {
    $thread_ids = explode(',', $_GET['bulk_trash']);
    $username = $_SESSION['username'];
    foreach ($thread_ids as $thread_id) {
        $thread_id = intval($thread_id); // Sanitize
        if ($thread_id > 0) {
            // Mark all messages in thread as deleted for this user in mail_recipients with timestamp
            $stmt = $db->prepare("UPDATE mail_recipients SET deleted_status='1', deleted_date=NOW() WHERE thread_id=:thread_id AND recipient_username=:username");
            $stmt->execute([':thread_id' => $thread_id, ':username' => $username]);
        }
    }
    $trash_count = count($thread_ids);
    header("Location: mailbox.php?trash_success={$trash_count}");
    exit;
}

// Handle single trash operation
if (isset($_GET['trash_single'])) {
    $thread_id = intval($_GET['trash_single']);
    $username = $_SESSION['username'];
    if ($thread_id > 0) {
        // Mark all messages in thread as deleted for this user in mail_recipients with timestamp
        $stmt = $db->prepare("UPDATE mail_recipients SET deleted_status='1', deleted_date=NOW() WHERE thread_id=:thread_id AND recipient_username=:username");
        $stmt->execute([':thread_id' => $thread_id, ':username' => $username]);
        header("Location: mailbox.php?trash_success=1");
        exit;
    }
}

// Handle restore thread operation
if (isset($_GET['restore_thread'])) {
    $thread_id = intval($_GET['restore_thread']);
    $username = $_SESSION['username'];
    if ($thread_id > 0) {
        // Restore all messages in thread for this user
        $stmt = $db->prepare("UPDATE mail_recipients SET deleted_status='0' WHERE thread_id=:thread_id AND recipient_username=:username");
        $stmt->execute([':thread_id' => $thread_id, ':username' => $username]);
        header("Location: mailbox.php?trash&restore_success=1");
        exit;
    }
}

// Handle restore single message operation
if (isset($_GET['restore_single_message'])) {
    $mail_id = intval($_GET['restore_single_message']);
    $username = $_SESSION['username'];
    if ($mail_id > 0) {
        // Get thread_id to redirect back
        $stmt_thread = $db->prepare("SELECT thread_id FROM mails WHERE sn = :mail_id");
        $stmt_thread->execute([':mail_id' => $mail_id]);
        $thread_info = $stmt_thread->fetch(PDO::FETCH_ASSOC);
        
        // Restore single message for this user
        $stmt = $db->prepare("UPDATE mail_recipients SET deleted_status='0' WHERE mail_id=:mail_id AND recipient_username=:username");
        $stmt->execute([':mail_id' => $mail_id, ':username' => $username]);
        
        // Redirect back to thread view
        header("Location: mail_thread_view.php?t=" . $thread_info['thread_id'] . "&msg_restored=1");
        exit;
    }
}

// Handle single message trash (from thread view)
if (isset($_GET['trash_single_message'])) {
    $mail_id = intval($_GET['trash_single_message']);
    $username = $_SESSION['username'];
    if ($mail_id > 0) {
        // Get thread_id to redirect back
        $stmt_thread = $db->prepare("SELECT thread_id FROM mails WHERE sn = :mail_id");
        $stmt_thread->execute([':mail_id' => $mail_id]);
        $thread_info = $stmt_thread->fetch(PDO::FETCH_ASSOC);
        
        // Mark single message as deleted for this user
        $stmt = $db->prepare("UPDATE mail_recipients SET deleted_status='1', deleted_date=NOW() WHERE mail_id=:mail_id AND recipient_username=:username");
        $stmt->execute([':mail_id' => $mail_id, ':username' => $username]);
        
        // Redirect back to thread view
        header("Location: mail_thread_view.php?t=" . $thread_info['thread_id'] . "&msg_deleted=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <?php include("../inc/header.php"); ?>

<body>

    <div id="wrapper">

        <?php include("../profile/nav_side.php"); ?>


        <div id="page-wrapper" class="gray-bg">
            <?php include("../profile/nav_header.php"); ?>


            <div class="wrapper wrapper-content">
                <div class="row">

                    <?php

                    $username = $_SESSION['username'];

                    if (isset($_GET['send'])) {
                        $search = "from_username='$username' and mail_status='send'";
                        $mail_status = 'Send';
                    } elseif (isset($_GET['trash'])) {
                        $search = "(from_username='$username' or on_contact_username='$username') and mail_status='trash'";
                        $mail_status = 'Trash';
                    } elseif (isset($_GET['draft'])) {
                        $search = "from_username='$username' and mail_status='draft'";
                        $mail_status = 'Draft';
                        $mycount = $count_draft;
                    } elseif (isset($_GET['inbox'])) {
                        $search = "on_contact_username='$username' and mail_status='send'";
                        $mail_status = 'Inbox';
                        $mycount = $inbox;
                    } else {
                        $search = "on_contact_username='$username' and mail_status='send'";
                        $mail_status = 'Inbox';
                        $mycount = $inbox;
                    }
                    ?>

                    <?php
                    // Updated query to get threads instead of individual mails
                    if ($mail_status == 'Inbox') {
                        // For inbox, get threads where user is a recipient
                        $stmt = $db->prepare("
                            SELECT 
                                mt.id as thread_id,
                                mt.subject,
                                mt.created_date,
                                mt.last_activity,
                                mt.message_count,
                                mt.created_by,
                                m.attachment_status,
                                m.mail_date,
                                m.from_name,
                                m.from_username,
                                m.msg as latest_message,
                                (SELECT COUNT(*) FROM mail_recipients mr 
                                 WHERE mr.thread_id = mt.id 
                                 AND mr.recipient_username = :username 
                                 AND mr.read_status = '0') as unread_count,
                                (SELECT GROUP_CONCAT(DISTINCT mr2.recipient_name SEPARATOR ', ') 
                                 FROM mail_recipients mr2 
                                 WHERE mr2.thread_id = mt.id 
                                 AND mr2.recipient_type IN ('to', 'cc')
                                 LIMIT 5) as all_recipients
                            FROM mail_threads mt
                            INNER JOIN mail_recipients mr ON mr.thread_id = mt.id
                            INNER JOIN mails m ON m.thread_id = mt.id 
                                AND m.mail_date = mt.last_activity
                            WHERE mr.recipient_username = :username2
                                AND mr.deleted_status = '0'
                            GROUP BY mt.id
                            ORDER BY (SELECT COUNT(*) FROM mail_recipients mr3 
                                     WHERE mr3.thread_id = mt.id 
                                     AND mr3.recipient_username = :username3
                                     AND mr3.read_status = '0') DESC,
                                     mt.last_activity DESC
                        ");
                        $stmt->execute([':username' => $username, ':username2' => $username, ':username3' => $username]);
                    } elseif ($mail_status == 'Send') {
                        // For sent, get threads created by user
                        $stmt = $db->prepare("
                            SELECT 
                                mt.id as thread_id,
                                mt.subject,
                                mt.created_date,
                                mt.last_activity,
                                mt.message_count,
                                mt.created_by,
                                m.attachment_status,
                                m.mail_date,
                                m.on_contact_name,
                                m.on_contact_username,
                                m.msg as latest_message,
                                0 as unread_count,
                                (SELECT GROUP_CONCAT(DISTINCT mr2.recipient_name SEPARATOR ', ') 
                                 FROM mail_recipients mr2 
                                 WHERE mr2.thread_id = mt.id 
                                 AND mr2.recipient_type IN ('to', 'cc')
                                 LIMIT 5) as all_recipients
                            FROM mail_threads mt
                            INNER JOIN mails m ON m.thread_id = mt.id 
                                AND m.mail_date = mt.last_activity
                            WHERE mt.created_by = :username
                            ORDER BY mt.last_activity DESC
                        ");
                        $stmt->execute([':username' => $username]);
                    } elseif ($mail_status == 'Trash') {
                        // For trash, show deleted threads (only last 30 days)
                        $stmt = $db->prepare("
                            SELECT 
                                mt.id as thread_id,
                                mt.subject,
                                mt.created_date,
                                mt.last_activity,
                                mt.message_count,
                                mt.created_by,
                                m.attachment_status,
                                m.mail_date,
                                m.from_name,
                                m.from_username,
                                m.msg as latest_message,
                                mr.deleted_date as deleted_date,
                                (SELECT GROUP_CONCAT(DISTINCT mr2.recipient_name SEPARATOR ', ') 
                                 FROM mail_recipients mr2 
                                 WHERE mr2.thread_id = mt.id 
                                 AND mr2.recipient_type IN ('to', 'cc')
                                 LIMIT 5) as all_recipients
                            FROM mail_threads mt
                            INNER JOIN mail_recipients mr ON mr.thread_id = mt.id
                            INNER JOIN mails m ON m.thread_id = mt.id 
                                AND m.mail_date = mt.last_activity
                            WHERE mr.recipient_username = :username
                                AND mr.deleted_status = '1'
                                AND mr.deleted_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                            GROUP BY mt.id
                            ORDER BY mr.deleted_date DESC
                        ");
                        $stmt->execute([':username' => $username]);
                    } elseif ($mail_status == 'Draft') {
                        // For drafts, show old single-message view
                        $stmt = $db->query("SELECT * FROM mails WHERE $search order by sn DESC");
                    }
                    
                    $others = $stmt->rowCount();
                    ?>
                    <?php include("mail_counters.php"); ?>
                    <?php include("mail_icons.php"); ?>

                    <div class="col-lg-9 animated fadeInRight">

                        <a href="index.php?profile=<?= $username; ?>" class="btn btn-xs btn-danger">Close & Return</a><br><br>
                        
                        <?php if (isset($_GET['trash_success'])): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-check"></i> <?php echo intval($_GET['trash_success']); ?> message(s) moved to trash successfully.
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['restore_success'])): ?>
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="fa fa-undo"></i> Message restored successfully.
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($mail_status == 'Trash'): ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <strong>Trash:</strong> Messages are kept for 30 days before permanent deletion.
                        </div>
                        <?php endif; ?>

                        <div class="mail-box-header">

                            <form method="get" action="index.html" class="pull-right mail-search">
                                <div class="input-group">
                                    <input type="text" class="form-control input-sm" name="search" placeholder="Search email">
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <h2>
                                <?php
                                echo $mail_status;
                                if ($mycount > 0) {
                                    echo '(' . $mycount . ')';
                                }
                                ?>
                            </h2>
                            <div class="mail-tools tooltip-demo m-t-md">
                                <div class="btn-group pull-right">
                                    <button class="btn btn-white btn-sm"><i class="fa fa-arrow-left"></i></button>
                                    <button class="btn btn-white btn-sm"><i class="fa fa-arrow-right"></i></button>

                                </div>
                                <button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="left" title="Refresh inbox" onclick="location.reload();"><i class="fa fa-refresh"></i> Refresh</button>
                                <button class="btn btn-white btn-sm" data-toggle="tooltip" data-placement="top" title="Mark as read"><i class="fa fa-eye"></i> </button>
                                <button class="btn btn-white btn-sm btn-bulk-trash" data-toggle="tooltip" data-placement="top" title="Move selected to trash"><i class="fa fa-trash-o"></i> </button>

                            </div>
                        </div>
                        <div class="mail-box">

                            <?php if ($stmt->rowCount() > 0) { ?>
                                <table class="table table-hover table-mail">
                                    <tbody>
                                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                                            <?php
                                            // For threaded view (Inbox/Send/Trash)
                                            if (isset($row['thread_id']) && $mail_status != 'Draft') {
                                                $thread_id = $row['thread_id'];
                                                $subject = $row['subject'] ?: 'No Subject';
                                                $unread_count = isset($row['unread_count']) ? intval($row['unread_count']) : 0;
                                                $message_count = intval($row['message_count']);
                                                $all_recipients = $row['all_recipients'];
                                                $latest_message = strip_tags($row['latest_message']);
                                                $latest_message = substr($latest_message, 0, 100) . '...';
                                                
                                                // Get sender info for inbox
                                                if ($mail_status == "Inbox") {
                                                    $stmt24 = $db->prepare("SELECT admin_users.*, hremp.Designation FROM admin_users 
                                                    LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
                                                    WHERE admin_users.username = :from_name");
                                                    $stmt24->execute([':from_name' => $row['from_username']]);
                                                    $sender_r = $stmt24->fetch(PDO::FETCH_ASSOC);
                                                    $designation = isset($sender_r["Designation"]) ? $sender_r["Designation"] : '';
                                                    $unitHead = (isset($sender_r["unit_head"]) && $sender_r["unit_head"] == 1) ? ', Unit Head' : '';
                                                    $display_name = $row['from_name'] . ' (' . $designation . $unitHead . ')';
                                                } else {
                                                    $display_name = $all_recipients ?: $row['on_contact_name'];
                                                }
                                                
                                                $is_unread = ($unread_count > 0);
                                            ?>
                                            <tr class="<?php echo $is_unread ? 'unread' : 'read'; ?>">
                                                <td class="check-mail">
                                                    <input type="checkbox" class="i-checks mail-checkbox" data-thread-id="<?php echo $thread_id; ?>">
                                                </td>
                                                <td class="mail-ontact">
                                                    <a href="mail_thread_view.php?t=<?php echo $thread_id; ?>">
                                                        <?php echo htmlspecialchars($display_name); ?>
                                                    </a>
                                                    <?php if ($unread_count > 0) { ?>
                                                        <span class="label label-warning pull-right" style="margin-right: 5px;">
                                                            <strong><?php echo $unread_count; ?> unread</strong>
                                                        </span>
                                                    <?php } ?>
                                                    <?php if ($message_count > 1) { ?>
                                                        <span class="label label-info pull-right" style="margin-right: 5px;">
                                                            <i class="fa fa-comments"></i> <strong><?php echo $message_count; ?> messages</strong>
                                                        </span>
                                                    <?php } ?>
                                                    <?php if ($row['attachment_status'] == "1") { ?>
                                                        <span class="label label-danger pull-right" style="margin-right: 5px;">Attachment</span>
                                                    <?php } ?>
                                                </td>
                                                <td class="mail-subject">
                                                    <a href="mail_thread_view.php?t=<?php echo $thread_id; ?>">
                                                        <strong><?php echo htmlspecialchars($subject); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($latest_message); ?></small>
                                                    </a>
                                                </td>
                                                <td class="">
                                                    <?php if ($row['attachment_status'] == "1") { ?>
                                                        <i class="fa fa-paperclip"></i>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-right mail-date">
                                                    <?php 
                                                    include_once("../inc/dd.php");
                                                    echo getTheDay3($row['last_activity']); 
                                                    ?>
                                                </td>
                                                <td class="text-right" style="width: 80px;">
                                                    <div class="btn-group">
                                                        <?php if ($mail_status == 'Trash'): ?>
                                                            <a href="mailbox.php?restore_thread=<?php echo $thread_id; ?>" 
                                                               class="btn btn-white btn-xs" 
                                                               data-toggle="tooltip" 
                                                               title="Restore conversation"
                                                               onclick="return confirm('Restore this conversation to inbox?');">
                                                                <i class="fa fa-undo text-success"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="mail_thread_view.php?t=<?php echo $thread_id; ?>" 
                                                               class="btn btn-white btn-xs" 
                                                               data-toggle="tooltip" 
                                                               title="View conversation">
                                                                <i class="fa fa-comments"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            } else {
                                                // Old single-message view for Draft/Trash
                                                $stmt24 = $db->prepare("SELECT admin_users.*, hremp.Designation FROM admin_users 
                                                LEFT JOIN hremp ON hremp.EmployeeCode = admin_users.EmployeeCode
                                                WHERE admin_users.username = :from_name");
                                                $stmt24->execute([':from_name' => $row['from_username']]);
                                                $sender_r = $stmt24->fetch(PDO::FETCH_ASSOC);
                                            ?>
                                            <tr class="<?php echo ($row['read_status'] == "0") ? "unread" : "read"; ?>">
                                                <td class="check-mail">
                                                    <input type="checkbox" class="i-checks mail-checkbox" data-mail-id="<?php echo $row['sn']; ?>">
                                                </td>
                                                <td class="mail-ontact">
                                                    <a href="<?php echo ($mail_status == 'Draft') ? 'mail_compose.php' : 'mail_detail.php'; ?>?m=<?php echo $row['sn']; ?>">
                                                        <?php
                                                        if ($mail_status == "Inbox") {
                                                            $designation = isset($sender_r["Designation"]) ? $sender_r["Designation"] : '';
                                                            $unitHead = (isset($sender_r["unit_head"]) && $sender_r["unit_head"] == 1) ? ', Unit Head' : '';
                                                            echo $row['from_name'] . ' (' . $designation . $unitHead . ')';
                                                        } else {
                                                            echo $row['on_contact_name'];
                                                        }
                                                        ?>
                                                    </a>
                                                    <?php if ($row['attachment_status'] == "1") { ?>
                                                        <span class="label label-danger pull-right">Attachment</span>
                                                    <?php } ?>
                                                </td>
                                                <td class="mail-subject">
                                                    <a href="<?php echo ($mail_status == 'Draft') ? 'mail_compose.php' : 'mail_detail.php'; ?>?m=<?php echo $row['sn']; ?>">
                                                        <?php echo empty($row['subject']) ? 'No Subject' : substr($row['subject'], 0, 50); ?>
                                                    </a>
                                                </td>
                                                <td class="">
                                                    <?php if ($row['attachment_status'] == "1") { ?>
                                                        <i class="fa fa-paperclip"></i>
                                                    <?php } ?>
                                                </td>
                                                <td class="text-right mail-date">
                                                    <?php 
                                                    include_once("../inc/dd.php");
                                                    echo getTheDay3($row['mail_date']); 
                                                    ?>
                                                </td>
                                                <td class="text-right" style="width: 80px;">
                                                    <div class="btn-group">
                                                        <a href="<?php echo ($mail_status == 'Draft') ? 'mail_compose.php' : 'mail_detail.php'; ?>?m=<?php echo $row['sn']; ?>" 
                                                           class="btn btn-white btn-xs" 
                                                           data-toggle="tooltip" 
                                                           title="View">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <?php if ($mail_status != 'Trash') { ?>
                                                        <a href="javascript:void(0);" 
                                                           class="btn btn-white btn-xs trash-single" 
                                                           data-mail-id="<?php echo $row['sn']; ?>"
                                                           data-toggle="tooltip" 
                                                           title="Move to trash">
                                                            <i class="fa fa-trash-o"></i>
                                                        </a>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        <?php } ?>
                                    </tbody>
                                </table>

                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>

            <?php include("../inc/footer.php") ?>


        </div>
    </div>

    <?php include('../modal_lock.php');; ?>
    <!-- Mainly scripts -->
    <script src="../js/jquery-2.1.1.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="../js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="../js/inspinia.js"></script>
    <script src="../js/plugins/pace/pace.min.js"></script>

    <!-- iCheck -->
    <script src="../js/plugins/iCheck/icheck.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
            
            // Bulk trash functionality
            $('.btn-bulk-trash').on('click', function() {
                var selected = [];
                $('.mail-checkbox:checked').each(function() {
                    selected.push($(this).data('thread-id'));
                });
                
                if(selected.length === 0) {
                    alert('Please select at least one conversation');
                    return;
                }
                
                if(confirm('Move ' + selected.length + ' conversation(s) to trash?')) {
                    window.location.href = 'mailbox.php?bulk_trash=' + selected.join(',');
                }
            });
            
            // Individual trash functionality
            $('.trash-single').on('click', function() {
                var threadId = $(this).data('thread-id');
                
                if(confirm('Move this conversation to trash?')) {
                    window.location.href = 'mailbox.php?trash_single=' + threadId;
                }
            });
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    <script src="../js/idle.js"></script>
</body>

</html>