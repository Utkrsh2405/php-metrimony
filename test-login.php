<?php
// Simple login test script - DELETE AFTER TESTING
session_start();
require_once("includes/dbconn.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "<h3>Debug Info:</h3>";
    echo "<p>Username submitted: " . htmlspecialchars($username) . "</p>";
    
    // Check if user exists
    $stmt = mysqli_prepare($conn, "SELECT id, username, password, userlevel, account_status FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo "<p style='color: green;'>✓ User found in database!</p>";
        echo "<p>User ID: " . $row['id'] . "</p>";
        echo "<p>Username: " . htmlspecialchars($row['username']) . "</p>";
        echo "<p>Userlevel: " . ($row['userlevel'] ?? 'NULL') . "</p>";
        echo "<p>Account Status: " . ($row['account_status'] ?? 'NULL') . "</p>";
        echo "<p>Password hash in DB: " . substr($row['password'], 0, 20) . "...</p>";
        
        // Test password
        echo "<h4>Password Check:</h4>";
        $password_hash = $row['password'];
        
        // Check if it's bcrypt
        if (password_verify($password, $password_hash)) {
            echo "<p style='color: green;'>✓ Password matches (bcrypt)</p>";
        } 
        // Check if it's MD5
        elseif ($password_hash === md5($password)) {
            echo "<p style='color: green;'>✓ Password matches (MD5)</p>";
        }
        // Check if it's plain text
        elseif ($password_hash === $password) {
            echo "<p style='color: green;'>✓ Password matches (plain text)</p>";
        }
        else {
            echo "<p style='color: red;'>✗ Password does NOT match</p>";
            echo "<p>MD5 of your password: " . md5($password) . "</p>";
            echo "<p>Your plain password: " . htmlspecialchars($password) . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ User NOT found in database</p>";
        
        // Check if username exists at all
        $check = mysqli_query($conn, "SELECT username FROM users LIMIT 5");
        echo "<p>Sample usernames in database:</p><ul>";
        while ($u = mysqli_fetch_assoc($check)) {
            echo "<li>" . htmlspecialchars($u['username']) . "</li>";
        }
        echo "</ul>";
    }
    
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body { font-family: Arial; margin: 40px; }
        form { background: #f5f5f5; padding: 20px; max-width: 400px; border-radius: 8px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Login Test Script</h2>
    <p style="color: red;"><strong>⚠️ DELETE THIS FILE AFTER TESTING!</strong></p>
    
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>
        
        <label>Password:</label>
        <input type="password" name="password" required>
        
        <button type="submit">Test Login</button>
    </form>
</body>
</html>
