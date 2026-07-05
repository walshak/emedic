<?php
ob_start();
session_start();
$timeout_duration = 3600; // 3600 = 60 minutes

// Check session timeout
if (isset($_SESSION['last_action']) && (time() - $_SESSION['last_action']) > $timeout_duration) {
    // Calculate how many minutes user was inactive
    $inactive_seconds = time() - $_SESSION['last_action'];
    $inactive_minutes = floor($inactive_seconds / 60);

    session_unset();
    session_destroy();
    header("Location: ../index.php?timeout&minute=" . $inactive_minutes);
    exit();
}

// Check if user is logged in
if (empty($_SESSION['username'])) {
    header("Location: ../index.php?error=no_login");
    exit();
}

// Define upload paths
define('staff_p', '../uploads/staff/');
define('enrollee_p', '../uploads/enrollee/');

// Session values
$rights = $_SESSION['rights'];
$fullname = $_SESSION['fullname'];
$username = $_SESSION['username'];

// Update last action time
$_SESSION['last_action'] = time();
