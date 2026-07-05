<?php
if (isset($_SESSION['username']) and $_SESSION['username'] != '' and $_SESSION['fullname'] != '') {

    $uname = $_SESSION['username'];
    $rights = $_SESSION['rights'];
    $primary_rights = $_SESSION['primary_rights'];
    $fullname = $_SESSION['fullname'];
    $specialist = $_SESSION['specialist'];
    $unit_head = $_SESSION['unit_head'];
    $ECode_logged = $_SESSION['EmployeeCode'];

    require_once('../Connections/Conn.php');

    $stmt22 = $db->query("SELECT sn FROM admin_users_logs WHERE username='$uname' order by sn desc limit 1");
    if ($stmt22->rowCount() > 0) {
        $rowx = $stmt22->fetch(PDO::FETCH_ASSOC);
        //$sn=$rowx['sn'];
        $_SESSION['last_login_id'] = $rowx['sn'];
    }
} else {
    $time = time();
    ob_start();
    ob_clean();

    if (!$_SESSION['username'] == '') {
        if (($time - $_SESSION['last_action']) > 60000) {
            unset($_SESSION['username']);
            unset($_SESSION['rights']);
            unset($_SESSION['primary_rights']);
            unset($_SESSION['fullname']);
            unset($_SESSION['last_action']);
            unset($_SESSION['specialist']);
            unset($_SESSION['unit_head']);
            session_destroy();
            header('location:../index.php');
        } else {
            //die('aaaa'.$_SESSION['last_action']);	
            $_SESSION['last_action'] = time();
            return true;
        }
    } else {
        header("location: ../index.php");
    }
}
?>