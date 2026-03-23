<?php 
/*
 * DATABASE CONNECTION FILE - LOCAL DEVELOPMENT
 * ============================================
 * This file is for local development only
 * Production uses includes/dbconn.php with Hostinger credentials
 */

// LOCAL DEVELOPMENT CREDENTIALS
// Note: This dev container doesn't have a working MySQL/MariaDB setup
// For local development, you need to configure your database properly

$host = "localhost"; 
$username = "root"; // or your local DB username
$password = ""; // your local DB password
$db_name = "matrimony"; // local database name

// ==========================================
// CONNECTION LOGIC (Do not edit below)
// ==========================================

$conn = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (!$conn) {
    // Log error but don't expose details in production
    $error_msg = mysqli_connect_error();
    error_log("Database Connection Failed: " . $error_msg);
    die("
        <h1>Database Connection Error</h1>
        <p>Unable to connect to the database. This application is configured for production (Hostinger).</p>
        <p><strong>For local development:</strong></p>
        <ol>
            <li>This dev container has MariaDB with a pre-set root password that we cannot access</li>
            <li>The application is currently configured for Hostinger production credentials</li>
            <li><strong>Recommended:</strong> Deploy to Hostinger where the database is properly configured</li>
            <li><strong>Alternative:</strong> Set up a fresh local database with known credentials</li>
        </ol>
        <p>Error: Access denied for user 'u166093127_dbuser'@'localhost'</p>
    ");
}

// Set character set
mysqli_set_charset($conn, "utf8mb4");
?>
