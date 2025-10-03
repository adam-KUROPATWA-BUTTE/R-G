<div class="shop-container">
    <div class="shop-header">
        <h1><?= htmlspecialchars($pageTitle ?? 'Nos Produits') ?></h1>
        <?php if (!empty($categoryDescription)): ?>
            <p class="category-description"><?= htmlspecialchars($categoryDescription) ?></p>
        <?php endif; ?>
    </div>
    
    <div class="shop-content">
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="/product.php?id=<?= $product['id'] ?>" class="product-image">
                            <img src="/uploads/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;300&quot; height=&quot;400&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;300&quot; height=&quot;400&quot;/></svg>'">
                            <?php if (($product['stock_quantity'] ?? 0) == 0): ?>
                                <span class="stock-badge out-of-stock">Rupture de stock</span>
                            <?php endif; ?>
                        </a>
                        
                        <div class="product-info">
                            <h3>
                                <a href="/product.php?id=<?= $product['id'] ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h3>
                            
                            <?php if (!empty($product['description'])): ?>
                                <p class="product-description">
                                    <?= htmlspecialchars(substr($product['description'], 0, 100)) ?>
                                    <?= strlen($product['description']) > 100 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="product-footer">
                                <span class="price"><?= number_format($product['price'], 2) ?> €</span>
                                
                                <?php if (($product['stock_quantity'] ?? 0) > 0): ?>
                                    <button class="btn-add-cart" data-product-id="<?= $product['id'] ?>">
                                        <i class="fas fa-shopping-cart"></i> Ajouter
                                    </button>
                                <?php else: ?>
                                    <button class="btn-disabled" disabled>
                                        Indisponible
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>Aucun produit disponible pour le moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
