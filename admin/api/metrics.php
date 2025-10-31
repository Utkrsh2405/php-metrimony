<?php
// Admin Dashboard Metrics API
session_start();
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

// Verify admin access
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$metrics = [];

// Total Members
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE userlevel = 0");
$row = mysqli_fetch_assoc($result);
$metrics['total_members'] = $row['count'];

// Active Members (logged in last 30 days)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE userlevel = 0 AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$row = mysqli_fetch_assoc($result);
$metrics['active_members'] = $row['count'];

// Pending Approvals (profiles not verified)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM customer WHERE is_verified = 0");
$row = mysqli_fetch_assoc($result);
$metrics['pending_approvals'] = $row['count'];

// Active Subscriptions
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active' AND end_date >= CURDATE()");
$row = mysqli_fetch_assoc($result);
$metrics['active_subscriptions'] = $row['count'];

// Total Payments This Month
$result = mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$row = mysqli_fetch_assoc($result);
$metrics['monthly_revenue'] = number_format($row['total'], 2);

// Unread Messages Count
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM messages WHERE is_read = 0");
$row = mysqli_fetch_assoc($result);
$metrics['unread_messages'] = $row['count'];

// Pending Interests
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM interests WHERE status = 'pending'");
$row = mysqli_fetch_assoc($result);
$metrics['pending_interests'] = $row['count'];

// New Members This Week
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE userlevel = 0 AND dateofbirth >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$row = mysqli_fetch_assoc($result);
$metrics['new_members_week'] = $row['count'];

// Member Growth (percentage change from last month)
$result = mysqli_query($conn, "SELECT 
    (SELECT COUNT(*) FROM users WHERE userlevel = 0 AND MONTH(dateofbirth) = MONTH(CURDATE())) as current_month,
    (SELECT COUNT(*) FROM users WHERE userlevel = 0 AND MONTH(dateofbirth) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) as last_month
");
$row = mysqli_fetch_assoc($result);
$current = $row['current_month'];
$last = $row['last_month'];
if ($last > 0) {
    $metrics['member_growth_percent'] = round((($current - $last) / $last) * 100, 1);
} else {
    $metrics['member_growth_percent'] = 0;
}

// Revenue Growth
$result = mysqli_query($conn, "SELECT 
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURDATE())) as current_month,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))) as last_month
");
$row = mysqli_fetch_assoc($result);
$current = $row['current_month'];
$last = $row['last_month'];
if ($last > 0) {
    $metrics['revenue_growth_percent'] = round((($current - $last) / $last) * 100, 1);
} else {
    $metrics['revenue_growth_percent'] = 0;
}

// Plan Distribution
$result = mysqli_query($conn, "
    SELECT p.name, COUNT(us.id) as count 
    FROM plans p 
    LEFT JOIN user_subscriptions us ON p.id = us.plan_id AND us.status = 'active' 
    GROUP BY p.id, p.name
    ORDER BY count DESC
");
$plan_distribution = [];
while ($row = mysqli_fetch_assoc($result)) {
    $plan_distribution[] = $row;
}
$metrics['plan_distribution'] = $plan_distribution;

// Recent Registrations (last 5)
$result = mysqli_query($conn, "
    SELECT u.id, u.username, u.email, u.dateofbirth as created_at, c.firstname, c.lastname
    FROM users u
    LEFT JOIN customer c ON u.id = c.cust_id
    WHERE u.userlevel = 0
    ORDER BY u.id DESC
    LIMIT 5
");
$recent_registrations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recent_registrations[] = [
        'id' => $row['id'],
        'name' => trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')) ?: $row['username'],
        'email' => $row['email'],
        'date' => $row['created_at']
    ];
}
$metrics['recent_registrations'] = $recent_registrations;

// Recent Payments (last 5)
$result = mysqli_query($conn, "
    SELECT p.id, p.amount, p.currency, p.status, p.created_at, u.username, u.email
    FROM payments p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT 5
");
$recent_payments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $recent_payments[] = $row;
}
$metrics['recent_payments'] = $recent_payments;

echo json_encode($metrics);
?>
