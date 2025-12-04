<?php
// Admin SMS API - Manage templates and send SMS
session_start();
header('Content-Type: application/json');

// Check admin authentication
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

// Verify admin
$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : 'templates';

// GET - List templates or logs
if ($method == 'GET') {
    if ($endpoint == 'templates') {
        $query = "SELECT * FROM sms_templates ORDER BY event_trigger, name";
        $result = mysqli_query($conn, $query);
        $templates = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['variables']) {
                $row['variables'] = json_decode($row['variables'], true);
            }
            $templates[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $templates]);
        
    } elseif ($endpoint == 'logs') {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = ($page - 1) * $limit;
        
        $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
        $event_type = isset($_GET['event_type']) ? mysqli_real_escape_string($conn, $_GET['event_type']) : '';
        
        $where = ["1=1"];
        if ($status) $where[] = "sl.status = '$status'";
        if ($event_type) $where[] = "sl.event_type = '$event_type'";
        $where_clause = implode(' AND ', $where);
        
        // Get total count
        $count_query = "SELECT COUNT(*) as total FROM sms_logs sl WHERE $where_clause";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $total = $count_row['total'];
        
        // Get logs
        $query = "SELECT sl.*, u.username, c.firstname, c.lastname
                  FROM sms_logs sl
                  LEFT JOIN users u ON sl.user_id = u.id
                  LEFT JOIN customer c ON u.id = c.cust_id
                  WHERE $where_clause
                  ORDER BY sl.created_at DESC
                  LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($conn, $query);
        $logs = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $logs,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]);
        
    } elseif ($endpoint == 'config') {
        $query = "SELECT * FROM sms_config ORDER BY is_active DESC";
        $result = mysqli_query($conn, $query);
        $configs = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['config_data']) {
                $row['config_data'] = json_decode($row['config_data'], true);
            }
            $configs[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $configs]);
        
    } elseif ($endpoint == 'template') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $query = "SELECT * FROM sms_templates WHERE id = $id";
        $result = mysqli_query($conn, $query);
        
        if ($row = mysqli_fetch_assoc($result)) {
            if ($row['variables']) {
                $row['variables'] = json_decode($row['variables'], true);
            }
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['error' => 'Template not found']);
        }
    }
}

// POST - Create/Update template or send test SMS
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($endpoint == 'template') {
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $name = mysqli_real_escape_string($conn, $data['name']);
        $event_trigger = mysqli_real_escape_string($conn, $data['event_trigger']);
        $subject = mysqli_real_escape_string($conn, $data['subject'] ?? '');
        $content = mysqli_real_escape_string($conn, $data['content']);
        $variables = mysqli_real_escape_string($conn, json_encode($data['variables'] ?? []));
        $is_active = isset($data['is_active']) ? 1 : 0;
        
        if ($id > 0) {
            // Update
            $query = "UPDATE sms_templates SET
                      name = '$name',
                      event_trigger = '$event_trigger',
                      subject = '$subject',
                      content = '$content',
                      variables = '$variables',
                      is_active = $is_active,
                      updated_at = NOW()
                      WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                echo json_encode(['success' => true, 'message' => 'Template updated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to update template']);
            }
        } else {
            // Create
            $query = "INSERT INTO sms_templates 
                      (name, event_trigger, subject, content, variables, is_active, created_at, updated_at)
                      VALUES 
                      ('$name', '$event_trigger', '$subject', '$content', '$variables', $is_active, NOW(), NOW())";
            
            if (mysqli_query($conn, $query)) {
                echo json_encode(['success' => true, 'message' => 'Template created successfully', 'id' => mysqli_insert_id($conn)]);
            } else {
                echo json_encode(['error' => 'Failed to create template']);
            }
        }
        
    } elseif ($endpoint == 'config') {
        $id = isset($data['id']) ? intval($data['id']) : 0;
        $gateway = mysqli_real_escape_string($conn, $data['gateway']);
        $api_key = mysqli_real_escape_string($conn, $data['api_key'] ?? '');
        $api_secret = mysqli_real_escape_string($conn, $data['api_secret'] ?? '');
        $sender_id = mysqli_real_escape_string($conn, $data['sender_id'] ?? '');
        $is_active = isset($data['is_active']) ? 1 : 0;
        $config_data = mysqli_real_escape_string($conn, json_encode($data['config_data'] ?? []));
        
        if ($id > 0) {
            // Deactivate all other configs if this is being activated
            if ($is_active) {
                mysqli_query($conn, "UPDATE sms_config SET is_active = 0 WHERE id != $id");
            }
            
            $query = "UPDATE sms_config SET
                      gateway = '$gateway',
                      api_key = '$api_key',
                      api_secret = '$api_secret',
                      sender_id = '$sender_id',
                      is_active = $is_active,
                      config_data = '$config_data',
                      updated_at = NOW()
                      WHERE id = $id";
            
            if (mysqli_query($conn, $query)) {
                echo json_encode(['success' => true, 'message' => 'Configuration updated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to update configuration']);
            }
        } else {
            echo json_encode(['error' => 'Invalid config ID']);
        }
        
    } elseif ($endpoint == 'test') {
        // Send test SMS
        $phone = mysqli_real_escape_string($conn, $data['phone']);
        $message = mysqli_real_escape_string($conn, $data['message']);
        
        require_once("../../includes/sms-sender.php");
        
        $result = sendSMS($phone, $message, 'test');
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Test SMS sent successfully']);
        } else {
            echo json_encode(['error' => $result['error'] ?? 'Failed to send SMS']);
        }
    }
}

// DELETE - Delete template
elseif ($method == 'DELETE') {
    if ($endpoint == 'template') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        $query = "DELETE FROM sms_templates WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Template deleted successfully']);
        } else {
            echo json_encode(['error' => 'Failed to delete template']);
        }
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
