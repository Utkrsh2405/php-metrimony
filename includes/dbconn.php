
<?php 

// Hostinger-ready database configuration
// Replace the values below if you used different names when creating the DB in cPanel
$host = "localhost"; // Host name (Hostinger expects 'localhost')
$username = "u166093127_dbuser"; // Mysql username (your cPanel prefixed username)
$password = "Uttu@2025"; // Mysql password
$db_name = "u166093127_matrimony"; // Database name (cPanel prefix + matrimony)

// Connect to server and select database.
$conn = mysqli_connect($host, $username, $password) or die("Cannot connect to database: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

mysqli_select_db($conn, $db_name) or die("Cannot select DB '" . $db_name . "': " . mysqli_error($conn));

?>