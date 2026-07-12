<?php
// Set error reporting and display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// PDO connection object
// Path to the connection file
$connectionFile = '../Connections/Conn.php';

// Check if the connection file exists
if (file_exists($connectionFile)) {
    // If it exists, include the connection file
    require_once $connectionFile;
} else {
    // If the file doesn't exist, fall back to the default connection
    try {
        $db = new PDO('mysql:host=localhost;dbname=webmedic;charset=utf8', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}


// Configuration
$backupDir = '../backup/' . date('Y-m-d_H-i-s');
$maxExecutionTime = 6000; // 10 * 10 minutes

// Increase max execution time
set_time_limit($maxExecutionTime);

// Function to safely create directory
function createDirectory($path)
{
    if (!file_exists($path)) {
        if (!mkdir($path, 0755, true)) {
            throw new Exception("Failed to create directory: $path");
        }
    }
}

try {
    // Create backup directory if it doesn't exist
    createDirectory($backupDir);

    // Database connection (assuming you have these variables set)
    //     $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $total = count($tables);
    $current = 0;

    echo "Starting Backup Sequence...\n";
    ob_flush(); flush();

    foreach ($tables as $table) {
        $current++;
        
        // Check for stop flag
        if (file_exists('../backup/stop_backup.flag')) {
            echo "🛑 Backup stopped by user.\n";
            unlink('../backup/stop_backup.flag');
            break;
        }
        try {
            // Open a file to save the table's SQL data
            $filePath = $backupDir . '/' . $table . '.sql';
            $file = fopen($filePath, 'w');
            if ($file === false) {
                throw new Exception("Unable to create backup file for table $table.");
            }

            // Get the CREATE TABLE statement
            $createStmt = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            fwrite($file, $createStmt['Create Table'] . ";\n\n");

            // Get the INSERT INTO statements for table data
            $dataStmt = $db->prepare("SELECT * FROM `$table`");
            $dataStmt->execute();

            while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_map(function ($col) {
                    return "`$col`";
                }, array_keys($row));
                $placeholders = array_fill(0, count($row), '?');
                $insertStmt = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ");\n";

                // Handle NULL values
                $quotedValues = array_map(function ($value) use ($db) {
                    if ($value === null) {
                        return 'NULL';
                    } else {
                        return $db->quote($value);
                    }
                }, $row);

                fwrite($file, vsprintf(str_replace('?', '%s', $insertStmt), $quotedValues));
            }

            fclose($file);

            echo "Exported table `$table` to $filePath ($current/$total)\n";
            ob_flush();
            flush();
        } catch (Exception $e) {
            echo "Error exporting table `$table`: " . $e->getMessage() . "\n";
            // Optionally: reconnect PDO here if needed
            // $db = new PDO(...);
            // Continue to next table without stopping script
        }
    }


    echo "Backup complete!\n";
    ob_flush(); flush();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Close the database connection if it exists
    if (isset($db)) {
        $db = null;
    }
}

