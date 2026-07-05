<?php

include("../Connections/Conn.php");
include('objects.php');

$id = $_POST['id'];
$DialysisData->delete($id);
echo 200;
