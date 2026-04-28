<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once("includes/dbconn.php");
$res = mysqli_query($conn, "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_subscribed BOOLEAN DEFAULT 0");
if (!$res) echo mysqli_error($conn) . "\n";
else echo "DB Sub Updated\n";
?>
