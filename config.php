<?php
/**
 * PHP Matrimony Configuration File
 * Generated for Hostinger deployment
 */

// Environment
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'MakeMyLove');

// Database - Hostinger Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'u166093127_matrimony');
define('DB_USER', 'u166093127_dbuser');
define('DB_PASS', 'Uttu@2405');

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5242880);
define('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp');
define('UPLOAD_DIR', 'uploads/');
define('PROFILE_DIR', 'profile/');

// Email Settings
define('SMTP_ENABLED', false);
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM_NAME', 'MakeMyLove');

// SMS Settings
define('SMS_GATEWAY', 'disabled');

// Payment Settings
define('PAYMENT_GATEWAY', 'disabled');

// Security Settings
define('SESSION_TIMEOUT', 1800);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 15);
define('FORCE_HTTPS', true);

// Admin Settings
define('DEFAULT_ADMIN_USER', 'admin');
define('ADMIN_ITEMS_PER_PAGE', 20);

// Error reporting for production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
date_default_timezone_set('Asia/Kolkata');
