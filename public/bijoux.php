<?php
// Handle both direct access (public/bijoux.php) and inclusion from root
$auth_path = file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php';
$functions_path = file_exists('../src/functions.php') ? '../src/functions.php' : 'src/functions.php';
require_once $auth_path;
require_once $functions_path;

// Set up page variables for header
$page_title = 'Bijoux - R&G';
$include_scripts = true;

// Get products from database
$products = products_list();

// Include header
require_once 'partials/header.php';
?>
    <!-- Main Content -->
    <main class="main-content">
        <section class="page-header">
            <div class="container">
                <h1><i class="fas fa-gem"></i> Bijoux</h1>
                <p>Découvrez notre collection exclusive de bijoux précieux</p>
            </div>
        </section>

        <section class="products-section">
            <div class="container">
                <?php if (empty($products)): ?>
                    <div class="no-products">
                        <i class="fas fa-gem"></i>
                        <p>Aucun produit disponible pour le moment.</p>
                        <p>Revenez bientôt pour découvrir nos nouveautés !</p>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" data-product-id="<?= $product['id'] ?>">
                                <div class="product-image">
                                    <?php if ($product['image_url']): ?>
                                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                    <?php else: ?>
                                        <i class="fas fa-gem"></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                    
                                    <?php if ($product['description']): ?>
                                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="product-details">
                                        <span class="product-price"><?= number_format($product['price'], 2) ?>€</span>
                                        
                                        <?php if ($product['category']): ?>
                                            <span class="product-category"><?= htmlspecialchars($product['category']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button class="btn btn-primary add-to-cart-btn" 
                                                    data-product-id="<?= $product['id'] ?>" 
                                                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                                    data-product-price="<?= $product['price'] ?>">
                                                <i class="fas fa-shopping-cart"></i>
                                                Ajouter au panier
                                            </button>
                                            <span class="stock-info"><?= $product['stock_quantity'] ?> en stock</span>
                                        <?php else: ?>
                                            <button class="btn btn-disabled" disabled>
                                                <i class="fas fa-times"></i>
                                                Rupture de stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <style>
        .page-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .products-section {
            padding: 60px 0;
        }
        
        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-products i {
            font-size: 4rem;
            color: #1e3a8a;
            margin-bottom: 20px;
            display: block;
        }
        
        .no-products p {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .product-image {
            height: 250px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-image i {
            font-size: 4rem;
            color: #1e3a8a;
            opacity: 0.3;
        }
        
        .product-info {
            padding: 25px;
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .product-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .product-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e3a8a;
        }
        
        .product-category {
            background: #e2e8f0;
            color: #1e3a8a;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .product-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: #1e3a8a;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-1px);
        }
        
        .btn-disabled {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
        }
        
        .stock-info {
            font-size: 0.85rem;
            color: #059669;
            text-align: center;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .product-info {
                padding: 20px;
            }
        }
    </style>

<?php
// Set additional scripts for this page
$additional_scripts = ['scripts/cart.js'];

// Include footer
require_once 'partials/footer.php';
?>