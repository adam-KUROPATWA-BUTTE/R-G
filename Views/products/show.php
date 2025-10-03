<?php if (empty($product)): ?>
    <div class="container">
        <div class="error-message">
            <h2>Produit non trouvé</h2>
            <p>Le produit que vous recherchez n'existe pas ou n'est plus disponible.</p>
            <a href="/" class="btn-primary">Retour à l'accueil</a>
        </div>
    </div>
<?php else: ?>
    <div class="product-detail">
        <div class="product-gallery">
            <div class="main-image">
                <img src="/uploads/<?= htmlspecialchars($product['image']) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;600&quot; height=&quot;600&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;600&quot; height=&quot;600&quot;/></svg>'">
            </div>
            
            <?php if (!empty($product['images'])): ?>
                <div class="thumbnail-images">
                    <?php 
                    $images = is_string($product['images']) ? explode(',', $product['images']) : [];
                    foreach ($images as $image): 
                        $image = trim($image);
                        if ($image):
                    ?>
                        <img src="/uploads/<?= htmlspecialchars($image) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             onclick="document.querySelector('.main-image img').src = this.src">
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-details">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product-price">
                <span class="price"><?= number_format($product['price'], 2) ?> €</span>
            </div>
            
            <?php if (!empty($product['description'])): ?>
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($product['sizes'])): ?>
                <div class="product-sizes">
                    <h3>Tailles disponibles</h3>
                    <div class="size-options">
                        <?php 
                        $sizes = is_string($product['sizes']) ? explode(',', $product['sizes']) : [];
                        foreach ($sizes as $size): 
                            $size = trim($size);
                            if ($size):
                        ?>
                            <label class="size-option">
                                <input type="radio" name="size" value="<?= htmlspecialchars($size) ?>">
                                <span><?= htmlspecialchars($size) ?></span>
                            </label>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="product-stock">
                <?php if (($product['stock_quantity'] ?? 0) > 0): ?>
                    <span class="in-stock">
                        <i class="fas fa-check-circle"></i> En stock (<?= $product['stock_quantity'] ?> disponible<?= $product['stock_quantity'] > 1 ? 's' : '' ?>)
                    </span>
                <?php else: ?>
                    <span class="out-of-stock">
                        <i class="fas fa-times-circle"></i> Rupture de stock
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="product-actions">
                <?php if (($product['stock_quantity'] ?? 0) > 0): ?>
                    <div class="quantity-selector">
                        <label for="quantity">Quantité:</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" 
                               max="<?= $product['stock_quantity'] ?>">
                    </div>
                    
                    <button class="btn-add-cart-large" data-product-id="<?= $product['id'] ?>">
                        <i class="fas fa-shopping-cart"></i> Ajouter au panier
                    </button>
                <?php else: ?>
                    <button class="btn-disabled" disabled>
                        Produit indisponible
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
