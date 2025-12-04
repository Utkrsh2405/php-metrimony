<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("../includes/dbconn.php");

$user_id = intval($_SESSION['id']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List shortlisted profiles
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    if ($action === 'list') {
        $query = "SELECT s.*, 
            u.name, u.age, u.location, u.occupation, c.verified
            FROM shortlists s
            LEFT JOIN users u ON s.profile_id = u.id
            LEFT JOIN customer c ON s.profile_id = c.user_id
            WHERE s.user_id = $user_id
            ORDER BY s.created_at DESC";
        
        $result = mysqli_query($conn, $query);
        $profiles = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $profiles[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $profiles,
            'count' => count($profiles)
        ]);
        exit();
    }
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $profile_id = isset($input['profile_id']) ? intval($input['profile_id']) : 0;
    if ($profile_id <= 0 || $profile_id == $user_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid profile id']);
        exit();
    }

    // Update notes if requested
    if (isset($input['update_notes']) && $input['update_notes'] === true) {
        $notes = isset($input['notes']) ? mysqli_real_escape_string($conn, $input['notes']) : '';
        $query = "UPDATE shortlists SET notes = '$notes' WHERE user_id = $user_id AND profile_id = $profile_id";
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Notes updated']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update notes']);
        }
        exit();
    }

    // Check if already shortlisted
    $check = mysqli_query($conn, "SELECT id FROM shortlists WHERE user_id = $user_id AND profile_id = $profile_id LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        // Already exists - remove (toggle)
        $row = mysqli_fetch_assoc($check);
        if (mysqli_query($conn, "DELETE FROM shortlists WHERE id = " . intval($row['id']))) {
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from shortlist']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to remove shortlist']);
        }
        exit();
    }

    // Enforce shortlist quota if plans set
    $plan_query = mysqli_query($conn, "SELECT us.*, p.max_shortlist FROM user_subscriptions us 
        LEFT JOIN plans p ON us.plan_id = p.id 
        WHERE us.user_id = $user_id AND us.status = 'active' ORDER BY us.end_date DESC LIMIT 1");
    $plan_limit = 0;
    $sub_start = null; $sub_end = null;
    if (mysqli_num_rows($plan_query) > 0) {
        $plan = mysqli_fetch_assoc($plan_query);
        $plan_limit = intval($plan['max_shortlist']);
        $sub_start = $plan['start_date'];
        $sub_end = $plan['end_date'];
    }

    // Count current shortlist items
    $count_query = "SELECT COUNT(*) as cnt FROM shortlists WHERE user_id = $user_id";
    $result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($result);
    $current_count = intval($count_row['cnt']);

    if ($plan_limit > 0 && $current_count >= $plan_limit) {
        echo json_encode(['success' => false, 'error' => 'Shortlist quota reached for your current plan']);
        exit();
    }

    // Insert shortlist
    $query = "INSERT INTO shortlists (user_id, profile_id, created_at) VALUES ($user_id, $profile_id, NOW())";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to shortlist']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add to shortlist: ' . mysqli_error($conn)]);
    }
    exit();
}

else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>