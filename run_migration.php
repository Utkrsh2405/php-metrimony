 <?php
require_once("includes/dbconn.php");

echo "<h2>Running Migration: Add Mobile to Customer Table</h2>";

// SQL statements to execute
$migrations = array(
    "ALTER TABLE customer ADD COLUMN IF NOT EXISTS mobile VARCHAR(20) DEFAULT NULL AFTER email",
    "ALTER TABLE customer ADD COLUMN IF NOT EXISTS phone_code VARCHAR(5) DEFAULT '91' AFTER mobile"
);

$success = true;
foreach($migrations as $sql) {
    echo "<p><strong>Executing:</strong> " . htmlspecialchars($sql) . "</p>";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✓ Success</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . mysqli_error($conn) . "</p>";
        $success = false;
    }
}

if ($success) {
    echo "<h3 style='color: green;'>✓ Migration completed successfully!</h3>";
    
    // Verify columns exist
    echo "<h3>Verifying columns:</h3>";
    $result = mysqli_query($conn, "SHOW COLUMNS FROM customer WHERE Field IN ('mobile', 'phone_code')");
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>".$row['Field']."</td><td>".$row['Type']."</td><td>".$row['Null']."</td><td>".($row['Default'] ?? 'NULL')."</td></tr>";
    }
    echo "</table>";
} else {
    echo "<h3 style='color: red;'>✗ Migration had errors</h3>";
}

mysqli_close($conn);
?>
