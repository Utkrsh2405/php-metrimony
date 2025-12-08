<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../includes/dbconn.php");
require_once("../includes/security.php");

// Make database connection available
global $conn;

$userlevel = $_GET['user'] ?? '1';
$error = '';

// Username and password sent from form 
$myusername = $_POST['username'] ?? ''; 
$mypassword = $_POST['password'] ?? ''; 

// Trim whitespace but don't sanitize username yet (we'll use prepared statements)
$myusername = trim($myusername);

if (empty($myusername) || empty($mypassword)) {
    $_SESSION['login_error'] = 'Please enter both username and password.';
    header('Location: ../login.php');
    exit();
}

// Rate limiting - 100 attempts per 15 minutes for users
$ip = $_SERVER['REMOTE_ADDR'];
if (!Security::checkRateLimit('user_login_' . $ip, 100, 900)) {
    $_SESSION['login_error'] = 'Too many login attempts. Please try again in 15 minutes.';
    Security::logSecurityEvent('user_login_rate_limit', 'User login rate limit exceeded', null, $ip);
    header('Location: ../login.php');
    exit();
}

// Use prepared statement to prevent SQL injection
// For users, allow userlevel = 0 or NULL (non-admin users)
// Admin users (userlevel = 1) should use admin/login.php
$stmt = mysqli_prepare($conn, "SELECT id, username, password, userlevel, account_status FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $myusername);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Check if user is admin (should use admin login page)
    if ($row['userlevel'] == 1) {
        $_SESSION['login_error'] = 'Admin users must login through the admin panel.';
        header('Location: ../admin/login.php');
        exit();
    }
    
    // Check if account is suspended or deleted
    $account_status = $row['account_status'] ?? 'active';
    if ($account_status === 'suspended') {
        $_SESSION['login_error'] = 'Your account has been suspended. Please contact support.';
        header('Location: ../login.php');
        exit();
    }
    if ($account_status === 'deleted') {
        $_SESSION['login_error'] = 'Your account is inactive. Please contact support.';
        header('Location: ../login.php');
        exit();
    }
    
    // Check if password is hashed or plain text
    if (password_verify($mypassword, $row['password'])) {
        // Password is bcrypt hashed and correct
        $password_valid = true;
    } elseif ($row['password'] === md5($mypassword)) {
        // MD5 hashed password match - upgrade to bcrypt
        $hashed = password_hash($mypassword, PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE users SET password = '$hashed' WHERE id = {$row['id']}");
        $password_valid = true;
    } elseif ($row['password'] === $mypassword) {
        // Plain text password match - upgrade to bcrypt
        $hashed = password_hash($mypassword, PASSWORD_BCRYPT);
        mysqli_query($conn, "UPDATE users SET password = '$hashed' WHERE id = {$row['id']}");
        $password_valid = true;
    } else {
        $password_valid = false;
    }
    
    if ($password_valid) {
        // Successful login
        $_SESSION['username'] = $row['username'];
        $_SESSION['id'] = $row['id'];
        $_SESSION['userlevel'] = $row['userlevel'];
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Update last login timestamp
        $user_id = intval($row['id']);
        mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = $user_id");
        
        // Redirect to user home
        header("location:../userhome.php?id={$row['id']}");
        exit();
    } else {
        $_SESSION['login_error'] = 'Invalid username or password.';
        Security::logSecurityEvent('user_login_failed', 'Failed login attempt for: ' . $myusername, null, $ip);
        header('Location: ../login.php');
        exit();
    }
} else {
    $_SESSION['login_error'] = 'Invalid username or password.';
    Security::logSecurityEvent('user_login_failed', 'Login attempt with non-existent user: ' . $myusername, null, $ip);
    header('Location: ../login.php');
    exit();
}

mysqli_stmt_close($stmt);
?>