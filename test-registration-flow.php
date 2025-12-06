<?php
/**
 * Test Registration Data Flow
 * This file tests if registration data flows correctly through the system
 */

require_once("includes/dbconn.php");

echo "<h1>Registration Data Flow Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Test 1: Check if tables exist and have correct structure
echo "<h2>Test 1: Database Structure</h2>";

$tables_check = [
    'users' => ['id', 'username', 'email', 'password', 'dateofbirth', 'gender', 'userlevel'],
    'customer' => ['id', 'cust_id', 'email', 'age', 'sex', 'height', 'religion', 'caste', 
                   'subcaste', 'state', 'district', 'country', 'maritalstatus', 'mothertounge',
                   'firstname', 'lastname', 'dateofbirth', 'profilecreatedby']
];

foreach ($tables_check as $table => $columns) {
    $result = mysqli_query($conn, "DESCRIBE $table");
    if ($result) {
        echo "<p class='success'>✓ Table '$table' exists</p>";
        $existing_columns = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $existing_columns[] = $row['Field'];
        }
        
        $missing = array_diff($columns, $existing_columns);
        if (empty($missing)) {
            echo "<p class='success'>✓ All required columns present in '$table'</p>";
        } else {
            echo "<p class='error'>✗ Missing columns in '$table': " . implode(', ', $missing) . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Table '$table' does not exist</p>";
    }
}

// Test 2: Check relationship between users and customer tables
echo "<h2>Test 2: Users ↔ Customer Relationship</h2>";

$sql = "SELECT u.id as user_id, u.username, u.email as user_email,
        c.id as customer_id, c.cust_id, c.email as customer_email, c.firstname,
        c.age, c.sex, c.religion, c.state
        FROM users u
        LEFT JOIN customer c ON u.id = c.cust_id
        ORDER BY u.id DESC
        LIMIT 10";

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    echo "<p class='success'>✓ Found " . mysqli_num_rows($result) . " user records</p>";
    echo "<table>";
    echo "<tr><th>User ID</th><th>Username</th><th>User Email</th><th>Customer ID</th><th>Cust ID (FK)</th><th>Match</th><th>Has Profile</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $match = ($row['user_id'] == $row['cust_id']) ? '<span class="success">✓</span>' : '<span class="error">✗</span>';
        $has_profile = $row['customer_id'] ? '<span class="success">Yes</span>' : '<span class="error">No</span>';
        
        echo "<tr>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['user_email']}</td>";
        echo "<td>{$row['customer_id']}</td>";
        echo "<td>{$row['cust_id']}</td>";
        echo "<td>$match</td>";
        echo "<td>$has_profile</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='info'>No user records found</p>";
}

// Test 3: Check which fields have data in customer table
echo "<h2>Test 3: Customer Profile Data Completeness</h2>";

$sql = "SELECT COUNT(*) as total,
        SUM(CASE WHEN firstname != '' THEN 1 ELSE 0 END) as has_firstname,
        SUM(CASE WHEN age > 0 THEN 1 ELSE 0 END) as has_age,
        SUM(CASE WHEN sex != '' THEN 1 ELSE 0 END) as has_sex,
        SUM(CASE WHEN height > 0 THEN 1 ELSE 0 END) as has_height,
        SUM(CASE WHEN religion != '' THEN 1 ELSE 0 END) as has_religion,
        SUM(CASE WHEN caste != '' THEN 1 ELSE 0 END) as has_caste,
        SUM(CASE WHEN state != '' THEN 1 ELSE 0 END) as has_state,
        SUM(CASE WHEN maritalstatus != '' THEN 1 ELSE 0 END) as has_maritalstatus,
        SUM(CASE WHEN mothertounge != '' THEN 1 ELSE 0 END) as has_mothertounge
        FROM customer";

$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $total = $row['total'];
    
    if ($total > 0) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Records with Data</th><th>Percentage</th></tr>";
        
        $fields = [
            'firstname' => 'First Name',
            'age' => 'Age',
            'sex' => 'Gender',
            'height' => 'Height',
            'religion' => 'Religion',
            'caste' => 'Caste',
            'state' => 'State',
            'maritalstatus' => 'Marital Status',
            'mothertounge' => 'Mother Tongue'
        ];
        
        foreach ($fields as $field => $label) {
            $count = $row["has_$field"];
            $percentage = round(($count / $total) * 100, 1);
            $class = $percentage >= 80 ? 'success' : ($percentage >= 50 ? 'info' : 'error');
            
            echo "<tr>";
            echo "<td>$label</td>";
            echo "<td>$count / $total</td>";
            echo "<td class='$class'>$percentage%</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p class='info'>No customer records found</p>";
    }
}

