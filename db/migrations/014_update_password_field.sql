-- Migration 014: Update password column for bcrypt hashes
-- Date: 2025-11-01

-- Increase password column size to support bcrypt (60 characters)
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Update admin password to bcrypt hash (password: admin123)
UPDATE users SET password = '$2y$10$JhGdMVBR4kjtPNyB4zP4tuO8P94Sm2VUetfaTl75MElKBkbELEbre' 
WHERE username = 'admin' AND userlevel = 1;
