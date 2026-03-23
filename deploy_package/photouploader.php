<?php
session_start();
require_once("includes/dbconn.php");
require_once("functions.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display, but log

// Check if user is logged in
if(!isloggedin()){
    header("location:login.php");
    exit();
}

$user_id = intval($_SESSION['id']);

// Handle photo upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $profile_id = isset($_POST['profile_id']) ? intval($_POST['profile_id']) : $user_id;
    
    // Security check - users can only upload photos to their own profile
    if ($profile_id != $user_id) {
        $_SESSION['upload_error'] = 'Unauthorized access';
        header("location:view_profile.php?id=$user_id");
        exit();
    }
    
    // Create profile directory if it doesn't exist
    $upload_dir = "profile/" . $profile_id . "/";
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $_SESSION['upload_error'] = 'Failed to create upload directory. Please contact support.';
            header("location:view_profile.php?id=$user_id");
            exit();
        }
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        $_SESSION['upload_error'] = 'Upload directory is not writable. Please contact support.';
        header("location:view_profile.php?id=$user_id");
        exit();
    }
    
    $uploaded_files = [];
    $errors = [];
    
    // Process each photo (photo1, photo2, photo3, photo4)
    for ($i = 1; $i <= 4; $i++) {
        $field_name = 'photo' . $i;
        
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == UPLOAD_ERR_OK && $_FILES[$field_name]['size'] > 0) {
            $file = $_FILES[$field_name];
            
            // Validate file type using both MIME and extension
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            // Check MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array(strtolower($mime_type), $allowed_types)) {
                $errors[] = "Photo $i: Invalid file type ($mime_type). Only JPG, PNG, GIF, and WebP allowed.";
                continue;
            }
            
            // Check extension
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowed_extensions)) {
                $errors[] = "Photo $i: Invalid file extension. Only JPG, PNG, GIF, and WebP allowed.";
                continue;
            }
            
            if ($file['size'] > $max_size) {
                $errors[] = "Photo $i: File too large (" . round($file['size']/1024/1024, 2) . "MB). Maximum 5MB allowed.";
                continue;
            }
            
            // Generate shorter unique filename (max ~20 chars)
            $filename = 'p' . $i . '_' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Delete old photo if exists
            $old_photo_sql = "SELECT pic$i FROM photos WHERE cust_id = $profile_id";
            $old_photo_result = mysqli_query($conn, $old_photo_sql);
            if ($old_photo_result && mysqli_num_rows($old_photo_result) > 0) {
                $old_photo = mysqli_fetch_assoc($old_photo_result);
                $old_file = $upload_dir . $old_photo['pic' . $i];
                if (!empty($old_photo['pic' . $i]) && file_exists($old_file)) {
                    @unlink($old_file);
                }
            }
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Set proper permissions
                chmod($filepath, 0644);
                $uploaded_files['pic' . $i] = $filename;
            } else {
                $upload_error = error_get_last();
                $errors[] = "Photo $i: Failed to upload. " . ($upload_error ? $upload_error['message'] : '');
            }
        } elseif (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] != UPLOAD_ERR_NO_FILE) {
            // Handle upload errors
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server limit',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
                UPLOAD_ERR_EXTENSION => 'Upload blocked by extension'
            ];
            $error_code = $_FILES[$field_name]['error'];
            $errors[] = "Photo $i: " . ($error_messages[$error_code] ?? "Unknown error (code: $error_code)");
        }
    }
    
    // Update database if any files were uploaded
    if (count($uploaded_files) > 0) {
        // Check if photos record exists
        $check_sql = "SELECT * FROM photos WHERE cust_id = $profile_id";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing record
            $update_parts = [];
            foreach ($uploaded_files as $field => $filename) {
                $update_parts[] = "$field = '" . mysqli_real_escape_string($conn, $filename) . "'";
            }
            $update_sql = "UPDATE photos SET " . implode(', ', $update_parts) . " WHERE cust_id = $profile_id";
            if (!mysqli_query($conn, $update_sql)) {
                $errors[] = "Database error: " . mysqli_error($conn);
            }
        } else {
            // Insert new record
            $pic1 = isset($uploaded_files['pic1']) ? mysqli_real_escape_string($conn, $uploaded_files['pic1']) : '';
            $pic2 = isset($uploaded_files['pic2']) ? mysqli_real_escape_string($conn, $uploaded_files['pic2']) : '';
            $pic3 = isset($uploaded_files['pic3']) ? mysqli_real_escape_string($conn, $uploaded_files['pic3']) : '';
            $pic4 = isset($uploaded_files['pic4']) ? mysqli_real_escape_string($conn, $uploaded_files['pic4']) : '';
            
            $insert_sql = "INSERT INTO photos (cust_id, pic1, pic2, pic3, pic4) 
                          VALUES ($profile_id, '$pic1', '$pic2', '$pic3', '$pic4')";
            if (!mysqli_query($conn, $insert_sql)) {
                $errors[] = "Database error: " . mysqli_error($conn);
            }
        }
        
        if (count($errors) == 0) {
            $_SESSION['upload_success'] = count($uploaded_files) . ' photo(s) uploaded successfully!';
        } else {
            $_SESSION['upload_error'] = implode('<br>', $errors);
            $_SESSION['upload_success'] = count($uploaded_files) . ' photo(s) uploaded, but with some errors.';
        }
    } else {
        if (count($errors) > 0) {
            $_SESSION['upload_error'] = implode('<br>', $errors);
        } else {
            $_SESSION['upload_error'] = 'No photos selected to upload. Please select at least one photo.';
        }
    }
    
    header("location:view_profile.php?id=$user_id");
    exit();
}

// If GET request, redirect to profile
header("location:view_profile.php?id=$user_id");
exit();
?>
