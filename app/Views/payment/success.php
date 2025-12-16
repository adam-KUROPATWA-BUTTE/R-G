<?php
/**
 * Payment Success View
 * Displayed after successful payment
 */
$pageTitle = 'Paiement réussi - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<style>
    .success-container {
        max-width: 600px;
        margin: 100px auto;
        padding: 40px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }
    .success-icon {
        font-size: 80px;
        color: #10b981;
        margin-bottom: 20px;
        animation: scaleIn 0.5s ease;
    }
    @keyframes scaleIn {
        from { transform: scale(0); }
        to { transform: scale(1); }
    }
    .success-container h1 {
        color: #10b981;
        margin-bottom: 20px;
        font-size: 2rem;
    }
    .success-container p {
        color: #6b7280;
        margin: 15px 0;
        font-size: 1.1rem;
    }
    .order-info {
        background: #f3f4f6;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
    }
    .btn {
        display: inline-block;
        padding: 12px 30px;
        margin: 10px;
        background: var(--primary-blue, #1e3a8a);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn:hover {
        background: var(--light-blue, #1e40af);
        transform: translateY(-2px);
    }
</style>

<div class="success-container">
    <div class="success-icon">✓</div>
    <h1>Paiement réussi !</h1>
    <p>Merci pour votre commande</p>
    
    <?php if (!empty($order)): ?>
        <div class="order-info">
            <p><strong>Commande #<?= htmlspecialchars($order['id']) ?></strong></p>
            <?php if (!empty($order['montant_total'])): ?>
                <p>Montant : <?= number_format((float)$order['montant_total'], 2, ',', ' ') ?> €</p>
            <?php endif; ?>
            <?php if (!empty($order['email'])): ?>
                <p>Un email de confirmation a été envoyé à : <?= htmlspecialchars($order['email']) ?></p>
            <?php endif; ?>
        </div>
    <?php elseif (!empty($sessionId)): ?>
        <div class="order-info">
            <p>Session : <?= htmlspecialchars($sessionId) ?></p>
            <p>Un email de confirmation vous a été envoyé.</p>
        </div>
    <?php endif; ?>
    
    <p>Votre commande est en cours de traitement.</p>
    <p>Vous allez recevoir un email de confirmation sous peu.</p>
    
    <div class="actions">
        <a href="<?= $base_path ?>/" class="btn">Retour à l'accueil</a>
        <?php if (!empty($order['id'])): ?>
            <a href="<?= $base_path ?>/compte" class="btn">Voir mes commandes</a>
        <?php endif; ?>
    </div>
</div>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
