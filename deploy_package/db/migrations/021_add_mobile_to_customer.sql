-- Migration: Add mobile column to customer table
-- This migration adds a mobile phone number field to the customer table

-- Add mobile column if it doesn't exist
ALTER TABLE customer ADD COLUMN IF NOT EXISTS mobile VARCHAR(20) DEFAULT NULL AFTER email;

-- Add phone_code column for international codes
ALTER TABLE customer ADD COLUMN IF NOT EXISTS phone_code VARCHAR(5) DEFAULT '91' AFTER mobile;
