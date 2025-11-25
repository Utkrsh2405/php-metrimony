<?php
session_start();
require_once("../includes/dbconn.php");
require_once("../includes/security.php");

$userlevel = $_GET['user'];
$error = '';

// Username and password sent from form 
$myusername = $_POST['username'] ?? ''; 
$mypassword = $_POST['password'] ?? ''; 

// Sanitize inputs
$myusername = Security::sanitizeInput($myusername);

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
// For users, only allow userlevel != 1 (non-admin users)
$stmt = mysqli_prepare($conn, "SELECT id, username, password, userlevel FROM users WHERE username = ? AND (userlevel IS NULL OR userlevel != 1)");
mysqli_stmt_bind_param($stmt, "s", $myusername);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Check if password is hashed or plain text
    if (password_verify($mypassword, $row['password'])) {
        // Password is hashed and correct
        $password_valid = true;
    } elseif ($row['password'] === $mypassword) {
        // Plain text password match - upgrade to hashed
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