<?php
/**
 * Quick Database Connection Test
 * Delete this file after testing
 */

echo "<h1>Database Connection Test</h1>";

$host = 'localhost';
$db_name = 'u166093127_matrimony';
$db_user = 'u166093127_dbuser';
$db_pass = 'Uttu@2005';

echo "<p><strong>Testing connection with:</strong></p>";
echo "<ul>";
echo "<li>Host: " . htmlspecialchars($host) . "</li>";
echo "<li>Database: " . htmlspecialchars($db_name) . "</li>";
echo "<li>Username: " . htmlspecialchars($db_user) . "</li>";
echo "<li>Password: " . str_repeat('*', strlen($db_pass)) . "</li>";
echo "</ul>";

$conn = @mysqli_connect($host, $db_user, $db_pass, $db_name);

if ($conn) {
    echo "<p style='color: green; font-weight: bold;'>✓ SUCCESS! Database connection established.</p>";
    
    // Test a simple query
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p>✓ Found {$row['count']} users in the database.</p>";
    }
    
    mysqli_close($conn);
    
    echo "<hr>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Delete this test-connection.php file</li>";
    echo "<li>Visit <a href='index.php'>index.php</a> to see your site</li>";
    echo "<li>Visit <a href='admin/'>admin/</a> to access admin panel</li>";
    echo "</ol>";
    
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ FAILED! Could not connect to database.</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars(mysqli_connect_error()) . "</p>";
    echo "<p><strong>Error Code:</strong> " . mysqli_connect_errno() . "</p>";
    
    echo "<hr>";
    echo "<p><strong>Troubleshooting:</strong></p>";
    echo "<ul>";
    echo "<li>Verify credentials in Hostinger cPanel → MySQL Databases</li>";
    echo "<li>Make sure the user has ALL PRIVILEGES on the database</li>";
    echo "<li>Check if the database exists</li>";
    echo "</ul>";
}
?>
