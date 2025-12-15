<?php
$config_file = __DIR__ . '/config.php';
if (file_exists($config_file)) {
  $config = require $config_file;
} else {
  // Fallback configuration for development when config.php doesn't exist
  $config = [
    'db' => [
      'dsn' => 'sqlite::memory:',
      'user' => '',
      'pass' => '',
      'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ],
    ],
  ];
}

function db(): PDO {
  static $pdo = null;
  global $config;
  if ($pdo === null) {
    $pdo = new PDO(
      $config['db']['dsn'],
      $config['db']['user'],
      $config['db']['pass'],
      $config['db']['options']
    );
    
    // If using in-memory SQLite, create tables and sample data
    if (strpos($config['db']['dsn'], 'sqlite::memory:') !== false) {
      init_test_database($pdo);
    }
  }
  return $pdo;
}

function init_test_database(PDO $pdo): void {
  // Create tables
  $pdo->exec("
    CREATE TABLE categories (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name VARCHAR(255) NOT NULL,
      slug VARCHAR(255) NOT NULL UNIQUE,
      description TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  ");
  
  $pdo->exec("
    CREATE TABLE products (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name VARCHAR(255) NOT NULL,
      description TEXT,
      price DECIMAL(10,2) NOT NULL,
      category_id INTEGER,
      image VARCHAR(255),
      stock_quantity INTEGER DEFAULT 0,
      status VARCHAR(20) DEFAULT 'active',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (category_id) REFERENCES categories(id)
    )
  ");
  
  $pdo->exec("
    CREATE TABLE users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      email VARCHAR(255) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      role VARCHAR(20) DEFAULT 'user',
      name VARCHAR(255),
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
  ");
  
  $pdo->exec("
    CREATE TABLE orders (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id INTEGER,
      total DECIMAL(10,2) NOT NULL,
      payment_method VARCHAR(50) DEFAULT 'card',
      status VARCHAR(20) DEFAULT 'pending',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id)
    )
  ");
  
  $pdo->exec("
    CREATE TABLE order_items (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      order_id INTEGER NOT NULL,
      product_id INTEGER,
      quantity INTEGER NOT NULL,
      price DECIMAL(10,2) NOT NULL,
      FOREIGN KEY (order_id) REFERENCES orders(id),
      FOREIGN KEY (product_id) REFERENCES products(id)
    )
  ");
  
  // Insert sample categories
  $pdo->exec("
    INSERT INTO categories (name, slug, description) VALUES
    ('Femme', 'femme', 'Vêtements et accessoires pour femme'),
    ('Homme', 'homme', 'Vêtements et accessoires pour homme'),
    ('Bijoux', 'bijoux', 'Bijoux et accessoires précieux')
  ");
  
  // Insert sample products
  $pdo->exec("
    INSERT INTO products (name, description, price, category_id, image, stock_quantity) VALUES
    ('Robe Élégante', 'Robe élégante pour soirée, coupe moderne et tissu de qualité', 299.99, 1, 'robe-elegante.jpg', 10),
    ('Chemisier Soie', 'Chemisier en soie naturelle, douceur et élégance', 199.99, 1, 'chemisier-soie.jpg', 15),
    ('Jupe Plissée', 'Jupe plissée tendance, parfaite pour un look sophistiqué', 149.99, 1, 'jupe-plissee.jpg', 12),
    ('Blazer Femme', 'Blazer féminin structuré, idéal pour le bureau', 249.99, 1, 'blazer-femme.jpg', 8),
    ('Costume Homme', 'Costume classique pour homme, coupe parfaite', 599.99, 2, 'costume-homme.jpg', 5),
    ('Chemise Business', 'Chemise business premium, coton égyptien', 129.99, 2, 'chemise-business.jpg', 20),
    ('Pantalon Chino', 'Pantalon chino en coton stretch, confort optimal', 89.99, 2, 'pantalon-chino.jpg', 18),
    ('Veste Blazer', 'Blazer homme en laine mérinos, élégance assurée', 349.99, 2, 'veste-blazer.jpg', 7),
    ('Collier Or', 'Collier en or 18 carats, pièce d''exception', 899.99, 3, 'collier-or.jpg', 3),
    ('Boucles d''Oreilles', 'Boucles d''oreilles en diamant, éclat unique', 1599.99, 3, 'boucles-oreilles.jpg', 4),
    ('Montre Luxe', 'Montre de luxe suisse, précision et style', 1299.99, 3, 'montre-luxe.jpg', 2),
    ('Bracelet Argent', 'Bracelet en argent massif, finition artisanale', 299.99, 3, 'bracelet-argent.jpg', 6)
  ");
  
  // Insert admin user (password: admin123)
  $pdo->exec("
    INSERT INTO users (email, password_hash, role, name) VALUES
    ('admin@rg-boutique.fr', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrateur R&G')
  ");
}