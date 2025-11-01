-- Migration 015: Add site settings table for banner and add custom HTML/CSS section support
-- Date: 2025-01-XX

-- Create site_settings table for global site configurations like banner
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text' COMMENT 'text, image, json, boolean',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default banner setting
INSERT INTO site_settings (setting_key, setting_value, setting_type) VALUES
('homepage_banner', '/images/hero-bg.jpg', 'image'),
('site_logo', '/images/logo.png', 'image'),
('site_name', 'Matrimony Portal', 'text'),
('site_tagline', 'Find Your Perfect Match', 'text')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Add custom HTML section to homepage_config
INSERT INTO homepage_config (section_key, section_title, section_content, is_active, display_order) VALUES
('custom_html', 'Custom HTML/CSS Block', '{"html": "<div class=\"custom-section\"><h2>Welcome</h2><p>Add your custom content here</p></div>", "css": ".custom-section { padding: 20px; text-align: center; }"}', 0, 10)
ON DUPLICATE KEY UPDATE section_key = section_key;

-- Create uploads directory info comment
-- Note: Create /uploads/banners/ directory with proper permissions (755)
