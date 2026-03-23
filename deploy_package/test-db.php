<?php
/**
 * Database Connection Test
 * Upload this to Hostinger and visit it to test the connection
 * DELETE THIS FILE AFTER TESTING!
 */

echo "<h1>Database Connection Test</h1>";

// Hostinger Credentials
$host = "localhost";
$username = "u166093127_dbuser";
$password = "Uttu@2005";
$db_name = "u166093127_matrimony";

echo "<h3>Testing with:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Username:</strong> $username</li>";
echo "<li><strong>Password:</strong> " . str_repeat('*', strlen($password)) . "</li>";
echo "<li><strong>Database:</strong> $db_name</li>";
echo "</ul>";

echo "<h3>Connection Test:</h3>";

// Test 1: Connect without database
echo "<p>1. Connecting to MySQL server... ";
$conn = @mysqli_connect($host, $username, $password);
if ($conn) {
    echo "<span style='color:green'>✓ SUCCESS</span></p>";
    
    // Test 2: Select database
    echo "<p>2. Selecting database... ";
    if (@mysqli_select_db($conn, $db_name)) {
        echo "<span style='color:green'>✓ SUCCESS</span></p>";
        
        // Test 3: Query tables
        echo "<p>3. Checking tables... ";
        $result = mysqli_query($conn, "SHOW TABLES");
        if ($result) {
            $count = mysqli_num_rows($result);
            echo "<span style='color:green'>✓ Found $count tables</span></p>";
            
            if ($count > 0) {
                echo "<ul>";
                while ($row = mysqli_fetch_array($result)) {
                    echo "<li>" . $row[0] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color:orange'>⚠ Database is empty. You need to import db/matrimony.sql</p>";
            }
        } else {
            echo "<span style='color:red'>✗ FAILED: " . mysqli_error($conn) . "</span></p>";
        }
    } else {
        echo "<span style='color:red'>✗ FAILED: " . mysqli_error($conn) . "</span></p>";
    }
    
    mysqli_close($conn);
} else {
    echo "<span style='color:red'>✗ FAILED</span></p>";
    echo "<p style='color:red'><strong>Error:</strong> " . mysqli_connect_error() . "</p>";
    
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Check if username 'u166093127_dbuser' exists in cPanel → MySQL Databases</li>";
    echo "<li>Check if password is correct (try resetting it)</li>";
    echo "<li>Make sure user is added to database with ALL PRIVILEGES</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><small>Delete this file after testing!</small></p>";
?>
