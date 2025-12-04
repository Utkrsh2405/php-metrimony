<?php
/**
 * Security Utilities
 * CSRF protection, XSS sanitization, rate limiting
 */

class Security {
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get CSRF token input field
     */
    public static function csrfField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Check rate limit
     */
    public static function checkRateLimit($key, $max_attempts = 5, $window = 60) {
        $cache_key = 'rate_limit_' . $key;
        
        if (!isset($_SESSION[$cache_key])) {
            $_SESSION[$cache_key] = [
                'count' => 0,
                'reset_time' => time() + $window
            ];
        }
        
        $data = $_SESSION[$cache_key];
        
        // Reset if window expired
        if (time() > $data['reset_time']) {
            $_SESSION[$cache_key] = [
                'count' => 0,
                'reset_time' => time() + $window
            ];
            $data = $_SESSION[$cache_key];
        }
        
        // Check if limit exceeded
        if ($data['count'] >= $max_attempts) {
            return false;
        }
        
        // Increment counter
        $_SESSION[$cache_key]['count']++;
        
        return true;
    }
    
    /**
     * Get remaining rate limit attempts
     */
    public static function getRateLimitRemaining($key, $max_attempts = 5) {
        $cache_key = 'rate_limit_' . $key;
        
        if (!isset($_SESSION[$cache_key])) {
            return $max_attempts;
        }
        
        $data = $_SESSION[$cache_key];
        
        if (time() > $data['reset_time']) {
            return $max_attempts;
        }
        
        return max(0, $max_attempts - $data['count']);
    }
    
    /**
     * Sanitize input (prevent XSS)
     */
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize HTML (allow safe tags)
     */
    public static function sanitizeHTML($html, $allowed_tags = '<p><br><b><i><u><strong><em><a><ul><ol><li>') {
        return strip_tags($html, $allowed_tags);
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return $ip;
    }
    
    /**
     * Check if IP is blacklisted
     */
    public static function isIPBlacklisted($conn, $ip) {
        $ip = mysqli_real_escape_string($conn, $ip);
        $query = "SELECT id FROM ip_blacklist WHERE ip_address = '$ip' LIMIT 1";
        $result = mysqli_query($conn, $query);
        return mysqli_num_rows($result) > 0;
    }
    
    /**
     * Add security headers
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (adjust as needed)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:; font-src 'self' https://cdnjs.cloudflare.com data:;");
        
        // HTTPS only (uncomment in production)
        // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    /**
     * Secure session configuration
     */
    public static function configureSecureSession() {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
    }
    
    /**
     * Prevent SQL injection (parameterized query helper)
     */
    public static function escapeSQL($conn, $value) {
        return mysqli_real_escape_string($conn, $value);
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent($conn, $event_type, $description, $user_id = null) {
        $event_type = mysqli_real_escape_string($conn, $event_type);
        $description = mysqli_real_escape_string($conn, $description);
        $user_id = $user_id ? intval($user_id) : 'NULL';
        $ip = mysqli_real_escape_string($conn, self::getClientIP());
        $user_agent = mysqli_real_escape_string($conn, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));
        
        $query = "INSERT INTO security_logs (event_type, description, user_id, ip_address, user_agent, created_at)
            VALUES ('$event_type', '$description', $user_id, '$ip', '$user_agent', NOW())";
        
        mysqli_query($conn, $query);
    }
    
    /**
     * Check for suspicious activity patterns
     */
    public static function detectSuspiciousActivity($conn, $user_id) {
        $user_id = intval($user_id);
        $ip = self::getClientIP();
        
        // Check for multiple failed login attempts
        $query = "SELECT COUNT(*) as cnt FROM security_logs 
            WHERE event_type = 'failed_login' 
            AND (user_id = $user_id OR ip_address = '$ip')
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $result = mysqli_query($conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            if ($row['cnt'] > 5) {
                return true;
            }
        }
        
        return false;
    }
}
?>
