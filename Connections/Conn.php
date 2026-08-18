<?php
include('error_reporting.php');

$s_ta_xs = 0;   //// 1 means check for mac
$db = new PDO('mysql:host=localhost;dbname=webmedic_gastro;charset=utf8mb4', 'root', '18781875');
///$db = new PDO('mysql:host=localhost;dbname=webmedic_bck;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

date_default_timezone_set('Africa/Lagos');

include('global_notify_.php');
