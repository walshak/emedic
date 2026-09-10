-- Migration Script: External Laboratory Information System (LIS) Integration
-- Date: 2026-09-09

-- 1. Global LIS Configuration & Credentials
CREATE TABLE IF NOT EXISTS `lis_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `provider_driver` VARCHAR(50) NOT NULL DEFAULT 'clinos',
  `base_url` VARCHAR(255) NOT NULL DEFAULT 'https://zmkc-livs.shares.zrok.io',
  `emr_key` VARCHAR(255) DEFAULT NULL,
  `catalogue_key` VARCHAR(255) DEFAULT NULL,
  `sync_mode` ENUM('poll', 'push', 'both') NOT NULL DEFAULT 'poll',
  `polling_interval_mins` INT(11) NOT NULL DEFAULT 5,
  `last_polled_after_id` BIGINT(20) NOT NULL DEFAULT 0,
  `last_poll_timestamp` DATETIME DEFAULT NULL,
  `webhook_secret` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default initial row if table is empty
INSERT INTO `lis_config` (`id`, `is_enabled`, `provider_driver`, `base_url`)
SELECT 1, 0, 'clinos', 'https://zmkc-livs.shares.zrok.io'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `lis_config` WHERE `id` = 1);

-- 2. Investigation Mapping Matrix (EMR Test <-> LIS Canonical Code)
CREATE TABLE IF NOT EXISTS `lis_test_mappings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `lab_scan_id` INT(11) NOT NULL,
  `emr_test_name` VARCHAR(150) NOT NULL,
  `canonical_code` VARCHAR(100) NOT NULL,
  `lis_provider` VARCHAR(50) NOT NULL DEFAULT 'clinos',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_scan_provider` (`lab_scan_id`, `lis_provider`),
  KEY `idx_canonical` (`canonical_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. External LIS Order Tracking
CREATE TABLE IF NOT EXISTS `lis_orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `labrequest_no` VARCHAR(50) NOT NULL,
  `external_order_id` VARCHAR(100) NOT NULL,
  `lis_provider` VARCHAR(50) NOT NULL DEFAULT 'clinos',
  `clinos_order_id` VARCHAR(100) DEFAULT NULL,
  `clinos_order_uid` VARCHAR(100) DEFAULT NULL,
  `clinos_label_url` VARCHAR(255) DEFAULT NULL,
  `clinos_specimens_json` LONGTEXT DEFAULT NULL,
  `status` ENUM('pending', 'sent', 'specimen_received', 'validated', 'failed') NOT NULL DEFAULT 'pending',
  `error_log` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_labrequest` (`labrequest_no`),
  KEY `idx_external_order` (`external_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Inbound Result Event Logs (Idempotency & Audit)
CREATE TABLE IF NOT EXISTS `lis_event_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `event_id` VARCHAR(100) NOT NULL,
  `external_order_id` VARCHAR(100) NOT NULL,
  `after_id` BIGINT(20) DEFAULT NULL,
  `payload_json` LONGTEXT NOT NULL,
  `status` ENUM('processed', 'skipped', 'error') NOT NULL DEFAULT 'processed',
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_event` (`event_id`),
  KEY `idx_ext_order` (`external_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
