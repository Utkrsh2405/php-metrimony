-- Create footer_settings table
CREATE TABLE IF NOT EXISTS footer_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    background_image VARCHAR(255),
    site_name VARCHAR(100) DEFAULT 'Shaadi Partner.com',
    address VARCHAR(255) DEFAULT 'New Delhi, India',
    phone VARCHAR(50) DEFAULT '+91 7099001862',
    email VARCHAR(100) DEFAULT 'admin@shaadipartner.com',
    copyright_text VARCHAR(255) DEFAULT 'Copyright ©2025 All rights reserved',
    facebook_link VARCHAR(255) DEFAULT '#',
    twitter_link VARCHAR(255) DEFAULT '#',
    instagram_link VARCHAR(255) DEFAULT '#',
    youtube_link VARCHAR(255) DEFAULT '#',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO footer_settings (id, site_name) VALUES (1, 'Shaadi Partner.com') ON DUPLICATE KEY UPDATE id=1;

-- Create footer_links table
CREATE TABLE IF NOT EXISTS footer_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    column_name ENUM('quick_links', 'links') NOT NULL,
    link_label VARCHAR(100) NOT NULL,
    link_url VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default links
INSERT INTO footer_links (column_name, link_label, link_url, display_order) VALUES 
('quick_links', 'Home', 'index.php', 1),
('quick_links', 'Search', 'search.php', 2),
('quick_links', 'Membership', 'plans.php', 3),
('quick_links', 'About Us', 'about.php', 4),
('quick_links', 'Terms & Condition', 'terms.php', 5),
('links', 'Registration', 'register.php', 1),
('links', 'Login', 'login.php', 2),
('links', 'Payment', 'plans.php', 3),
('links', 'Privacy', 'privacy.php', 4),
('links', 'Contact Us', 'contact.php', 5);
