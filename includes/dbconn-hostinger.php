<?php 
/**
 * HOSTINGER DATABASE CONNECTION
 * 
 * IMPORTANT: Update these values with your Hostinger cPanel credentials!
 * 
 * To find your credentials:
 * 1. Login to cPanel
 * 2. Go to MySQL Databases
 * 3. Your database name will be: cpanelusername_matrimony
 * 4. Your username will be: cpanelusername_dbuser
 * 
 * Example:
 * If your cPanel username is "mysite", then:
 * - Database: mysite_matrimony
 * - Username: mysite_dbuser
 */

// CHANGE THESE VALUES ↓↓↓
$host = "localhost";  // Keep as "localhost" for Hostinger (NOT 127.0.0.1)
$username = "YOUR_CPANEL_USERNAME_dbuser";  // Example: mysite_dbuser
$password = "Uttu@2025";  // Your database password
$db_name = "YOUR_CPANEL_USERNAME_matrimony";  // Example: mysite_matrimony

// Connect to database
$conn = mysqli_connect($host, $username, $password) or die("Cannot connect to database. Error: " . mysqli_connect_error()); 
mysqli_select_db($conn, $db_name) or die("Cannot select database '$db_name'. Error: " . mysqli_error($conn));

// Set charset to UTF-8 for proper character support
mysqli_set_charset($conn, "utf8mb4");

// Optional: Uncomment to test connection
// echo "Database connected successfully!";
?>
