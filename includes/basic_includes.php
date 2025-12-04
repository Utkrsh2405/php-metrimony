<?php 
session_start();

// Load config file at the very beginning
$config_path = __DIR__ . '/../config.php';
if (file_exists($config_path) && !defined('DB_HOST')) {
    require_once($config_path);
}
?>
