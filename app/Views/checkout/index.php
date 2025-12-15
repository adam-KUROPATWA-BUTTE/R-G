<?php
$page_title = 'Paiement - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<div class="checkout-container">
    <h1>Paiement</h1>
    
    <?php if (!empty($cancelMessage)): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($cancelMessage) ?></div>
    <?php endif; ?>

    <div class="checkout-grid">
        <div class="checkout-section">
            <h2>Récapitulatif de la commande</h2>
            
            <?php if (!empty($items)): ?>
                <div class="order-summary">
                    <?php foreach ($items as $item): ?>
                        <div class="summary-item">
                            <div class="item-info">
                                <strong><?= htmlspecialchars($item['name'] ?? '') ?></strong>
                                <?php if (!empty($item['size'])): ?>
                                    <span class="item-size">Taille: <?= htmlspecialchars($item['size']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="item-quantity">x<?= (int)($item['qty'] ?? 1) ?></div>
                            <div class="item-price"><?= number_format((float)($item['price'] ?? 0) * (int)($item['qty'] ?? 1), 2, ',', ' ') ?> €</div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-total">
                        <strong>Total:</strong>
                        <strong><?= number_format($total, 2, ',', ' ') ?> €</strong>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="checkout-section">
            <h2>Procéder au paiement</h2>
            <p>Cliquez sur le bouton ci-dessous pour finaliser votre commande.</p>
            
            <form method="post" action="<?= $base_path ?>/create_checkout.php">
                <?php if (function_exists('csrf_input')) echo csrf_input(); ?>
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-lock"></i>
                    Payer <?= number_format($total, 2, ',', ' ') ?> €
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 20px;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.checkout-section {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.checkout-section h2 {
    margin-top: 0;
    color: #1e3a8a;
}

.order-summary {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
}

.summary-item {
    display: grid;
    grid-template-columns: 2fr auto auto;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f3f4f6;
}

.summary-item:last-child {
    border-bottom: none;
}

.item-info strong {
    display: block;
    margin-bottom: 5px;
}

.item-size {
    font-size: 0.85rem;
    color: #6b7280;
}

.item-price {
    font-weight: 600;
    color: #d4af37;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    padding-top: 20px;
    margin-top: 20px;
    border-top: 2px solid #1e3a8a;
    font-size: 1.3rem;
    color: #1e3a8a;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: #1e3a8a;
    color: white;
}

.btn-primary:hover {
    background: #1e40af;
    transform: translateY(-2px);
}

.btn-lg {
    padding: 1.2rem 2.5rem;
    font-size: 1.2rem;
}

.btn-block {
    width: 100%;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-warning {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    color: #92400e;
}

@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .summary-item {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
