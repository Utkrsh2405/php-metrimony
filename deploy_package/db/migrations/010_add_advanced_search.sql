-- Migration: Advanced Member Search System
-- Description: Add tables for saved searches and search history

-- Create saved searches table
CREATE TABLE IF NOT EXISTS saved_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    search_name VARCHAR(100) NOT NULL,
    search_filters JSON NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create search history table
CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    search_filters JSON NOT NULL,
    results_count INT DEFAULT 0,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_searched_at (searched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add search preferences to users table
-- Check if columns exist before adding
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname1 = 'search_notifications';
SET @columnname2 = 'last_search_date';

SET @query1 = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = @columnname1) = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname1, ' TINYINT(1) DEFAULT 1'),
    'SELECT "Column search_notifications already exists" AS message'
);

PREPARE stmt1 FROM @query1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @query2 = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = @columnname2) = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname2, ' TIMESTAMP NULL'),
    'SELECT "Column last_search_date already exists" AS message'
);

PREPARE stmt2 FROM @query2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Insert some default saved searches for testing (for user_id 1 if exists)
INSERT INTO saved_searches (user_id, search_name, search_filters, is_default) 
SELECT 1, 'Professionals in My City', 
       '{"education": "Graduate", "location": "Delhi", "age_min": 25, "age_max": 35}', 
       0
WHERE EXISTS (SELECT 1 FROM users WHERE id = 1)
AND NOT EXISTS (SELECT 1 FROM saved_searches WHERE user_id = 1 AND search_name = 'Professionals in My City');

INSERT INTO saved_searches (user_id, search_name, search_filters, is_default) 
SELECT 1, 'Quick Match', 
       '{"age_min": 25, "age_max": 30, "marital_status": "Never Married"}', 
       1
WHERE EXISTS (SELECT 1 FROM users WHERE id = 1)
AND NOT EXISTS (SELECT 1 FROM saved_searches WHERE user_id = 1 AND search_name = 'Quick Match');
