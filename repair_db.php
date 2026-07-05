<?php
// Configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = 'surepass098';
$db_name = 'emedic';

// Create a connection to the database
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get a list of all tables in the database
$tables = array();
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Repair each table
foreach ($tables as $table) {
    $query = "REPAIR TABLE $table";
    $conn->query($query);
    echo "Repaired table: $table\n";
}

// Close the connection
$conn->close();
