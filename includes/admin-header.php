<?php
// Admin Header and Navigation
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

require_once("../includes/dbconn.php");

// Verify admin access
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    header("Location: ../index.php");
    exit();
}

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
<link href="../css/admin.css" rel='stylesheet' type='text/css' />
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<style>
body {
    background: #f4f6f9;
}
.admin-wrapper {
    display: flex;
    min-height: 100vh;
}
.admin-sidebar {
    width: 250px;
    background: #2c3e50;
    color: #ecf0f1;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
}
.admin-sidebar .brand {
    padding: 20px;
    background: #1a252f;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    color: #fff;
}
.admin-sidebar .nav {
    list-style: none;
    padding: 0;
    margin: 0;
}
.admin-sidebar .nav li {
    border-bottom: 1px solid #34495e;
}
.admin-sidebar .nav li a {
    display: block;
    padding: 15px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.3s;
}
.admin-sidebar .nav li a:hover,
.admin-sidebar .nav li a.active {
    background: #34495e;
    padding-left: 30px;
}
.admin-sidebar .nav li a i {
    margin-right: 10px;
    width: 20px;
}
.admin-content {
    flex: 1;
    margin-left: 250px;
    padding: 0;
}
.admin-topbar {
    background: #fff;
    padding: 15px 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.admin-main {
    padding: 30px;
}
.stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #7f8c8d;
    text-transform: uppercase;
}
.stat-card .number {
    font-size: 32px;
    font-weight: bold;
    color: #2c3e50;
}
.stat-card .change {
    font-size: 12px;
    margin-top: 5px;
}
.stat-card .change.up {
    color: #27ae60;
}
.stat-card .change.down {
    color: #e74c3c;
}
.stat-card.blue { border-left: 4px solid #3498db; }
.stat-card.green { border-left: 4px solid #27ae60; }
.stat-card.orange { border-left: 4px solid #f39c12; }
.stat-card.purple { border-left: 4px solid #9b59b6; }
</style>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <div class="admin-sidebar">
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
                <a href="search-analytics.php" class="<?php echo ($current_page == 'search-analytics.php') ? 'active' : ''; ?>">
                    <i class="fa fa-search"></i> Search Analytics
                </a>
            </li>
            <li>
                <a href="messages.php" class="<?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>">
                    <i class="fa fa-envelope"></i> Messages
                </a>
            </li>
            <li>
                <a href="interest-logs.php" class="<?php echo ($current_page == 'interest-logs.php') ? 'active' : ''; ?>">
                    <i class="fa fa-heart"></i> Interests/Logs
                </a>
            </li>
            <li>
                <a href="../index.php">
                    <i class="fa fa-external-link"></i> View Site
                </a>
            </li>
            <li>
                <a href="../logout.php">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="admin-content">
        <div class="admin-topbar">
            <div>
                <h4 style="margin: 0;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h4>
            </div>
            <div>
                <span style="color: #7f8c8d;">
                    <i class="fa fa-clock-o"></i> <?php echo date('F d, Y h:i A'); ?>
                </span>
            </div>
        </div>
        <div class="admin-main">
