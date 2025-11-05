<?php
/**
 * Hostinger Debug Helper
 * Upload this file to your Hostinger public_html directory
 * Visit: http://yourdomain.com/debug.php
 * DELETE THIS FILE after debugging!
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Hostinger Debug Helper</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; }
        table td:first-child { font-weight: bold; width: 300px; }
        .alert { padding: 15px; border-radius: 5px; margin: 10px 0; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>";

echo "<h1>🔧 Hostinger Debug Helper</h1>";
echo "<p><strong>IMPORTANT:</strong> Delete this file (debug.php) after troubleshooting!</p>";

// 1. PHP Version Check
echo "<div class='section'>";
echo "<h2>1. PHP Environment</h2>";
echo "<table>";
echo "<tr><td>PHP Version:</td><td>" . phpversion() . " ";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<span class='success'>✓ OK</span>";
} else {
    echo "<span class='error'>✗ Too Old (Need 7.4+)</span>";
}
echo "</td></tr>";

echo "<tr><td>Server Software:</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>Document Root:</td><td>" . $_SERVER['DOCUMENT_ROOT'] . "</td></tr>";
echo "<tr><td>Current Directory:</td><td>" . getcwd() . "</td></tr>";
echo "</table>";
echo "</div>";

// 2. Required PHP Extensions
echo "<div class='section'>";
echo "<h2>2. Required PHP Extensions</h2>";
echo "<table>";

$required_extensions = [
    'mysqli' => 'MySQL Database Connection',
    'session' => 'User Sessions',
    'json' => 'JSON Processing',
    'mbstring' => 'Multibyte String Functions',
    'gd' => 'Image Processing',
    'curl' => 'HTTP Requests',
    'openssl' => 'Encryption & Security'
];

foreach ($required_extensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    echo "<tr><td>$ext</td><td>$description - ";
    if ($loaded) {
        echo "<span class='success'>✓ Loaded</span>";
    } else {
        echo "<span class='error'>✗ Missing</span>";
    }
    echo "</td></tr>";
}
echo "</table>";
echo "</div>";

// 3. File System Check
echo "<div class='section'>";
echo "<h2>3. File System & Permissions</h2>";
echo "<table>";

$files_to_check = [
    'includes/dbconn.php' => 'Database Connection File',
    'functions.php' => 'Functions File',
    'admin/login.php' => 'Admin Login',
    'index.php' => 'Homepage',
    'uploads' => 'Upload Directory',
    'uploads/banners' => 'Banner Upload Directory'
];

foreach ($files_to_check as $file => $description) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $file;
    $exists = file_exists($full_path);
    echo "<tr><td>$description</td><td>$file - ";
    if ($exists) {
        echo "<span class='success'>✓ Exists</span>";
        if (is_dir($full_path)) {
            $writable = is_writable($full_path);
            echo " | Writable: " . ($writable ? "<span class='success'>Yes</span>" : "<span class='error'>No</span>");
        }
    } else {
        echo "<span class='error'>✗ Missing</span>";
    }
    echo "</td></tr>";
}
echo "</table>";
echo "</div>";

// 4. Database Connection Test
echo "<div class='section'>";
echo "<h2>4. Database Connection Test</h2>";

if (file_exists('includes/dbconn.php')) {
    // Try to include and test
    ob_start();
    try {
        include('includes/dbconn.php');
        $include_output = ob_get_clean();
        
        if (isset($conn) && $conn) {
            echo "<div class='alert alert-success'>";
            echo "✓ <strong>Database Connected Successfully!</strong><br>";
            echo "MySQL Version: " . mysqli_get_server_info($conn) . "<br>";
            
            // Test query
            $result = @mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                echo "Users in database: " . $row['count'] . "<br>";
            }
            
            // Check important tables
            $tables = ['users', 'admin_activity_logs', 'security_logs', 'homepage_config', 'site_settings'];
            echo "<br><strong>Database Tables:</strong><br>";
            foreach ($tables as $table) {
                $result = @mysqli_query($conn, "SHOW TABLES LIKE '$table'");
                if ($result && mysqli_num_rows($result) > 0) {
                    echo "✓ $table exists<br>";
                } else {
                    echo "✗ $table missing<br>";
                }
            }
            echo "</div>";
        } else {
            echo "<div class='alert alert-danger'>";
            echo "✗ <strong>Database Connection Failed!</strong><br>";
            echo "Error: " . mysqli_connect_error() . "<br>";
            echo "<br><strong>Common Fixes:</strong>";
            echo "<ul>";
            echo "<li>Check database credentials in includes/dbconn.php</li>";
            echo "<li>Use 'localhost' not '127.0.0.1' for host</li>";
            echo "<li>Ensure database user has privileges</li>";
            echo "<li>Verify database name includes cPanel username prefix</li>";
            echo "</ul>";
            echo "</div>";
        }
        
        if (!empty($include_output)) {
            echo "<div class='alert alert-warning'>";
            echo "<strong>Output from dbconn.php:</strong><br>";
            echo "<pre>" . htmlspecialchars($include_output) . "</pre>";
            echo "</div>";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "<div class='alert alert-danger'>";
        echo "✗ Error loading dbconn.php: " . $e->getMessage();
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>";
    echo "✗ <strong>includes/dbconn.php not found!</strong><br>";
    echo "Make sure you uploaded all files to the correct directory.";
    echo "</div>";
}
echo "</div>";

// 5. PHP Configuration
echo "<div class='section'>";
echo "<h2>5. PHP Configuration</h2>";
echo "<table>";
echo "<tr><td>Upload Max Filesize:</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>Post Max Size:</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>Max Execution Time:</td><td>" . ini_get('max_execution_time') . " seconds</td></tr>";
echo "<tr><td>Memory Limit:</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>Display Errors:</td><td>" . (ini_get('display_errors') ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>Error Reporting:</td><td>" . ini_get('error_reporting') . "</td></tr>";
echo "<tr><td>Session Save Path:</td><td>" . ini_get('session.save_path') . "</td></tr>";
echo "</table>";
echo "</div>";

// 6. Session Test
echo "<div class='section'>";
echo "<h2>6. Session Test</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['test'] = 'Session Working!';
if (isset($_SESSION['test'])) {
    echo "<span class='success'>✓ Sessions are working correctly</span>";
} else {
    echo "<span class='error'>✗ Sessions not working</span>";
}
echo "</div>";

// 7. Error Log Location
echo "<div class='section'>";
echo "<h2>7. Error Logging</h2>";
$error_log = ini_get('error_log');
if ($error_log) {
    echo "<p>Error log location: <code>" . $error_log . "</code></p>";
} else {
    $default_log = $_SERVER['DOCUMENT_ROOT'] . '/error_log';
    echo "<p>Default error log: <code>" . $default_log . "</code></p>";
}
echo "<p>Check this file for PHP errors if something isn't working.</p>";
echo "</div>";

// 8. Recommendations
echo "<div class='section'>";
echo "<h2>8. Quick Fixes & Recommendations</h2>";

$issues = [];

// Check PHP version
if (version_compare(phpversion(), '7.4.0', '<')) {
    $issues[] = "Upgrade PHP to version 7.4 or higher in cPanel → Select PHP Version";
}

// Check mysqli
if (!extension_loaded('mysqli')) {
    $issues[] = "Enable mysqli extension in cPanel → Select PHP Version → Extensions";
}

// Check upload directory
if (!is_writable($_SERVER['DOCUMENT_ROOT'] . '/uploads')) {
    $issues[] = "Set uploads/ directory permissions to 755: chmod 755 uploads/";
}

// Check for common database file issues
if (file_exists('includes/dbconn.php')) {
    $content = file_get_contents('includes/dbconn.php');
    if (strpos($content, '127.0.0.1') !== false) {
        $issues[] = "Change database host from '127.0.0.1' to 'localhost' in includes/dbconn.php";
    }
    if (strpos($content, '$password=""') !== false || strpos($content, '$password=\'\'') !== false) {
        $issues[] = "Set database password in includes/dbconn.php (should be 'Uttu@2025')";
    }
}

if (empty($issues)) {
    echo "<div class='alert alert-success'>";
    echo "✓ <strong>No issues detected!</strong> Your configuration looks good.";
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>";
    echo "<strong>Issues Found:</strong>";
    echo "<ol>";
    foreach ($issues as $issue) {
        echo "<li>" . $issue . "</li>";
    }
    echo "</ol>";
    echo "</div>";
}
echo "</div>";

// 9. Next Steps
echo "<div class='section'>";
echo "<h2>9. Next Steps</h2>";
echo "<ol>";
echo "<li>Fix any issues listed above</li>";
echo "<li>If database connection failed, update includes/dbconn.php with correct credentials</li>";
echo "<li>If tables are missing, import db/matrimony.sql in phpMyAdmin</li>";
echo "<li>Test your site: <a href='index.php'>Homepage</a> | <a href='admin/'>Admin Panel</a></li>";
echo "<li><strong>DELETE THIS FILE (debug.php) after debugging!</strong></li>";
echo "</ol>";
echo "</div>";

echo "<div class='alert alert-danger'>";
echo "<strong>⚠️ SECURITY WARNING:</strong> Delete this debug.php file immediately after use. ";
echo "It exposes sensitive server information!";
echo "</div>";

echo "</body></html>";
?>
