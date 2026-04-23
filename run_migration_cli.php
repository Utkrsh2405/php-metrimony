<?php
// Direct CLI migration runner
$host = "localhost";
$db_name = "u166093127_matrimony";
$db_user = "u166093127_dbuser";
$db_pass = "Uttu@2005";

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "\n");
}

echo "=== Running Migration: Add Mobile to Customer ===\n";

$migrations = array(
    "ALTER TABLE customer ADD COLUMN IF NOT EXISTS mobile VARCHAR(20) DEFAULT NULL AFTER email",
    "ALTER TABLE customer ADD COLUMN IF NOT EXISTS phone_code VARCHAR(5) DEFAULT '91' AFTER mobile"
);

$success = true;
foreach($migrations as $sql) {
    echo "\nExecuting: " . $sql . "\n";
    if (mysqli_query($conn, $sql)) {
        echo "✓ Success\n";
    } else {
        echo "✗ Error: " . mysqli_error($conn) . "\n";
        $success = false;
    }
}

if ($success) {
    echo "\n=== Migration completed successfully! ===\n";
    
    echo "\nVerifying columns:\n";
    $result = mysqli_query($conn, "SHOW COLUMNS FROM customer WHERE Field IN ('mobile', 'phone_code')");
    while($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . " (" . $row['Type'] . ") - Default: " . ($row['Default'] ?? 'NULL') . "\n";
    }
    
    echo "\nSample customer data:\n";
    $result = mysqli_query($conn, "SELECT cust_id, firstname, mobile, phone_code FROM customer LIMIT 3");
    while($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['cust_id'] . " | Name: " . $row['firstname'] . " | Mobile: " . ($row['mobile'] ?? 'NULL') . " | Code: " . ($row['phone_code'] ?? 'NULL') . "\n";
    }
} else {
    echo "\n✗ Migration had errors\n";
}

mysqli_close($conn);
?>
