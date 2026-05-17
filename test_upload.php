<?php
error_reporting(E_ALL); ini_set('display_errors', 1);

// We need to test if the upload form size limits or memory limits are the issue
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";
?>
