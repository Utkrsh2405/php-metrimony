<?php
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
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$plan_id = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;
$payment_method = isset($_GET['payment_method']) ? mysqli_real_escape_string($conn, $_GET['payment_method']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($conn, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($conn, $_GET['date_to']) : '';

// Build WHERE clause
$where = ["1=1"];

if ($status) {
    $where[] = "p.status = '$status'";
}

if ($plan_id > 0) {
    $where[] = "p.plan_id = $plan_id";
}

if ($payment_method) {
    $where[] = "p.payment_method = '$payment_method'";
}

if ($date_from) {
    $where[] = "DATE(p.created_at) >= '$date_from'";
}

if ($date_to) {
    $where[] = "DATE(p.created_at) <= '$date_to'";
}

$where_clause = implode(' AND ', $where);

// Get all payments
$query = "SELECT p.*, 
                 pl.name as plan_name,
                 u.username, u.email,
                 c.firstname, c.lastname, c.mobile
          FROM payments p
          LEFT JOIN plans pl ON p.plan_id = pl.id
          LEFT JOIN users u ON p.user_id = u.id
          LEFT JOIN customer c ON u.id = c.cust_id
          WHERE $where_clause
          ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=payments_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add CSV headers
fputcsv($output, [
    'Payment ID',
    'Date',
    'Member ID',
    'Member Name',
    'Email',
    'Mobile',
    'Plan',
    'Amount',
    'Refund Amount',
    'Payment Method',
    'Transaction ID',
    'Status',
    'Notes',
    'Created At'
]);

// Add data rows
while ($row = mysqli_fetch_assoc($result)) {
    $name = trim(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')) ?: $row['username'];
    
    fputcsv($output, [
        $row['id'],
        date('Y-m-d', strtotime($row['created_at'])),
        $row['user_id'],
        $name,
        $row['email'],
        $row['mobile'] ?? '',
        $row['plan_name'],
        $row['amount'],
        $row['refund_amount'] ?? '0.00',
        $row['payment_method'] ?? '',
        $row['transaction_id'] ?? '',
        $row['status'],
        $row['notes'] ?? '',
        $row['created_at']
    ]);
}

fclose($output);
exit();
?>
