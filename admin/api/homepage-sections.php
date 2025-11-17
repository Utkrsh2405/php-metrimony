<?php
session_start();
require_once("../../includes/dbconn.php");
require_once("../../includes/security.php");

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || $_SESSION['userlevel'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - Retrieve sections
if ($method == 'GET') {
    $sql = "SELECT * FROM homepage_sections ORDER BY section_order ASC";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $sections = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sections[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $sections]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to fetch sections']);
    }
}

// POST - Create or Update section
else if ($method == 'POST') {
    $section_key = Security::sanitizeInput($_POST['section_key'] ?? '');
    $section_title = Security::sanitizeInput($_POST['section_title'] ?? '');
    $section_subtitle = Security::sanitizeInput($_POST['section_subtitle'] ?? '');
    $section_content = Security::sanitizeInput($_POST['section_content'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($section_key)) {
        echo json_encode(['success' => false, 'message' => 'Section key is required']);
        exit();
    }
    
    // Handle image upload
    $section_image = null;
    $image_field_name = $section_key . '_image';
    
    if (isset($_FILES[$image_field_name]) && $_FILES[$image_field_name]['error'] == 0) {
        $upload_dir = '../../uploads/homepage/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
            chmod($upload_dir, 0777);
        }
        
        $file_extension = strtolower(pathinfo($_FILES[$image_field_name]['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = $section_key . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES[$image_field_name]['tmp_name'], $upload_path)) {
                chmod($upload_path, 0666);
                $section_image = 'uploads/homepage/' . $new_filename;
                
                // Delete old image if exists
                $old_image_sql = "SELECT section_image FROM homepage_sections WHERE section_key = ?";
                $stmt = mysqli_prepare($conn, $old_image_sql);
                mysqli_stmt_bind_param($stmt, "s", $section_key);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    if (!empty($row['section_image']) && file_exists('../../' . $row['section_image'])) {
                        unlink('../../' . $row['section_image']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image. Check directory permissions.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: JPG, JPEG, PNG, GIF, WEBP']);
            exit();
        }
    } else if (isset($_FILES[$image_field_name]) && $_FILES[$image_field_name]['error'] != 0 && $_FILES[$image_field_name]['error'] != 4) {
        $upload_errors = [
            1 => 'File too large (server limit)',
            2 => 'File too large (form limit)',
            3 => 'File partially uploaded',
            6 => 'No temporary folder',
            7 => 'Failed to write to disk',
            8 => 'Extension blocked upload'
        ];
        $error_msg = $upload_errors[$_FILES[$image_field_name]['error']] ?? 'Unknown upload error';
        echo json_encode(['success' => false, 'message' => 'Upload error: ' . $error_msg]);
        exit();
    }
    
    // Check if section exists
    $check_sql = "SELECT id FROM homepage_sections WHERE section_key = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $section_key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing section
        if ($section_image) {
            $update_sql = "UPDATE homepage_sections SET 
                           section_title = ?, 
                           section_subtitle = ?, 
                           section_content = ?,
                           section_image = ?,
                           is_active = ?,
                           updated_at = NOW()
                           WHERE section_key = ?";
            $stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $section_title, $section_subtitle, $section_content, $section_image, $is_active, $section_key);
        } else {
            $update_sql = "UPDATE homepage_sections SET 
                           section_title = ?, 
                           section_subtitle = ?, 
                           section_content = ?, 
                           is_active = ?,
                           updated_at = NOW()
                           WHERE section_key = ?";
            $stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt, "sssss", $section_title, $section_subtitle, $section_content, $is_active, $section_key);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Section updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update section: ' . mysqli_error($conn)]);
        }
    } else {
        // Insert new section
        $insert_sql = "INSERT INTO homepage_sections (section_key, section_title, section_subtitle, section_content, section_image, is_active) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "ssssss", $section_key, $section_title, $section_subtitle, $section_content, $section_image, $is_active);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Section created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create section: ' . mysqli_error($conn)]);
        }
    }
}

// DELETE - Delete section
else if ($method == 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $section_key = Security::sanitizeInput($_DELETE['section_key'] ?? '');
    
    if (empty($section_key)) {
        echo json_encode(['success' => false, 'message' => 'Section key is required']);
        exit();
    }
    
    $sql = "DELETE FROM homepage_sections WHERE section_key = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $section_key);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Section deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete section']);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
