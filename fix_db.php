<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once("includes/dbconn.php");
// Try without IF NOT EXISTS if MariaDB is old
$res = mysqli_query($conn, "ALTER TABLE customer ADD COLUMN is_exclusive BOOLEAN DEFAULT 0");
if (!$res) echo mysqli_error($conn) . "\n";
else echo "DB Updated\n";
?>