// Test 4: Sample profile data
echo "<h2>Test 4: Sample Profile Data (Latest 5)</h2>";

$sql = "SELECT c.cust_id, c.firstname, c.age, c.sex, c.height, c.religion, c.caste, 
        c.state, c.district, c.maritalstatus, c.mothertounge, c.email, c.dateofbirth
        FROM customer c
        ORDER BY c.id DESC
        LIMIT 5";

$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        echo "<h3>Profile ID: {$row['cust_id']}</h3>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Value</th><th>Status</th></tr>";
        
        foreach ($row as $field => $value) {
            if ($field == 'cust_id') continue;
            $status = (!empty($value) && $value != '0' && $value != '0000-00-00') ? 
                      '<span class="success">✓</span>' : 
                      '<span class="error">Empty</span>';
            $display_value = empty($value) ? '<em>-</em>' : htmlspecialchars($value);
            echo "<tr><td><strong>$field</strong></td><td>$display_value</td><td>$status</td></tr>";
        }
        echo "</table>";
        echo "</div>";
    }
} else {
    echo "<p class='info'>No customer records found</p>";
}

// Test 5: Search readiness
echo "<h2>Test 5: Search & Profile View Readiness</h2>";

echo "<p><strong>Checking if profiles are searchable...</strong></p>";

$sql = "SELECT COUNT(*) as searchable FROM customer c
        LEFT JOIN users u ON c.cust_id = u.id
        WHERE c.sex != '' 
        AND c.age > 0
        AND (u.userlevel = 0 OR u.userlevel IS NULL)";

$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $searchable = $row['searchable'];
    
    if ($searchable > 0) {
        echo "<p class='success'>✓ $searchable profiles are searchable</p>";
    } else {
        echo "<p class='error'>✗ No profiles are searchable yet</p>";
        echo "<p class='info'>Profiles need: gender (sex) and age to be searchable</p>";
    }
}

// Test 6: Check API compatibility
echo "<h2>Test 6: API Search Query Test</h2>";

$test_user_id = 1; // Use first user for test
$sql = "SELECT c.cust_id as id, c.firstname, c.lastname, c.sex as gender, c.age, c.height, 
        c.religion, c.maritalstatus as marital_status, c.education, c.occupation, 
        CONCAT(c.district, ', ', c.state) as location,
        c.mothertounge, c.caste, c.subcaste, c.country,
        u.username, u.profilestat
        FROM customer c
        LEFT JOIN users u ON c.cust_id = u.id
        WHERE c.cust_id != $test_user_id AND (u.userlevel = 0 OR u.userlevel IS NULL)
        LIMIT 5";

$result = mysqli_query($conn, $sql);
if ($result) {
    $count = mysqli_num_rows($result);
    if ($count > 0) {
        echo "<p class='success'>✓ API search query works! Found $count profiles</p>";
        echo "<p class='info'>Sample results:</p>";
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<li>ID: {$row['id']}, Name: {$row['firstname']} {$row['lastname']}, " .
                 "Gender: {$row['gender']}, Age: {$row['age']}, Location: {$row['location']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ API search query returned no results</p>";
    }
} else {
    echo "<p class='error'>✗ API search query failed: " . mysqli_error($conn) . "</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>To ensure registration data works properly:</strong></p>";
echo "<ol>";
echo "<li>Users must fill registration form completely</li>";
echo "<li>Registration saves to both 'users' and 'customer' tables</li>";
echo "<li>The 'cust_id' in customer table MUST match 'id' in users table</li>";
echo "<li>Search queries use 'c.cust_id' to link to users</li>";
echo "<li>Profile view uses 'cust_id' parameter in URL</li>";
echo "</ol>";

echo "<p><strong>Delete this file after testing!</strong></p>";

mysqli_close($conn);
?>
