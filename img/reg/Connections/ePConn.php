<?php
$conn =  mysqli_connect('localhost', 'root', '$$Mysql@futgk123#', 'pg_database');


$db = new PDO('mysql:host=localhost;dbname=pg_database;charset=utf8mb4', 'root', '$$Mysql@futgk123#');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);


?>

