<?php
// =============================
// Database connection (PDO)
// =============================
require_once('Connections/Conn.php');

// =============================
// Daily Cleanup Logic
// =============================
date_default_timezone_set('Africa/Lagos');

$logFile = __DIR__ . '/last_notification_cleanup.txt';

// Ensure the log file exists
if (!file_exists($logFile)) {
    file_put_contents($logFile, '');
}

$lastRun = trim(file_get_contents($logFile));
$today = date('Y-m-d');

// Run only once per day
if ($lastRun !== $today) {
    try {
        $db->beginTransaction();

        // Cutoff = 1 month ago
        $cutoff = date('Y-m-d H:i:s', strtotime('-1 month'));

        // Fetch old notification IDs
        $stmt = $db->prepare("SELECT notification_id FROM notifications WHERE created_at < :cutoff");
        $stmt->execute([':cutoff' => $cutoff]);
        $oldIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($oldIds)) {
            $placeholders = implode(',', array_fill(0, count($oldIds), '?'));

            // Delete from notification_status
            $delStatus = $db->prepare("DELETE FROM notification_status WHERE notification_id IN ($placeholders)");
            $delStatus->execute($oldIds);

            // Delete from notifications
            $delNotifs = $db->prepare("DELETE FROM notifications WHERE notification_id IN ($placeholders)");
            $delNotifs->execute($oldIds);
        }

        // Extra cleanup tasks
        // 1. Deactivate admin users not updated in last 30 days
        $update = $db->prepare("
        UPDATE admin_users 
        SET status = 0 
        WHERE date_updated < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
        $update->execute();

        // 2. Remove old temporary/inactive records
        $db->prepare("DELETE FROM saleprint WHERE date_time_stamp < NOW() - INTERVAL 1 DAY")->execute();
        $db->prepare("DELETE FROM invoice_temp2 WHERE date_time_stamp < NOW() - INTERVAL 1 DAY")->execute();
        $db->prepare("DELETE FROM apptm_fellowup WHERE date_time_stamp < NOW() - INTERVAL 2 DAY")->execute();
        $db->prepare("DELETE FROM autosave WHERE saved_at < NOW() - INTERVAL 1 DAY")->execute();

        $db->commit();

        // Mark cleanup as done today
        file_put_contents($logFile, $today);
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Daily cleanup failed: " . $e->getMessage());
    }
}
