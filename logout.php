<?php include 'Connections/Conn.php'; ?>
<?php
ob_start();
session_start();
$last_login_id = $_SESSION['last_login_id'];
$setdate = date('Y-m-d H:i:s');
$update = "UPDATE admin_users_logs SET Log_out='$setdate' WHERE sn='$last_login_id'";
$db->exec($update);
unset($_SESSION['username']);
unset($_SESSION['rights']);
unset($_SESSION['fullname']);
unset($_SESSION['specialist']);
unset($_SESSION['inventory']);
unset($_SESSION['navigate']);
unset($_SESSION['dept_name']);
unset($_SESSION['dept_id']);
unset($_SESSION['dept_group_name']);
session_destroy();
header('Location: index.php');
exit();
 ?>
