<?php
session_start();

// Log the logout activity if user is logged in
if (isset($_SESSION['id'])) {
    require_once("../includes/dbconn.php");
    require_once("../includes/activity-logger.php");
    
    $logger = getActivityLogger($conn);
    $logger->log(
        $_SESSION['id'],
        'logout',
        'admin',
        $_SESSION['id'],
        'Admin logout'
    );
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
