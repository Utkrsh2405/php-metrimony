<?php
session_start();
require_once("../includes/dbconn.php");
require_once("../includes/security.php");

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['id']) && isset($_SESSION['userlevel']) && $_SESSION['userlevel'] == 1) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
$timeout_message = '';

// Check for timeout parameter
if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $timeout_message = 'Your session has expired due to inactivity. Please login again.';
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Validate CSRF token
    if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Rate limiting - 5 attempts per 15 minutes
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!Security::checkRateLimit('admin_login_' . $ip, 5, 900)) {
            $error = 'Too many login attempts. Please try again in 15 minutes.';
            Security::logSecurityEvent('admin_login_rate_limit', 'Admin login rate limit exceeded', null, $ip);
        } else {
            if (empty($username) || empty($password)) {
                $error = 'Please enter both username and password.';
            } else {
                // Check if user exists and is admin
                $stmt = mysqli_prepare($conn, "SELECT id, username, password, userlevel FROM users WHERE username = ? AND userlevel = 1");
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if ($user = mysqli_fetch_assoc($result)) {
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Successful login
                        $_SESSION['id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['userlevel'] = $user['userlevel'];
                        
                        // Log successful login
                        require_once("../includes/activity-logger.php");
                        $logger = getActivityLogger($conn);
                        $logger->log(
                            $user['id'],
                            'login',
                            'admin',
                            $user['id'],
                            'Admin login successful'
                        );
                        
                        // Regenerate session ID for security
                        session_regenerate_id(true);
                        
                        header("Location: index.php");
                        exit();
                    } else {
                        $error = 'Invalid username or password.';
                        Security::logSecurityEvent('admin_login_failed', 'Failed admin login attempt for: ' . $username, null, $ip);
                    }
                } else {
                    $error = 'Invalid username or password.';
                    Security::logSecurityEvent('admin_login_failed', 'Admin login attempt with non-admin account: ' . $username, null, $ip);
                }
            }
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Admin Login - MakeMyLove</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<link href="../css/font-awesome.css" rel="stylesheet"> 
<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.login-container {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    overflow: hidden;
    max-width: 400px;
    width: 100%;
}
.login-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 30px;
    text-align: center;
}
.login-header h2 {
    margin: 0;
    font-size: 28px;
}
.login-header p {
    margin: 10px 0 0 0;
    opacity: 0.9;
}
.login-body {
    padding: 40px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}
.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 14px;
    transition: all 0.3s;
}
.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
.input-icon {
    position: relative;
}
.input-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}
.input-icon input {
    padding-left: 45px;
}
.btn-login {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
}
.alert {
    padding: 12px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}
.alert-danger {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}
.alert-success {
    background: #efe;
    color: #3c3;
    border: 1px solid #cfc;
}
.back-link {
    text-align: center;
    margin-top: 20px;
}
.back-link a {
    color: #667eea;
    text-decoration: none;
}
.back-link a:hover {
    text-decoration: underline;
}
.security-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
    font-size: 12px;
    color: #666;
    text-align: center;
}
</style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h2><i class="fa fa-lock"></i> Admin Login</h2>
        <p>Secure Administrative Access Only</p>
        <p style="font-size: 12px; margin-top: 5px; opacity: 0.9;">
            <i class="fa fa-info-circle"></i> For member login, please use the <a href="../login.php" style="color: #fff; text-decoration: underline;">member login page</a>
        </p>
    </div>
    
    <div class="login-body">
        <?php if ($timeout_message): ?>
            <div class="alert alert-warning" style="background: #fff3cd; color: #856404; border: 1px solid #ffc107;">
                <i class="fa fa-clock-o"></i> <?php echo htmlspecialchars($timeout_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa fa-check"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo Security::csrfField(); ?>
            
            <div class="form-group">
                <label for="username">
                    <i class="fa fa-user"></i> Username
                </label>
                <div class="input-icon">
                    <i class="fa fa-user"></i>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           placeholder="Enter admin username"
                           required 
                           autofocus
                           autocomplete="username">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fa fa-lock"></i> Password
                </label>
                <div class="input-icon">
                    <i class="fa fa-lock"></i>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Enter admin password"
                           required
                           autocomplete="current-password">
                </div>
            </div>
            
            <button type="submit" name="login" class="btn-login">
                <i class="fa fa-sign-in"></i> Login to Admin Panel
            </button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">
                <i class="fa fa-arrow-left"></i> Back to Main Site
            </a>
        </div>
        
        <div class="security-info">
            <i class="fa fa-shield"></i> This page is protected with CSRF tokens and rate limiting
        </div>
    </div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
