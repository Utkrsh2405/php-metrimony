<?php
/**
 * PHP Matrimony Configuration File
 * ================================
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to 'config.php'
 * 2. Update the values below with your hosting credentials
 * 3. For Hostinger: Get credentials from cPanel → MySQL Databases
 * 
 * DO NOT commit config.php to git - it contains sensitive data!
 */

// ==========================================
// ENVIRONMENT SETTINGS
// ==========================================

// Set to 'production' for live site, 'development' for local testing
define('ENVIRONMENT', 'production');

// Enable/disable debug mode (set to false in production!)
define('DEBUG_MODE', false);

// Your website URL (without trailing slash)
define('SITE_URL', 'https://yourdomain.com');

// Site name for emails and titles
define('SITE_NAME', 'MakeMyLove');

// ==========================================
// DATABASE CONFIGURATION
// ==========================================

// Database Host
// - For Hostinger: Use 'localhost'
// - For Local: Use '127.0.0.1' or 'localhost'
define('DB_HOST', 'localhost');

// Database Name
// - For Hostinger: Usually 'cpanelusername_databasename'
// - Example: 'abcd1234_matrimony'
define('DB_NAME', 'matrimony');

// Database Username
// - For Hostinger: Usually 'cpanelusername_username'
// - Example: 'abcd1234_dbuser'
define('DB_USER', 'root');

// Database Password
define('DB_PASS', 'Uttu@2025');

// ==========================================
// FILE UPLOAD SETTINGS
// ==========================================

// Maximum file size for uploads (in bytes)
// 5MB = 5 * 1024 * 1024 = 5242880
define('MAX_UPLOAD_SIZE', 5242880);

// Allowed image types
define('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp');

// Upload directory (relative to root)
define('UPLOAD_DIR', 'uploads/');

// Profile photos directory
define('PROFILE_DIR', 'profile/');

// ==========================================
// EMAIL SETTINGS (Optional)
// ==========================================

// SMTP Configuration for sending emails
define('SMTP_ENABLED', false);
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@yourdomain.com');
define('SMTP_PASS', '');
define('SMTP_FROM_NAME', 'MakeMyLove');

// ==========================================
// SMS SETTINGS (Optional)
// ==========================================

// SMS Gateway: 'twilio', 'msg91', 'textlocal', or 'disabled'
define('SMS_GATEWAY', 'disabled');

// Twilio Settings
define('TWILIO_SID', '');
define('TWILIO_AUTH_TOKEN', '');
define('TWILIO_FROM_NUMBER', '');

// MSG91 Settings
define('MSG91_AUTH_KEY', '');
define('MSG91_SENDER_ID', '');

// ==========================================
// PAYMENT GATEWAY (Optional)
// ==========================================

// Payment Gateway: 'stripe', 'razorpay', 'paypal', or 'disabled'
define('PAYMENT_GATEWAY', 'disabled');

// Stripe Settings
define('STRIPE_PUBLIC_KEY', '');
define('STRIPE_SECRET_KEY', '');

// Razorpay Settings
define('RAZORPAY_KEY_ID', '');
define('RAZORPAY_KEY_SECRET', '');

// ==========================================
// SECURITY SETTINGS
// ==========================================

// Session timeout in seconds (default: 30 minutes)
define('SESSION_TIMEOUT', 1800);

// Maximum login attempts before lockout
define('MAX_LOGIN_ATTEMPTS', 5);

// Lockout duration in minutes
define('LOCKOUT_DURATION', 15);

// Enable HTTPS redirect (set to true if you have SSL)
define('FORCE_HTTPS', true);

// ==========================================
// ADMIN SETTINGS
// ==========================================

// Default admin username (for first-time setup)
define('DEFAULT_ADMIN_USER', 'admin');

// Items per page in admin lists
define('ADMIN_ITEMS_PER_PAGE', 20);

// ==========================================
// DO NOT EDIT BELOW THIS LINE
// ==========================================

// Error reporting based on environment
if (ENVIRONMENT === 'development' || DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');
