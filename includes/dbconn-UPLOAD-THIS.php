
<?php 

$host="localhost"; // Host name (MUST be localhost for Hostinger, not 127.0.0.1)
$username="u166093127_dbuser"; // Mysql username 
$password="Uttu@2025"; // Mysql password 
$db_name="u166093127_matrimony"; // Database name 

// Connect to server and select databse.
$conn=mysqli_connect("$host", "$username", "$password")or die("cannot connect"); 

mysqli_select_db($conn,"$db_name")or die("cannot select DB");

?>
