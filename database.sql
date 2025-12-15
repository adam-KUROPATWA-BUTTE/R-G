-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_status (status)
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'card',
    status ENUM('pending', 'paid', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
);

-- Insert default categories
INSERT INTO categories (name, slug, description) VALUES
('Femme', 'femme', 'Vêtements et accessoires pour femme'),
('Homme', 'homme', 'Vêtements et accessoires pour homme'),
('Bijoux', 'bijoux', 'Bijoux et accessoires précieux');

-- Insert sample products
INSERT INTO products (name, description, price, category_id, image, stock_quantity) VALUES
('Robe Élégante', 'Robe élégante pour soirée', 299.99, 1, 'robe-elegante.jpg', 10),
('Costume Homme', 'Costume classique pour homme', 599.99, 2, 'costume-homme.jpg', 5),
('Collier Or', 'Collier en or 18 carats', 899.99, 3, 'collier-or.jpg', 3),
('Chemisier Soie', 'Chemisier en soie naturelle', 199.99, 1, 'chemisier-soie.jpg', 15),
('Montre Luxe', 'Montre de luxe pour homme', 1299.99, 2, 'montre-luxe.jpg', 2),
('Boucles d\'Oreilles', 'Boucles d\'oreilles en diamant', 1599.99, 3, 'boucles-oreilles.jpg', 4);

-- Create admin user (password: admin123)
INSERT INTO users (email, password_hash, role, name) VALUES
('admin@rg-boutique.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrateur R&G');

-- Page content table for CMS
CREATE TABLE IF NOT EXISTS page_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_name VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page_name (page_name)
);

-- Insert default info page content
INSERT INTO page_content (page_name, title, content) VALUES 
('info', 'À propos de R&G', 'Bienvenue dans l\'univers de R&G, où chaque pièce est pensée et créée avec soin.\n\nDiplômée en couture, R&G propose des vêtements entièrement faits sur mesure, pensés pour s\'adapter à votre morphologie, à vos envies et à votre style. Ici, pas de production en série ni de stock : chaque création est unique, à votre image.\n\nEn parallèle, elle imagine et assemble également une collection de bijoux en acier inoxydable, pour sublimer vos tenues avec des pièces durables, élégantes et accessibles.\n\nQue vous soyez à la recherche d\'un vêtement sur-mesure ou d\'un bijou coup de cœur, vous êtes au bon endroit. Merci de soutenir l\'artisanat local et la création indépendante ❤️');