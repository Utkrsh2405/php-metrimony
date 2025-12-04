<?php 
/*
 * DATABASE CONNECTION FILE
 * ========================
 * Hostinger Production Credentials
 */

// HOSTINGER CREDENTIALS - ACTIVE
$host = "localhost"; 
$username = "u166093127_dbuser"; 
$password = "Uttu@2405"; 
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
        die("Database connection error. Please try again later.");
    }
}

// Set character set to UTF-8
mysqli_set_charset($conn, "utf8mb4");

?>
