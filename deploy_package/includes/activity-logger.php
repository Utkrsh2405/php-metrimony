<?php
/**
 * Admin Activity Logger
 * Log all admin actions for audit trail
 */

class ActivityLogger {
    private $conn;
    private $admin_id;
    
    public function __construct($db_connection, $admin_id = null) {
        $this->conn = $db_connection;
        $this->admin_id = $admin_id ?? (isset($_SESSION['id']) ? intval($_SESSION['id']) : 0);
    }
    
    /**
     * Log an admin action
     */
    public function log($action, $entity_type, $entity_id = null, $description = '', $old_data = null, $new_data = null) {
        $admin_id = intval($this->admin_id);
        $action = mysqli_real_escape_string($this->conn, $action);
        $entity_type = mysqli_real_escape_string($this->conn, $entity_type);
        $entity_id = $entity_id ? intval($entity_id) : 'NULL';
        $description = mysqli_real_escape_string($this->conn, $description);
        $ip_address = mysqli_real_escape_string($this->conn, $this->getClientIP());
        $user_agent = mysqli_real_escape_string($this->conn, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));
        
        $old_data_json = $old_data ? "'" . mysqli_real_escape_string($this->conn, json_encode($old_data)) . "'" : 'NULL';
        $new_data_json = $new_data ? "'" . mysqli_real_escape_string($this->conn, json_encode($new_data)) . "'" : 'NULL';
        
        $query = "INSERT INTO admin_activity_logs 
            (admin_id, action, entity_type, entity_id, description, old_data, new_data, ip_address, user_agent, created_at)
            VALUES 
            ($admin_id, '$action', '$entity_type', $entity_id, '$description', $old_data_json, $new_data_json, '$ip_address', '$user_agent', NOW())";
        
