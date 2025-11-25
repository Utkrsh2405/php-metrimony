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

// GET - List messages or get stats
if ($method === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Get statistics
    if ($action === 'stats') {
        $stats = [
            'total' => 0,
            'read' => 0,
            'unread' => 0,
            'today' => 0
        ];
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['total'] = intval($row['cnt']);
        }
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE is_read = 1");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['read'] = intval($row['cnt']);
        }
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE is_read = 0");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['unread'] = intval($row['cnt']);
        }
        
        $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE DATE(created_at) = CURDATE()");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['today'] = intval($row['cnt']);
        }
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit();
    }
    
    // View single message
    if ($action === 'view') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
            exit();
        }
        
        $query = "SELECT m.*,
            u1.username as from_name,
            u2.username as to_name
            FROM messages m
            LEFT JOIN users u1 ON m.from_user_id = u1.id
            LEFT JOIN users u2 ON m.to_user_id = u2.id
            WHERE m.id = $id LIMIT 1";
        
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'error' => 'Message not found']);
            exit();
        }
        
        $message = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'data' => $message]);
        exit();
    }
    
    // List messages with filters and pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 50;
    $offset = $page * $per_page;
    
    $where = [];
    if (isset($_GET['from_user']) && $_GET['from_user'] !== '') {
        $from_user = intval($_GET['from_user']);
        $where[] = "m.from_user_id = $from_user";
    }
    if (isset($_GET['to_user']) && $_GET['to_user'] !== '') {
        $to_user = intval($_GET['to_user']);
        $where[] = "m.to_user_id = $to_user";
    }
    if (isset($_GET['is_read']) && $_GET['is_read'] !== '') {
        $is_read = intval($_GET['is_read']);
        $where[] = "m.is_read = $is_read";
    }
    
    $where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT m.*,
        u1.username as from_name,
        u2.username as to_name
        FROM messages m
        LEFT JOIN users u1 ON m.from_user_id = u1.id
        LEFT JOIN users u2 ON m.to_user_id = u2.id
        $where_sql
        ORDER BY m.created_at DESC
        LIMIT $per_page OFFSET $offset";
    
    $result = mysqli_query($conn, $query);
    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    
    // Check if there are more records
    $count_query = "SELECT COUNT(*) as cnt FROM messages m $where_sql";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total = intval($count_row['cnt']);
    $has_more = ($offset + $per_page) < $total;
    
    echo json_encode([
        'success' => true,
        'data' => $messages,
        'total' => $total,
        'page' => $page,
        'has_more' => $has_more
    ]);
    exit();
}

// DELETE - Remove message
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message_id = isset($input['message_id']) ? intval($input['message_id']) : 0;
    
    if ($message_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
        exit();
    }
    
    $query = "DELETE FROM messages WHERE id = $message_id";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Message deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete message: ' . mysqli_error($conn)]);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Method not allowed']);
?>
