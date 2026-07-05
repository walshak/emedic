<?php


ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set("error_log", "error_log");
error_reporting(E_ALL);
session_start();
define('DB_HOST', 'localhost');
define('DB_NAME', 'emedic');
define('DB_USER', 'root');
define('DB_PASS', 'WEBMEDICc@1');


/// mysql_connect(hostname,username,password,databasename);

$conn = mysql_connect('localhost', 'root', 'WEBMEDICc@1', 'emedic');
if (!$conn) {
    die('Could not select database: ' . mysql_error());
}

$conni = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conni) {
    die('Could not connect database: ' . mysqli_error($conni));
} else {
}

$db = new PDO('mysql:host=localhost;dbname=emedic;charset=utf8mb4', 'root', 'WEBMEDICc@1');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

date_default_timezone_set('Africa/Lagos');
