<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("../includes/dbconn.php");

$sender = intval($_SESSION['id']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$receiver = isset($input['receiver_id']) ? intval($input['receiver_id']) : 0;
$message = isset($input['message']) ? substr($input['message'], 0, 2000) : null;

if ($receiver <= 0 || $receiver == $sender) {
    echo json_encode(['success' => false, 'error' => 'Invalid receiver']);
    exit();
}

// Check receiver exists
$res = mysqli_query($conn, "SELECT id FROM users WHERE id = $receiver LIMIT 1");
if (mysqli_num_rows($res) == 0) {
    echo json_encode(['success' => false, 'error' => 'Receiver not found']);
    exit();
}

// Check duplicate
$check = mysqli_query($conn, "SELECT * FROM interests WHERE from_user_id = $sender AND to_user_id = $receiver LIMIT 1");
if (mysqli_num_rows($check) > 0) {
    $row = mysqli_fetch_assoc($check);
    echo json_encode(['success' => false, 'error' => 'You have already expressed interest', 'status' => $row['status']]);
    exit();
}

// Determine sender plan limit
$plan_query = mysqli_query($conn, "SELECT us.*, p.max_interests_express FROM user_subscriptions us 
    LEFT JOIN plans p ON us.plan_id = p.id 
    WHERE us.user_id = $sender AND us.status = 'active' ORDER BY us.end_date DESC LIMIT 1");

$plan_limit = 0; // 0 = unlimited
$sub_start = null;
$sub_end = null;
if (mysqli_num_rows($plan_query) > 0) {
    $plan = mysqli_fetch_assoc($plan_query);
    $plan_limit = intval($plan['max_interests_express']);
    $sub_start = $plan['start_date'];
    $sub_end = $plan['end_date'];
}

// Count interests sent within subscription window (if window exists)
$count_query = "SELECT COUNT(*) as cnt FROM interests WHERE from_user_id = $sender";
if ($sub_start && $sub_end) {
    $count_query .= " AND created_at >= '" . mysqli_real_escape_string($conn, $sub_start) . "' AND created_at <= '" . mysqli_real_escape_string($conn, $sub_end) . "'";
}
$result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($result);
$sent_count = intval($count_row['cnt']);

if ($plan_limit > 0 && $sent_count >= $plan_limit) {
    echo json_encode(['success' => false, 'error' => 'Interest quota reached for your current plan']);
    exit();
}

// Insert interest
$sender = intval($sender);
$receiver = intval($receiver);
$message_escaped = $message ? "'" . mysqli_real_escape_string($conn, $message) . "'" : "NULL";
$query = "INSERT INTO interests (from_user_id, to_user_id, status, message, created_at, updated_at) 
          VALUES ($sender, $receiver, 'pending', $message_escaped, NOW(), NOW())";

if (!mysqli_query($conn, $query)) {
    echo json_encode(['success' => false, 'error' => 'Failed to express interest: ' . mysqli_error($conn)]);
    exit();
}

$interest_id = mysqli_insert_id($conn);

// Update quota usage table
$today = date('Y-m-d');
$qup = mysqli_query($conn, "INSERT INTO interest_quota_usage (user_id, date, interests_sent) 
            VALUES ($sender, '$today', 1) ON DUPLICATE KEY UPDATE interests_sent = interests_sent + 1");

// Increment receiver's received count (if row exists)
$qup2 = mysqli_query($conn, "INSERT INTO interest_quota_usage (user_id, date, interests_received) 
            VALUES ($receiver, '$today', 1) ON DUPLICATE KEY UPDATE interests_received = interests_received + 1");

// Optionally: trigger SMS/email notification here (left as future enhancement)

echo json_encode(['success' => true, 'message' => 'Interest expressed successfully', 'id' => $interest_id]);

?>