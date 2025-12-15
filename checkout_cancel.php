<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/Services/OrderService.php';

$orderId = $_GET['order_id'] ?? '';
$order = null;

if (!empty($orderId) && ctype_digit($orderId)) {
    try {
        $orderService = new OrderService();
        $order = $orderService->find((int)$orderId);
        
        if ($order && $order['status'] === 'pending') {
            // Marquer la commande comme annulée
            $orderService->updateStatus((int)$orderId, 'canceled');
        }
    } catch (Exception $e) {
        error_log('Erreur lors de l\'annulation de la commande: ' . $e->getMessage());
    }
}

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$page_title = 'Paiement annulé - R&G';
require_once __DIR__ . '/partials/header.php';
?>

<main class="cancel-container">
    <div class="cancel-content">
        <div class="icon-container">
            <i class="fas fa-times-circle"></i>
        </div>
        
        <h1>Paiement annulé</h1>
        <p>Votre paiement a été annulé. Aucun montant n'a été débité de votre compte.</p>
        
        <?php if ($order): ?>
            <div class="order-info">
                <h2>Informations de la commande</h2>
                <div class="info-box">
                    <div class="info-row">
                        <span class="label">Numéro de commande:</span>
                        <span class="value">#<?= (int)$order['id'] ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Statut:</span>
                        <span class="value status-canceled">Annulée</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Montant:</span>
                        <span class="value"><?= number_format($order['total'], 2, ',', ' ') ?> €</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="next-steps">
            <h3>Que pouvez-vous faire maintenant ?</h3>
            <div class="options">
                <div class="option">
                    <i class="fas fa-shopping-cart"></i>
                    <h4>Retourner au panier</h4>
                    <p>Vos articles sont toujours dans votre panier. Vous pouvez modifier votre commande et réessayer.</p>
                    <a href="/cart.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Voir le panier
                    </a>
                </div>
                
                <div class="option">
                    <i class="fas fa-store"></i>
                    <h4>Continuer vos achats</h4>
                    <p>Découvrez notre collection complète de vêtements et accessoires.</p>
                    <a href="/" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>
                
                <div class="option">
                    <i class="fas fa-headset"></i>
                    <h4>Besoin d'aide ?</h4>
                    <p>Notre équipe est là pour vous aider avec votre commande.</p>
                    <a href="/contact.php" class="btn btn-outline">
                        <i class="fas fa-envelope"></i> Nous contacter
                    </a>
                </div>
            </div>
        </div>
        
        <div class="reassurance">
            <div class="reassurance-item">
                <i class="fas fa-lock"></i>
                <span>Paiement 100% sécurisé</span>
            </div>
            <div class="reassurance-item">
                <i class="fas fa-undo"></i>
                <span>Retours gratuits sous 30 jours</span>
            </div>
            <div class="reassurance-item">
                <i class="fas fa-shipping-fast"></i>
                <span>Livraison rapide</span>
            </div>
        </div>
    </div>
</main>

<style>
.cancel-container {
    max-width: 800px;
    margin: 3rem auto;
    padding: 0 1rem;
}

.cancel-content {
    background: white;
    border-radius: 1rem;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.icon-container {
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

h1 {
    color: #1e3a8a;
    margin-bottom: 1rem;
    font-size: 2rem;
}

p {
    color: #6b7280;
    margin-bottom: 2rem;
    font-size: 1.125rem;
}

.order-info {
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 2rem;
    margin: 2rem 0;
}

.order-info h2 {
    color: #374151;
    margin-bottom: 1.5rem;
    font-size: 1.25rem;
}

.info-box {
    max-width: 300px;
    margin: 0 auto;
    text-align: left;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    color: #6b7280;
    font-weight: 500;
}

.info-row .value {
    color: #374151;
    font-weight: 600;
}

.status-canceled {
    color: #dc2626;
    background: #fee2e2;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
}

.next-steps {
    margin: 3rem 0;
}

.next-steps h3 {
    color: #374151;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.option {
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 2rem 1.5rem;
    text-align: center;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.option:hover {
    border-color: #e0e7ff;
    transform: translateY(-2px);
}

.option i {
    font-size: 2rem;
    color: #1d4ed8;
    margin-bottom: 1rem;
}

.option h4 {
    color: #374151;
    margin-bottom: 0.75rem;
    font-size: 1.125rem;
}

.option p {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
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
    font-size: 0.875rem;
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

.btn-outline {
    background: transparent;
    color: #1d4ed8;
    border: 2px solid #1d4ed8;
}

.btn-outline:hover {
    background: #1d4ed8;
    color: white;
}

.reassurance {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.reassurance-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.reassurance-item i {
    color: #10b981;
}

@media (max-width: 768px) {
    .cancel-content {
        padding: 2rem 1.5rem;
    }
    
    .options {
        grid-template-columns: 1fr;
    }
    
    .reassurance {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }
}
</style>

<?php require_once __DIR__ . '/partials/footer.php'; ?>