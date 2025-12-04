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

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $interest_id = isset($input['interest_id']) ? intval($input['interest_id']) : 0;
    $action = isset($input['action']) ? $input['action'] : '';
    
    if ($interest_id <= 0 || !in_array($action, ['accept', 'decline'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
        exit();
    }
    
    // Verify this interest was sent TO current user
    $check = mysqli_query($conn, "SELECT * FROM interests WHERE id = $interest_id AND to_user_id = $user_id LIMIT 1");
    if (mysqli_num_rows($check) == 0) {
        echo json_encode(['success' => false, 'error' => 'Interest not found']);
        exit();
    }
    
    $interest = mysqli_fetch_assoc($check);
    if ($interest['status'] !== 'pending') {
        echo json_encode(['success' => false, 'error' => 'Interest already ' . $interest['status']]);
        exit();
    }
    
    $new_status = ($action === 'accept') ? 'accepted' : 'declined';
    $sender_id = intval($interest['from_user_id']);
    
    // Update status
    $update = mysqli_query($conn, "UPDATE interests SET status = '$new_status', updated_at = NOW() WHERE id = $interest_id");
    
    if (!$update) {
        echo json_encode(['success' => false, 'error' => 'Failed to update interest']);
        exit();
    }
    
    // Send notification to sender
    if ($action === 'accept') {
        // Get receiver info
        $receiver_query = mysqli_query($conn, "SELECT c.firstname, c.lastname FROM customer c WHERE c.id = $user_id LIMIT 1");
        $receiver_data = mysqli_fetch_assoc($receiver_query);
        $receiver_name = ($receiver_data['firstname'] ?? 'Someone') . ' ' . ($receiver_data['lastname'] ?? '');
        
        // Send SMS notification (interest_accepted template)
        $sms_template_query = mysqli_query($conn, "SELECT * FROM sms_templates WHERE event_trigger = 'interest_accepted' AND is_active = 1 LIMIT 1");
        if (mysqli_num_rows($sms_template_query) > 0) {
            $template = mysqli_fetch_assoc($sms_template_query);
            
            // Get sender info for SMS
            $sender_query = mysqli_query($conn, "SELECT c.mobile, c.firstname FROM customer c WHERE c.id = $sender_id LIMIT 1");
            $sender_data = mysqli_fetch_assoc($sender_query);
            
            if (!empty($sender_data['mobile'])) {
                $message = renderSMSTemplate($template['content'], [
                    'name' => $sender_data['firstname'] ?? 'User',
                    'receiver_name' => $receiver_name,
                    'chat_url' => 'https://yoursite.com/messages.php?user=' . $user_id
                ]);
                
                sendSMS($sender_data['mobile'], $message, 'interest_accepted', $sender_id);
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Interest ' . $new_status . ' successfully',
        'new_status' => $new_status
    ]);
}

elseif ($method === 'GET') {
    // Get interests for current user
    $type = isset($_GET['type']) ? $_GET['type'] : 'received'; // received or sent
    $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
    
    if ($type === 'received') {
        $query = "SELECT i.*, 
                         c.firstname, c.lastname, c.age, c.location, c.occupation, c.verified,
                         u.username
                  FROM interests i
                  LEFT JOIN customer c ON i.from_user_id = c.id
                  LEFT JOIN users u ON i.from_user_id = u.id
                  WHERE i.to_user_id = $user_id";
    } else {
        $query = "SELECT i.*, 
                         c.firstname, c.lastname, c.age, c.location, c.occupation, c.verified,
                         u.username
                  FROM interests i
                  LEFT JOIN customer c ON i.to_user_id = c.id
                  LEFT JOIN users u ON i.to_user_id = u.id
                  WHERE i.from_user_id = $user_id";
    }
    
    if ($status) {
        $query .= " AND i.status = '$status'";
    }
    
    $query .= " ORDER BY i.created_at DESC LIMIT 100";
    
    $result = mysqli_query($conn, $query);
    $interests = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $interests[] = [
            'id' => $row['id'],
            'user_id' => ($type === 'received') ? $row['from_user_id'] : $row['to_user_id'],
            'name' => ($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''),
            'age' => $row['age'],
            'location' => $row['location'],
            'occupation' => $row['occupation'],
            'verified' => $row['verified'],
            'status' => $row['status'],
            'message' => $row['message'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $interests, 'count' => count($interests)]);
}

else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>
