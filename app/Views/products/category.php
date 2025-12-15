<?php
$page_title = ($title ?? 'Produits') . ' - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<!-- Page Header -->
<header class="page-header">
    <div class="header-content">
        <h1><i class="fas <?= $icon ?? 'fa-shopping-bag' ?>"></i> <?= htmlspecialchars($title ?? 'Produits') ?></h1>
        <p><?= htmlspecialchars($description ?? 'Découvrez notre sélection') ?></p>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <!-- Products Grid -->
    <section class="products-section">
        <div class="products-container">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <p>Aucun produit disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $productId = $product['id'];
                        $productName = htmlspecialchars($product['name'] ?? 'Produit');
                        $productPrice = number_format((float)($product['price'] ?? 0), 2, ',', ' ');
                        $productImage = $product['image'] ?? '';
                        
                        // Parse images if available
                        if (!empty($product['images'])) {
                            $images = json_decode($product['images'], true);
                            if (is_array($images) && !empty($images)) {
                                $productImage = $images[0];
                            }
                        }
                        
                        $inStock = (int)($product['stock_quantity'] ?? 0) > 0;
                        ?>
                        
                        <div class="product-card" data-product-id="<?= $productId ?>">
                            <a href="<?= $base_path ?>/product/<?= $productId ?>" class="product-link">
                                <div class="product-image-wrapper">
                                    <?php if (!empty($productImage)): ?>
                                        <img src="<?= htmlspecialchars($productImage) ?>" 
                                             alt="<?= $productName ?>" 
                                             class="product-image"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!$inStock): ?>
                                        <div class="out-of-stock-badge">Rupture de stock</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-name"><?= $productName ?></h3>
                                    <div class="product-price"><?= $productPrice ?> €</div>
                                    
                                    <?php if ($inStock): ?>
                                        <div class="product-stock in-stock">En stock</div>
                                    <?php else: ?>
                                        <div class="product-stock out-of-stock">Rupture de stock</div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<style>
.page-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #d4af37 100%);
    color: white;
    padding: 3rem 2rem;
    text-align: center;
}

.header-content h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.products-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.product-link {
    text-decoration: none;
    color: inherit;
}

.product-image-wrapper {
    position: relative;
    width: 100%;
    padding-top: 100%;
    overflow: hidden;
    background: #f8f9fa;
}

.product-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #ccc;
}

.out-of-stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #dc3545;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.product-info {
    padding: 1.5rem;
}

.product-name {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    color: #1e3a8a;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #d4af37;
    margin-bottom: 0.5rem;
}

.product-stock {
    font-size: 0.9rem;
    font-weight: 600;
}

.product-stock.in-stock {
    color: #28a745;
}

.product-stock.out-of-stock {
    color: #dc3545;
}

.no-products {
    text-align: center;
    padding: 4rem 2rem;
    font-size: 1.2rem;
    color: #6c757d;
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
