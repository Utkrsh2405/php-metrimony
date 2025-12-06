<?php
/**
 * API: Check Email Availability
 * Returns JSON response indicating if email is available
 */

header('Content-Type: application/json');

// Include database connection
require_once('../includes/dbconn.php');

// Get email from request
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate email format
if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter an email address'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

// Check if email exists in database
$email_escaped = mysqli_real_escape_string($conn, $email);
$query = "SELECT COUNT(*) as count FROM customer WHERE email = '$email_escaped'";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
    exit;
}

$row = mysqli_fetch_assoc($result);
$exists = $row['count'] > 0;

if ($exists) {
    echo json_encode([
        'success' => false,
        'available' => false,
        'message' => 'This email is already registered. Please use a different email or <a href="login.php">login</a>.'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'available' => true,
        'message' => 'This email is available!'
    ]);
}

mysqli_close($conn);
?>
