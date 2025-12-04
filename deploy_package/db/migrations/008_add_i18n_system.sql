-- Create internationalization tables

CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE COMMENT 'Language code (e.g., en, es, hi)',
    name VARCHAR(100) NOT NULL COMMENT 'Language name (e.g., English, Spanish)',
    native_name VARCHAR(100) COMMENT 'Native language name (e.g., English, Español)',
    is_rtl TINYINT(1) DEFAULT 0 COMMENT 'Right-to-left language',
    is_active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,
    flag_icon VARCHAR(50) COMMENT 'Flag icon or emoji',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    language_code VARCHAR(10) NOT NULL,
    translation_key VARCHAR(255) NOT NULL COMMENT 'Unique key for translation (e.g., welcome_message, login_button)',
    translation_value TEXT NOT NULL COMMENT 'Translated text',
    category VARCHAR(50) DEFAULT 'general' COMMENT 'Category: general, auth, profile, dashboard, etc',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_translation (language_code, translation_key),
    FOREIGN KEY (language_code) REFERENCES languages(code) ON DELETE CASCADE,
    INDEX idx_language (language_code),
    INDEX idx_key (translation_key),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default languages
INSERT INTO languages (code, name, native_name, is_rtl, is_active, is_default, flag_icon) VALUES
('en', 'English', 'English', 0, 1, 1, '🇬🇧'),
('hi', 'Hindi', 'हिन्दी', 0, 1, 0, '🇮🇳'),
('es', 'Spanish', 'Español', 0, 0, 0, '🇪🇸'),
('ar', 'Arabic', 'العربية', 1, 0, 0, '🇸🇦'),
('fr', 'French', 'Français', 0, 0, 0, '🇫🇷');

-- Insert default English translations
INSERT INTO translations (language_code, translation_key, translation_value, category) VALUES
-- General
('en', 'site_name', 'MakeMyLove', 'general'),
('en', 'welcome', 'Welcome', 'general'),
('en', 'home', 'Home', 'general'),
('en', 'about', 'About', 'general'),
('en', 'contact', 'Contact', 'general'),
('en', 'search', 'Search', 'general'),
('en', 'save', 'Save', 'general'),
('en', 'cancel', 'Cancel', 'general'),
('en', 'delete', 'Delete', 'general'),
('en', 'edit', 'Edit', 'general'),
('en', 'view', 'View', 'general'),
('en', 'close', 'Close', 'general'),
('en', 'submit', 'Submit', 'general'),
('en', 'loading', 'Loading...', 'general'),

-- Authentication
('en', 'login', 'Login', 'auth'),
('en', 'logout', 'Logout', 'auth'),
('en', 'register', 'Register', 'auth'),
('en', 'email', 'Email', 'auth'),
('en', 'password', 'Password', 'auth'),
('en', 'forgot_password', 'Forgot Password?', 'auth'),
('en', 'remember_me', 'Remember Me', 'auth'),
('en', 'login_success', 'Login successful!', 'auth'),
('en', 'login_failed', 'Invalid credentials', 'auth'),

-- Profile
('en', 'profile', 'Profile', 'profile'),
('en', 'my_profile', 'My Profile', 'profile'),
('en', 'edit_profile', 'Edit Profile', 'profile'),
('en', 'personal_info', 'Personal Information', 'profile'),
('en', 'contact_info', 'Contact Information', 'profile'),
('en', 'preferences', 'Preferences', 'profile'),
('en', 'photos', 'Photos', 'profile'),
('en', 'upload_photo', 'Upload Photo', 'profile'),

-- Dashboard
('en', 'dashboard', 'Dashboard', 'dashboard'),
('en', 'my_matches', 'My Matches', 'dashboard'),
('en', 'interests', 'Interests', 'dashboard'),
('en', 'messages', 'Messages', 'dashboard'),
('en', 'shortlist', 'Shortlist', 'dashboard'),
('en', 'notifications', 'Notifications', 'dashboard'),

-- Plans
('en', 'subscription_plans', 'Subscription Plans', 'plans'),
('en', 'upgrade_plan', 'Upgrade Plan', 'plans'),
('en', 'current_plan', 'Current Plan', 'plans'),
('en', 'choose_plan', 'Choose Plan', 'plans'),
('en', 'free_plan', 'Free Plan', 'plans'),
('en', 'premium_plan', 'Premium Plan', 'plans');

-- Insert corresponding Hindi translations
INSERT INTO translations (language_code, translation_key, translation_value, category) VALUES
-- General
('hi', 'site_name', 'मेक माय लव', 'general'),
('hi', 'welcome', 'स्वागत है', 'general'),
('hi', 'home', 'होम', 'general'),
('hi', 'about', 'के बारे में', 'general'),
('hi', 'contact', 'संपर्क करें', 'general'),
('hi', 'search', 'खोजें', 'general'),
('hi', 'save', 'सहेजें', 'general'),
('hi', 'cancel', 'रद्द करें', 'general'),
('hi', 'delete', 'हटाएं', 'general'),
('hi', 'edit', 'संपादित करें', 'general'),
('hi', 'view', 'देखें', 'general'),
('hi', 'close', 'बंद करें', 'general'),
('hi', 'submit', 'जमा करें', 'general'),
('hi', 'loading', 'लोड हो रहा है...', 'general'),

-- Authentication
('hi', 'login', 'लॉगिन', 'auth'),
('hi', 'logout', 'लॉगआउट', 'auth'),
('hi', 'register', 'पंजीकरण करें', 'auth'),
('hi', 'email', 'ईमेल', 'auth'),
('hi', 'password', 'पासवर्ड', 'auth'),
('hi', 'forgot_password', 'पासवर्ड भूल गए?', 'auth'),
('hi', 'remember_me', 'मुझे याद रखें', 'auth'),
('hi', 'login_success', 'लॉगिन सफल!', 'auth'),
('hi', 'login_failed', 'अमान्य क्रेडेंशियल', 'auth'),

-- Profile
('hi', 'profile', 'प्रोफ़ाइल', 'profile'),
('hi', 'my_profile', 'मेरी प्रोफ़ाइल', 'profile'),
('hi', 'edit_profile', 'प्रोफ़ाइल संपादित करें', 'profile'),
('hi', 'personal_info', 'व्यक्तिगत जानकारी', 'profile'),
('hi', 'contact_info', 'संपर्क जानकारी', 'profile'),
('hi', 'preferences', 'प्राथमिकताएं', 'profile'),
('hi', 'photos', 'फोटो', 'profile'),
('hi', 'upload_photo', 'फोटो अपलोड करें', 'profile'),

-- Dashboard
('hi', 'dashboard', 'डैशबोर्ड', 'dashboard'),
('hi', 'my_matches', 'मेरे मैच', 'dashboard'),
('hi', 'interests', 'रुचियां', 'dashboard'),
('hi', 'messages', 'संदेश', 'dashboard'),
('hi', 'shortlist', 'शॉर्टलिस्ट', 'dashboard'),
('hi', 'notifications', 'सूचनाएं', 'dashboard'),

-- Plans
('hi', 'subscription_plans', 'सदस्यता योजनाएं', 'plans'),
('hi', 'upgrade_plan', 'योजना अपग्रेड करें', 'plans'),
('hi', 'current_plan', 'वर्तमान योजना', 'plans'),
('hi', 'choose_plan', 'योजना चुनें', 'plans'),
('hi', 'free_plan', 'मुफ्त योजना', 'plans'),
('hi', 'premium_plan', 'प्रीमियम योजना', 'plans');
