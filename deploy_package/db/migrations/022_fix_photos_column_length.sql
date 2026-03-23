-- Migration: Fix photos table column lengths
-- The pic1 column was only varchar(25) which is too short for generated filenames

-- Increase column lengths to accommodate longer filenames
ALTER TABLE photos MODIFY COLUMN pic1 VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE photos MODIFY COLUMN pic2 VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE photos MODIFY COLUMN pic3 VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE photos MODIFY COLUMN pic4 VARCHAR(100) NOT NULL DEFAULT '';
