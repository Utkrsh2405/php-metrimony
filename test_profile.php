<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
session_start();
$_SESSION['id'] = 111;
require_once("includes/dbconn.php");
// Mock file upload
$_FILES['photo1'] = array(
    'name' => 'test.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => '/tmp/phpYzdqkD',
    'error' => 0,
    'size' => 1000
);
// We can't actually move_uploaded_file with a fake file, but we can capture the output of photouploader.php up to that point
?>
