<?php
/**
 * SMS Sender Utility
 * Supports multiple SMS gateways
 */

/**
 * Send SMS to a phone number
 * @param string $phone Phone number with country code
 * @param string $message Message content
 * @param string $event_type Event type for logging
 * @param int $user_id User ID (optional)
 * @return array ['success' => bool, 'message_id' => string, 'error' => string]
 */
function sendSMS($phone, $message, $event_type = 'manual', $user_id = null) {
    global $conn;
    
    // Get active SMS gateway config
    $config_query = "SELECT * FROM sms_config WHERE is_active = 1 LIMIT 1";
    $config_result = mysqli_query($conn, $config_query);
    
    if (mysqli_num_rows($config_result) == 0) {
        return ['success' => false, 'error' => 'No active SMS gateway configured'];
    }
    
    $config = mysqli_fetch_assoc($config_result);
    $config_data = json_decode($config['config_data'], true);
    
    $gateway = $config['gateway'];
    $result = ['success' => false];
    
    // Send via appropriate gateway
    switch ($gateway) {
        case 'twilio':
            $result = sendViaTwilio($phone, $message, $config, $config_data);
            break;
            
        case 'msg91':
            $result = sendViaMsg91($phone, $message, $config, $config_data);
            break;
            
        default:
            // Mock send for development (always succeeds)
            $result = [
                'success' => true,
                'message_id' => 'MOCK_' . uniqid(),
                'gateway' => $gateway
            ];
            break;
    }
    
    // Log SMS
    logSMS($user_id, $phone, $message, $event_type, $result, $gateway);
    
    return $result;
}

/**
 * Send SMS via Twilio
 */
function sendViaTwilio($phone, $message, $config, $config_data) {
    $account_sid = $config_data['account_sid'] ?? '';
    $auth_token = $config['api_secret'] ?? '';
    $from_number = $config_data['from_number'] ?? '';
    
    if (empty($account_sid) || empty($auth_token) || empty($from_number)) {
        return ['success' => false, 'error' => 'Twilio not properly configured'];
    }
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json";
    
    $data = [
        'From' => $from_number,
        'To' => $phone,
        'Body' => $message
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $response_data = json_decode($response, true);
    
    if ($http_code == 201 && isset($response_data['sid'])) {
        return [
            'success' => true,
            'message_id' => $response_data['sid'],
            'gateway' => 'twilio'
        ];
    } else {
        return [
            'success' => false,
            'error' => $response_data['message'] ?? 'Twilio API error',
            'gateway' => 'twilio'
        ];
    }
}

/**
 * Send SMS via MSG91
 */
function sendViaMsg91($phone, $message, $config, $config_data) {
    $auth_key = $config['api_key'] ?? '';
    $sender_id = $config['sender_id'] ?? '';
    $route = $config_data['route'] ?? '4';
    $country_code = $config_data['country_code'] ?? '91';
    
    if (empty($auth_key) || empty($sender_id)) {
        return ['success' => false, 'error' => 'MSG91 not properly configured'];
    }
    
    // Remove + from phone if present
    $phone = str_replace('+', '', $phone);
    
    // Add country code if not present
    if (!str_starts_with($phone, $country_code)) {
        $phone = $country_code . $phone;
    }
    
    $url = "https://api.msg91.com/api/sendhttp.php";
    
    $data = [
        'authkey' => $auth_key,
        'mobiles' => $phone,
        'message' => $message,
        'sender' => $sender_id,
        'route' => $route
    ];
    
    $url_with_params = $url . '?' . http_build_query($data);
    
    $ch = curl_init($url_with_params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $response_data = json_decode($response, true);
    
    if ($http_code == 200 && isset($response_data['type']) && $response_data['type'] == 'success') {
        return [
            'success' => true,
            'message_id' => $response_data['message'] ?? 'MSG91_' . uniqid(),
            'gateway' => 'msg91'
        ];
    } else {
        return [
            'success' => false,
            'error' => $response_data['message'] ?? 'MSG91 API error',
            'gateway' => 'msg91'
        ];
    }
}

/**
 * Log SMS to database
 */
function logSMS($user_id, $phone, $message, $event_type, $result, $gateway) {
    global $conn;
    
    $user_id = $user_id ? intval($user_id) : 0;
    $phone = mysqli_real_escape_string($conn, $phone);
    $message = mysqli_real_escape_string($conn, $message);
    $event_type = mysqli_real_escape_string($conn, $event_type);
    $status = $result['success'] ? 'sent' : 'failed';
    $error_message = isset($result['error']) ? mysqli_real_escape_string($conn, $result['error']) : null;
    $gateway = mysqli_real_escape_string($conn, $gateway);
    $gateway_message_id = isset($result['message_id']) ? mysqli_real_escape_string($conn, $result['message_id']) : null;
    $sent_at = $result['success'] ? 'NOW()' : 'NULL';
    
    $query = "INSERT INTO sms_logs 
              (user_id, phone_number, message, event_type, status, error_message, gateway, gateway_message_id, sent_at, created_at)
              VALUES ";
    
    if ($user_id > 0) {
        $query .= "($user_id, '$phone', '$message', '$event_type', '$status', " . 
                  ($error_message ? "'$error_message'" : "NULL") . ", '$gateway', " .
                  ($gateway_message_id ? "'$gateway_message_id'" : "NULL") . ", $sent_at, NOW())";
    } else {
        $query .= "(0, '$phone', '$message', '$event_type', '$status', " .
                  ($error_message ? "'$error_message'" : "NULL") . ", '$gateway', " .
                  ($gateway_message_id ? "'$gateway_message_id'" : "NULL") . ", $sent_at, NOW())";
    }
    
    mysqli_query($conn, $query);
}

/**
 * Render SMS template with variables
 * @param string $template Template content with {{variable}} placeholders
 * @param array $variables Associative array of variable values
 * @return string Rendered message
 */
function renderSMSTemplate($template, $variables) {
    $message = $template;
    
    foreach ($variables as $key => $value) {
        $message = str_replace('{{' . $key . '}}', $value, $message);
    }
    
    return $message;
}

/**
 * Send SMS from template
 * @param int $template_id SMS template ID
 * @param int $user_id User ID to send to
 * @param array $variables Variables to replace in template
 * @return array Result from sendSMS()
 */
function sendSMSFromTemplate($template_id, $user_id, $variables = []) {
    global $conn;
    
    // Get template
    $template_query = "SELECT * FROM sms_templates WHERE id = $template_id AND is_active = 1";
    $template_result = mysqli_query($conn, $template_query);
    
    if (mysqli_num_rows($template_result) == 0) {
        return ['success' => false, 'error' => 'Template not found or inactive'];
    }
    
    $template = mysqli_fetch_assoc($template_result);
    
    // Get user phone number
    $user_query = "SELECT c.mobile FROM customer c WHERE c.cust_id = $user_id";
    $user_result = mysqli_query($conn, $user_query);
    
    if (mysqli_num_rows($user_result) == 0) {
        return ['success' => false, 'error' => 'User phone number not found'];
    }
    
    $user = mysqli_fetch_assoc($user_result);
    $phone = $user['mobile'];
    
    if (empty($phone)) {
        return ['success' => false, 'error' => 'User has no phone number'];
    }
    
    // Render template
    $message = renderSMSTemplate($template['content'], $variables);
    
    // Send SMS
    return sendSMS($phone, $message, $template['event_trigger'], $user_id);
}
?>
