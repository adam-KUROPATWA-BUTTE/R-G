<?php
// Fichier inclus par create_checkout.php pour afficher le formulaire
$user = current_user();
$cart = cart_get();
$cartTotal = cart_total();

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$page_title = 'Finaliser la commande - R&G';
require_once __DIR__ . '/partials/header.php';
?>

<main class="checkout-container">
    <div class="checkout-content">
        <h1><i class="fas fa-credit-card"></i> Finaliser votre commande</h1>
        
        <!-- Résumé du panier -->
        <div class="checkout-section">
            <h2><i class="fas fa-shopping-cart"></i> Résumé de votre commande</h2>
            <div class="cart-summary">
                <?php if (empty($cart['items'])): ?>
                    <p>Votre panier est vide.</p>
                <?php else: ?>
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="cart-item">
                            <div class="item-info">
                                <h4><?= h($item['name']) ?></h4>
                                <?php if (!empty($item['size'])): ?>
                                    <span class="item-size">Taille: <?= h($item['size']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <span class="item-qty">Qty: <?= (int)$item['qty'] ?></span>
                                <span class="item-price"><?= number_format($item['price'], 2, ',', ' ') ?> €</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="cart-total">
                        <strong>Total: <?= number_format($cartTotal, 2, ',', ' ') ?> €</strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Formulaire de commande -->
        <div class="checkout-section">
            <h2><i class="fas fa-user"></i> Informations de livraison</h2>
            
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= h($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="/create_checkout.php" class="checkout-form">
                <?= csrf_input() ?>
                
                <div class="form-group">
                    <label for="customer_name">Nom complet *</label>
                    <input type="text" 
                           id="customer_name" 
                           name="customer_name" 
                           value="<?= h($user['first_name'] ?? $user['name'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="customer_email">Adresse email *</label>
                    <input type="email" 
                           id="customer_email" 
                           name="customer_email" 
                           value="<?= h($user['email'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="customer_address">Adresse de livraison</label>
                    <textarea id="customer_address" 
                              name="customer_address" 
                              rows="3" 
                              placeholder="Adresse, code postal, ville"></textarea>
                </div>
                
                <div class="form-actions">
                    <a href="/cart.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour au panier
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> 
                        Payer <?= number_format($cartTotal, 2, ',', ' ') ?> €
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.checkout-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.checkout-content h1 {
    color: #1e3a8a;
    margin-bottom: 2rem;
    text-align: center;
}

.checkout-section {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.checkout-section h2 {
    color: #374151;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
}

.cart-summary {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-info h4 {
    margin: 0 0 0.25rem 0;
    color: #374151;
}

.item-size {
    font-size: 0.875rem;
    color: #6b7280;
}

.item-details {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.item-qty {
    color: #6b7280;
    font-size: 0.875rem;
}

.item-price {
    font-weight: 600;
    color: #374151;
}

.cart-total {
    text-align: right;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
    font-size: 1.125rem;
    color: #1e3a8a;
}

.checkout-form {
    max-width: 500px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #1d4ed8;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    margin-top: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1rem;
}

.btn-primary {
    background: #1d4ed8;
    color: white;
}

.btn-primary:hover {
    background: #1e40af;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.alert-error {
    background: #fef2f2;
    border: 1px solid #f87171;
    color: #991b1b;
}

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .cart-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .item-details {
        align-self: flex-end;
    }
}
</style>

<?php require_once __DIR__ . '/partials/footer.php'; ?>