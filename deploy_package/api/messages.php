<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once("../includes/dbconn.php");
require_once("../includes/sms-sender.php");

$user_id = intval($_SESSION['id']);
$method = $_SERVER['REQUEST_METHOD'];

// GET - List messages (inbox, sent, conversation)
if ($method === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : 'inbox';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
    $offset = $page * $per_page;
    
    // Get inbox messages
    if ($action === 'inbox') {
        $query = "SELECT m.*, 
            u.name as sender_name, 
            c.verified as sender_verified,
            u.age as sender_age,
            u.location as sender_location
            FROM messages m
            LEFT JOIN users u ON m.from_user_id = u.id
            LEFT JOIN customer c ON m.from_user_id = c.user_id
            WHERE m.to_user_id = $user_id 
            AND m.is_deleted_by_receiver = 0
            ORDER BY m.created_at DESC
            LIMIT $per_page OFFSET $offset";
        
        $result = mysqli_query($conn, $query);
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
        
        // Count total
        $count_query = "SELECT COUNT(*) as cnt FROM messages 
            WHERE to_user_id = $user_id AND is_deleted_by_receiver = 0";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $total = intval($count_row['cnt']);
        
        // Count unread
        $unread_query = "SELECT COUNT(*) as cnt FROM messages 
            WHERE to_user_id = $user_id AND is_deleted_by_receiver = 0 AND is_read = 0";
        $unread_result = mysqli_query($conn, $unread_query);
        $unread_row = mysqli_fetch_assoc($unread_result);
        $unread = intval($unread_row['cnt']);
        
        echo json_encode([
            'success' => true,
            'data' => $messages,
            'total' => $total,
            'unread' => $unread,
            'page' => $page,
            'has_more' => ($offset + $per_page) < $total
        ]);
        exit();
    }
    
    // Get sent messages
    if ($action === 'sent') {
        $query = "SELECT m.*, 
            u.name as receiver_name, 
            c.verified as receiver_verified
            FROM messages m
            LEFT JOIN users u ON m.to_user_id = u.id
            LEFT JOIN customer c ON m.to_user_id = c.user_id
            WHERE m.from_user_id = $user_id 
            AND m.is_deleted_by_sender = 0
            ORDER BY m.created_at DESC
            LIMIT $per_page OFFSET $offset";
        
        $result = mysqli_query($conn, $query);
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
        
        // Count total
        $count_query = "SELECT COUNT(*) as cnt FROM messages 
            WHERE from_user_id = $user_id AND is_deleted_by_sender = 0";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $total = intval($count_row['cnt']);
        
        echo json_encode([
            'success' => true,
            'data' => $messages,
            'total' => $total,
            'page' => $page,
            'has_more' => ($offset + $per_page) < $total
        ]);
        exit();
    }
    
    // Get conversation with a specific user
    if ($action === 'conversation') {
        $with_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        if ($with_user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            exit();
        }
        
        $query = "SELECT m.*, 
            u1.name as from_name,
            u2.name as to_name,
            c1.verified as from_verified,
            c2.verified as to_verified
            FROM messages m
            LEFT JOIN users u1 ON m.from_user_id = u1.id
            LEFT JOIN users u2 ON m.to_user_id = u2.id
            LEFT JOIN customer c1 ON m.from_user_id = c1.user_id
            LEFT JOIN customer c2 ON m.to_user_id = c2.user_id
            WHERE ((m.from_user_id = $user_id AND m.to_user_id = $with_user_id AND m.is_deleted_by_sender = 0)
            OR (m.from_user_id = $with_user_id AND m.to_user_id = $user_id AND m.is_deleted_by_receiver = 0))
            ORDER BY m.created_at ASC
            LIMIT $per_page OFFSET $offset";
        
        $result = mysqli_query($conn, $query);
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
        
        // Mark messages as read
        mysqli_query($conn, "UPDATE messages SET is_read = 1 
            WHERE from_user_id = $with_user_id AND to_user_id = $user_id AND is_read = 0");
        
        // Get user info
        $user_query = "SELECT u.*, c.verified FROM users u 
            LEFT JOIN customer c ON u.id = c.user_id 
            WHERE u.id = $with_user_id LIMIT 1";
        $user_result = mysqli_query($conn, $user_query);
        $user_info = mysqli_fetch_assoc($user_result);
        
        echo json_encode([
            'success' => true,
            'data' => $messages,
            'user_info' => $user_info,
            'page' => $page
        ]);
        exit();
    }
    
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit();
}

// POST - Send a new message
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $to_user_id = isset($input['to_user_id']) ? intval($input['to_user_id']) : 0;
    $subject = isset($input['subject']) ? mysqli_real_escape_string($conn, trim($input['subject'])) : '';
    $message = isset($input['message']) ? mysqli_real_escape_string($conn, trim($input['message'])) : '';
    
    if ($to_user_id <= 0 || $to_user_id == $user_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid recipient']);
        exit();
    }
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        exit();
    }
    
    // Check if recipient exists
    $user_check = mysqli_query($conn, "SELECT id FROM users WHERE id = $to_user_id LIMIT 1");
    if (mysqli_num_rows($user_check) === 0) {
        echo json_encode(['success' => false, 'error' => 'Recipient not found']);
        exit();
    }
    
    // Enforce message quota from active plan
    $plan_query = mysqli_query($conn, "SELECT us.*, p.max_messages_send FROM user_subscriptions us 
        LEFT JOIN plans p ON us.plan_id = p.id 
        WHERE us.user_id = $user_id AND us.status = 'active' ORDER BY us.end_date DESC LIMIT 1");
    
    $message_limit = 0; // 0 = unlimited
    if (mysqli_num_rows($plan_query) > 0) {
        $plan = mysqli_fetch_assoc($plan_query);
        $message_limit = intval($plan['max_messages_send']);
        $sub_start = $plan['start_date'];
        $sub_end = $plan['end_date'];
        
        if ($message_limit > 0) {
            // Count messages sent within subscription period
            $count_query = "SELECT COUNT(*) as cnt FROM messages 
                WHERE from_user_id = $user_id 
                AND created_at >= '$sub_start' 
                AND created_at <= '$sub_end'";
            $count_result = mysqli_query($conn, $count_query);
            $count_row = mysqli_fetch_assoc($count_result);
            $current_count = intval($count_row['cnt']);
            
            if ($current_count >= $message_limit) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Message quota exceeded for your current plan',
                    'quota_info' => [
                        'limit' => $message_limit,
                        'used' => $current_count,
                        'remaining' => 0
                    ]
                ]);
                exit();
            }
        }
    }
    
    // Insert message
    $query = "INSERT INTO messages (from_user_id, to_user_id, subject, message, created_at) 
        VALUES ($user_id, $to_user_id, '$subject', '$message', NOW())";
    
    if (mysqli_query($conn, $query)) {
        $message_id = mysqli_insert_id($conn);
        
        // Get sender info for SMS
        $sender_query = mysqli_query($conn, "SELECT name FROM users WHERE id = $user_id LIMIT 1");
        $sender = mysqli_fetch_assoc($sender_query);
        
        // Get recipient info for SMS
        $recipient_query = mysqli_query($conn, "SELECT u.name, c.phone FROM users u 
            LEFT JOIN customer c ON u.id = c.user_id 
            WHERE u.id = $to_user_id LIMIT 1");
        $recipient = mysqli_fetch_assoc($recipient_query);
        
        // Send SMS notification
        if ($recipient && !empty($recipient['phone'])) {
            $sms_vars = [
                'name' => $recipient['name'],
                'sender_name' => $sender['name'],
                'inbox_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/messages.php'
            ];
            sendSMSFromTemplate($recipient['phone'], 'message_received', $sms_vars, $conn);
        }
        
        echo json_encode([
            'success' => true, 
            'message_id' => $message_id,
            'message' => 'Message sent successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message: ' . mysqli_error($conn)]);
    }
    exit();
}

