<section class="hero">
    <div class="hero-content">
        <h1>Bienvenue chez R&G</h1>
        <p>Découvrez notre collection exclusive de vêtements et bijoux</p>
        <a href="/vetements-femme.php" class="cta-button">Découvrir nos collections</a>
    </div>
</section>

<section class="categories-preview">
    <h2>Nos Collections</h2>
    <div class="categories-grid">
        <div class="category-card" data-category="femme">
            <img src="/assets/category-femme.jpg" alt="Vêtements Femme" 
                 onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;300&quot; height=&quot;400&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;300&quot; height=&quot;400&quot;/><text x=&quot;50%&quot; y=&quot;50%&quot; fill=&quot;%23999&quot; text-anchor=&quot;middle&quot; dy=&quot;.3em&quot;>Femme</text></svg>'">
            <div class="category-overlay">
                <h3>Vêtements Femme</h3>
                <p>Élégance et raffinement</p>
                <a href="/vetements-femme.php" class="btn-primary">Découvrir</a>
            </div>
        </div>
        
        <div class="category-card" data-category="homme">
            <img src="/assets/category-homme.jpg" alt="Vêtements Homme"
                 onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;300&quot; height=&quot;400&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;300&quot; height=&quot;400&quot;/><text x=&quot;50%&quot; y=&quot;50%&quot; fill=&quot;%23999&quot; text-anchor=&quot;middle&quot; dy=&quot;.3em&quot;>Homme</text></svg>'">
            <div class="category-overlay">
                <h3>Vêtements Homme</h3>
                <p>Classe et distinction</p>
                <a href="/vetements-homme.php" class="btn-primary">Découvrir</a>
            </div>
        </div>
        
        <div class="category-card" data-category="bijoux">
            <img src="/assets/category-bijoux.jpg" alt="Bijoux"
                 onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;300&quot; height=&quot;400&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;300&quot; height=&quot;400&quot;/><text x=&quot;50%&quot; y=&quot;50%&quot; fill=&quot;%23999&quot; text-anchor=&quot;middle&quot; dy=&quot;.3em&quot;>Bijoux</text></svg>'">
            <div class="category-overlay">
                <h3>Bijoux</h3>
                <p>Pièces précieuses</p>
                <a href="/bijoux.php" class="btn-primary">Découvrir</a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featuredProducts)): ?>
<section class="featured-products">
    <h2>Produits en vedette</h2>
    <div class="products-grid">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <a href="/product.php?id=<?= $product['id'] ?>">
                    <img src="/uploads/<?= htmlspecialchars($product['image']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;200&quot; height=&quot;200&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;200&quot; height=&quot;200&quot;/></svg>'">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="price"><?= number_format($product['price'], 2) ?> €</p>
                </a>
                <button class="btn-add-cart" data-product-id="<?= $product['id'] ?>">
                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
