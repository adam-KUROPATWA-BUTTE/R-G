-- Migration: Add images column to products table for multiple image support
-- Created: 2025-01-20
-- Description: Add support for multiple product images (refs #36, #37)
-- Note: Maintains backward compatibility with existing 'image' column

-- For MySQL/MariaDB
ALTER TABLE products ADD COLUMN images TEXT NULL COMMENT 'JSON array of image paths for gallery';

-- For SQLite (use this if using SQLite)
-- ALTER TABLE products ADD COLUMN images TEXT;

-- Usage notes:
-- - The 'images' column will store a JSON array of image paths
-- - Example: ["uploads/products/1/img1.jpg", "uploads/products/1/img2.jpg"]
-- - The existing 'image' column remains for backward compatibility
-- - If 'images' is empty/null, fallback to 'image' column
