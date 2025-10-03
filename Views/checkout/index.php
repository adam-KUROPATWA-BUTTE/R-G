<div class="checkout-container">
    <h1>Finalisation de la commande</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Votre panier est vide</p>
            <a href="/" class="btn-primary">Retour à l'accueil</a>
        </div>
    <?php else: ?>
        <form id="checkoutForm" method="POST" action="/create_checkout.php" class="checkout-form">
            <?= csrf_field() ?>
            
            <div class="checkout-content">
                <div class="checkout-main">
                    <section class="checkout-section">
                        <h2>Informations de livraison</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Prénom *</label>
                                <input type="text" id="first_name" name="first_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name">Nom *</label>
                                <input type="text" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Téléphone *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Adresse *</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">Ville *</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Code postal *</label>
                                <input type="text" id="postal_code" name="postal_code" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Pays *</label>
                            <input type="text" id="country" name="country" value="France" required>
                        </div>
                    </section>
                    
                    <section class="checkout-section">
                        <h2>Mode de paiement</h2>
                        
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="card" checked>
                                <span>
                                    <i class="fas fa-credit-card"></i>
                                    Carte bancaire
                                </span>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="revolut">
                                <span>
                                    <i class="fab fa-rev"></i>
                                    Revolut
                                </span>
                            </label>
                        </div>
                    </section>
                </div>
                
                <div class="checkout-sidebar">
                    <div class="order-summary">
                        <h2>Récapitulatif</h2>
                        
                        <div class="summary-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-item">
                                    <span class="item-name">
                                        <?= htmlspecialchars($item['name']) ?>
                                        <small>x<?= $item['cart_quantity'] ?></small>
                                    </span>
                                    <span class="item-price">
                                        <?= number_format($item['subtotal'], 2) ?> €
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-totals">
                            <div class="summary-row">
                                <span>Sous-total</span>
                                <span><?= number_format($cartTotal, 2) ?> €</span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Livraison</span>
                                <span>Gratuite</span>
                            </div>
                            
                            <div class="summary-total">
                                <span>Total</span>
                                <span><?= number_format($cartTotal, 2) ?> €</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-checkout-submit">
                            Procéder au paiement
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>
