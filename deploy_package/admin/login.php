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
        
        // Rate limiting - 100 attempts per 15 minutes
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!Security::checkRateLimit('admin_login_' . $ip, 100, 900)) {
            $error = 'Too many login attempts. Please try again in 15 minutes.';
            Security::logSecurityEvent($conn, 'admin_login_rate_limit', 'Admin login rate limit exceeded from IP: ' . $ip, null);
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
                        Security::logSecurityEvent($conn, 'admin_login_failed', 'Failed admin login attempt for: ' . $username . ' from IP: ' . $ip, null);
                    }
                } else {
                    $error = 'Invalid username or password.';
                    Security::logSecurityEvent($conn, 'admin_login_failed', 'Admin login attempt with non-admin account: ' . $username . ' from IP: ' . $ip, null);
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
:root {
    --primary-color: #4f46e5;
    --primary-hover: #4338ca;
    --bg-gradient-start: #1e293b;
    --bg-gradient-end: #0f172a;
    --text-main: #1e293b;
    --text-muted: #64748b;
}

body {
    background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    margin: 0;
}

.login-container {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    max-width: 420px;
    width: 90%;
    position: relative;
}

.login-header {
    background: #fff;
    color: var(--text-main);
    padding: 40px 40px 20px;
    text-align: center;
}

.login-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 800;
    color: var(--text-main);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.login-header h2 i {
    color: var(--primary-color);
}

.login-header p {
    margin: 10px 0 0 0;
    color: var(--text-muted);
    font-size: 15px;
}

.login-body {
    padding: 20px 40px 40px;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-main);
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.2s;
    color: var(--text-main);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.input-icon {
    position: relative;
}

.input-icon i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    transition: color 0.2s;
}

.input-icon input:focus + i,
.input-icon:focus-within i {
    color: var(--primary-color);
}

.input-icon input {
    padding-left: 48px;
}

.btn-login {
    width: 100%;
    padding: 14px;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.1), 0 2px 4px -1px rgba(79, 70, 229, 0.06);
}

.btn-login:hover {
    background: var(--primary-hover);
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.back-link {
    text-align: center;
    margin-top: 24px;
}

.back-link a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.back-link a:hover {
    color: var(--primary-color);
}

.security-info {
    background: #f8fafc;
    padding: 12px;
    border-radius: 8px;
    margin-top: 24px;
    font-size: 12px;
    color: var(--text-muted);
    text-align: center;
    border: 1px solid #e2e8f0;
}
</style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h2><i class="fa fa-lock"></i> Admin Login</h2>
        <p>Secure Administrative Access Only</p>
        <p style="font-size: 13px; margin-top: 8px; opacity: 0.8;">
            <i class="fa fa-info-circle"></i> For member login, please use the <a href="../login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">member login page</a>
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
