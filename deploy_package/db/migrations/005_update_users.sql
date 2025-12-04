-- Migration: Update users table with admin fields
-- Created: 2025-10-31

-- Add columns to users table (check if exists first)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'matrimony' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'account_status');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `users` ADD COLUMN `account_status` ENUM(\'active\', \'suspended\', \'deleted\') DEFAULT \'active\' AFTER `userlevel`',
    'SELECT "Column account_status already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'matrimony' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'profile_completeness');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `users` ADD COLUMN `profile_completeness` INT DEFAULT 0 AFTER `userlevel`',
    'SELECT "Column profile_completeness already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'matrimony' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'last_login');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `users` ADD COLUMN `last_login` TIMESTAMP NULL AFTER `userlevel`',
    'SELECT "Column last_login already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = 'matrimony' AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_account_status');

SET @query = IF(@idx_exists = 0, 
    'ALTER TABLE `users` ADD INDEX `idx_account_status` (`account_status`)',
    'SELECT "Index idx_account_status already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add columns to customer table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'matrimony' AND TABLE_NAME = 'customer' AND COLUMN_NAME = 'is_verified');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `customer` ADD COLUMN `is_verified` BOOLEAN DEFAULT 0',
    'SELECT "Column is_verified already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'matrimony' AND TABLE_NAME = 'customer' AND COLUMN_NAME = 'admin_notes');

SET @query = IF(@col_exists = 0, 
    'ALTER TABLE `customer` ADD COLUMN `admin_notes` TEXT',
    'SELECT "Column admin_notes already exists" AS message');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
