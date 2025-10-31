<?php
/**
 * Plan Quota Manager
 * Centralized quota enforcement and tracking system
 */

class QuotaManager {
    private $conn;
    private $user_id;
    private $plan_data = null;
    
    public function __construct($db_connection, $user_id) {
        $this->conn = $db_connection;
        $this->user_id = intval($user_id);
        $this->loadPlanData();
    }
    
    /**
     * Load active plan data for user
     */
    private function loadPlanData() {
        $query = "SELECT us.*, p.* 
            FROM user_subscriptions us
            LEFT JOIN plans p ON us.plan_id = p.id
            WHERE us.user_id = {$this->user_id} 
            AND us.status = 'active'
            ORDER BY us.end_date DESC
            LIMIT 1";
        
        $result = mysqli_query($this->conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $this->plan_data = mysqli_fetch_assoc($result);
        }
    }
    
    /**
     * Get active plan details
     */
    public function getPlan() {
        return $this->plan_data;
    }
    
    /**
     * Check if user has active subscription
     */
    public function hasActivePlan() {
        return $this->plan_data !== null;
    }
    
    /**
     * Get quota limit for a specific feature
     */
    public function getLimit($feature) {
        if (!$this->plan_data) {
            return 0; // No plan = no access
        }
        
        $limit_field = 'max_' . $feature;
        return isset($this->plan_data[$limit_field]) ? intval($this->plan_data[$limit_field]) : 0;
    }
    
    /**
     * Get usage count for a feature within subscription period
     */
    public function getUsage($feature, $table, $user_field = 'from_user_id', $date_field = 'created_at') {
        if (!$this->plan_data) {
            return 0;
        }
        
        $start_date = $this->plan_data['start_date'];
        $end_date = $this->plan_data['end_date'];
        
        $query = "SELECT COUNT(*) as cnt FROM `{$table}` 
            WHERE `{$user_field}` = {$this->user_id}
            AND `{$date_field}` >= '{$start_date}'
            AND `{$date_field}` <= '{$end_date}'";
        
        $result = mysqli_query($this->conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return intval($row['cnt']);
        }
        
        return 0;
    }
    
    /**
     * Check if quota is available for a feature
     */
    public function hasQuota($feature, $table, $user_field = 'from_user_id', $date_field = 'created_at') {
        $limit = $this->getLimit($feature);
        
        // 0 means unlimited
        if ($limit === 0) {
            return true;
        }
        
        $usage = $this->getUsage($feature, $table, $user_field, $date_field);
        return $usage < $limit;
    }
    
    /**
     * Get remaining quota for a feature
     */
    public function getRemaining($feature, $table, $user_field = 'from_user_id', $date_field = 'created_at') {
        $limit = $this->getLimit($feature);
        
        // 0 means unlimited
        if ($limit === 0) {
            return PHP_INT_MAX; // Unlimited
        }
        
        $usage = $this->getUsage($feature, $table, $user_field, $date_field);
        return max(0, $limit - $usage);
    }
    
    /**
     * Get quota info for a feature
     */
    public function getQuotaInfo($feature, $table, $user_field = 'from_user_id', $date_field = 'created_at') {
        $limit = $this->getLimit($feature);
        $usage = $this->getUsage($feature, $table, $user_field, $date_field);
        $remaining = $limit === 0 ? 'Unlimited' : max(0, $limit - $usage);
        
        return [
            'feature' => $feature,
            'limit' => $limit === 0 ? 'Unlimited' : $limit,
            'used' => $usage,
            'remaining' => $remaining,
            'percentage' => $limit > 0 ? round(($usage / $limit) * 100, 2) : 0,
            'is_unlimited' => $limit === 0,
            'has_quota' => $limit === 0 || $usage < $limit
        ];
    }
    
