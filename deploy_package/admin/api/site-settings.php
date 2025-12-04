<?php
// Admin Site Settings API
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

// GET - Get all settings or specific setting
if ($method == 'GET') {
    if (isset($_GET['key'])) {
        $key = mysqli_real_escape_string($conn, $_GET['key']);
        $query = "SELECT * FROM site_settings WHERE setting_key = '$key'";
        $result = mysqli_query($conn, $query);
        
        if ($row = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['error' => 'Setting not found']);
        }
    } else {
        $query = "SELECT * FROM site_settings ORDER BY setting_key ASC";
        $result = mysqli_query($conn, $query);
        $settings = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $settings]);
    }
}

// POST - Update setting
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['setting_key']) || !isset($data['setting_value'])) {
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }
    
    $key = mysqli_real_escape_string($conn, $data['setting_key']);
    $value = mysqli_real_escape_string($conn, $data['setting_value']);
    $type = isset($data['setting_type']) ? mysqli_real_escape_string($conn, $data['setting_type']) : 'text';
    
    // Check if setting exists
    $check = mysqli_query($conn, "SELECT id FROM site_settings WHERE setting_key = '$key'");
    
    if (mysqli_num_rows($check) > 0) {
        // Update existing
        $query = "UPDATE site_settings SET setting_value = '$value', setting_type = '$type', updated_by = $user_id, updated_at = NOW() WHERE setting_key = '$key'";
    } else {
        // Insert new
        $query = "INSERT INTO site_settings (setting_key, setting_value, setting_type, updated_by) VALUES ('$key', '$value', '$type', $user_id)";
    }
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Setting updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update setting: ' . mysqli_error($conn)]);
    }
}

else {
    echo json_encode(['error' => 'Method not allowed']);
}

mysqli_close($conn);
?>
