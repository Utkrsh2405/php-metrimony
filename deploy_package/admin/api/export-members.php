<?php
// Export Members to CSV
session_start();

// Check admin authentication
if (!isset($_SESSION['id'])) {
    die('Unauthorized');
}

require_once("../../includes/dbconn.php");

// Verify admin
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    die('Unauthorized');
}

// Get filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;

// Build WHERE clause
$where = ["u.userlevel = 0"];

if ($search) {
    $where[] = "(u.username LIKE '%$search%' OR u.email LIKE '%$search%' OR c.firstname LIKE '%$search%' OR c.lastname LIKE '%$search%')";
}

if ($status) {
    $where[] = "u.account_status = '$status'";
}

if ($plan_id > 0) {
    $where[] = "us.plan_id = $plan_id";
}

$where_clause = implode(' AND ', $where);

// Get members
$query = "SELECT u.id, u.username, u.email, u.dateofbirth, u.account_status, 
                 u.profile_completeness, u.last_login, u.created_at,
                 c.firstname, c.lastname, c.sex, c.age, c.state, c.mobile, 
                 c.marital_status, c.religion, c.caste, c.education, c.occupation, 
                 c.income, c.height, c.weight, c.is_verified,
                 p.name as plan_name, us.start_date, us.end_date
          FROM users u
          LEFT JOIN customer c ON u.id = c.cust_id
          LEFT JOIN user_subscriptions us ON u.id = us.user_id AND us.status = 'active'
          LEFT JOIN plans p ON us.plan_id = p.id
          WHERE $where_clause
          ORDER BY u.id DESC";

$result = mysqli_query($conn, $query);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=members_export_' . date('Y-m-d_His') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// CSV Headers
fputcsv($output, [
    'ID',
    'Username',
    'Email',
    'First Name',
    'Last Name',
    'Gender',
    'Age',
    'Date of Birth',
    'Mobile',
    'State',
    'Marital Status',
    'Religion',
    'Caste',
    'Education',
    'Occupation',
    'Income',
    'Height',
    'Weight',
    'Account Status',
    'Verified',
    'Profile Completeness',
    'Current Plan',
    'Plan Start',
    'Plan End',
    'Last Login',
    'Registration Date'
]);

// CSV Data
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['id'],
        $row['username'],
        $row['email'],
        $row['firstname'],
        $row['lastname'],
        $row['sex'],
        $row['age'],
        $row['dateofbirth'],
        $row['mobile'],
        $row['state'],
        $row['marital_status'],
        $row['religion'],
        $row['caste'],
        $row['education'],
        $row['occupation'],
        $row['income'],
        $row['height'],
        $row['weight'],
        $row['account_status'],
        $row['is_verified'] == 1 ? 'Yes' : 'No',
        $row['profile_completeness'] . '%',
        $row['plan_name'] ?: 'Free',
        $row['start_date'],
        $row['end_date'],
        $row['last_login'],
        $row['created_at']
    ]);
}

fclose($output);
exit();
?>
