<?php
header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE HTML>
<html>
<head>
<title>Database Migration - Add Mobile to Customer</title>
<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background-color: #f5f5f5;
}
.container {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
h1 {
    color: #333;
    border-bottom: 3px solid #667eea;
    padding-bottom: 10px;
}
.success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 12px;
    border-radius: 4px;
    margin: 15px 0;
}
.error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 12px;
    border-radius: 4px;
    margin: 15px 0;
}
.info {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
    padding: 12px;
    border-radius: 4px;
    margin: 15px 0;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
table th {
    background-color: #f0f0f0;
    font-weight: bold;
}
button {
    background-color: #667eea;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}
button:hover {
    background-color: #5568d3;
}
.status-ok {
    color: green;
    font-weight: bold;
}
.status-missing {
    color: red;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
    <h1>Database Migration Tool</h1>
    
<?php
require_once("includes/dbconn.php");

$run_migration = isset($_POST['run_migration']);

if ($run_migration) {
    echo "<h2>Running Migration...</h2>";
    
    $migrations = array(
        "ALTER TABLE customer ADD COLUMN IF NOT EXISTS mobile VARCHAR(20) DEFAULT NULL AFTER email",
        "ALTER TABLE customer ADD COLUMN IF NOT EXISTS phone_code VARCHAR(5) DEFAULT '91' AFTER mobile"
    );
    
    $success = true;
    foreach($migrations as $sql) {
        echo "<p><strong>Executing:</strong></p>";
        echo "<pre>" . htmlspecialchars($sql) . "</pre>";
        
        if (mysqli_query($conn, $sql)) {
            echo "<p class='success'>✓ Success</p>";
        } else {
            echo "<p class='error'>✗ Error: " . htmlspecialchars(mysqli_error($conn)) . "</p>";
            $success = false;
        }
    }
    
    if ($success) {
        echo "<h2 style='color: green;'>✓ Migration Completed Successfully!</h2>";
    }
}

echo "<h2>Current Database Status</h2>";

$mobile_exists = false;
$phone_code_exists = false;

$result = mysqli_query($conn, "SHOW COLUMNS FROM customer WHERE Field IN ('mobile', 'phone_code')");
if (mysqli_num_rows($result) > 0) {
    echo "<h3>Column Details:</h3>";
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
        
        if ($row['Field'] === 'mobile') $mobile_exists = true;
        if ($row['Field'] === 'phone_code') $phone_code_exists = true;
    }
    echo "</table>";
}

echo "<h3>Column Status:</h3>";
echo "<ul>";
echo "<li>Mobile column: <span class='" . ($mobile_exists ? 'status-ok' : 'status-missing') . "'>" . ($mobile_exists ? '✓ EXISTS' : '✗ MISSING') . "</span></li>";
echo "<li>Phone Code column: <span class='" . ($phone_code_exists ? 'status-ok' : 'status-missing') . "'>" . ($phone_code_exists ? '✓ EXISTS' : '✗ MISSING') . "</span></li>";
echo "</ul>";

if ($mobile_exists) {
    echo "<h3>Sample Customer Data:</h3>";
    $result = mysqli_query($conn, "SELECT cust_id, firstname, lastname, mobile, phone_code FROM customer ORDER BY cust_id DESC LIMIT 5");
    if (mysqli_num_rows($result) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Mobile</th><th>Phone Code</th></tr>";
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['cust_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['firstname'] . " " . $row['lastname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['mobile'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['phone_code'] ?? '91') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

if (!$mobile_exists || !$phone_code_exists) {
    echo "<div class='info'>";
    echo "<p>The mobile and phone_code columns are missing from the customer table.</p>";
    echo "<p>Click the button below to run the migration and add these columns.</p>";
    echo "</div>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='run_migration'>Run Migration</button>";
    echo "</form>";
} else {
    echo "<div class='success'>";
    echo "<p>✓ All required columns exist! The database is ready.</p>";
    echo "</div>";
}

mysqli_close($conn);
?>

</div>
</body>
</html>
