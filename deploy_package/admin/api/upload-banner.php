<?php
// Admin Banner Upload API
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

// Check if file was uploaded
if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['banner'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed']);
    exit();
}

// Validate file size
if ($file['size'] > $max_size) {
    echo json_encode(['error' => 'File too large. Maximum size is 5MB']);
    exit();
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'banner_' . time() . '_' . uniqid() . '.' . $extension;
$upload_dir = '../../uploads/banners/';
$upload_path = $upload_dir . $filename;

// Create directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    $relative_path = '/uploads/banners/' . $filename;
    
    // Update database
    $stmt = mysqli_prepare($conn, "UPDATE site_settings SET setting_value = ?, updated_by = ?, updated_at = NOW() WHERE setting_key = 'homepage_banner'");
    mysqli_stmt_bind_param($stmt, "si", $relative_path, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Delete old banner file if exists
        $old_banner = mysqli_query($conn, "SELECT setting_value FROM site_settings WHERE setting_key = 'homepage_banner'");
        if ($old_row = mysqli_fetch_assoc($old_banner)) {
            $old_file = '../../' . ltrim($old_row['setting_value'], '/');
            if (file_exists($old_file) && strpos($old_file, '/uploads/banners/') !== false) {
                @unlink($old_file);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Banner uploaded successfully',
            'path' => $relative_path,
            'filename' => $filename
        ]);
    } else {
        // Delete uploaded file if database update fails
        @unlink($upload_path);
        echo json_encode(['error' => 'Failed to update database']);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'Failed to move uploaded file']);
}

mysqli_close($conn);
?>
