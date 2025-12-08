<?php
session_start();
require_once("includes/dbconn.php");
require_once("functions.php");

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
        mkdir($upload_dir, 0755, true);
    }
    
    $uploaded_files = [];
    $errors = [];
    
    // Process each photo (photo1, photo2, photo3, photo4)
    for ($i = 1; $i <= 4; $i++) {
        $field_name = 'photo' . $i;
        
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == 0) {
            $file = $_FILES[$field_name];
            
            // Validate file
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array(strtolower($file['type']), $allowed_types)) {
                $errors[] = "Photo $i: Invalid file type. Only JPG, PNG, and GIF allowed.";
                continue;
            }
            
            if ($file['size'] > $max_size) {
                $errors[] = "Photo $i: File too large. Maximum 5MB allowed.";
                continue;
            }
            
            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'photo_' . $i . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $uploaded_files['pic' . $i] = $filename;
            } else {
                $errors[] = "Photo $i: Failed to upload.";
            }
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
            mysqli_query($conn, $update_sql);
        } else {
            // Insert new record - include all 4 fields
            $pic1 = isset($uploaded_files['pic1']) ? "'" . mysqli_real_escape_string($conn, $uploaded_files['pic1']) . "'" : "''";
            $pic2 = isset($uploaded_files['pic2']) ? "'" . mysqli_real_escape_string($conn, $uploaded_files['pic2']) . "'" : "''";
            $pic3 = isset($uploaded_files['pic3']) ? "'" . mysqli_real_escape_string($conn, $uploaded_files['pic3']) . "'" : "''";
            $pic4 = isset($uploaded_files['pic4']) ? "'" . mysqli_real_escape_string($conn, $uploaded_files['pic4']) . "'" : "''";
            
            $insert_sql = "INSERT INTO photos (cust_id, pic1, pic2, pic3, pic4) 
                          VALUES ($profile_id, $pic1, $pic2, $pic3, $pic4)";
            mysqli_query($conn, $insert_sql);
        }
        
        $_SESSION['upload_success'] = count($uploaded_files) . ' photo(s) uploaded successfully!';
    } else {
        $_SESSION['upload_error'] = count($errors) > 0 ? implode('<br>', $errors) : 'No photos selected to upload.';
    }
    
    header("location:view_profile.php?id=$user_id");
    exit();
}

// If GET request, redirect to profile
header("location:view_profile.php?id=$user_id");
exit();
?>
