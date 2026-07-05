<?php
/*
$host = 'localhost';
$user = 'root';
$pass = '';
$db1 = 'mluth3';     // Source
$db2 = 'webmedic_bck';   // Target

$host = 'localhost';
$user = 'u129178536_lastest';
$pass = 'U129178536_lastest';
$db1 = 'u129178536_lastest';     // Source
$db2 = 'webmedic_bck';   // Target
*/

$host1 = 'localhost';
$user1 = 'root';
$pass1 = 'surepass098';
$db1 = 'latest';  // Source

$host2 = 'localhost';
$user2 = 'root';
$pass2 = 'surepass098';
$db2 = 'emedic';  // Target

// Setup source connection
$pdo1 = new PDO("mysql:host=$host1;dbname=$db1", $user1, $pass1);
$pdo1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Setup target connection
$pdo2 = new PDO("mysql:host=$host2;dbname=$db2", $user2, $pass2);
$pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Schema Synchronization Report\nDate: " . date('Y-m-d H:i:s') . "\n\n";

function logMessage($msg)
{
    echo $msg;
}

function getColumnDefinition($col)
{
    $def = "`{$col['COLUMN_NAME']}` {$col['COLUMN_TYPE']}";
    $def .= $col['IS_NULLABLE'] === 'NO' ? " NOT NULL" : " NULL";

    if ($col['COLUMN_DEFAULT'] !== null) {
        $default = $col['COLUMN_DEFAULT'];
        if (strtoupper($default) === 'CURRENT_TIMESTAMP') {
            $def .= " DEFAULT CURRENT_TIMESTAMP";
        } else {
            $def .= " DEFAULT " . (is_numeric($default) ? $default : "'$default'");
        }
    }

    if (!empty($col['EXTRA'])) {
        $def .= " {$col['EXTRA']}";
    }

    return $def;
}

function needsUpdate($col1, $col2)
{
    return (
        $col1['COLUMN_TYPE'] !== $col2['COLUMN_TYPE'] ||
        $col1['IS_NULLABLE'] !== $col2['IS_NULLABLE'] ||
        $col1['COLUMN_DEFAULT'] != $col2['COLUMN_DEFAULT'] ||
        $col1['EXTRA'] !== $col2['EXTRA']
    );
}

function logError($action, $table, $column, $sql, $exception)
{
    logMessage("    ❌ ERROR during $action\n");
    logMessage("       Table: `$table`\n");
    if ($column) logMessage("       Column: `$column`\n");
    logMessage("       SQL: $sql\n");
    logMessage("       Reason: {$exception->getMessage()}\n\n");
}

// Fetch tables from source DB
$tables = $pdo1->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$db1'")
    ->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    $exists = $pdo2->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = :db2 AND TABLE_NAME = :table");
    $exists->execute(['db2' => $db2, 'table' => $table]);

    if ($exists->fetchColumn() == 0) {
        logMessage("🛠 Creating table `$table` in `$db2`...\n");
        try {
            $createStmt = $pdo1->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            $createSQL = str_replace("CREATE TABLE `$table`", "CREATE TABLE `$db2`.`$table`", $createStmt['Create Table']);
            $pdo2->exec($createSQL);
            logMessage("✅ Table `$table` created.\n\n");
        } catch (PDOException $e) {
            logError("table creation", $table, null, $createSQL, $e);
        }
    } else {
        logMessage("🔍 Comparing table `$table`...\n");

        $cols_db1 = $pdo1->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db1' AND TABLE_NAME = '$table'")
            ->fetchAll(PDO::FETCH_ASSOC);
        $cols_db2 = $pdo2->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$db2' AND TABLE_NAME = '$table'")
            ->fetchAll(PDO::FETCH_ASSOC);

        $cols2_map = [];
        foreach ($cols_db2 as $col) {
            $cols2_map[$col['COLUMN_NAME']] = $col;
        }

        foreach ($cols_db1 as $col1) {
            $colName = $col1['COLUMN_NAME'];

            if (!isset($cols2_map[$colName])) {
                logMessage("  ➕ Adding missing column `$colName`...\n");
                $colDef = getColumnDefinition($col1);
                $sql = "ALTER TABLE `$db2`.`$table` ADD COLUMN $colDef";
                try {
                    $pdo2->exec($sql);
                    logMessage("    ✅ Column `$colName` added.\n");
                } catch (PDOException $e) {
                    logError("adding column", $table, $colName, $sql, $e);
                }
            } else {
                $col2 = $cols2_map[$colName];
                if (needsUpdate($col1, $col2)) {
                    logMessage("  ⚠️ Modifying column `$colName` due to mismatch...\n");
                    $colDef = getColumnDefinition($col1);
                    $sql = "ALTER TABLE `$db2`.`$table` MODIFY COLUMN $colDef";
                    try {
                        $pdo2->exec($sql);
                        logMessage("    ✅ Column `$colName` modified.\n");
                    } catch (PDOException $e) {
                        logError("modifying column", $table, $colName, $sql, $e);
                    }
                }
            }
        }

        logMessage("✅ Finished comparing `$table`.\n\n");
    }
}

logMessage("✅ Schema sync complete.\n");
