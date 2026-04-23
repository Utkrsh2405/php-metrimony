<?php
require_once("includes/dbconn.php");

// Check if mobile column exists
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM customer WHERE Field = 'mobile'");
if (mysqli_num_rows($column_check) > 0) {
    echo "<h3 style='color: green;'>✓ Mobile column EXISTS in customer table</h3>";
} else {
    echo "<h3 style='color: red;'>✗ Mobile column DOES NOT exist in customer table</h3>";
}

// Check phone_code column
$phone_code_check = mysqli_query($conn, "SHOW COLUMNS FROM customer WHERE Field = 'phone_code'");
if (mysqli_num_rows($phone_code_check) > 0) {
    echo "<h3 style='color: green;'>✓ Phone_code column EXISTS</h3>";
} else {
    echo "<h3 style='color: red;'>✗ Phone_code column DOES NOT exist</h3>";
}

// Show sample data from first user
echo "<h3>Sample data from customer table:</h3>";
$result = mysqli_query($conn, "SELECT cust_id, firstname, lastname, mobile, phone_code FROM customer LIMIT 5");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Name</th><th>Mobile</th><th>Phone Code</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>".$row['cust_id']."</td><td>".$row['firstname']." ".$row['lastname']."</td><td>".($row['mobile'] ?? 'NULL')."</td><td>".($row['phone_code'] ?? 'NULL')."</td></tr>";
}
echo "</table>";

// Show all columns of customer table
echo "<h3>All columns in customer table:</h3>";
$columns = mysqli_query($conn, "SHOW COLUMNS FROM customer");
echo "<ul>";
while($col = mysqli_fetch_assoc($columns)) {
    echo "<li>".$col['Field']." (".$col['Type'].")</li>";
}
echo "</ul>";
?>
