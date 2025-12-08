<?php
// Admin Members API - CRUD operations
session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

// Verify admin
$user_id = intval($_SESSION['id']);
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Initialize activity logger
require_once("../../includes/activity-logger.php");
$logger = new ActivityLogger($conn, $user_id);

$method = $_SERVER['REQUEST_METHOD'];

// GET - List members with filters
if ($method == 'GET') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
    $offset = ($page - 1) * $limit;
    
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
    
    // Get total count
    $count_query = "SELECT COUNT(DISTINCT u.id) as total 
                    FROM users u
                    LEFT JOIN customer c ON u.id = c.cust_id
                    LEFT JOIN user_subscriptions us ON u.id = us.user_id AND us.status = 'active'
                    WHERE $where_clause";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total = $count_row['total'];
    
    // Get members
    $query = "SELECT u.id, u.username, u.email, u.dateofbirth, u.account_status, 
                     u.profile_completeness, u.last_login,
                     c.firstname, c.lastname, c.sex, c.age, c.state, c.is_verified,
                     p.name as plan_name, us.end_date as subscription_end
              FROM users u
              LEFT JOIN customer c ON u.id = c.cust_id
              LEFT JOIN user_subscriptions us ON u.id = us.user_id AND us.status = 'active'
              LEFT JOIN plans p ON us.plan_id = p.id
              WHERE $where_clause
              ORDER BY u.id DESC
              LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $query);
    $members = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $members[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $members,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]
    ]);
}

// POST - Update member status/details
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $member_id = isset($data['member_id']) ? intval($data['member_id']) : 0;
    
    if ($member_id == 0) {
        echo json_encode(['error' => 'Invalid member ID']);
        exit();
    }
    
    switch ($action) {
        case 'suspend':
            $query = "UPDATE users SET account_status = 'suspended' WHERE id = $member_id";
            if (mysqli_query($conn, $query)) {
                $logger->log('suspend', 'member', $member_id, "Suspended member #$member_id");
                echo json_encode(['success' => true, 'message' => 'Member suspended']);
            } else {
                echo json_encode(['error' => 'Failed to suspend member']);
            }
            break;
            
        case 'activate':
            $query = "UPDATE users SET account_status = 'active' WHERE id = $member_id";
            if (mysqli_query($conn, $query)) {
                $logger->log('activate', 'member', $member_id, "Activated member #$member_id");
                echo json_encode(['success' => true, 'message' => 'Member activated']);
            } else {
                echo json_encode(['error' => 'Failed to activate member']);
            }
            break;
            
        case 'verify':
            $query = "UPDATE customer SET is_verified = 1 WHERE cust_id = $member_id";
            if (mysqli_query($conn, $query)) {
                $logger->log('verify', 'member', $member_id, "Verified member #$member_id");
                echo json_encode(['success' => true, 'message' => 'Member verified']);
            } else {
                echo json_encode(['error' => 'Failed to verify member']);
            }
            break;
            
        case 'delete':
            // Permanently delete from database
            // First, get user details for logging
            $user_query = mysqli_query($conn, "SELECT username, email FROM users WHERE id = $member_id");
            $user_data = mysqli_fetch_assoc($user_query);
            $username = $user_data['username'] ?? "ID#$member_id";
            
            // Delete from customer table first (foreign key relationship)
            mysqli_query($conn, "DELETE FROM customer WHERE cust_id = $member_id");
            
            // Delete from other related tables
            mysqli_query($conn, "DELETE FROM user_subscriptions WHERE user_id = $member_id");
            mysqli_query($conn, "DELETE FROM interests WHERE sender_id = $member_id OR receiver_id = $member_id");
            mysqli_query($conn, "DELETE FROM shortlist WHERE user_id = $member_id OR shortlisted_user_id = $member_id");
            mysqli_query($conn, "DELETE FROM messages WHERE sender_id = $member_id OR receiver_id = $member_id");
            
            // Finally delete from users table
            $query = "DELETE FROM users WHERE id = $member_id AND userlevel = 0";
            if (mysqli_query($conn, $query)) {
                $logger->log('delete', 'member', $member_id, "Permanently deleted member: $username");
                echo json_encode(['success' => true, 'message' => 'Member permanently deleted from database']);
            } else {
                echo json_encode(['error' => 'Failed to delete member: ' . mysqli_error($conn)]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}

// DELETE - Permanently delete member
elseif ($method == 'DELETE') {
    $member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($member_id == 0) {
        echo json_encode(['error' => 'Invalid member ID']);
        exit();
    }
    
    // Delete from all related tables (cascade should handle most)
    $query = "DELETE FROM users WHERE id = $member_id AND userlevel = 0";
    if (mysqli_query($conn, $query)) {
        $logger->log('permanent_delete', 'member', $member_id, "Permanently deleted member #$member_id");
        echo json_encode(['success' => true, 'message' => 'Member permanently deleted']);
    } else {
        echo json_encode(['error' => 'Failed to delete member']);
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
