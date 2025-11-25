<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");
require_once("../../includes/activity-logger.php");

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
$logger = getActivityLogger($conn);

// GET - List logs or view single log
if ($method === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // View single log
    if ($action === 'view') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid log ID']);
            exit();
        }
        
        $query = "SELECT l.*, u.username as admin_name, u.email as admin_email
            FROM admin_activity_logs l
            LEFT JOIN users u ON l.admin_id = u.id
            WHERE l.id = $id LIMIT 1";
        
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'error' => 'Log not found']);
            exit();
        }
        
        $log = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'data' => $log]);
        exit();
    }
    
    // List logs with filters
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 50;
    $offset = $page * $per_page;
    
    $where = [];
    if (isset($_GET['action']) && $_GET['action'] !== '') {
        $act = mysqli_real_escape_string($conn, $_GET['action']);
        $where[] = "l.action = '$act'";
    }
    if (isset($_GET['entity_type']) && $_GET['entity_type'] !== '') {
        $entity = mysqli_real_escape_string($conn, $_GET['entity_type']);
        $where[] = "l.entity_type = '$entity'";
    }
    if (isset($_GET['admin_id']) && $_GET['admin_id'] !== '') {
        $admin_id = intval($_GET['admin_id']);
        $where[] = "l.admin_id = $admin_id";
    }
    
    $where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $query = "SELECT l.*, u.username as admin_name, u.email as admin_email
        FROM admin_activity_logs l
        LEFT JOIN users u ON l.admin_id = u.id
        $where_sql
        ORDER BY l.created_at DESC
        LIMIT $per_page OFFSET $offset";
    
    $result = mysqli_query($conn, $query);
    $logs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
    
    // Check if there are more records
    $count_query = "SELECT COUNT(*) as cnt FROM admin_activity_logs l $where_sql";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total = intval($count_row['cnt']);
    $has_more = ($offset + $per_page) < $total;
    
    echo json_encode([
        'success' => true,
        'data' => $logs,
        'total' => $total,
        'page' => $page,
        'has_more' => $has_more
    ]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Method not allowed']);
?>
