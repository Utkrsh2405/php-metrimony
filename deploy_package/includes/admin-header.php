<?php
// Admin Header and Navigation
if (!isset($_SESSION)) {
    session_start();
}

// Session timeout - 30 minutes of inactivity
$timeout_duration = 1800; // 30 minutes in seconds

// Check if user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check for session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

require_once("../includes/dbconn.php");

// Verify admin access (userlevel = 1)
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    // Not an admin, redirect to main site
    session_destroy();
    header("Location: login.php");
    exit();
}

// Prevent session hijacking
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    // Session hijacking detected
    session_destroy();
    header("Location: login.php");
    exit();
}

// Session timeout (30 minutes of inactivity)
$timeout = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Admin Panel - MakeMyLove</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<link href="../css/font-awesome.css" rel="stylesheet"> 
<link href="../css/style.css" rel='stylesheet' type='text/css' />
<link href="../css/admin-custom.css" rel='stylesheet' type='text/css' />
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="brand">
            <i class="fa fa-heart"></i> Admin Panel
        </div>
        <ul class="nav">
            <li>
                <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="fa fa-dashboard"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="members.php" class="<?php echo ($current_page == 'members.php') ? 'active' : ''; ?>">
                    <i class="fa fa-users"></i> Members
                </a>
            </li>
            <li>
                <a href="plans.php" class="<?php echo ($current_page == 'plans.php') ? 'active' : ''; ?>">
                    <i class="fa fa-credit-card"></i> Plans
                </a>
            </li>
            <li>
                <a href="payments.php" class="<?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
                    <i class="fa fa-money"></i> Payments
                </a>
            </li>
            <li>
                <a href="sms-templates.php" class="<?php echo ($current_page == 'sms-templates.php') ? 'active' : ''; ?>">
                    <i class="fa fa-mobile"></i> SMS Templates
                </a>
            </li>
            <li>
                <a href="pages.php" class="<?php echo ($current_page == 'pages.php') ? 'active' : ''; ?>">
                    <i class="fa fa-file-text"></i> Pages (CMS)
                </a>
            </li>
            <li>
                <a href="translations.php" class="<?php echo ($current_page == 'translations.php') ? 'active' : ''; ?>">
                    <i class="fa fa-globe"></i> Translations
                </a>
            </li>
            <li>
                <a href="frontpage.php" class="<?php echo ($current_page == 'frontpage.php') ? 'active' : ''; ?>">
                    <i class="fa fa-home"></i> Homepage Config
                </a>
            </li>
            <li>
                <a href="homepage-sections.php" class="<?php echo ($current_page == 'homepage-sections.php') ? 'active' : ''; ?>">
                    <i class="fa fa-th-large"></i> Homepage Sections
                </a>
            </li>
            <li>
                <a href="footer-settings.php" class="<?php echo ($current_page == 'footer-settings.php') ? 'active' : ''; ?>">
                    <i class="fa fa-columns"></i> Footer Settings
                </a>
            </li>
            <li>
                <a href="search-categories.php" class="<?php echo ($current_page == 'search-categories.php') ? 'active' : ''; ?>">
                    <i class="fa fa-sitemap"></i> Search Categories
                </a>
            </li>
            <li>
                <a href="search-analytics.php" class="<?php echo ($current_page == 'search-analytics.php') ? 'active' : ''; ?>">
                    <i class="fa fa-search"></i> Search Analytics
                </a>
            </li>
            <li>
                <a href="interest-logs.php" class="<?php echo ($current_page == 'interest-logs.php') ? 'active' : ''; ?>">
                    <i class="fa fa-heart"></i> Interest Logs
                </a>
            </li>
            <li>
                <a href="message-logs.php" class="<?php echo ($current_page == 'message-logs.php') ? 'active' : ''; ?>">
                    <i class="fa fa-comments"></i> Message Logs
                </a>
            </li>
            <li>
                <a href="activity-logs.php" class="<?php echo ($current_page == 'activity-logs.php') ? 'active' : ''; ?>">
                    <i class="fa fa-history"></i> Activity Logs
                </a>
            </li>
            <li>
                <a href="admin-users.php" class="<?php echo ($current_page == 'admin-users.php') ? 'active' : ''; ?>">
                    <i class="fa fa-user-secret"></i> Admin Users
                </a>
            </li>
            <li>
                <a href="../index.php">
                    <i class="fa fa-external-link"></i> View Site
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="admin-content">
        <div class="admin-topbar">
            <div style="display: flex; align-items: center;">
                <button id="sidebarToggle" class="btn btn-default visible-xs" style="margin-right: 15px;">
                    <i class="fa fa-bars"></i>
                </button>
                <h4 style="margin: 0;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h4>
            </div>
            <div class="user-info">
                <span class="date-display">
                    <i class="fa fa-clock-o"></i> <?php echo date('F d, Y h:i A'); ?>
                </span>
            </div>
        </div>
        <div class="admin-main">
            <script>
                document.getElementById('sidebarToggle').addEventListener('click', function() {
                    document.getElementById('adminSidebar').classList.toggle('open');
                });
            </script>
