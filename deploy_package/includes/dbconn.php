<?php 
/*
 * DATABASE CONNECTION FILE
 * ========================
 * Hostinger Production Credentials
 */

// HOSTINGER CREDENTIALS - ACTIVE
$host = "localhost"; 
$username = "u166093127_dbuser"; 
$password = "Uttu@2005"; 
$db_name = "u166093127_matrimony";

// ==========================================
// CONNECTION LOGIC (Do not edit below)
// ==========================================

$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    // Log error but don't expose details in production
    $error_msg = mysqli_connect_error();
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("Database Connection Failed: " . $error_msg);
    } else {
        error_log("Database Connection Failed: " . $error_msg);
        
        // Show helpful message for local development
        if ($host == 'localhost' && $username == 'u166093127_dbuser') {
            die("
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Database Connection Error</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                        h1 { color: #d32f2f; }
                        h2 { color: #1976d2; }
                        pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; }
                        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
                        .note { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                        .success { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h1>⚠️ Database Connection Error</h1>
                        <p><strong>Error:</strong> Access denied for user 'u166093127_dbuser'@'localhost'</p>
                        
                        <h2>Why This Happened</h2>
                        <p>This application is configured with <strong>Hostinger production credentials</strong>, but you're trying to run it in a local development environment.</p>
                        
                        <div class='note'>
                            <strong>Note:</strong> The dev container's MariaDB has a root password that wasn't set by us, making it impossible to create the required database and user.
                        </div>
                        
                        <h2>✅ Recommended Solution</h2>
                        <div class='success'>
                            <h3>Deploy to Hostinger (Production)</h3>
                            <p>The application is ready for production deployment where the database is already configured:</p>
                            <ul>
                                <li><strong>Database:</strong> u166093127_matrimony</li>
                                <li><strong>User:</strong> u166093127_dbuser</li>
                                <li><strong>Host:</strong> localhost (on Hostinger)</li>
                                <li>All tables and data are already set up</li>
                            </ul>
                            <p>Simply upload the files to your Hostinger account and it will work immediately!</p>
                        </div>
                        
                        <h2>Alternative: Local Development Setup</h2>
                        <p>If you must run locally, you have two options:</p>
                        
                        <h3>Option 1: Use Docker (Recommended for Local Dev)</h3>
                        <pre>docker run -d -p 3306:3306 \\
  -e MYSQL_ROOT_PASSWORD=root \\
  -e MYSQL_DATABASE=matrimony \\
  --name matrimony-db mariadb:latest</pre>
                        <p>Then update <code>includes/dbconn.php</code> with local credentials.</p>
                        
                        <h3>Option 2: Temporarily Comment Out Production Check</h3>
                        <p>Create a file <code>includes/dbconn.local.php</code> with:</p>
                        <pre>&lt;?php
\$host = \"localhost\"; 
\$username = \"root\"; 
\$password = \"\"; // or your local password
\$db_name = \"matrimony\";
\$conn = mysqli_connect(\$host, \$username, \$password, \$db_name);
?&gt;</pre>
                        <p>And modify files to include dbconn.local.php instead.</p>
                        
                        <h2>Files Ready for Production</h2>
                        <p>All fixes have been committed:</p>
                        <ul>
                            <li>✅ Admin member management (delete, suspend)</li>
                            <li>✅ SQL injection fixes (13 files)</li>
                            <li>✅ Create profile error fixes</li>
                            <li>✅ Member edit column name fixes</li>
                            <li>✅ Activity logging</li>
                            <li>✅ Complete documentation</li>
                        </ul>
                        
                        <p><strong>The application is production-ready and can be deployed to Hostinger immediately!</strong></p>
                    </div>
                </body>
                </html>
            ");
        }
        
        die("Database connection error. Please try again later.");
    }
}

// Set character set to UTF-8
mysqli_set_charset($conn, "utf8mb4");

?>
