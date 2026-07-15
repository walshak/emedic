<?php
require_once(__DIR__ . '/../Connections/Conn.php');

try {
    // 0. Ensure schema exists
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

    try {
        $db->exec("ALTER TABLE `mails` 
          ADD COLUMN `thread_id` int(11) DEFAULT NULL,
          ADD COLUMN `parent_mail_id` int(11) DEFAULT NULL,
          ADD COLUMN `reply_type` varchar(50) DEFAULT 'new';");
    } catch (PDOException $e) {
        // Ignore duplicate column errors if it has already been altered
    }

    $db->beginTransaction();

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

    echo "Found " . count($groups) . " unique conversation groups.<br>\n";
    $consolidated_count = 0;
    $recipients_added = 0;

    foreach ($groups as $group_key => $group_mails) {
        // The first message is the origin
        $origin = $group_mails[0];
        
        $last_activity = end($group_mails)['mail_date'];
        $message_count = count($group_mails);

        // Create the master thread
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

        // Update all messages in this group
        foreach ($group_mails as $i => $current) {
            // Update mail
            $reply_type = ($i == 0) ? 'new' : 'reply';
            $update_mail = $db->prepare("UPDATE mails SET thread_id = :tid, parent_mail_id = :pid, reply_type = :rtype WHERE sn = :sn");
            $update_mail->execute([
                ':tid' => $master_thread_id,
                ':pid' => $prev_mail_id,
                ':rtype' => $reply_type,
                ':sn' => $current['sn']
            ]);

            // Add recipients (treated as TO since they were the only recipients)
            // But wait, the original sender is also a participant in the thread!
            // Actually, we only insert the recipients for this specific message.
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
            
            // Also add the sender to mail_recipients so they can see it in their "Sent" thread view
            // Wait, sent items are fetched where mt.created_by = :username OR where they sent a reply in the thread
            // If we just add them as 'bcc' or we don't need to add sender because they are the sender of this message.

            $prev_mail_id = $current['sn'];
            $consolidated_count++;
        }
    }

    $db->commit();
    echo "Successfully consolidated {$consolidated_count} messages into proper threads.<br>\n";
    echo "Added {$recipients_added} recipients.<br>\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}
