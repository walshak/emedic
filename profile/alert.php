<?php
include("../inc/session.php");
include_once("../Connections/Conn.php");

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_notification'])) {
        try {
            $db->beginTransaction();

            $title = $_POST['title'];
            $message = $_POST['message'];
            $is_handover = isset($_POST['is_handover']) ? 1 : 0;
            $target_type = $_POST['target_type'];
            $target_value = $_POST['target_value'];

            if ($target_type == 'user') {
                $stmt = $db->prepare("INSERT INTO notifications (title, message, is_handover, created_by, target_user) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $message, $is_handover, $_SESSION['username'], $target_value]);
            } else {
                $stmt = $db->prepare("INSERT INTO notifications (title, message, is_handover, created_by, channel_id) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $message, $is_handover, $_SESSION['username'], $target_value]);
            }

            $notification_id = $db->lastInsertId();

            // Create notification status entries for recipients
            if ($target_type == 'user') {
                $stmt = $db->prepare("INSERT INTO notification_status (notification_id, employee_code) 
                    VALUES (?, ?)");
                $stmt->execute([$notification_id, $target_value]);
            } else {
                $stmt = $db->prepare("SELECT employee_code FROM notification_channel_subscriptions WHERE channel_id = ?");
                $stmt->execute([$target_value]);
                $recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $status_stmt = $db->prepare("INSERT INTO notification_status (notification_id, employee_code) 
                    VALUES (?, ?)");
                foreach ($recipients as $recipient) {
                    $status_stmt->execute([$notification_id, $recipient['employee_code']]);
                }
            }

            $db->commit();

            // REDIRECT AFTER SUCCESS
            $_SESSION['success_message'] = 'Notification sent successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Error sending notification: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error sending notification: ' . $e->getMessage();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    if (isset($_POST['create_channel'])) {
        try {
            $stmt = $db->prepare("INSERT INTO notification_channels (channel_name, channel_type, created_by) 
                VALUES (?, 'custom', ?)");
            $stmt->execute([$_POST['channel_name'], $_SESSION['username']]);
            // REDIRECT AFTER SUCCESS
            $_SESSION['success_message'] = 'Channel created successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            error_log("Error creating channel: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error creating channel: ' . $e->getMessage();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    if (isset($_POST['toggle_subscription'])) {
        try {
            $channel_id = (int)$_POST['channel_id'];
            $action = $_POST['action'];

            if ($action == 'subscribe') {
                $stmt = $db->prepare("INSERT INTO notification_channel_subscriptions (channel_id, employee_code) 
                    VALUES (?, ?)");
                $stmt->execute([$channel_id, $_SESSION['EmployeeCode']]);
            } else {
                $stmt = $db->prepare("DELETE FROM notification_channel_subscriptions 
                    WHERE channel_id = ? AND employee_code = ?");
                $stmt->execute([$channel_id, $_SESSION['EmployeeCode']]);
            }

            // REDIRECT AFTER SUCCESS
            $_SESSION['success_message'] = 'Subscription Toggled!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            error_log("Error toggling subscription: " . $e->getMessage());
            $_SESSION['error_message'] = 'Error creating channel: ' . $e->getMessage();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Get user's notifications
// Fetch filter options
$senders = $db->query("SELECT DISTINCT username, fullname FROM admin_users WHERE status=1")->fetchAll(PDO::FETCH_ASSOC);
$receiver_staff_options = $db->query("SELECT DISTINCT EmployeeCode, fullname FROM admin_users WHERE status=1")->fetchAll(PDO::FETCH_ASSOC);

// Get filter parameters with null checks
$is_handover = isset($_GET['is_handover']) ? $_GET['is_handover'] : '';
$sender = isset($_GET['sender']) ? $_GET['sender'] : '';
$receiver_staff = isset($_GET['receiver_staff']) ? $_GET['receiver_staff'] : '';
$receiver_channel = isset($_GET['receiver_channel']) ? $_GET['receiver_channel'] : '';
$is_acknowledged = isset($_GET['is_acknowledged']) ? $_GET['is_acknowledged'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('yesterday'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Base query
$sql = "
    SELECT n.*, ns.is_acknowledged, ns.acknowledged_at, ns.snoozed_until, 
           au1.fullname as sender_name, au2.fullname as rec_name, nc.channel_name as channel_name
    FROM notifications n 
    INNER JOIN notification_status ns ON n.notification_id = ns.notification_id 
    LEFT JOIN admin_users au1 ON au1.username = n.created_by
    LEFT JOIN admin_users au2 ON au2.EmployeeCode = n.target_user
    LEFT JOIN notification_channels nc ON nc.channel_id = n.channel_id
    WHERE ns.employee_code = ?
";

$params = array($_SESSION['EmployeeCode']);

// Add filters
if ($is_handover !== '' && in_array($is_handover, array('0', '1'))) {
    $sql .= " AND n.is_handover = ?";
    $params[] = $is_handover;
}

if (!empty($sender)) {
    $sql .= " AND n.created_by = ?";
    $params[] = $sender;
}

if (!empty($receiver_staff)) {
    $sql .= " AND n.target_user = ?";
    $params[] = $receiver_staff;
}

if (!empty($receiver_channel)) {
    $sql .= " AND n.channel_id = ?";
    $params[] = $receiver_channel;
}

if ($is_acknowledged !== '' && in_array($is_acknowledged, array('0', '1'))) {
    $sql .= " AND ns.is_acknowledged = ?";
    $params[] = $is_acknowledged;
}

// Add date range
$sql .= " AND n.created_at BETWEEN ? AND ?";
$params[] = $start_date . ' 00:00:00';
$params[] = $end_date . ' 23:59:59';

// Add sorting
$sql .= " ORDER BY n.created_at DESC";

// Execute query
$notifications_stmt = $db->prepare($sql);
$notifications_stmt->execute($params);
$notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter duplicates by notification ID and recipient combination
$filteredNotifications = [];
foreach ($notifications as $notification) {
    $key = $notification['notification_id'] . '-' .
        ($notification['channel_name'] ?: $notification['rec_name']);

    if (!isset($filteredNotifications[$key])) {
        $filteredNotifications[$key] = $notification;
    }
}

// Reset array keys
$notifications = array_values($filteredNotifications);

// Get available channels
if ($_SESSION['unit_head'] == 1) {
    // Show unit heads all available channels
    $channels_stmt = $db->prepare("
        SELECT nc.*, cs.subscription_id 
        FROM notification_channels nc 
        LEFT JOIN notification_channel_subscriptions cs 
            ON nc.channel_id = cs.channel_id 
            AND cs.employee_code = ? 
        WHERE nc.channel_type != 'custom'
        ORDER BY nc.channel_name
    ");
} else {
    // Show others only their default channels
    $channels_stmt = $db->prepare("
        SELECT nc.*, cs.subscription_id 
        FROM notification_channels nc 
        INNER JOIN notification_channel_subscriptions cs 
            ON nc.channel_id = cs.channel_id 
            AND cs.employee_code = ? 
        WHERE nc.channel_type != 'custom'
        ORDER BY nc.channel_name
    ");
}

// Execute the query
$channels_stmt->execute([$_SESSION['EmployeeCode']]);
$channels = $channels_stmt->fetchAll(PDO::FETCH_ASSOC);

// Show everyone custom channels
$custom_channels_stmt = $db->prepare("
    SELECT nc.*, cs.subscription_id 
    FROM notification_channels nc 
    LEFT JOIN notification_channel_subscriptions cs 
        ON nc.channel_id = cs.channel_id 
        AND cs.employee_code = ? 
    WHERE nc.channel_type = 'custom'
    ORDER BY nc.channel_name
");

// Execute the custom channel query
$custom_channels_stmt->execute([$_SESSION['EmployeeCode']]);
$custom_channels = $custom_channels_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>

<head>
    <?php include("../inc/header.php"); ?>
    <style>
        .message-container {
            max-width: 600px;
        }

        .message-meta {
            margin-top: 8px;
            font-size: 0.9em;
        }

        .unread {
            background-color: #f9f9f9;
        }

        .acknowledge-btn {
            padding: 3px 8px;
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php include("nav_side.php"); ?>
        <div id="page-wrapper" class="gray-bg">
            <?php include("nav_header.php"); ?>
            <div class="wrapper wrapper-content">
                <?php
                // Display messages if they exist
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                    unset($_SESSION['error_message']);
                }
                ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Manage Alerts / Handover Notes</h5>
                            </div>
                            <div class="ibox-content">
                                <!-- Send Notification Form -->
                                <form method="post" class="form-horizontal">
                                    <input type="hidden" name="send_notification" value="1">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Title</label>
                                        <div class="col-sm-10">
                                            <input type="text" name="title" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Message</label>
                                        <div class="col-sm-10">
                                            <textarea name="message" class="form-control" rows="4" required></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="is_handover" id="is_handover" value="1"> This is a Handover Note
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Add this warning div -->
                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10" id="handover-warning"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Send To</label>
                                        <div class="col-sm-4">
                                            <select name="target_type" class="form-control" id="target_type">
                                                <option value="channel">Channel</option>
                                                <option value="user">Specific User</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <select name="target_value" class="form-control" id="target_value">
                                                <?php
                                                $channels_list = $db->prepare("SELECT nc.*, cs.subscription_id 
                                                        FROM notification_channels nc 
                                                        INNER JOIN notification_channel_subscriptions cs ON nc.channel_id = cs.channel_id 
                                                            AND cs.employee_code = ? ORDER BY nc.channel_name
                                                    ");
                                                $channels_list->execute([$_SESSION['EmployeeCode']]);
                                                $channels_list = $channels_list->fetchAll(PDO::FETCH_ASSOC);
                                                foreach ($channels_list as $channel) {
                                                    echo "<option value='{$channel['channel_id']}' class='channel-option'>" .
                                                        htmlspecialchars($channel['channel_name']) . "</option>";
                                                }

                                                $users = $db->query("SELECT EmployeeCode, fullname FROM admin_users WHERE status = 1");
                                                foreach ($users as $user) {
                                                    echo "<option value='{$user['EmployeeCode']}' class='user-option' style='display:none'>" .
                                                        htmlspecialchars($user['fullname']) . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-offset-2 col-sm-10">
                                            <button type="submit" class="btn btn-primary">Send Alert / Handover Note</button>
                                        </div>
                                    </div>
                                </form>
                                <!-- Filter Form -->
                                <form method="GET" class="notification-filters well" style="padding:20px; margin-bottom:20px;">
                                    <div class="row">
                                        <div class="col-md-2 form-group">
                                            <label>Handover Status:</label>
                                            <select name="is_handover" class="form-control">
                                                <option value="">All</option>
                                                <option value="1" <?php echo $is_handover === '1' ? 'selected' : ''; ?>>Handover Notes</option>
                                                <option value="0" <?php echo $is_handover === '0' ? 'selected' : ''; ?>>Generic Alerts</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label>Sender:</label>
                                            <select name="sender" class="form-control">
                                                <option value="">All Senders</option>
                                                <?php foreach ($senders as $s): ?>
                                                    <option value="<?php echo htmlspecialchars($s['username'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $sender === $s['username'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($s['fullname'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label>Receiver (Staff):</label>
                                            <select name="receiver_staff" class="form-control">
                                                <option value="">All Staff</option>
                                                <?php foreach ($receiver_staff_options as $rs): ?>
                                                    <option value="<?php echo htmlspecialchars($rs['EmployeeCode'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $receiver_staff === $rs['EmployeeCode'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($rs['fullname'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label>Channel:</label>
                                            <select name="receiver_channel" class="form-control">
                                                <option value="">All Channels</option>
                                                <?php foreach ($channels_list as $c): ?>
                                                    <option value="<?php echo htmlspecialchars($c['channel_id'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $receiver_channel === $c['channel_id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($c['channel_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label>Acknowledgment:</label>
                                            <select name="is_acknowledged" class="form-control">
                                                <option value="">All</option>
                                                <option value="1" <?php echo $is_acknowledged === '1' ? 'selected' : ''; ?>>Acknowledged</option>
                                                <option value="0" <?php echo $is_acknowledged === '0' ? 'selected' : ''; ?>>Unacknowledged</option>
                                            </select>
                                        </div>

                                        <div class="col-md-8 form-group">
                                            <label>Date Range:</label>
                                            <div class="input-daterange input-group">
                                                <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8'); ?>">
                                                <span class="input-group-addon">to</span>
                                                <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-2 form-group m-5">
                                            <button type="submit" class="btn btn-primary" id="filter_btn">Filter</button>
                                            <a href="?" class="btn btn-default">Reset</a>
                                        </div>
                                    </div>
                                </form>

                                <!-- Notifications Table -->
                                <div class="table-responsive">
                                    <table id="notifications-table" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Message Details</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($notifications as $notification): ?>
                                                <tr class="<?php echo !$notification['is_acknowledged'] ? 'unread' : ''; ?>">
                                                    <td>
                                                        <span class="label label-<?php echo ($notification['is_handover'] == 1) ? 'success' : 'default' ?>">
                                                            <?php echo ($notification['is_handover'] == 1) ? 'Handover' : 'Alert' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="message-container">
                                                            <strong><?php echo htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                            <div class="text-muted small">
                                                                <?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </div>
                                                            <div class="message-meta">
                                                                <span class="text-primary">From: <?php echo htmlspecialchars($notification['sender_name'], ENT_QUOTES, 'UTF-8'); ?></span> |
                                                                <span class="text-info">
                                                                    To: <?php echo $notification['channel_name'] ?
                                                                            htmlspecialchars($notification['channel_name'], ENT_QUOTES, 'UTF-8') :
                                                                            htmlspecialchars($notification['rec_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                                </span> |
                                                                <span class="text-muted"><?php echo htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <style>
                                                            .acknowledgement-log ul {
                                                                margin-top: 5px;
                                                                padding-left: 15px;
                                                                max-height: 100px;
                                                                overflow-y: auto;
                                                            }

                                                            .acknowledgement-log li {
                                                                margin-bottom: 3px;
                                                            }
                                                        </style>
                                                        <!-- Existing status column -->
                                                        <div>
                                                            <?php if ($notification['is_acknowledged']): ?>
                                                                <span class="text-success">Acknowledged<br>
                                                                    <small><?php echo htmlspecialchars($notification['acknowledged_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="text-danger">Pending</span>
                                                            <?php endif; ?>

                                                            <hr>
                                                            <?php if (!$notification['is_acknowledged']): ?>
                                                                <a href="#" class="btn btn-xs btn-success acknowledge-btn"
                                                                    data-id="<?php echo htmlspecialchars($notification['notification_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <i class="glyphicon glyphicon-ok"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                —
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if ($_SESSION['unit_head'] == 1): ?>
                                                            <!-- Acknowledgement Log for Unit Heads -->
                                                            <hr>
                                                            <?php
                                                            // Fetch acknowledgement details for this notification
                                                            $ack_stmt = $db->prepare("
                                                                            SELECT au.fullname, ns.acknowledged_at 
                                                                            FROM notification_status ns
                                                                            JOIN admin_users au ON au.EmployeeCode = ns.employee_code
                                                                            WHERE ns.notification_id = ? AND ns.is_acknowledged = 1
                                                                            ORDER BY ns.acknowledged_at
                                                                        ");
                                                            $ack_stmt->execute([$notification['notification_id']]);
                                                            $acknowledgements = $ack_stmt->fetchAll(PDO::FETCH_ASSOC);

                                                            if (!empty($acknowledgements)): ?>
                                                                <div class="acknowledgement-log small">
                                                                    <strong>Acknowledgement Log:</strong>
                                                                    <ul class="list-unstyled">
                                                                        <?php foreach ($acknowledgements as $ack): ?>
                                                                            <li>
                                                                                <?php echo htmlspecialchars($ack['fullname'], ENT_QUOTES, 'UTF-8'); ?>:
                                                                                <?php echo htmlspecialchars($ack['acknowledged_at'], ENT_QUOTES, 'UTF-8'); ?>
                                                                            </li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="text-muted small">No acknowledgements logs yet</div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="ibox float-e-margins">
                            <div class="ibox-title">
                                <h5>Notification Channels</h5>
                            </div>
                            <div class="ibox-content">
                                <!-- Create Channel Form -->
                                <form method="post" class="form-horizontal">
                                    <input type="hidden" name="create_channel" value="1">
                                    <div class="form-group">
                                        <div class="col-sm-8">
                                            <input type="text" name="channel_name" class="form-control"
                                                placeholder="New Channel Name" required>
                                        </div>
                                        <div class="col-sm-4">
                                            <button type="submit" class="btn btn-primary">Create</button>
                                        </div>
                                    </div>
                                </form>

                                <!-- Channels List -->
                                <div class="channels-list">
                                    <h4>My Subscriptions/ Default Channels</h4>
                                    <table class="table table-striped table-hover dataTable-example">
                                        <thead>
                                            <tr>
                                                <th>Channel Name</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($channels as $channel): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($channel['channel_name']); ?></td>
                                                    <td class="text-right">
                                                        <form method="post" style="display: inline;">
                                                            <input type="hidden" name="toggle_subscription" value="1">
                                                            <input type="hidden" name="channel_id"
                                                                value="<?php echo $channel['channel_id']; ?>">
                                                            <input type="hidden" name="action"
                                                                value="<?php echo $channel['subscription_id'] ? 'unsubscribe' : 'subscribe'; ?>">
                                                            <?php if ($_SESSION['unit_head']  == 1): ?>
                                                                <button type="submit" onclick="return confirm('Are you sure you wish to proceed?')"
                                                                    class="btn btn-xs <?php echo $channel['subscription_id'] ?
                                                                                            'btn-danger' : 'btn-success'; ?>">
                                                                    <?php echo $channel['subscription_id'] ? 'Unsubscribe' : 'Subscribe'; ?>
                                                                </button>
                                                            <?php endif ?>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <hr>
                                    <h4>My Custom Channels</h4>
                                    <table class="table table-striped table-hover dataTable-example">
                                        <thead>
                                            <tr>
                                                <th>Channel Name</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($custom_channels as $channel): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($channel['channel_name']); ?></td>
                                                    <td class="text-right">
                                                        <form method="post" style="display: inline;">
                                                            <input type="hidden" name="toggle_subscription" value="1">
                                                            <input type="hidden" name="channel_id"
                                                                value="<?php echo $channel['channel_id']; ?>">
                                                            <input type="hidden" name="action"
                                                                value="<?php echo $channel['subscription_id'] ? 'unsubscribe' : 'subscribe'; ?>">
                                                            <button type="submit" onclick="return confirm('Are you sure you wish to proceed?')"
                                                                class="btn btn-xs <?php echo $channel['subscription_id'] ?
                                                                                        'btn-danger' : 'btn-success'; ?>">
                                                                <?php echo $channel['subscription_id'] ? 'Unsubscribe' : 'Subscribe'; ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include("../inc/footer.php"); ?>
        </div>
    </div>

    <?php include("../inc/footer_scripts.php"); ?>
    <script src="../js/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../js/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script src="../js/plugins/dataTables/dataTables.responsive.js"></script>
    <script src="../js/plugins/dataTables/dataTables.tableTools.min.js"></script>
    <script src="../js/plugins/dataTables/datatables.min.js"></script>


    <!-- Custom JavaScript for notification handling -->
    <script>
        $(document).ready(function() {
            $('#notifications-table').DataTable({
                "order": [
                    [1, 'desc']
                ], // Order by the hidden date in message details
                "columnDefs": [{
                        "orderable": false,
                        "targets": [0, 2, ] // Only message details column is orderable
                    },
                    {
                        "type": "date",
                        "targets": 1,
                        "render": function(data, type, row) {
                            if (type === 'sort') {
                                return $(row[1]).find('.message-meta span.text-muted').text().trim();
                            }
                            return data;
                        }
                    },
                    {
                        "width": "45%",
                        "targets": [1]
                    }, // Message details column width
                    {
                        "width": "10%",
                        "targets": [0, ]
                    }, // Type and Actions columns
                ],
                "pageLength": 25,
                "stateSave": true,
                dom: '<"html5buttons"B>lTfgitp',
                buttons: ['csv', 'print', 'copy'],
                "language": {
                    "search": "Filter messages:"
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if dates exist in URL
            const urlParams = new URLSearchParams(window.location.search);
            const hasDates = urlParams.has('start_date') && urlParams.has('end_date');

            // Auto-submit if dates are missing
            if (!hasDates) {
                const form = document.querySelector('form.notification-filters');
                // Set default dates in inputs before submitting
                const startDateInput = form.querySelector('[name="start_date"]');
                const endDateInput = form.querySelector('[name="end_date"]');

                if (!startDateInput.value) {
                    startDateInput.value = '<?php echo date("Y-m-d", strtotime("yesterday")); ?>';
                }
                if (!endDateInput.value) {
                    endDateInput.value = '<?php echo date("Y-m-d"); ?>';
                }

                form.submit();
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Track handover checkbox state
            $('#is_handover').change(function() {
                const isHandoverChecked = $(this).is(':checked');
                const targetTypeSelect = $('#target_type');
                const handoverWarning = $('#handover-warning');

                if (isHandoverChecked) {
                    // Force channel selection
                    targetTypeSelect.val('channel');
                    targetTypeSelect.prop('disabled', true);

                    // Show warning message
                    handoverWarning.html(
                        '<div class="alert alert-warning">' +
                        '<strong>Note:</strong> Handover notes can only be sent to channels.' +
                        '</div>'
                    ).show();

                    // Hide user options, show only channel options
                    $('.user-option').hide();
                    $('.channel-option').show();
                } else {
                    // Restore normal functionality
                    targetTypeSelect.prop('disabled', false);
                    handoverWarning.hide();

                    // Trigger change to reset target value visibility
                    targetTypeSelect.trigger('change');
                }
            });

            // Initial setup for target type dropdown
            $('#target_type').change(function() {
                if ($(this).val() == 'user') {
                    $('.channel-option').hide();
                    $('.user-option').show();
                } else {
                    $('.channel-option').show();
                    $('.user-option').hide();
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Toggle between user and channel selection
            $('#target_type').change(function() {
                if ($(this).val() == 'user') {
                    $('.channel-option').hide();
                    $('.user-option').show();
                } else {
                    $('.channel-option').show();
                    $('.user-option').hide();
                }
            });

            // Handle acknowledge button clicks
            $('.acknowledge-btn').click(function(e) {
                e.preventDefault();
                var notificationId = $(this).data('id');
                var $btn = $(this);

                $.post('notification_fetch.php', {
                    notification_id: notificationId
                }, function(response) {
                    if (response.success) {
                        $btn.closest('.notification-item').removeClass('unread');
                        $btn.parent().html('| Acknowledged: ' + response.acknowledged_at);
                    }
                }, 'json');
            });
        });
    </script>

</body>

</html>