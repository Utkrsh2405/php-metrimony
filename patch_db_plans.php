<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once("includes/dbconn.php");

$queries = [
    "ALTER TABLE plans ADD COLUMN IF NOT EXISTS features TEXT AFTER duration_days;",
    "ALTER TABLE plans ADD COLUMN IF NOT EXISTS max_contacts INT DEFAULT 0 AFTER features;",
    "ALTER TABLE plans ADD COLUMN IF NOT EXISTS max_messages INT DEFAULT 0 AFTER max_contacts;",
    "ALTER TABLE plans ADD COLUMN IF NOT EXISTS max_interests INT DEFAULT 0 AFTER max_messages;",
    "ALTER TABLE plans ADD COLUMN IF NOT EXISTS max_shortlist INT DEFAULT 0 AFTER max_interests;",
    "ALTER TABLE plans ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0;",
    "ALTER TABLE plans ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;"
];

foreach ($queries as $query) {
    if (mysqli_query($conn, $query)) {
        echo "Successfully executed: $query<br>\n";
    } else {
        echo "Error executing $query: " . mysqli_error($conn) . "<br>\n";
    }
}
echo "Done.";
?>