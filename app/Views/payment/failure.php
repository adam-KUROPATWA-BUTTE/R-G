<?php
/**
 * Payment Failure View
 * Displayed after failed payment
 */
$pageTitle = 'Paiement échoué - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<style>
    .failure-container {
        max-width: 600px;
        margin: 100px auto;
        padding: 40px;
        text-align: center;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    .failure-icon {
        font-size: 80px;
        color: #dc3545;
        margin-bottom: 20px;
        animation: shake 0.5s ease;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
    }
    .failure-container h1 {
        color: #dc3545;
        margin-bottom: 20px;
        font-size: 2rem;
    }
    .failure-container p {
        color: #6b7280;
        margin: 15px 0;
        font-size: 1.1rem;
    }
    .error-info {
        background: #fee;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        border-left: 4px solid #dc3545;
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
    .btn-secondary {
        background: #6b7280;
    }
    .btn-secondary:hover {
        background: #4b5563;
    }
</style>

<div class="failure-container">
    <div class="failure-icon">❌</div>
    <h1>Paiement échoué</h1>
    <p>Votre paiement n'a pas pu être traité.</p>
    
    <?php if (!empty($orderId)): ?>
        <div class="error-info">
            <p><strong>Commande #<?= htmlspecialchars($orderId) ?></strong></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="error-info">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>
    
    <p>Aucun montant n'a été débité de votre compte.</p>
    <p>Vous pouvez réessayer ou nous contacter si le problème persiste.</p>
    
    <div class="actions">
        <a href="<?= $base_path ?>/checkout" class="btn">Réessayer</a>
        <a href="<?= $base_path ?>/cart" class="btn btn-secondary">Retour au panier</a>
    </div>
</div>

<?php
require __DIR__ . '/../layouts/footer.php';
?>
