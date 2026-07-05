<?php
require_once('Connections/Conn.php');

try {
    // Step 1: Get all tables in the current database
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    if (!$tables) {
        echo "No tables found in the database.\n";
        exit;
    }

    echo "Found " . count($tables) . " tables. Starting repair...\n\n";

    // Step 2: Loop and repair each table
    foreach ($tables as $table) {
        try {
            $repair = $db->query("REPAIR TABLE `$table`");
            $result = $repair->fetchAll(PDO::FETCH_ASSOC);

            echo "Table: $table\n";
            foreach ($result as $row) {
                echo "  Status: " . $row['Msg_type'] . " - " . $row['Msg_text'] . "\n";
            }
            echo "\n";
        } catch (PDOException $e) {
            echo "Error repairing $table: " . $e->getMessage() . "\n\n";
        }
    }

    echo "Repair process completed.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
