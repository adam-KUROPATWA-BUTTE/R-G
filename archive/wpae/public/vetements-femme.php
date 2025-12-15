<?php
// Handle both direct access and inclusion
$auth_path = file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php';
$functions_path = file_exists('../src/functions.php') ? '../src/functions.php' : 'src/functions.php';
require_once $auth_path;
require_once $functions_path;

// Get products for femme category
$products = products_list_by_category('femme');

// Set up page variables for header
$page_title = 'Vêtements Femme - R&G';

// Include header
require_once 'partials/header.php';
?>

    <!-- Page Header -->
    <header class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-female"></i> Vêtements Femme</h1>
            <p>Élégance et sophistication pour la femme moderne</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Filters Section -->
        <section class="filters-section">
            <div class="filters-container">
                <h3>Filtrer par :</h3>
                <div class="filters">
                    <select id="categoryFilter">
                        <option value="">Toutes les catégories</option>
                        <option value="robes">Robes</option>
                        <option value="hauts">Hauts</option>
                        <option value="pantalons">Pantalons</option>
                        <option value="vestes">Vestes</option>
                    </select>
                    
                    <select id="priceFilter">
                        <option value="">Tous les prix</option>
                        <option value="0-150">0 - 150€</option>
                        <option value="150-300">150 - 300€</option>
                        <option value="300+">300€ et plus</option>
                    </select>
                    
                    <select id="stockFilter">
                        <option value="">Tous les articles</option>
                        <option value="inStock">En stock</option>
                        <option value="onDemand">Sur demande</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Products Grid -->
        <section class="products-section">
            <div class="products-container">
                <div class="products-grid" id="productsGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-product-id="<?= $product['id'] ?>">
                            <div class="product-image">
                                <?php if ($product['image']): ?>
                                    <img src="assets/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php else: ?>
                                    <div class="placeholder-image">
                                        <i class="fas fa-tshirt"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="product-overlay">
                                    <button class="quick-view-btn" onclick="showProductDetails('<?= $product['id'] ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                <div class="product-price"><?= number_format($product['price'], 2) ?>€</div>
                                <div class="product-status <?= $product['stock_quantity'] > 0 ? 'in-stock' : 'on-demand' ?>">
                                    <?= $product['stock_quantity'] > 0 ? 'En stock' : 'Sur demande' ?>
                                </div>
                                <div class="product-actions">
                                    <button class="add-to-cart-btn" onclick="addToCart({
                                        id: '<?= $product['id'] ?>',
                                        name: '<?= htmlspecialchars($product['name']) ?>',
                                        price: <?= $product['price'] ?>,
                                        image: '<?= htmlspecialchars($product['image']) ?>'
                                    })">
                                        <i class="fas fa-shopping-cart"></i>
                                        Ajouter au panier
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <i class="fas fa-tshirt"></i>
                            <p>Aucun produit disponible pour le moment dans cette catégorie.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <style>
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--gold) 100%);
            color: var(--white);
            padding: 4rem 2rem;
            text-align: center;
        }

        .header-content h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .header-content p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Filters Section */
        .filters-section {
            padding: 2rem;
            background-color: var(--light-gray);
        }

        .filters-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .filters-container h3 {
            color: var(--primary-blue);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .filters select {
            padding: 0.8rem;
            border: 2px solid var(--primary-blue);
            border-radius: 5px;
            background-color: var(--white);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filters select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 5px rgba(211, 170, 54, 0.3);
        }

        /* Products Section */
        .products-section {
            padding: 2rem;
            background-color: var(--white);
        }

        .products-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: var(--gold);
        }

        .product-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-image {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--light-gray) 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-blue);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(30, 58, 138, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .quick-view-btn {
            background: var(--gold);
            color: var(--primary-blue);
            border: none;
            padding: 1rem;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-view-btn:hover {
            background: var(--white);
            transform: scale(1.1);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-info h3 {
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .product-description {
            color: var(--dark-gray);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }

        .product-status {
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            display: inline-block;
        }

        .product-status.in-stock {
            background: #d4edda;
            color: #155724;
        }

        .product-status.on-demand {
            background: #fff3cd;
            color: #856404;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 0.8rem;
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .add-to-cart-btn:hover {
            background-color: var(--light-blue);
            transform: translateY(-1px);
        }

        .no-products {
            text-align: center;
            padding: 3rem;
            color: var(--dark-gray);
            grid-column: 1 / -1;
        }

        .no-products i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-blue);
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            
            .filters select {
                width: 100%;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content h1 {
                font-size: 2rem;
            }
        }
    </style>

    <script>
        function addToCart(product) {
            // Add to cart functionality
            console.log('Adding to cart:', product);
            // You can implement AJAX call to add to cart here
        }

        function showProductDetails(productId) {
            // Show product details modal
            console.log('Show details for product:', productId);
            // You can implement product details modal here
        }

        // Filter functionality can be added here if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any JavaScript functionality
        });
    </script>

<?php
// Include footer
require_once 'partials/footer.php';
?>