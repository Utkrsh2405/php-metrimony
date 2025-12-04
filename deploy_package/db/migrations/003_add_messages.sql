-- Migration: Add internal messaging system
-- Created: 2025-10-31

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `from_user_id` INT NOT NULL,
    `to_user_id` INT NOT NULL,
    `subject` VARCHAR(255),
    `message` TEXT NOT NULL,
    `is_read` BOOLEAN DEFAULT 0,
    `is_deleted_by_sender` BOOLEAN DEFAULT 0,
    `is_deleted_by_receiver` BOOLEAN DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_receiver` (`to_user_id`, `is_read`, `is_deleted_by_receiver`),
    INDEX `idx_sender` (`from_user_id`, `is_deleted_by_sender`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
