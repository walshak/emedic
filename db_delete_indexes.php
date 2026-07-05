<?php

// Database configuration
$host = 'localhost';
$dbname = 'emedic';
$username = 'root';
///$password = 'WEBMEDICc@1';
$password = 'surepass098';

try {
    // Create database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all table names in the database
    $tablesStmt = $conn->query("SHOW TABLES");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "No tables found in database $dbname.<br>";
    }

    foreach ($tables as $tableName) {
        echo "<strong>Processing table: $tableName</strong><br>";

        $sql = "SELECT index_name 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name = :table_name
                AND index_name != 'PRIMARY'";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':table_name', $tableName);
        $stmt->execute();

        $indexes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($indexes)) {
            foreach ($indexes as $indexName) {
                try {
                    $dropSql = "ALTER TABLE `$tableName` DROP INDEX `$indexName`";
                    $conn->exec($dropSql);
                    echo "Dropped index: $indexName<br>";
                } catch (PDOException $e) {
                    echo "<span style='color:red;'>Error dropping $indexName on $tableName: " . $e->getMessage() . "</span><br>";
                    // Continue to next index
                }
            }
        } else {
            echo "No non-primary indexes found on table $tableName.<br>";
        }
        echo "<hr>";
    }
} catch (PDOException $e) {
    echo "<strong>Critical Connection Error: " . $e->getMessage() . "</strong>";
}

// Close connection
$conn = null;
