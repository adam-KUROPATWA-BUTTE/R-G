<div class="cart-container">
    <h1>Panier</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Votre panier est vide</p>
            <a href="/" class="btn-primary">Continuer mes achats</a>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr data-product-id="<?= $item['id'] ?>">
                                <td class="product-info">
                                    <img src="/uploads/<?= htmlspecialchars($item['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;80&quot; height=&quot;80&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;80&quot; height=&quot;80&quot;/></svg>'">
                                    <div>
                                        <a href="/product.php?id=<?= $item['id'] ?>">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="price"><?= number_format($item['price'], 2) ?> €</td>
                                <td class="quantity">
                                    <input type="number" 
                                           class="quantity-input" 
                                           value="<?= $item['cart_quantity'] ?>" 
                                           min="1" 
                                           max="<?= $item['stock_quantity'] ?>"
                                           data-product-id="<?= $item['id'] ?>">
                                </td>
                                <td class="subtotal"><?= number_format($item['subtotal'], 2) ?> €</td>
                                <td class="actions">
                                    <button class="btn-remove" data-product-id="<?= $item['id'] ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cart-summary">
                <h2>Récapitulatif</h2>
                
                <div class="summary-row">
                    <span>Sous-total</span>
                    <span class="cart-subtotal"><?= number_format($cartTotal, 2) ?> €</span>
                </div>
                
                <div class="summary-row">
                    <span>Livraison</span>
                    <span>Calculée à l'étape suivante</span>
                </div>
                
                <div class="summary-total">
                    <span>Total</span>
                    <span class="cart-total"><?= number_format($cartTotal, 2) ?> €</span>
                </div>
                
                <div class="cart-actions">
                    <a href="/checkout_form.php" class="btn-checkout">
                        Passer la commande
                    </a>
                    <a href="/" class="btn-continue">
                        Continuer mes achats
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
