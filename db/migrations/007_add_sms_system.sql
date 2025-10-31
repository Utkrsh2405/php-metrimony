-- Create SMS templates and logs tables

CREATE TABLE IF NOT EXISTS sms_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    event_trigger VARCHAR(50) NOT NULL COMMENT 'registration, plan_expiry, new_match, interest_received, message_received, etc',
    subject VARCHAR(200),
    content TEXT NOT NULL,
    variables TEXT COMMENT 'JSON array of available variables',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    event_type VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, sent, failed',
    error_message TEXT,
    gateway VARCHAR(50) COMMENT 'twilio, msg91, etc',
    gateway_message_id VARCHAR(100),
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_event_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sms_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gateway VARCHAR(50) NOT NULL COMMENT 'twilio, msg91, etc',
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    sender_id VARCHAR(20),
    is_active TINYINT(1) DEFAULT 0,
    config_data TEXT COMMENT 'JSON for additional gateway-specific settings',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default SMS templates
INSERT INTO sms_templates (name, event_trigger, subject, content, variables) VALUES
('Welcome SMS', 'registration', 'Welcome to MakeMyLove', 'Hi {{name}}, Welcome to MakeMyLove! Your account has been created successfully. Start finding your perfect match today. Login: {{login_url}}', '["name", "email", "login_url"]'),
('Plan Expiry Reminder', 'plan_expiry', 'Your Plan is Expiring Soon', 'Hi {{name}}, Your {{plan_name}} plan expires on {{expiry_date}}. Renew now to continue accessing premium features. Visit: {{renew_url}}', '["name", "plan_name", "expiry_date", "renew_url"]'),
('New Interest Received', 'interest_received', 'New Interest from {{sender_name}}', 'Hi {{name}}, {{sender_name}} has expressed interest in your profile. Login to view and respond. {{profile_url}}', '["name", "sender_name", "profile_url"]'),
('Interest Accepted', 'interest_accepted', 'Your Interest was Accepted!', 'Hi {{name}}, {{receiver_name}} has accepted your interest! You can now connect and message them. {{chat_url}}', '["name", "receiver_name", "chat_url"]'),
('New Message Received', 'message_received', 'New Message from {{sender_name}}', 'Hi {{name}}, You have a new message from {{sender_name}}. Login to read and reply. {{inbox_url}}', '["name", "sender_name", "inbox_url"]'),
('Payment Successful', 'payment_success', 'Payment Confirmation', 'Hi {{name}}, Your payment of Rs.{{amount}} for {{plan_name}} plan has been received successfully. Subscription valid till {{expiry_date}}. Thank you!', '["name", "amount", "plan_name", "expiry_date"]');

-- Insert default SMS config (inactive by default)
INSERT INTO sms_config (gateway, is_active, config_data) VALUES
('twilio', 0, '{"account_sid": "", "auth_token": "", "from_number": ""}'),
('msg91', 0, '{"auth_key": "", "route": "4", "country_code": "91"}');