    /**
     * Get all quota info for user's plan
     */
    public function getAllQuotas() {
        $quotas = [];
        
        // Interests quota
        $quotas['interests'] = $this->getQuotaInfo('interests_express', 'interests', 'from_user_id', 'created_at');
        
        // Messages quota
        $quotas['messages'] = $this->getQuotaInfo('messages_send', 'messages', 'from_user_id', 'created_at');
        
        // Contacts view quota
        $contacts_usage = $this->getContactsViewedCount();
        $contacts_limit = $this->getLimit('contacts_view');
        $quotas['contacts'] = [
            'feature' => 'contacts_view',
            'limit' => $contacts_limit === 0 ? 'Unlimited' : $contacts_limit,
            'used' => $contacts_usage,
            'remaining' => $contacts_limit === 0 ? 'Unlimited' : max(0, $contacts_limit - $contacts_usage),
            'percentage' => $contacts_limit > 0 ? round(($contacts_usage / $contacts_limit) * 100, 2) : 0,
            'is_unlimited' => $contacts_limit === 0,
            'has_quota' => $contacts_limit === 0 || $contacts_usage < $contacts_limit
        ];
        
        // Shortlist quota (not time-based, just max count)
        $shortlist_count = $this->getShortlistCount();
        $shortlist_limit = $this->getLimit('shortlist');
        $quotas['shortlist'] = [
            'feature' => 'shortlist',
            'limit' => $shortlist_limit === 0 ? 'Unlimited' : $shortlist_limit,
            'used' => $shortlist_count,
            'remaining' => $shortlist_limit === 0 ? 'Unlimited' : max(0, $shortlist_limit - $shortlist_count),
            'percentage' => $shortlist_limit > 0 ? round(($shortlist_count / $shortlist_limit) * 100, 2) : 0,
            'is_unlimited' => $shortlist_limit === 0,
            'has_quota' => $shortlist_limit === 0 || $shortlist_count < $shortlist_limit
        ];
        
        // Chat feature access
        $quotas['chat'] = [
            'feature' => 'chat',
            'enabled' => $this->plan_data ? (bool)$this->plan_data['can_chat'] : false,
            'limit' => $this->plan_data && $this->plan_data['can_chat'] ? 'Enabled' : 'Disabled'
        ];
        
        return $quotas;
    }
    
    /**
     * Get contacts viewed count (from a hypothetical contact_views table)
     */
    private function getContactsViewedCount() {
        if (!$this->plan_data) {
            return 0;
        }
        
        $start_date = $this->plan_data['start_date'];
        $end_date = $this->plan_data['end_date'];
        
        // Check if contact_views table exists, otherwise return 0
        $table_check = mysqli_query($this->conn, "SHOW TABLES LIKE 'contact_views'");
        if (mysqli_num_rows($table_check) === 0) {
            return 0;
        }
        
        $query = "SELECT COUNT(DISTINCT profile_id) as cnt FROM contact_views 
            WHERE user_id = {$this->user_id}
            AND viewed_at >= '{$start_date}'
            AND viewed_at <= '{$end_date}'";
        
        $result = mysqli_query($this->conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return intval($row['cnt']);
        }
        
        return 0;
    }
    
    /**
     * Get current shortlist count
     */
    private function getShortlistCount() {
        $query = "SELECT COUNT(*) as cnt FROM shortlists WHERE user_id = {$this->user_id}";
        $result = mysqli_query($this->conn, $query);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            return intval($row['cnt']);
        }
        return 0;
    }
    
    /**
     * Enforce quota or throw error
     */
    public function enforceQuota($feature, $table, $user_field = 'from_user_id', $date_field = 'created_at') {
        if (!$this->hasQuota($feature, $table, $user_field, $date_field)) {
            $info = $this->getQuotaInfo($feature, $table, $user_field, $date_field);
            throw new Exception("Quota exceeded for {$feature}. Limit: {$info['limit']}, Used: {$info['used']}");
        }
        return true;
    }
    
    /**
     * Get plan name
     */
    public function getPlanName() {
        return $this->plan_data ? $this->plan_data['name'] : 'No Active Plan';
    }
    
    /**
     * Get plan expiry date
     */
    public function getExpiryDate() {
        return $this->plan_data ? $this->plan_data['end_date'] : null;
    }
    
    /**
     * Get days remaining in subscription
     */
    public function getDaysRemaining() {
        if (!$this->plan_data) {
            return 0;
        }
        
        $end_date = new DateTime($this->plan_data['end_date']);
        $now = new DateTime();
        $diff = $now->diff($end_date);
        
        return $diff->days * ($diff->invert ? -1 : 1);
    }
}

/**
 * Helper function to get quota manager instance
 */
function getQuotaManager($conn, $user_id = null) {
    if ($user_id === null) {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
    }
    return new QuotaManager($conn, $user_id);
}
?>
