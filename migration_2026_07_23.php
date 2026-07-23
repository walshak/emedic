<?php
/**
 * Database Migration Script - Date: 2026-07-23
 * 
 * This script documents and executes all SQL schema changes outlined in the recent 
 * system upgrade guides. It is designed to be robust and idempotent, meaning it 
 * can be run multiple times safely without throwing errors.
 * 
 * Covers features:
 * 1. AI Integration (ai_integration_guide.md)
 * 2. Autosave Improvements (autosave_integration_guide.md)
 * 3. External Notifications (external_notifications_guide.md)
 * 4. Intra Mail Improvements (mail_system_upgrade_guide.md)
 */

require_once(__DIR__ . '/Connections/Conn.php');

try {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>Starting Database Migration (2026-07-23)...</h3>\n";

    /**
     * Helper function to safely add columns only if they do not exist.
     * Overcomes the lack of `ADD COLUMN IF NOT EXISTS` in older MySQL versions.
     */
    function addColumnIfNotExists($db, $table, $column, $definition) {
        try {
            $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE '$column'");
            $stmt->execute();
            if (!$stmt->fetch()) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
                echo "<p style='color: green;'>Success: Added column `$column` to `$table`.</p>\n";
            } else {
                echo "<p style='color: gray;'>Skipped: Column `$column` already exists in `$table`.</p>\n";
            }
        } catch (PDOException $e) {
             echo "<p style='color: red;'>Error checking/adding `$column` to `$table`: " . $e->getMessage() . "</p>\n";
        }
    }

    // ---------------------------------------------------------
    // 1. AI Features Migration
    // ---------------------------------------------------------
    echo "<h4>1. AI Features Migration</h4>\n";
    addColumnIfNotExists($db, 'hospital_details', 'llm_config', 'TEXT NULL DEFAULT NULL');

    // ---------------------------------------------------------
    // 2. Autosave Improvements Migration
    // ---------------------------------------------------------
    echo "<h4>2. Autosave Improvements Migration</h4>\n";
    addColumnIfNotExists($db, 'autosave', 'title', 'VARCHAR(255) NULL DEFAULT NULL AFTER `doctor`');

    // ---------------------------------------------------------
    // 3. External Notifications Migration
    // ---------------------------------------------------------
    echo "<h4>3. External Notifications Migration</h4>\n";
    addColumnIfNotExists($db, 'hospital_details', 'twilio_sid', 'VARCHAR(255) NULL DEFAULT NULL');
    addColumnIfNotExists($db, 'hospital_details', 'twilio_auth_token', 'VARCHAR(255) NULL DEFAULT NULL');
    addColumnIfNotExists($db, 'hospital_details', 'twilio_phone_number', 'VARCHAR(50) NULL DEFAULT NULL');
    addColumnIfNotExists($db, 'hospital_details', 'welcome_email_template', 'TEXT NULL DEFAULT NULL');
    addColumnIfNotExists($db, 'hospital_details', 'welcome_sms_template', 'TEXT NULL DEFAULT NULL');
    
    // Legacy SMTP columns for external notifications
    addColumnIfNotExists($db, 'hospital_details', 'smtp_host', 'VARCHAR(255) NULL DEFAULT NULL');
    addColumnIfNotExists($db, 'hospital_details', 'smtp_username', 'VARCHAR(255) NULL DEFAULT NULL');
    addColumnIfNotExists($db, 'hospital_details', 'smtp_password', 'VARCHAR(255) NULL DEFAULT NULL');
    addColumnIfNotExists($db, 'hospital_details', 'smtp_port', 'INT NULL DEFAULT 587');
    addColumnIfNotExists($db, 'hospital_details', 'smtp_encryption', "VARCHAR(50) NULL DEFAULT 'tls'");

    // ---------------------------------------------------------
    // 4. Intra Mail Improvements Migration
    // ---------------------------------------------------------
    echo "<h4>4. Intra Mail Improvements Migration</h4>\n";
    
    // Create new tables safely
    $db->exec("CREATE TABLE IF NOT EXISTS `mail_threads` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `original_mail_id` int(11) NOT NULL,
      `subject` varchar(255) NOT NULL,
      `created_by` varchar(255) NOT NULL,
      `created_date` datetime NOT NULL,
      `last_activity` datetime NOT NULL,
      `message_count` int(11) DEFAULT 1,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p style='color: green;'>Success: Ensured table `mail_threads` exists.</p>\n";

    $db->exec("CREATE TABLE IF NOT EXISTS `mail_recipients` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `mail_id` int(11) NOT NULL,
      `thread_id` int(11) NOT NULL,
      `recipient_username` varchar(255) NOT NULL,
      `recipient_name` varchar(255) DEFAULT NULL,
      `recipient_type` varchar(50) DEFAULT 'to',
      `read_status` varchar(50) DEFAULT 'unread',
      `deleted_status` tinyint(1) DEFAULT 0,
      `deleted_date` datetime DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `idx_recipient_thread` (`recipient_username`,`thread_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p style='color: green;'>Success: Ensured table `mail_recipients` exists.</p>\n";

    // Add related columns to existing mails table
    addColumnIfNotExists($db, 'mails', 'thread_id', 'INT(11) DEFAULT NULL');
    addColumnIfNotExists($db, 'mails', 'parent_mail_id', 'INT(11) DEFAULT NULL');
    addColumnIfNotExists($db, 'mails', 'reply_type', "VARCHAR(50) DEFAULT 'new'");

    // Check if data migration is already done
    $stmt_check = $db->query("SELECT COUNT(*) as count FROM mail_threads");
    $thread_count = $stmt_check->fetch(PDO::FETCH_ASSOC)['count'];

    if ($thread_count == 0) {
        echo "<p>Starting mail threads data migration...</p>\n";
        
        // 1. Fetch all messages
        $stmt = $db->query("SELECT m.sn, m.subject, m.from_username, m.from_name, m.on_contact_username, m.on_contact_name, m.mail_date, m.read_status
                            FROM mails m
                            WHERE m.mail_status = 'send' OR m.mail_status = 'trash'
                            ORDER BY m.mail_date ASC");
        $mails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Group messages
        $groups = [];
        foreach ($mails as $mail) {
            $subject = trim($mail['subject']);
            $subject = preg_replace('/^(?:Re|Fwd|Forward|RE|FWD):\s*/i', '', $subject);
            $subject = trim($subject);
            if (empty($subject)) $subject = "No Subject";

            $participants = [];
            $participants[] = trim($mail['from_username']);
            $contacts = explode(';', $mail['on_contact_username']);
            foreach ($contacts as $c) {
                $c = trim($c);
                if (!empty($c)) $participants[] = $c;
            }
            $participants = array_unique($participants);
            sort($participants);
            $participants_key = implode(',', $participants);

            $group_key = md5(strtolower($subject) . '|' . strtolower($participants_key));

            if (!isset($groups[$group_key])) {
                $groups[$group_key] = [];
            }
            $groups[$group_key][] = $mail;
        }

        echo "<p>Found " . count($groups) . " unique conversation groups.</p>\n";
        $consolidated_count = 0;
        $recipients_added = 0;

        foreach ($groups as $group_key => $group_mails) {
            $origin = $group_mails[0];
            $last_activity = end($group_mails)['mail_date'];
            $message_count = count($group_mails);

            $insert_thread = $db->prepare("INSERT INTO mail_threads (original_mail_id, subject, created_by, created_date, last_activity, message_count) 
                                           VALUES (:omid, :subj, :cby, :cdate, :last_act, :msg_cnt)");
            $insert_thread->execute([
                ':omid' => $origin['sn'],
                ':subj' => $origin['subject'],
                ':cby' => $origin['from_username'],
                ':cdate' => $origin['mail_date'],
                ':last_act' => $last_activity,
                ':msg_cnt' => $message_count
            ]);
            
            $master_thread_id = $db->lastInsertId();
            $prev_mail_id = null;

            foreach ($group_mails as $i => $current) {
                $reply_type = ($i == 0) ? 'new' : 'reply';
                $update_mail = $db->prepare("UPDATE mails SET thread_id = :tid, parent_mail_id = :pid, reply_type = :rtype WHERE sn = :sn");
                $update_mail->execute([
                    ':tid' => $master_thread_id,
                    ':pid' => $prev_mail_id,
                    ':rtype' => $reply_type,
                    ':sn' => $current['sn']
                ]);

                $contacts = explode(';', $current['on_contact_username']);
                $names = explode(';', $current['on_contact_name']);
                foreach ($contacts as $index => $c) {
                    $c = trim($c);
                    if (empty($c)) continue;
                    $name = isset($names[$index]) ? trim($names[$index]) : $c;

                    $insert_recipient = $db->prepare("INSERT INTO mail_recipients (mail_id, thread_id, recipient_username, recipient_name, recipient_type, read_status) 
                                                      VALUES (:mid, :tid, :runame, :rname, 'to', :rstatus)");
                    $insert_recipient->execute([
                        ':mid' => $current['sn'],
                        ':tid' => $master_thread_id,
                        ':runame' => $c,
                        ':rname' => $name,
                        ':rstatus' => $current['read_status']
                    ]);
                    $recipients_added++;
                }

                $prev_mail_id = $current['sn'];
                $consolidated_count++;
            }
        }

        echo "<p style='color: green;'>Success: Consolidated {$consolidated_count} messages into proper threads. Added {$recipients_added} recipients.</p>\n";
    } else {
        echo "<p style='color: gray;'>Skipped: Mail threads data migration already performed ({$thread_count} threads exist).</p>\n";
    }

    echo "<h3>Migration Completed Successfully.</h3>\n";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>Migration Failed!</h3>\n";
    echo "Error: " . $e->getMessage();
}
