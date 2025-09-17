-- Migration: Create orders and order_items tables
-- Created: 2025-01-17
-- Description: Add order management system with support for Stripe checkout

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('pending','paid','canceled','failed') NOT NULL DEFAULT 'pending',
  total_cents INT NOT NULL,
  customer_name VARCHAR(150) NULL,
  customer_email VARCHAR(150) NULL,
  customer_address TEXT NULL,
  stripe_session_id VARCHAR(191) NULL,
  payment_reference VARCHAR(191) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  size VARCHAR(50) NULL,
  unit_price_cents INT NOT NULL,
  quantity INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- For SQLite compatibility (if using SQLite instead of MySQL)
-- Note: SQLite doesn't support ENUM or ON UPDATE CURRENT_TIMESTAMP
-- Alternative for SQLite:
/*
CREATE TABLE IF NOT EXISTS orders (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL,
  status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','paid','canceled','failed')),
  total_cents INTEGER NOT NULL,
  customer_name TEXT,
  customer_email TEXT,
  customer_address TEXT,
  stripe_session_id TEXT,
  payment_reference TEXT
);

CREATE TABLE IF NOT EXISTS order_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  order_id INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  product_name TEXT NOT NULL,
  size TEXT,
  unit_price_cents INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
*/