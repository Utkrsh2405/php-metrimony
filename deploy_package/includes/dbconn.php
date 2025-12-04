<?php 
/*
 * DATABASE CONNECTION FILE
 * ------------------------
 * This file automatically loads config from config.php if it exists,
 * otherwise uses the fallback values below.
 * 
 * For Hostinger deployment:
 * - Use the install.php wizard, OR
 * - Copy config.sample.php to config.php and update values
 */

// ==========================================
// LOAD CONFIG FILE IF EXISTS
// ==========================================
$config_file = __DIR__ . '/../config.php';

if (file_exists($config_file)) {
    require_once($config_file);
    
    // Use config constants
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $username = defined('DB_USER') ? DB_USER : 'root';
    $password = defined('DB_PASS') ? DB_PASS : '';
    $db_name = defined('DB_NAME') ? DB_NAME : 'matrimony';
} else {
    // ==========================================
    // FALLBACK CONFIGURATION (Development)
    // ==========================================
    // Edit these values for local development
    // For production, use config.php instead
    
    $host = "127.0.0.1"; 
    $username = "root"; 
    $password = "Uttu@2025"; 
    $db_name = "matrimony"; 
}

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
