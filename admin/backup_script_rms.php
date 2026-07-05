<?php
// Check if the directory exists
$backupDir = '../backup';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// PDO connection
try {
    $db = new PDO('mysql:host=localhost;dbname=emedic;charset=utf8', 'root', 'surepass098');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Configuration
$backupDir = '../backup/' . date('Y-m-d_H-i-s');
$maxExecutionTime = 6000; // 100 minutes

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

// HTML page start with CSS styling
?>
<!DOCTYPE html>
<html>

<head>
    <title>Database Backup Progress</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }

        .progress-container {
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-header {
            padding: 10px;
            background-color: #f5f5f5;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .progress-bar {
            height: 25px;
            background-color: #e0e0e0;
        }

        .progress-fill {
            height: 100%;
            background-color: #4CAF50;
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .status-message {
            padding: 10px;
        }

        .success {
            background-color: #e8f5e9;
            border-left: 5px solid #4CAF50;
        }

        .error {
            background-color: #ffebee;
            border-left: 5px solid #f44336;
        }

        .skipped {
            background-color: #fff3e0;
            border-left: 5px solid #ff9800;
        }

        h1 {
            color: #333;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-link {
            padding: 8px 15px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-link:hover {
            background-color: #0b7dda;
        }
    </style>
</head>

<body>
    <div class="header-section">
        <h1>Database Backup Progress</h1>
        <a href="backup_page.php" class="back-link">Go Back</a>
    </div>
    <?php

    // Start output buffering for real-time progress
    ob_implicit_flush(true);
    ob_end_flush();

    echo "<div id='progress-container'>";
    try {
        // Create backup directory if it doesn't exist
        createDirectory($backupDir);

        // Get the list of all tables
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "<h3>Starting Backup Sequence...</h3>";

        foreach ($tables as $table) {
            $tableId = str_replace(' ', '-', $table); // Create DOM-friendly ID
            echo "<div class='progress-container' id='container-$tableId'>";
            echo "<div class='progress-header'><span>Table: <strong>$table</strong></span><span id='count-$tableId'></span></div>";
            echo "<div class='progress-bar'><div class='progress-fill' id='progress-$tableId'>0%</div></div>";
            echo "<div class='status-message' id='status-$tableId'></div>";
            echo "</div>";

            // Flush the output buffer to show progress container immediately
            ob_flush();
            flush();

            try {
                $filePath = $backupDir . '/' . $table . '.sql';

                // Check if backup file already exists
                if (file_exists($filePath)) {
                    echo "<script>
                    document.getElementById('status-$tableId').innerHTML = 'Backup file already exists - skipping';
                    document.getElementById('container-$tableId').classList.add('skipped');
                </script>";
                    ob_flush();
                    flush();
                    continue;
                }

                // Get total rows count for progress calculation
                $countStmt = $db->query("SELECT COUNT(*) FROM `$table`");
                $totalRows = $countStmt->fetchColumn();

                echo "<script>
                document.getElementById('count-$tableId').innerHTML = 'Total rows: $totalRows';
            </script>";
                ob_flush();
                flush();

                $file = fopen($filePath, 'w');
                if ($file === false) {
                    throw new Exception("Unable to create backup file for table $table.");
                }

                // Write table structure
                $createStmt = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                fwrite($file, $createStmt['Create Table'] . ";\n\n");

                // Prepare data export
                $dataStmt = $db->prepare("SELECT * FROM `$table`");
                $dataStmt->execute();

                $processedRows = 0;
                $batchSize = 1000; // Update progress every 1000 rows
                $startTime = microtime(true);

                while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                    $columns = array_map(function ($col) {
                        return "`$col`";
                    }, array_keys($row));

                    $values = array_map(function ($value) use ($db) {
                        return $value === null ? 'NULL' : $db->quote($value);
                    }, array_values($row));

                    $insertStmt = "INSERT INTO `$table` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                    fwrite($file, $insertStmt);

                    $processedRows++;

                    // Update progress periodically
                    if ($processedRows % $batchSize === 0 || $processedRows === $totalRows) {
                        $progress = round(($processedRows / $totalRows) * 100);
                        $elapsedTime = number_format(microtime(true) - $startTime, 2);
                        $rowsPerSec = number_format($processedRows / max(1, $elapsedTime), 0);

                        echo "<script>
                        document.getElementById('progress-$tableId').style.width = '$progress%';
                        document.getElementById('progress-$tableId').innerHTML = '$progress%';
                        document.getElementById('status-$tableId').innerHTML = 
                            'Processed $processedRows/$totalRows rows ($rowsPerSec rows/sec)';
                    </script>";
                        ob_flush();
                        flush();
                    }
                }

                fclose($file);

                // Mark as completed
                echo "<script>
                document.getElementById('container-$tableId').classList.add('success');
                document.getElementById('status-$tableId').innerHTML += '<br>Backup completed successfully';
            </script>";
                ob_flush();
                flush();
            } catch (Exception $e) {
                echo "<script>
                document.getElementById('container-$tableId').classList.add('error');
                document.getElementById('status-$tableId').innerHTML = 'Error: " . addslashes($e->getMessage()) . "';
            </script>";
                ob_flush();
                flush();
            }
        }

        echo "<h3 style='color:#4CAF50;'>All tables backed up successfully!</h3>";
        echo "<script>
        setTimeout(function() {
            window.location.href = 'backup_page.php';
        }, 3000);
    </script>";
    } catch (Exception $e) {
        echo "<div class='status-message error'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    } finally {
        if (isset($db)) {
            $db = null;
        }
    }
    echo "</div>";
    ?>
</body>

</html>