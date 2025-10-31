-- Migration: Add subscription plans and user subscriptions
-- Created: 2025-10-31

-- Create plans table
CREATE TABLE IF NOT EXISTS `plans` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `duration_days` INT NOT NULL,
    `max_contacts_view` INT DEFAULT 0 COMMENT '0 = unlimited',
    `max_messages_send` INT DEFAULT 0 COMMENT '0 = unlimited',
    `max_interests_express` INT DEFAULT 0 COMMENT '0 = unlimited',
    `can_chat` BOOLEAN DEFAULT 0,
    `is_active` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create user subscriptions table
CREATE TABLE IF NOT EXISTS `user_subscriptions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `status` ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE RESTRICT,
    INDEX `idx_user_status` (`user_id`, `status`),
    INDEX `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default plans
INSERT INTO `plans` (`name`, `description`, `price`, `duration_days`, `max_contacts_view`, `max_messages_send`, `max_interests_express`, `can_chat`) VALUES
('Free', 'Basic features for free users', 0.00, 365, 5, 10, 5, 0),
('Silver', 'Silver membership with more features', 29.99, 30, 50, 100, 25, 0),
('Gold', 'Gold membership with premium features', 49.99, 30, 200, 500, 100, 1),
('Platinum', 'Unlimited access to all features', 99.99, 30, 0, 0, 0, 1);

-- Assign free plan to existing users
INSERT INTO `user_subscriptions` (`user_id`, `plan_id`, `start_date`, `end_date`, `status`)
SELECT `id`, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 'active'
FROM `users`
WHERE NOT EXISTS (
    SELECT 1 FROM `user_subscriptions` WHERE `user_id` = `users`.`id`
);
