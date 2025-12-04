-- Migration: Express Interest System
-- Description: Add tables for interest management with quotas and tracking

-- Create interests table
CREATE TABLE IF NOT EXISTS interests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'declined', 'cancelled') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES customer(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES customer(id) ON DELETE CASCADE,
    UNIQUE KEY unique_interest (sender_id, receiver_id),
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create interest_quota_usage table for daily tracking
CREATE TABLE IF NOT EXISTS interest_quota_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    interests_sent INT DEFAULT 0,
    interests_received INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES customer(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date),
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add interest preferences to customer table
SET @dbname = DATABASE();
SET @tablename = 'customer';
SET @columnname1 = 'interest_notifications';
SET @columnname2 = 'auto_decline_interests';

SET @query1 = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = @columnname1) = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname1, ' TINYINT(1) DEFAULT 1'),
    'SELECT "Column interest_notifications already exists" AS message'
);

PREPARE stmt1 FROM @query1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @query2 = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = @columnname2) = 0,
    CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname2, ' TINYINT(1) DEFAULT 0'),
    'SELECT "Column auto_decline_interests already exists" AS message'
);

PREPARE stmt2 FROM @query2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- Insert a test interest if we have at least 2 customers
SET @first_customer = (SELECT id FROM customer ORDER BY id LIMIT 1);
SET @second_customer = (SELECT id FROM customer ORDER BY id LIMIT 1, 1);

INSERT IGNORE INTO interests (sender_id, receiver_id, status, message)
VALUES (@first_customer, @second_customer, 'pending', 'Hi, I would like to connect with you. Your profile matches my preferences.');

-- Initialize quota usage for today for all customers
INSERT IGNORE INTO interest_quota_usage (user_id, date, interests_sent)
SELECT id, CURDATE(), 0
FROM customer;