// DELETE - Delete message(s)
if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message_id = isset($input['message_id']) ? intval($input['message_id']) : 0;
    
    if ($message_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
        exit();
    }
    
    // Check if user is sender or receiver
    $check_query = "SELECT from_user_id, to_user_id FROM messages WHERE id = $message_id LIMIT 1";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) === 0) {
        echo json_encode(['success' => false, 'error' => 'Message not found']);
        exit();
    }
    
    $msg = mysqli_fetch_assoc($check_result);
    
    // Mark as deleted based on role
    if ($msg['from_user_id'] == $user_id) {
        mysqli_query($conn, "UPDATE messages SET is_deleted_by_sender = 1 WHERE id = $message_id");
    } else if ($msg['to_user_id'] == $user_id) {
        mysqli_query($conn, "UPDATE messages SET is_deleted_by_receiver = 1 WHERE id = $message_id");
    } else {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit();
    }
    
    echo json_encode(['success' => true, 'message' => 'Message deleted']);
    exit();
}

// PATCH - Mark as read
if ($method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message_id = isset($input['message_id']) ? intval($input['message_id']) : 0;
    
    if ($message_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
        exit();
    }
    
    // Only receiver can mark as read
    $query = "UPDATE messages SET is_read = 1 
        WHERE id = $message_id AND to_user_id = $user_id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Message marked as read']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update message']);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Method not allowed']);
?>
