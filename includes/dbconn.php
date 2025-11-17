
<?php 

// Local development database configuration
// For Hostinger deployment, change these values:
// - host: localhost
// - username: u166093127_dbuser (your cPanel prefixed username)
// - db_name: u166093127_matrimony (cPanel prefix + matrimony)
$host = "127.0.0.1"; // Host name (use 127.0.0.1 for local Docker, 'localhost' for Hostinger)
$username = "root"; // Mysql username (use 'root' for local, cPanel username for Hostinger)
$password = "Uttu@2025"; // Mysql password
$db_name = "matrimony"; // Database name (use 'matrimony' for local, cPanel prefixed for Hostinger)

// Connect to server and select database.
$conn = mysqli_connect($host, $username, $password) or die("Cannot connect to database: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

mysqli_select_db($conn, $db_name) or die("Cannot select DB '" . $db_name . "': " . mysqli_error($conn));

?>