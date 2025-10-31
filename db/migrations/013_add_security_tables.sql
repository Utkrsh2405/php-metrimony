-- Migration: Add security tables
-- Created: 2025-10-31

-- IP Blacklist table
CREATE TABLE IF NOT EXISTS `ip_blacklist` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `ip_address` VARCHAR(45) NOT NULL,
    `reason` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_ip` (`ip_address`),
    INDEX `idx_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Security logs table
CREATE TABLE IF NOT EXISTS `security_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `event_type` VARCHAR(50) NOT NULL COMMENT 'failed_login, suspicious_activity, blocked_ip, etc',
    `description` TEXT,
    `user_id` INT DEFAULT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event` (`event_type`, `created_at`),
    INDEX `idx_user` (`user_id`, `created_at`),
    INDEX `idx_ip` (`ip_address`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
