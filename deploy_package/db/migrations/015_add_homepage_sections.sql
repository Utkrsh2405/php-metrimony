-- Migration: Add homepage sections for Bride & Groom and Search Profiles By
-- Date: 2025-11-16

-- Create homepage_sections table for managing dynamic homepage sections
CREATE TABLE IF NOT EXISTS `homepage_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) NOT NULL UNIQUE,
  `section_title` varchar(255) NOT NULL,
  `section_subtitle` varchar(255) DEFAULT NULL,
  `section_content` text,
  `section_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create homepage_search_categories table for "Search Profiles By" section
CREATE TABLE IF NOT EXISTS `homepage_search_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_type` enum('location','religion','community') NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `category_value` varchar(100) NOT NULL,
  `category_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_type` (`category_type`),
  KEY `category_order` (`category_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default sections
INSERT INTO `homepage_sections` (`section_key`, `section_title`, `section_subtitle`, `section_content`, `section_order`, `is_active`) VALUES
('hero', 'Shaadi Partner', 'Love is Looking for You', 'An ideal life partner and consequently they are looking for different things in a ideal match making solution.', 1, 1),
('about', 'JOIN US EXCLUSIVE MATCHMAKING SERVICE FOR', 'Shaadi Partner', 'An ideal life partner and consequently they are looking for different things in a ideal match making solution.', 2, 1),
('bride_groom', 'Bride & Groom', NULL, NULL, 3, 1),
('search_by', 'Search Profiles By', NULL, NULL, 4, 1);

-- Insert default search categories for Location
INSERT INTO `homepage_search_categories` (`category_type`, `category_name`, `category_value`, `category_order`, `is_active`) VALUES
('location', 'West Bengal', 'West Bengal', 1, 1),
('location', 'Madhya Pradesh', 'Madhya Pradesh', 2, 1),
('location', 'Gujarat', 'Gujarat', 3, 1),
('location', 'Haryana', 'Haryana', 4, 1),
('location', 'Delhi', 'Delhi', 5, 1),
('location', 'Rajput', 'Rajput', 6, 1),
('location', 'Maharashtra', 'Maharashtra', 7, 1),
('location', 'Kerala', 'Kerala', 8, 1),
('location', 'Jharkhand', 'Jharkhand', 9, 1),
('location', 'Karnataka', 'Karnataka', 10, 1);

-- Insert default search categories for Religion
INSERT INTO `homepage_search_categories` (`category_type`, `category_name`, `category_value`, `category_order`, `is_active`) VALUES
('religion', 'Hindu', 'Hindu', 1, 1),
('religion', 'Muslim', 'Muslim', 2, 1),
('religion', 'Christian', 'Christian', 3, 1),
('religion', 'Protestant', 'Protestant', 4, 1),
('religion', 'Muslim Sunni', 'Muslim Sunni', 5, 1),
('religion', 'Jain', 'Jain', 6, 1),
('religion', 'Jain - Digamber', 'Jain - Digamber', 7, 1),
('religion', 'Sikh', 'Sikh', 8, 1),
('religion', 'Orthodox', 'Orthodox', 9, 1),
('religion', 'Catholic', 'Catholic', 10, 1),
('religion', 'Christian', 'Christian', 11, 1);

-- Insert default search categories for Community
INSERT INTO `homepage_search_categories` (`category_type`, `category_name`, `category_value`, `category_order`, `is_active`) VALUES
('community', 'Hindu', 'Hindu', 1, 1),
('community', 'Maratha', 'Maratha', 2, 1),
('community', 'Bhumihar', 'Bhumihar', 3, 1),
('community', 'Muslim', 'Muslim', 4, 1),
('community', 'Kayastha', 'Kayastha', 5, 1),
('community', 'Malayalee', 'Malayalee', 6, 1),
('community', 'Rajput', 'Rajput', 7, 1),
('community', 'Aggarwal', 'Aggarwal', 8, 1);
