<!-- notification-modal.php -->
<?php

// Include this file at the bottom of any page where you want notifications
///session_start();
///include_once("../Connections/Conn.php");

$employeeCode = $_SESSION['EmployeeCode'];
// Function to get user's default channels
function getUserDefaultChannels($db, $employeeCode)
{
    $stmt = $db->prepare("SELECT a.*, h.Department, h.Designation 
        FROM admin_users a 
        JOIN hremp h ON a.EmployeeCode = h.EmployeeCode 
        WHERE a.EmployeeCode = ?");
    $stmt->execute([$employeeCode]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'department' => $user['Department'],
        'designation' => $user['Designation'],
        'rights' => $user['rights']
    ];
}
//sub the user to his default channels based on department, designation and Rights
function subscribeToDefaultChannels($db, $employeeCode)
{
    try {
        // Get user's default channels
        $userChannels = getUserDefaultChannels($db, $employeeCode);

        // Begin transaction
        $db->beginTransaction();

        // Prepare subscription check statement
        $checkStmt = $db->prepare("
            SELECT channel_id 
            FROM notification_channel_subscriptions 
            WHERE employee_code = ? AND channel_id = ?
        ");

        // Prepare subscription insert statement
        $subscribeStmt = $db->prepare("
            INSERT INTO notification_channel_subscriptions (channel_id, employee_code) 
            VALUES (?, ?)
        ");

        // Get channel IDs for department
        $deptStmt = $db->prepare("
            SELECT channel_id 
            FROM notification_channels 
            WHERE channel_type = 'department' 
            AND reference_value = ?
        ");
        $deptStmt->execute([$userChannels['department']]);
        $deptChannels = $deptStmt->fetchAll(PDO::FETCH_COLUMN);

        // Get channel IDs for designation
        $desigStmt = $db->prepare("
            SELECT channel_id 
            FROM notification_channels 
            WHERE channel_type = 'designation' 
            AND channel_name = ?
        ");
        $desigStmt->execute(['Designation-' . $userChannels['designation']]);
        $desigChannels = $desigStmt->fetchAll(PDO::FETCH_COLUMN);

        // Get channel IDs for rights
        $rightsStmt = $db->prepare("
            SELECT channel_id 
            FROM notification_channels 
            WHERE channel_type = 'rights' 
            AND channel_name = ?
        ");
        $rightsStmt->execute(['Rights-' . $userChannels['rights']]);
        $rightsChannels = $rightsStmt->fetchAll(PDO::FETCH_COLUMN);

        // Combine all default channels
        $defaultChannels = array_merge($deptChannels, $desigChannels, $rightsChannels);

        // print_r($defaultChannels);
        // die();

        // Subscribe to each channel if not already subscribed
        foreach ($defaultChannels as $channelId) {
            // Check if already subscribed
            $checkStmt->execute([$employeeCode, $channelId]);
            if (!$checkStmt->fetch()) {
                // Not subscribed, so add subscription
                $subscribeStmt->execute([$channelId, $employeeCode]);
            }
        }

        $db->commit();
        return true;
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Error subscribing to default channels: " . $e->getMessage());
        return false;
    }
}

function check_unvalidated_claims($db)
{
    try {
        // Clean old notifications
        $cleanupStmt = $db->prepare("
            DELETE FROM claim_notifications 
            WHERE notification_date < DATE_SUB(NOW(), INTERVAL 1 WEEK)
        ");
        $cleanupStmt->execute();

        // Get recent unnotified claims
        $stmt = $db->prepare("
            SELECT ps.*, e.surname, e.fname, e.insurance, e.hmo_no, e.employer_no
            FROM patient_ap_services ps
            JOIN enrollee e ON ps.hospital_no = e.hospital_no
            LEFT JOIN claim_notifications cn ON ps.sn = cn.service_sn
            WHERE ps.pay_mode = 'claim'
            AND (ps.claim_valid_by IS NULL OR ps.claim_valid_by = '')
            AND ps.created_by IS NOT NULL
            AND ps.date_entry >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
            AND cn.id IS NULL
        ");
        $stmt->execute();
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($claims)) return;

        $claimsList = array_map(function ($claim) {
            return "Patient: {$claim['fname']} {$claim['surname']}<br>" .
                "Hospital No: {$claim['hospital_no']}<br>" .
                "Insurance: {$claim['insurance']}<br>" .
                "HMO No: {$claim['hmo_no']}<br>" .
                "Service: {$claim['item_services']}<br>" .
                "Amount: {$claim['claim_amt']}<br>" .
                "Date: {$claim['date_entry']}<br><br>";
        }, $claims);

        $message = "The following claims from the past week require validation:<br><br>" .
            implode("\n\n", $claimsList);

        $notifications = [
            global_notify_($db, 'rights', '%RE%', 'Pending Claims Validation', $message),
            global_notify_($db, 'designation', '%HMO%', 'Pending Claims Validation', $message),
            global_notify_($db, 'department', '%HMO%', 'Pending Claims Validation', $message)
        ];

        $insertStmt = $db->prepare("
            INSERT INTO claim_notifications (service_sn, notification_id) 
            VALUES (?, ?)
        ");

        foreach ($claims as $claim) {
            $insertStmt->execute([$claim['sn'], $notifications[0]['notification_id']]);
        }

        return $notifications;
    } catch (Exception $e) {
        error_log("Error checking claims: " . $e->getMessage());
        return false;
    }
}
function check_pharmacy_post($db)
{
    try {
        $cleanupStmt = $db->prepare("
        DELETE FROM claim_notifications
        WHERE DATE(notification_date) <> CURDATE() AND station='PH'");
        $cleanupStmt->execute();

        $stmt = $db->prepare("
        SELECT ps.*, e.surname, e.fname, e.insurance, e.hmo_no, e.employer_no
        FROM patient_ap_services ps
        JOIN enrollee e ON ps.hospital_no = e.hospital_no
        LEFT JOIN claim_notifications cn ON ps.sn = cn.service_sn
        WHERE ps.serv_group = 'Pharmacy'
        AND ps.invoice_status = '0'
        AND ps.created_by IS NOT NULL
        AND ps.date_entry >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        AND cn.id IS NULL
    ");
        $stmt->execute();
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if (empty($claims)) return;

        $claimsList = array_map(function ($claim) {
            return "Patient: {$claim['fname']} {$claim['surname']}<br>" .
                "Hospital No: {$claim['hospital_no']}<br>" .
                "Insurance: {$claim['insurance']}<br>" .
                "HMO No: {$claim['hmo_no']}<br>" .
                "Service: {$claim['item_services']}<br>" .
                "Time: " . date('h:i A', strtotime($claim['date_entry'])) . "<br><br>";
        }, $claims);

        $message = "The following Requests require Invoice:<br><br>" .
            implode("\n\n", $claimsList);


        $notifications = [
            global_notify_($db, 'rights', '%PH%', 'Pending Pharmacy Requests', $message),
            global_notify_($db, 'department', '%Pharmacy%', 'Pending Pharmacy Requests', $message)
        ];

        // ✅ Safely insert claim notifications if valid
        if (!empty($notifications) && isset($notifications[0]['notification_id'])) {
            $notification_id = $notifications[0]['notification_id'];
            $insertStmt = $db->prepare("
            INSERT INTO claim_notifications (service_sn, notification_id, station)
            VALUES (?, ?, ?)
        ");

            foreach ($claims as $claim) {
                $insertStmt->execute([$claim['sn'], $notification_id, 'PH']);
            }
        }

        return $notifications;
    } catch (Exception $e) {
        error_log("Error checking claims: " . $e->getMessage());
        return false;
    }
}



?>
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">New Notifications</h4>
            </div>
            <div class="modal-body">
                <div id="notification-list"></div>
            </div>
            <div class="modal-footer">
                <div class="bulk-actions mb-3">
                    <button class="btn btn-primary btn-sm acknowledge-all-btn">Acknowledge All</button>
                    <div class="btn-group">
                        <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            Snooze All <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" class="snooze-all-btn" data-time="60">1 minute</a></li>
                            <li><a href="#" class="snooze-all-btn" data-time="300">5 minutes</a></li>
                            <li><a href="#" class="snooze-all-btn" data-time="900">15 minutes</a></li>
                            <li><a href="#" class="snooze-all-btn" data-time="3600">1 hour</a></li>
                            <li><a href="#" class="snooze-all-btn" data-time="86400">1 day</a></li>
                        </ul>
                    </div>
                    <a href="../profile/alert.php" class="btn btn-success btn-sm">Manage My Notifications/ Handover Notes</a>
                </div>
            </div>
        </div>
    </div>

    <audio id="notification-sound" preload="auto">
        <source src="../sounds/notification.wav" type="audio/wav">
    </audio>

    <!-- Notification Check AJAX Script -->
    <script>
        $(document).ready(function() {
            var lastNotificationId = 0;
            var snoozedNotifications = {};



            // Function to check and request notification permission
            function requestNotificationPermission() {
                if (Notification.permission === "default") {
                    Notification.requestPermission().then(permission => {
                        if (permission === "granted") {
                            console.log("Notification permission granted.");
                        } else {
                            console.log("Notification permission denied.");
                        }
                    });
                }
            }

            // **Request permission when page loads**
            requestNotificationPermission();


            // Function to show a desktop notification
            function showDesktopNotification(title, message) {
                if (Notification.permission === "granted") {
                    var notification = new Notification('From Webmedic: ' + title, {
                        body: message,
                        icon: "/favicon.ico" // webemdic icon path
                    });

                    // Play the notification sound
                    $('#notification-sound')[0].play();

                    // Close notification after 5 seconds
                    setTimeout(() => notification.close(), 5000);

                    // Add a click event to bring the user to the notifications page
                    notification.onclick = function() {
                        window.focus();
                        window.location.href = "/profile/alert.php"; // Change to the correct notification page
                    };
                }
            }

            function checkNotifications() {


                $.ajax({
                    url: '../profile/notification_fetch.php',
                    method: 'GET',
                    data: {
                        last_id: lastNotificationId
                    },
                    success: function(response) {
                        try {
                            var data = (response);
                            if (data.notifications && data.notifications.length > 0) {
                                var notificationHtml = '';
                                var playSound = false;

                                data.notifications.forEach(function(notification) {
                                    // Skip snoozed notifications
                                    if (snoozedNotifications[notification.notification_id]) {
                                        var snoozeTime = snoozedNotifications[notification.notification_id];
                                        if (new Date() < new Date(snoozeTime)) {
                                            return;
                                        } else {
                                            // Snooze period expired, remove from snoozed list
                                            delete snoozedNotifications[notification.notification_id];
                                        }
                                    }

                                    // Update last notification ID
                                    if (notification.notification_id > lastNotificationId) {
                                        lastNotificationId = notification.notification_id;
                                        playSound = true;

                                        // **Trigger Desktop Notification**
                                        showDesktopNotification(
                                            notification.title,
                                            `From: ${notification.sender}\n${notification.message}`
                                        );
                                    }

                                    notificationHtml += `
                                    <div class="notification-item ${!notification.acknowledged ? 'unread' : ''}" 
                                        data-id="${notification.notification_id}">
                                        <div class="notification-header">
                                            <h4>${notification.title}</h4>
                                            <span class="label label-${notification.is_handover ? 'success' : 'default'}">
                                                ${notification.is_handover ? 'Handover' : 'Alert'}
                                            </span>
                                        </div>
                                        <div class="notification-body">
                                            <p>${notification.message}</p>
                                            <div class="notification-meta">
                                                <small class="text-muted">
                                                    <strong>From:</strong> ${notification.sender}<br>
                                                    <strong>To:</strong> ${notification.receiver}
                                                </small>
                                            </div>
                                            <small class="text-muted">Received: ${notification.created_at}</small>
                                        </div>
                                        <div class="notification-actions">
                                            ${(notification.acknowledged == '0') ? `
                                            <button class="btn btn-xs btn-primary acknowledge-btn" 
                                                data-id="${notification.notification_id}">Acknowledge</button>
                                            <div class="btn-group">
                                                <button class="btn btn-xs btn-default dropdown-toggle" 
                                                    data-toggle="dropdown">
                                                    Snooze <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#" class="snooze-btn" data-time="60">1 minute</a></li>
                                                    <li><a href="#" class="snooze-btn" data-time="300">5 minutes</a></li>
                                                    <li><a href="#" class="snooze-btn" data-time="900">15 minutes</a></li>
                                                    <li><a href="#" class="snooze-btn" data-time="3600">1 hour</a></li>
                                                    <li><a href="#" class="snooze-btn" data-time="86400">1 day</a></li>
                                                </ul>
                                            </div>
                                            ` : '<span class="text-success">Acknowledged</span>'}
                                        </div>
                                    </div>
                                `;
                                });

                                if (notificationHtml) {
                                    $('#notification-list').html(notificationHtml);
                                    $('#notificationModal').modal('show');
                                    if (playSound) {
                                        $('#notification-sound')[0].play();
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing notifications:', e);
                        }
                    }
                });
            }

            // Check for notifications every 30 seconds
            setInterval(checkNotifications, 30000);
            // Initial check
            checkNotifications();

            // Handle acknowledgment
            $(document).on('click', '.acknowledge-btn', function(e) {
                e.preventDefault();
                var notificationId = $(this).data('id');
                var $item = $(this).closest('.notification-item');

                $.ajax({
                    url: '../profile/notification_fetch.php',
                    method: 'POST',
                    data: {
                        notification_id: notificationId
                    },
                    success: function(response) {
                        try {
                            var data = (response);
                            if (data.success) {
                                $item.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#notification-list').children().length === 0) {
                                        $('#notificationModal').modal('hide');
                                    }
                                });
                            }
                        } catch (e) {
                            console.error('Error processing acknowledgment:', e);
                        }
                    }
                });
            });

            // Handle snooze
            $(document).on('click', '.snooze-btn', function(e) {
                e.preventDefault();

                var snoozeTime = parseInt($(this).data('time')); // Snooze duration in seconds
                var $item = $(this).closest('.notification-item');
                var notificationId = $item.data('id');

                // Store in snoozed notifications (optional, for frontend reference)
                snoozedNotifications[notificationId] = new Date().getTime() + snoozeTime * 1000;

                $.ajax({
                    url: '../profile/notification_fetch.php',
                    method: 'POST',
                    data: {
                        notification_id: notificationId,
                        snooze_duration: snoozeTime, // Send only duration, let the server compute the time
                    },
                    success: function(response) {
                        try {
                            var data = response;
                            if (data.success) {
                                $item.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#notification-list').children().length === 0) {
                                        $('#notificationModal').modal('hide');
                                    }
                                });
                            }
                        } catch (e) {
                            console.error('Error processing snooze:', e);
                        }
                    }
                });
            });

            $(document).on('click', '.acknowledge-all-btn', function(e) {
                e.preventDefault();
                var $unacknowledgedItems = $('.notification-item:has(.acknowledge-btn)');
                var notificationIds = $unacknowledgedItems.map(function() {
                    return $(this).data('id');
                }).get();

                if (notificationIds.length === 0) return;

                $.ajax({
                    url: '../profile/notification_fetch.php',
                    method: 'POST',
                    data: {
                        bulk_acknowledge: true,
                        notification_ids: notificationIds
                    },
                    success: function(response) {
                        try {
                            var data = response;
                            if (data.success) {
                                $unacknowledgedItems.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#notification-list').children().length === 0) {
                                        $('#notificationModal').modal('hide');
                                    }
                                });
                            }
                        } catch (e) {
                            console.error('Error processing bulk acknowledgment:', e);
                        }
                    }
                });
            });

            $(document).on('click', '.snooze-all-btn', function(e) {
                e.preventDefault();
                var snoozeTime = parseInt($(this).data('time'));
                var $unacknowledgedItems = $('.notification-item:has(.acknowledge-btn)');
                var notificationIds = $unacknowledgedItems.map(function() {
                    return $(this).data('id');
                }).get();

                if (notificationIds.length === 0) return;

                // Update frontend snoozed notifications
                notificationIds.forEach(function(id) {
                    snoozedNotifications[id] = new Date().getTime() + snoozeTime * 1000;
                });

                $.ajax({
                    url: '../profile/notification_fetch.php',
                    method: 'POST',
                    data: {
                        bulk_snooze: true,
                        notification_ids: notificationIds,
                        snooze_duration: snoozeTime
                    },
                    success: function(response) {
                        try {
                            var data = response;
                            if (data.success) {
                                $unacknowledgedItems.fadeOut(300, function() {
                                    $(this).remove();
                                    if ($('#notification-list').children().length === 0) {
                                        $('#notificationModal').modal('hide');
                                    }
                                });
                            }
                        } catch (e) {
                            console.error('Error processing bulk snooze:', e);
                        }
                    }
                });
            });


            // Modal close handler
            $('#notificationModal').on('hidden.bs.modal', function() {
                // Clear the notification list when modal is closed

                $('#notification-list').html('');
            });
        });
    </script>

    <style>
        .notification-item {
            border-bottom: 1px solid #eee;
            padding: 15px;
            margin-bottom: 10px;
        }

        .notification-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .notification-actions {
            margin-top: 10px;
        }

        .notification-actions .btn {
            margin-right: 5px;
        }

        .notification-item h4 {
            margin-top: 0;
            color: #333;
        }

        .notification-item p {
            margin: 10px 0;
            color: #666;
        }

        .notification-item small {
            color: #999;
        }
    </style>