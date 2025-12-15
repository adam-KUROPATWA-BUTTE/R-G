-- SQL Migration for Product Sizes Feature
-- Execute this manually in your database:

ALTER TABLE products ADD COLUMN sizes VARCHAR(120) NULL AFTER stock_quantity;

-- This adds a new column 'sizes' to store comma-separated size codes (e.g., "XS,S,M,L,XL" or "TU")
-- The column is nullable, so existing products without sizes will work fine