<?php
require_once 'includes/dbconn.php';
$query = "ALTER TABLE customer ADD COLUMN mobile VARCHAR(20) DEFAULT NULL, ADD COLUMN phone_code VARCHAR(10) DEFAULT ''";
mysqli_query($conn, $query);
echo "Added mobile columns\n";
?>