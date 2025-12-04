<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

// Check admin role
$user_id = intval($_SESSION['id']);
$role_check = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id LIMIT 1");
if (!$role_check || mysqli_num_rows($role_check) === 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}
$user = mysqli_fetch_assoc($role_check);
if ($user['userlevel'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - List interests with filters
if ($method === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Get statistics
    if ($action === 'stats') {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'accepted' => 0,
            'declined' => 0
        ];
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM interests");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['total'] = intval($row['cnt']);
        }
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM interests WHERE status = 'pending'");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['pending'] = intval($row['cnt']);
        }
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM interests WHERE status = 'accepted'");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['accepted'] = intval($row['cnt']);
        }
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM interests WHERE status = 'declined'");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['declined'] = intval($row['cnt']);
        }
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit();
    }
    
    // List interests with filters and pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 50;
    $offset = $page * $per_page;
    
    $where = [];
    if (isset($_GET['status']) && $_GET['status'] !== '') {
        $status = mysqli_real_escape_string($conn, $_GET['status']);
        $where[] = "i.status = '$status'";
    }
    if (isset($_GET['from_user']) && $_GET['from_user'] !== '') {
        $from_user = intval($_GET['from_user']);
        $where[] = "i.from_user_id = $from_user";
    }
    if (isset($_GET['to_user']) && $_GET['to_user'] !== '') {
        $to_user = intval($_GET['to_user']);
        $where[] = "i.to_user_id = $to_user";
    }
    
    $where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT i.*,
        u1.username as from_name,
        u2.username as to_name
        FROM interests i
        LEFT JOIN users u1 ON i.from_user_id = u1.id
        LEFT JOIN users u2 ON i.to_user_id = u2.id
        $where_sql
        ORDER BY i.created_at DESC
        LIMIT $per_page OFFSET $offset";
    
    $result = mysqli_query($conn, $query);
    $interests = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $interests[] = $row;
    }
    
    // Check if there are more records
    $count_query = "SELECT COUNT(*) as cnt FROM interests i $where_sql";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total = intval($count_row['cnt']);
    $has_more = ($offset + $per_page) < $total;
    
    echo json_encode([
        'success' => true,
        'data' => $interests,
        'total' => $total,
        'page' => $page,
        'has_more' => $has_more
    ]);
    exit();
}

// DELETE - Remove interest
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $interest_id = isset($input['interest_id']) ? intval($input['interest_id']) : 0;
    
    if ($interest_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid interest ID']);
        exit();
    }
    
    $query = "DELETE FROM interests WHERE id = $interest_id";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Interest deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete interest: ' . mysqli_error($conn)]);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Method not allowed']);
?>
