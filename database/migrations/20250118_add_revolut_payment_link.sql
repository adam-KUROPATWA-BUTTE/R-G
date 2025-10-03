-- Migration: Add revolut_payment_link column to products table
-- Created: 2025-01-18
-- Description: Add support for Revolut Business payment links

-- For MySQL/MariaDB
ALTER TABLE products ADD COLUMN revolut_payment_link VARCHAR(500) NULL;

-- For SQLite (use this if using SQLite)
-- ALTER TABLE products ADD COLUMN revolut_payment_link TEXT;
