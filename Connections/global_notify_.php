<?php
if (!function_exists('global_notify_')) {
    /**
     * Sends a notification to a specific department, designation, rights group, or user.
     * 
     * This function allows broadcasting notifications based on different criteria such as
     * department, designation, rights, or specific users. Supports pattern matching.
     *
     * @param PDO    $db       Database connection instance.
     * @param string $type     The recipient type ('department', 'designation', 'rights', 'user').
     * @param string $pattern  Pattern or comma-separated values for matching recipients.
     * @param string $title    Title of the notification.
     * @param string $message  Message content of the notification.
     * @param string|null $sender (Optional) The username or ID of the sender. Defaults to null.
     *
     * Example Usage:
     * --------------
     * // Notify all IT department staff
     * global_notify_($db, 'department', 'IT%', 'Server Maintenance', 'Server will be down for maintenance tonight');
     * 
     * // Notify all managers
     * global_notify_($db, 'designation', '%Manager%', 'Meeting Reminder', 'Management meeting tomorrow at 10 AM');
     * 
     * // Notify all admins
     * global_notify_($db, 'rights', 'admin%', 'New Security Policy', 'Please review the new security guidelines');
     * 
     * // Notify specific users (supports pattern matching)
     * global_notify_($db, 'user', 'john%', 'Task Assignment', 'Please review the latest report');
     * 
     * // Notify multiple departments
     * global_notify_($db, 'department', 'IT%,HR%', 'System Update', 'New HR system deployment next week');
     */

    function global_notify_($db, $type, $pattern, $title, $message, $sender = null)
    {
        try {
            // Only start transaction if one isn't already active (avoid nested transactions)
            $startedTransaction = false;
            if (!$db->inTransaction()) {
                $db->beginTransaction();
                $startedTransaction = true;
            }

            $now = date("Y-m-d H:i:s");
            $today = date("Y-m-d");
            $sender = $sender ? $sender : 'SYSTEM';
            $recipients = array();

            $likePatterns = array_map('trim', explode(',', $pattern));
            $patternCount = count($likePatterns);
            $args = array();

            switch (strtolower($type)) {
                case 'department':
                    $conditions = array();
                    foreach ($likePatterns as $p) {
                        $conditions[] = "Department LIKE ?";
                        $args[] = $p;
                    }
                    $sql = "SELECT DISTINCT EmployeeCode FROM hremp WHERE " . implode(' OR ', $conditions);
                    $stmt = $db->prepare($sql);
                    $stmt->execute($args);
                    break;

                case 'designation':
                    $conditions = array();
                    foreach ($likePatterns as $p) {
                        $conditions[] = "Designation LIKE ?";
                        $args[] = $p;
                    }
                    $sql = "SELECT DISTINCT EmployeeCode FROM hremp WHERE " . implode(' OR ', $conditions);
                    $stmt = $db->prepare($sql);
                    $stmt->execute($args);
                    break;

                case 'rights':
                    $conditions = array();
                    foreach ($likePatterns as $p) {
                        $conditions[] = "rights LIKE ?";
                        $args[] = $p;
                    }
                    $sql = "SELECT DISTINCT EmployeeCode FROM admin_users WHERE status = 1 AND (" . implode(' OR ', $conditions) . ")";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($args);
                    break;

                case 'user':
                    $conditions = array();
                    foreach ($likePatterns as $p) {
                        $conditions[] = "(EmployeeCode LIKE ? OR username LIKE ?)";
                        $args[] = $p;
                        $args[] = $p;
                    }
                    $sql = "SELECT DISTINCT EmployeeCode FROM admin_users WHERE status = 1 AND (" . implode(' OR ', $conditions) . ")";
                    $stmt = $db->prepare($sql);
                    $stmt->execute($args);
                    break;

                case 'discharge':
                    $stmt = $db->prepare("SELECT DISTINCT emp_code FROM admin_users_logs WHERE DATE(Log_in) = ? AND Rights = 'RE' AND emp_code IS NOT NULL");
                    $stmt->execute(array($today));
                    break;

                case 'notifyurgent':
                case 'notify':
                case 'rights2':
                    $stmt = $db->prepare("SELECT DISTINCT emp_code FROM admin_users_logs WHERE DATE(Log_in) = ? AND Rights = ? AND emp_code IS NOT NULL");
                    $stmt->execute(array($today, $pattern));
                    break;

                default:
                    throw new Exception("Invalid notification type: " . $type);
            }

            $recipients = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($recipients)) {
                throw new Exception("No recipients found for pattern: " . $pattern . " in type: " . $type);
            }

            $created_by = isset($_SESSION['username']) ? $_SESSION['username'] : $sender;
            $notificationId = null;

            // Avoid spamming same notification
            if (in_array(strtolower($type), array('notify', 'notifyurgent'))) {

                // First, check the last notification with the same message + created_by
                $checkStmt = $db->prepare("
                    SELECT created_at 
                    FROM notifications 
                    WHERE message = ? 
                    AND created_by = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ");
                $checkStmt->execute(array($message, $created_by));
                $lastNotification = $checkStmt->fetch(PDO::FETCH_ASSOC);

                $canInsert = true;
                if ($lastNotification) {
                    $lastTime = strtotime($lastNotification['created_at']);
                    $now = time();

                    // if less than 120 seconds difference, don't insert
                    if (($now - $lastTime) <= 120) {
                        $canInsert = false;
                    }
                }

                if ($canInsert) {
                    $insertStmt = $db->prepare("
                        INSERT INTO notifications (title, message, target_user, created_by, created_at) 
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $insertStmt->execute(array($title, $message, $pattern, $created_by));
                    $notificationId = $db->lastInsertId();
                }
            } else {
                $insertStmt = $db->prepare("INSERT INTO notifications (title, message, created_by) VALUES (?, ?, ?)");
                $insertStmt->execute(array($title, $message, $created_by));
                $notificationId = $db->lastInsertId();
            }

            if ($notificationId) {
                $snoozed_until = date("Y-m-d H:i:s", strtotime("+2 minutes"));
                $statusStmt = $db->prepare("INSERT INTO notification_status (notification_id, employee_code, snoozed_until) VALUES (?, ?, ?)");

                foreach ($recipients as $emp) {
                    $statusStmt->execute(array(
                        $notificationId,
                        $emp,
                        in_array(strtolower($type), array('notify', 'notifyurgent')) ? $snoozed_until : null
                    ));
                }

                // Only commit if this function started the transaction
                if ($startedTransaction) {
                    $db->commit();
                }

                return array(
                    'success' => true,
                    'notification_id' => $notificationId,
                    'recipient_count' => count($recipients)
                );
            } else {
                // Only rollback if this function started the transaction
                if ($startedTransaction) {
                    $db->rollBack();
                }
                return array('success' => false, 'error' => 'Notification not inserted.');
            }
        } catch (Exception $e) {
            // Only rollback if this function started the transaction
            if ($startedTransaction) {
                $db->rollBack();
            }
            error_log("Notification error: " . $e->getMessage());
            return array('success' => false, 'error' => $e->getMessage());
        }
    }
}