        return mysqli_query($this->conn, $query);
    }
    
    /**
     * Log user creation
     */
    public function logUserCreate($user_id, $user_data) {
        return $this->log('create', 'user', $user_id, 'Created new user', null, $user_data);
    }
    
    /**
     * Log user update
     */
    public function logUserUpdate($user_id, $old_data, $new_data) {
        return $this->log('update', 'user', $user_id, 'Updated user details', $old_data, $new_data);
    }
    
    /**
     * Log user deletion
     */
    public function logUserDelete($user_id, $user_data) {
        return $this->log('delete', 'user', $user_id, 'Deleted user', $user_data, null);
    }
    
    /**
     * Log photo verification
     */
    public function logPhotoVerification($user_id, $status) {
        return $this->log('verify_photo', 'user', $user_id, "Photo verification: $status");
    }
    
    /**
     * Log plan creation/update/deletion
     */
    public function logPlan($action, $plan_id, $old_data = null, $new_data = null) {
        $description = ucfirst($action) . ' subscription plan';
        return $this->log($action, 'plan', $plan_id, $description, $old_data, $new_data);
    }
    
    /**
     * Log payment actions
     */
    public function logPayment($action, $payment_id, $description = '', $data = null) {
        return $this->log($action, 'payment', $payment_id, $description, null, $data);
    }
    
    /**
     * Log message deletion
     */
    public function logMessageDelete($message_id, $message_data) {
        return $this->log('delete', 'message', $message_id, 'Deleted message', $message_data, null);
    }
    
    /**
     * Log interest deletion
     */
    public function logInterestDelete($interest_id, $interest_data) {
        return $this->log('delete', 'interest', $interest_id, 'Deleted interest', $interest_data, null);
    }
    
    /**
     * Log CMS page actions
     */
    public function logCMSPage($action, $page_id, $page_title, $old_data = null, $new_data = null) {
        $description = ucfirst($action) . " CMS page: $page_title";
        return $this->log($action, 'cms_page', $page_id, $description, $old_data, $new_data);
    }
    
    /**
     * Log SMS template actions
     */
    public function logSMSTemplate($action, $template_id, $template_name, $old_data = null, $new_data = null) {
        $description = ucfirst($action) . " SMS template: $template_name";
        return $this->log($action, 'sms_template', $template_id, $description, $old_data, $new_data);
    }
    
    /**
     * Log translation actions
     */
    public function logTranslation($action, $lang, $key) {
        $description = ucfirst($action) . " translation: [$lang] $key";
        return $this->log($action, 'translation', null, $description);
    }
    
    /**
     * Log homepage configuration
     */
    public function logHomepageConfig($action, $section) {
        $description = ucfirst($action) . " homepage section: $section";
        return $this->log($action, 'homepage_config', null, $description);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
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
     * Get recent activity logs
     */
    public function getRecentLogs($limit = 50, $offset = 0) {
        $limit = intval($limit);
        $offset = intval($offset);
        
        $query = "SELECT l.*, u.name as admin_name, u.email as admin_email
            FROM admin_activity_logs l
            LEFT JOIN users u ON l.admin_id = u.id
            ORDER BY l.created_at DESC
            LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $query);
        $logs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        
        return $logs;
    }
    
    /**
     * Get logs by admin
     */
    public function getLogsByAdmin($admin_id, $limit = 50, $offset = 0) {
        $admin_id = intval($admin_id);
        $limit = intval($limit);
        $offset = intval($offset);
        
        $query = "SELECT * FROM admin_activity_logs
            WHERE admin_id = $admin_id
            ORDER BY created_at DESC
            LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $query);
        $logs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        
        return $logs;
    }
    
    /**
     * Get logs by entity
     */
    public function getLogsByEntity($entity_type, $entity_id, $limit = 50, $offset = 0) {
        $entity_type = mysqli_real_escape_string($this->conn, $entity_type);
        $entity_id = intval($entity_id);
        $limit = intval($limit);
        $offset = intval($offset);
        
        $query = "SELECT l.*, u.name as admin_name, u.email as admin_email
            FROM admin_activity_logs l
            LEFT JOIN users u ON l.admin_id = u.id
            WHERE l.entity_type = '$entity_type' AND l.entity_id = $entity_id
            ORDER BY l.created_at DESC
            LIMIT $limit OFFSET $offset";
        
        $result = mysqli_query($this->conn, $query);
        $logs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
        
        return $logs;
    }
    
    /**
     * Get activity statistics
     */
    public function getStats($days = 30) {
        $stats = [
            'total' => 0,
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
            'by_action' => [],
            'by_entity' => []
        ];
        
        // Total logs
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as cnt FROM admin_activity_logs");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['total'] = intval($row['cnt']);
        }
        
        // Today
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as cnt FROM admin_activity_logs WHERE DATE(created_at) = CURDATE()");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['today'] = intval($row['cnt']);
        }
        
        // This week
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as cnt FROM admin_activity_logs WHERE YEARWEEK(created_at) = YEARWEEK(NOW())");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['this_week'] = intval($row['cnt']);
        }
        
        // This month
        $result = mysqli_query($this->conn, "SELECT COUNT(*) as cnt FROM admin_activity_logs WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $stats['this_month'] = intval($row['cnt']);
        }
        
        // By action
        $result = mysqli_query($this->conn, "SELECT action, COUNT(*) as cnt FROM admin_activity_logs GROUP BY action ORDER BY cnt DESC LIMIT 10");
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['by_action'][$row['action']] = intval($row['cnt']);
        }
        
        // By entity type
        $result = mysqli_query($this->conn, "SELECT entity_type, COUNT(*) as cnt FROM admin_activity_logs GROUP BY entity_type ORDER BY cnt DESC LIMIT 10");
        while ($row = mysqli_fetch_assoc($result)) {
            $stats['by_entity'][$row['entity_type']] = intval($row['cnt']);
        }
        
        return $stats;
    }
}

/**
 * Helper function to get activity logger instance
 */
function getActivityLogger($conn, $admin_id = null) {
    return new ActivityLogger($conn, $admin_id);
}
?>
