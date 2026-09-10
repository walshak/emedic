CREATE TABLE IF NOT EXISTS `lis_notifications_seen` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL DEFAULT 0,
  `username` VARCHAR(100) NOT NULL,
  `labrequest_no` VARCHAR(50) NOT NULL,
  `event_type` VARCHAR(50) NOT NULL DEFAULT 'result_received',
  `seen_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_lab_event` (`username`, `labrequest_no`, `event_type`),
  KEY `idx_labrequest` (`labrequest_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
