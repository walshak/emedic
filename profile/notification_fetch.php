<?php
session_start();
include_once("../Connections/Conn.php");



//get notices
if (isset($_GET['last_id'])) {
    header('Content-Type: application/json');

    try {
        $lastId = (int) $_GET['last_id'];
        $employeeCode = isset($_SESSION['EmployeeCode']) ? $_SESSION['EmployeeCode'] : null;

        if (!$employeeCode) {
            throw new Exception("Missing employee session.");
        }

        $stmt = $db->prepare("
            SELECT 
                n.notification_id,
                n.title,
                n.message,
                n.created_at,
                n.created_by,
                n.is_handover,
                ns.is_acknowledged,
                ns.snoozed_until,
                au_sender.fullname AS sender_name,
                COALESCE(au_receiver.fullname, nc.channel_name) AS receiver_info
            FROM notifications n
            JOIN notification_status ns ON n.notification_id = ns.notification_id
            LEFT JOIN admin_users au_sender ON au_sender.username = n.created_by
            LEFT JOIN admin_users au_receiver ON au_receiver.EmployeeCode = n.target_user
            LEFT JOIN notification_channels nc ON nc.channel_id = n.channel_id
            WHERE ns.employee_code = ?
              AND ns.is_acknowledged = 0
              AND (ns.snoozed_until IS NULL OR ns.snoozed_until < NOW())
              AND n.notification_id > ?
            ORDER BY n.created_at DESC
        ");

        $stmt->execute(array($employeeCode, $lastId));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notifications = array();
        $seenIds = array(); // for deduplication if needed

        foreach ($rows as $row) {
            $nid = $row['notification_id'];

            if (!isset($seenIds[$nid])) {
                $seenIds[$nid] = true;

                $notifications[] = array(
                    'notification_id' => $nid,
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'created_at' => date('d F Y', strtotime($row['created_at'])),
                    'sender' => $row['sender_name'] ? $row['sender_name'] : 'System',
                    'receiver' => $row['receiver_info'] ? $row['receiver_info'] : 'N/A',
                    'is_handover' => $row['is_handover'],
                    'acknowledged' => $row['is_acknowledged'],
                    'snoozed_until' => $row['snoozed_until']
                );
            }
        }

        echo json_encode(array(
            'success' => true,
            'notifications' => $notifications,
            'last_id' => $lastId
        ));
    } catch (Exception $e) {
        echo json_encode(array(
            'success' => false,
            'error' => $e->getMessage()
        ));
    }
}


// Snooze Notification
if (isset($_POST['notification_id']) && isset($_POST['snooze_duration'])) {
    header('Content-Type: application/json');

    try {
        $notificationId = (int)$_POST['notification_id'];
        $snoozeDuration = (int)$_POST['snooze_duration']; // Duration in seconds

        // Compute snooze time based on the server's current time
        $stmt = $db->prepare("
            UPDATE notification_status
            SET snoozed_until = NOW() + INTERVAL ? SECOND
            WHERE notification_id = ?
            AND employee_code = ?
        ");

        $stmt->execute([$snoozeDuration, $notificationId, $_SESSION['EmployeeCode']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Notification not found');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}


//acknowledge notices
if (isset($_POST['notification_id']) && !isset($_POST['snooze_duration'])) {
    header('Content-Type: application/json');
    try {

        $notificationId = (int)$_POST['notification_id'];

        $stmt = $db->prepare("
            UPDATE notification_status
            SET is_acknowledged = 1,
            acknowledged_at = NOW()
            WHERE notification_id = ?
            AND employee_code = ?
            ");

        $stmt->execute([$notificationId, $_SESSION['EmployeeCode']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'acknowledged_at' => date('Y-m-d H:i:s')]);
        } else {
            throw new Exception('Notification not found or already acknowledged');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

if (isset($_POST['bulk_acknowledge']) && isset($_POST['notification_ids'])) {
    header('Content-Type: application/json');
    try {
        $notificationIds = array_map('intval', $_POST['notification_ids']);

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';

        $stmt = $db->prepare("
            UPDATE notification_status
            SET is_acknowledged = 1,
                acknowledged_at = NOW()
            WHERE notification_id IN ($placeholders)
            AND employee_code = ?
            AND is_acknowledged = 0
        ");

        // Add employee code to the parameters
        $params = array_merge($notificationIds, [$_SESSION['EmployeeCode']]);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('No notifications were updated');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

if (isset($_POST['bulk_snooze']) && isset($_POST['notification_ids']) && isset($_POST['snooze_duration'])) {
    header('Content-Type: application/json');
    try {
        $notificationIds = array_map('intval', $_POST['notification_ids']);
        $snoozeDuration = (int)$_POST['snooze_duration'];

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($notificationIds) - 1) . '?';

        $stmt = $db->prepare("
            UPDATE notification_status
            SET snoozed_until = NOW() + INTERVAL ? SECOND
            WHERE notification_id IN ($placeholders)
            AND employee_code = ?
        ");

        // Add snooze duration and employee code to the parameters
        $params = array_merge([$snoozeDuration], $notificationIds, [$_SESSION['EmployeeCode']]);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('No notifications were updated');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
