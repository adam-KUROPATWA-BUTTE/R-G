<?php
$page_title = 'Commande Confirmée - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Merci pour votre commande !</h1>
        <p class="success-message">Votre paiement a été traité avec succès.</p>
        
        <?php if (!empty($order)): ?>
            <div class="order-details">
                <h2>Détails de la commande</h2>
                <p><strong>Numéro de commande :</strong> #<?= $order['id'] ?></p>
                <p><strong>Total :</strong> <?= number_format($order['total'], 2, ',', ' ') ?> €</p>
                <p><strong>Statut :</strong> <?= htmlspecialchars($order['status']) ?></p>
            </div>
        <?php endif; ?>
        
        <p>Vous recevrez un email de confirmation avec les détails de votre commande.</p>
        
        <div class="action-buttons">
            <a href="<?= $base_path ?>/" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Retour à l'accueil
            </a>
            <a href="<?= $base_path ?>/compte" class="btn btn-secondary">
                <i class="fas fa-user"></i>
                Voir mes commandes
            </a>
        </div>
    </div>
</div>

<style>
.success-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
}

.success-card {
    background: white;
    padding: 3rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
    max-width: 600px;
    width: 100%;
}

.success-icon {
    font-size: 5rem;
    color: #22c55e;
    margin-bottom: 1.5rem;
    animation: bounce 1s ease;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.success-card h1 {
    color: #1e3a8a;
    margin-bottom: 1rem;
}

.success-message {
    font-size: 1.2rem;
    color: #6b7280;
    margin-bottom: 2rem;
}

.order-details {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 2rem 0;
    text-align: left;
}

.order-details h2 {
    color: #1e3a8a;
    margin-top: 0;
    margin-bottom: 1rem;
}

.order-details p {
    margin: 0.5rem 0;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
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

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
