<?php
require_once("includes/dbconn.php");
$query = "ALTER TABLE users ADD COLUMN is_subscribed TINYINT(1) DEFAULT 0";
$result = mysqli_query($conn, $query);
if ($result) {
    echo "Column added successfully.\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}
?>
