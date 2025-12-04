<?php
/**
 * Profile Completion Calculator
 * Calculate profile completion percentage and suggest missing fields
 */

class ProfileCompletion {
    private $conn;
    private $user_id;
    private $profile_data = null;
    private $customer_data = null;
    
    // Field weights for completion calculation
    private $field_weights = [
        // Basic Info (40%)
        'name' => 5,
        'age' => 5,
        'gender' => 5,
        'date_of_birth' => 5,
        'email' => 5,
        'phone' => 5,
        'location' => 5,
        'profile_picture' => 5,
        
        // Personal Details (30%)
        'height' => 3,
        'weight' => 2,
        'marital_status' => 5,
        'religion' => 5,
        'caste' => 3,
        'mother_tongue' => 3,
        'body_type' => 2,
        'complexion' => 2,
        'diet' => 2,
        'drinking' => 2,
        'smoking' => 2,
        
        // Professional Details (15%)
        'education' => 5,
        'occupation' => 5,
        'annual_income' => 3,
        'employed_in' => 2,
        
        // Family Details (10%)
        'family_status' => 3,
        'family_type' => 3,
        'father_occupation' => 2,
        'mother_occupation' => 2,
        
        // About & Preferences (5%)
        'about_me' => 3,
        'partner_expectations' => 2
    ];
    
    public function __construct($db_connection, $user_id) {
        $this->conn = $db_connection;
        $this->user_id = intval($user_id);
        $this->loadProfileData();
    }
    
    private function loadProfileData() {
        // Load from users table
        $user_query = "SELECT * FROM users WHERE id = {$this->user_id} LIMIT 1";
        $result = mysqli_query($this->conn, $user_query);
        if ($result && mysqli_num_rows($result) > 0) {
            $this->profile_data = mysqli_fetch_assoc($result);
        }
        
        // Load from customer table
        $customer_query = "SELECT * FROM customer WHERE user_id = {$this->user_id} LIMIT 1";
        $result = mysqli_query($this->conn, $customer_query);
        if ($result && mysqli_num_rows($result) > 0) {
            $this->customer_data = mysqli_fetch_assoc($result);
        }
    }
    
