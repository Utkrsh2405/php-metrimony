<?php
// Admin Homepage Configuration API
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once("../../includes/dbconn.php");

$user_id = $_SESSION['id'];
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - List all sections
if ($method == 'GET') {
    $query = "SELECT * FROM homepage_config ORDER BY display_order ASC";
    $result = mysqli_query($conn, $query);
    $sections = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['section_content']) {
            $row['section_content'] = json_decode($row['section_content'], true);
        }
        $sections[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $sections]);
}

// POST - Update section
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = intval($data['id']);
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 0;
    
    // Prepare content - ensure it's properly encoded
    $content = isset($data['content']) ? $data['content'] : [];
    $section_content = mysqli_real_escape_string($conn, json_encode($content));
    
    $query = "UPDATE homepage_config SET
              section_content = '$section_content',
              is_active = $is_active,
              updated_at = NOW()
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Section updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update section: ' . mysqli_error($conn)]);
    }
}

// PUT - Reorder sections
elseif ($method == 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['order']) && is_array($data['order'])) {
        $success = true;
        foreach ($data['order'] as $order_index => $section_id) {
            $id = intval($section_id);
            $display_order = $order_index + 1;
            
            $query = "UPDATE homepage_config SET display_order = $display_order WHERE id = $id";
            if (!mysqli_query($conn, $query)) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Sections reordered successfully']);
        } else {
            echo json_encode(['error' => 'Failed to reorder sections']);
        }
    } else {
        echo json_encode(['error' => 'Invalid reorder data']);
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
