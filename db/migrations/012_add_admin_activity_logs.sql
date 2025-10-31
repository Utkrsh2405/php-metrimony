-- Migration: Add admin activity logging
-- Created: 2025-10-31

CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `admin_id` INT NOT NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'create, update, delete, approve, reject, etc',
    `entity_type` VARCHAR(50) NOT NULL COMMENT 'user, plan, payment, message, etc',
    `entity_id` INT DEFAULT NULL,
    `description` TEXT,
    `old_data` JSON DEFAULT NULL,
    `new_data` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_admin` (`admin_id`, `created_at`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
