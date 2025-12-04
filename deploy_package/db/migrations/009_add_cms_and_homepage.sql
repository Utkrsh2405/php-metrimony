-- Create CMS pages table

CREATE TABLE IF NOT EXISTS cms_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    content LONGTEXT,
    meta_title VARCHAR(200),
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    status VARCHAR(20) DEFAULT 'draft' COMMENT 'draft, published',
    is_featured TINYINT(1) DEFAULT 0,
    view_count INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create homepage configuration table
CREATE TABLE IF NOT EXISTS homepage_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(50) NOT NULL UNIQUE COMMENT 'hero_banner, featured_profiles, success_stories, statistics, testimonials',
    section_title VARCHAR(200),
    section_content TEXT COMMENT 'JSON data for the section',
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default CMS pages
INSERT INTO cms_pages (title, slug, content, meta_title, meta_description, status, published_at) VALUES
('About Us', 'about-us', '<h2>Welcome to MakeMyLove</h2><p>MakeMyLove is India\'s leading matrimonial service dedicated to helping you find your perfect life partner. With thousands of verified profiles, advanced matching algorithms, and personalized assistance, we make your search for love easier and more meaningful.</p><h3>Our Mission</h3><p>To create happy, successful marriages by connecting compatible individuals through trust, technology, and tradition.</p>', 'About Us - MakeMyLove Matrimony', 'Learn about MakeMyLove matrimonial services, our mission, and how we help thousands find their life partners.', 'published', NOW()),

('Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Last updated: October 31, 2025</p><h3>Information We Collect</h3><p>We collect personal information including name, email, phone number, photos, and profile details to provide matrimonial services.</p><h3>How We Use Your Information</h3><p>Your information is used to create your profile, match you with compatible partners, and provide customer support.</p><h3>Data Security</h3><p>We implement industry-standard security measures to protect your personal information.</p>', 'Privacy Policy - MakeMyLove', 'Read our privacy policy to understand how we collect, use, and protect your personal information.', 'published', NOW()),

('Terms of Service', 'terms-of-service', '<h2>Terms of Service</h2><p>By using MakeMyLove services, you agree to these terms and conditions.</p><h3>User Responsibilities</h3><p>Users must provide accurate information and use the platform responsibly.</p><h3>Account Security</h3><p>You are responsible for maintaining the confidentiality of your account credentials.</p>', 'Terms of Service - MakeMyLove', 'Read our terms of service and user agreement for MakeMyLove matrimonial platform.', 'published', NOW()),

('Success Stories', 'success-stories', '<h2>Real Love Stories</h2><p>Discover how MakeMyLove has helped thousands of couples find their perfect match and build beautiful relationships.</p><div class="success-story"><h3>Rahul & Priya</h3><p>"We found each other on MakeMyLove and it was love at first chat! Thank you for bringing us together." - Married in 2024</p></div>', 'Success Stories - Happy Couples', 'Read inspiring success stories from couples who found love through MakeMyLove matrimony.', 'published', NOW()),

('Contact Us', 'contact-us', '<h2>Get in Touch</h2><p>Have questions? We\'re here to help!</p><p><strong>Email:</strong> support@makemylove.com</p><p><strong>Phone:</strong> +91 9876543210</p><p><strong>Address:</strong> 123 Love Street, Mumbai, India</p>', 'Contact Us - MakeMyLove Support', 'Contact MakeMyLove customer support for assistance with your matrimonial profile and services.', 'published', NOW());

-- Insert default homepage configuration
INSERT INTO homepage_config (section_key, section_title, section_content, is_active, display_order) VALUES
('hero_banner', 'Hero Banner', '{"heading": "Find Your Perfect Match", "subheading": "India\'s Most Trusted Matrimony Service", "cta_text": "Register Free", "cta_link": "/register.php", "background_image": "/images/hero-bg.jpg"}', 1, 1),

('statistics', 'Statistics Counter', '{"stats": [{"label": "Happy Couples", "value": "50000+"}, {"label": "Active Members", "value": "100000+"}, {"label": "Success Rate", "value": "85%"}, {"label": "Years of Service", "value": "10+"}]}', 1, 2),

('featured_profiles', 'Featured Profiles', '{"title": "Featured Profiles", "subtitle": "Browse our premium members", "count": 8}', 1, 3),

('success_stories', 'Success Stories', '{"title": "Success Stories", "subtitle": "Real people, real love stories", "stories": [{"couple": "Rahul & Priya", "quote": "We found love on MakeMyLove!", "image": "/images/couple1.jpg"}, {"couple": "Amit & Neha", "quote": "Best decision we ever made!", "image": "/images/couple2.jpg"}]}', 1, 4),

('testimonials', 'Testimonials', '{"title": "What Our Members Say", "testimonials": [{"name": "Rohan Kumar", "text": "Amazing platform! Found my soulmate within 3 months.", "rating": 5}, {"name": "Anjali Sharma", "text": "Professional service with genuine profiles. Highly recommended!", "rating": 5}]}', 1, 5);