    /**
     * Check if a field is filled
     */
    private function isFieldFilled($field) {
        // Check in users table
        if ($this->profile_data && isset($this->profile_data[$field])) {
            $value = $this->profile_data[$field];
            if (!empty($value) && $value !== null && $value !== '') {
                return true;
            }
        }
        
        // Check in customer table
        if ($this->customer_data && isset($this->customer_data[$field])) {
            $value = $this->customer_data[$field];
            if (!empty($value) && $value !== null && $value !== '') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Calculate completion percentage
     */
    public function getCompletionPercentage() {
        $total_weight = array_sum($this->field_weights);
        $filled_weight = 0;
        
        foreach ($this->field_weights as $field => $weight) {
            if ($this->isFieldFilled($field)) {
                $filled_weight += $weight;
            }
        }
        
        return round(($filled_weight / $total_weight) * 100, 2);
    }
    
    /**
     * Get missing fields
     */
    public function getMissingFields() {
        $missing = [];
        
        foreach ($this->field_weights as $field => $weight) {
            if (!$this->isFieldFilled($field)) {
                $missing[] = [
                    'field' => $field,
                    'label' => $this->getFieldLabel($field),
                    'weight' => $weight,
                    'category' => $this->getFieldCategory($field)
                ];
            }
        }
        
        // Sort by weight (highest first)
        usort($missing, function($a, $b) {
            return $b['weight'] - $a['weight'];
        });
        
        return $missing;
    }
    
    /**
     * Get field label (user-friendly name)
     */
    private function getFieldLabel($field) {
        $labels = [
            'name' => 'Full Name',
            'age' => 'Age',
            'gender' => 'Gender',
            'date_of_birth' => 'Date of Birth',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'location' => 'Location',
            'profile_picture' => 'Profile Picture',
            'height' => 'Height',
            'weight' => 'Weight',
            'marital_status' => 'Marital Status',
            'religion' => 'Religion',
            'caste' => 'Caste',
            'mother_tongue' => 'Mother Tongue',
            'body_type' => 'Body Type',
            'complexion' => 'Complexion',
            'diet' => 'Diet Preference',
            'drinking' => 'Drinking Habits',
            'smoking' => 'Smoking Habits',
            'education' => 'Education',
            'occupation' => 'Occupation',
            'annual_income' => 'Annual Income',
            'employed_in' => 'Employed In',
            'family_status' => 'Family Status',
            'family_type' => 'Family Type',
            'father_occupation' => 'Father\'s Occupation',
            'mother_occupation' => 'Mother\'s Occupation',
            'about_me' => 'About Me',
            'partner_expectations' => 'Partner Expectations'
        ];
        
        return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
    }
    
    /**
     * Get field category
     */
    private function getFieldCategory($field) {
        $categories = [
            'name' => 'Basic',
            'age' => 'Basic',
            'gender' => 'Basic',
            'date_of_birth' => 'Basic',
            'email' => 'Basic',
            'phone' => 'Basic',
            'location' => 'Basic',
            'profile_picture' => 'Basic',
            'height' => 'Personal',
            'weight' => 'Personal',
            'marital_status' => 'Personal',
            'religion' => 'Personal',
            'caste' => 'Personal',
            'mother_tongue' => 'Personal',
            'body_type' => 'Personal',
            'complexion' => 'Personal',
            'diet' => 'Lifestyle',
            'drinking' => 'Lifestyle',
            'smoking' => 'Lifestyle',
            'education' => 'Professional',
            'occupation' => 'Professional',
            'annual_income' => 'Professional',
            'employed_in' => 'Professional',
            'family_status' => 'Family',
            'family_type' => 'Family',
            'father_occupation' => 'Family',
            'mother_occupation' => 'Family',
            'about_me' => 'About',
            'partner_expectations' => 'Preferences'
        ];
        
        return $categories[$field] ?? 'Other';
    }
    
    /**
     * Get completion status message
     */
    public function getStatusMessage() {
        $percentage = $this->getCompletionPercentage();
        
        if ($percentage >= 90) {
            return "Excellent! Your profile is almost complete.";
        } elseif ($percentage >= 70) {
            return "Good job! Add a few more details to make your profile stand out.";
        } elseif ($percentage >= 50) {
            return "You're halfway there! Complete your profile to get better matches.";
        } elseif ($percentage >= 30) {
            return "Keep going! A complete profile gets 3x more responses.";
        } else {
            return "Let's get started! Complete your profile to find your perfect match.";
        }
    }
    
    /**
     * Get top 5 suggestions
     */
    public function getTopSuggestions($limit = 5) {
        $missing = $this->getMissingFields();
        return array_slice($missing, 0, $limit);
    }
    
    /**
     * Get category-wise completion
     */
    public function getCategoryCompletion() {
        $categories = ['Basic' => 0, 'Personal' => 0, 'Lifestyle' => 0, 'Professional' => 0, 'Family' => 0, 'About' => 0, 'Preferences' => 0];
        $category_totals = ['Basic' => 0, 'Personal' => 0, 'Lifestyle' => 0, 'Professional' => 0, 'Family' => 0, 'About' => 0, 'Preferences' => 0];
        $category_filled = ['Basic' => 0, 'Personal' => 0, 'Lifestyle' => 0, 'Professional' => 0, 'Family' => 0, 'About' => 0, 'Preferences' => 0];
        
        foreach ($this->field_weights as $field => $weight) {
            $category = $this->getFieldCategory($field);
            $category_totals[$category] += $weight;
            
            if ($this->isFieldFilled($field)) {
                $category_filled[$category] += $weight;
            }
        }
        
        foreach ($categories as $category => $value) {
            if ($category_totals[$category] > 0) {
                $categories[$category] = round(($category_filled[$category] / $category_totals[$category]) * 100, 2);
            }
        }
        
        return $categories;
    }
}

/**
 * Helper function to get profile completion instance
 */
function getProfileCompletion($conn, $user_id = null) {
    if ($user_id === null) {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
    }
    return new ProfileCompletion($conn, $user_id);
}
?>
