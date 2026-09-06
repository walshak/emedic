<?php
require_once(__DIR__ . '/Connections/Conn.php');

try {
    echo "Starting Migration...\n";

    // 1. Add hospital_details columns if they don't exist
    $columns = [
        'frontdesk_can_book_procedures' => "INT(11) NOT NULL DEFAULT 0",
        'frontdesk_can_book_medical_services' => "INT(11) NOT NULL DEFAULT 0",
        'nurses_can_fully_admit_discharge' => "INT(11) NOT NULL DEFAULT 0"
    ];

    foreach ($columns as $col => $def) {
        $checkCol = $db->query("SHOW COLUMNS FROM hospital_details LIKE '$col'");
        if ($checkCol->rowCount() === 0) {
            $db->exec("ALTER TABLE hospital_details ADD COLUMN $col $def");
            echo "Added column $col to hospital_details.\n";
        } else {
            echo "Column $col already exists in hospital_details.\n";
        }
    }

    // 2. Create table admission_discharge_checklists
    $db->exec("CREATE TABLE IF NOT EXISTS `admission_discharge_checklists` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `type` ENUM('admission', 'discharge') NOT NULL,
        `status` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Created or verified admission_discharge_checklists table.\n";

    // 3. Create table patient_admission_checklist_logs
    $db->exec("CREATE TABLE IF NOT EXISTS `patient_admission_checklist_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `admission_id` INT NOT NULL,
        `hospital_no` VARCHAR(50) NOT NULL,
        `checklist_id` INT NOT NULL,
        `checklist_type` ENUM('admission', 'discharge') NOT NULL,
        `is_checked` TINYINT(1) NOT NULL DEFAULT 0,
        `notes` TEXT DEFAULT NULL,
        `checked_by` VARCHAR(200) NOT NULL,
        `checked_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Created or verified patient_admission_checklist_logs table.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
