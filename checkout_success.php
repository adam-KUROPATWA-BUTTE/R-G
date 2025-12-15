<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/Services/OrderService.php';
require_once __DIR__ . '/src/CartService.php';

$sessionId = $_GET['session_id'] ?? '';
$order = null;
$error = '';

if (empty($sessionId)) {
    $error = 'Session de paiement non spécifiée';
} else {
    try {
        $orderService = new OrderService();
        $order = $orderService->findByStripeSession($sessionId);
        
        if (!$order) {
            $error = 'Commande non trouvée pour cette session';
        } else {
            // Vider le panier après paiement réussi
            cart_clear();
            $_SESSION['success'] = 'Votre commande a été traitée avec succès!';
        }
    } catch (Exception $e) {
        $error = 'Erreur lors de la récupération de la commande: ' . $e->getMessage();
    }
}

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$page_title = 'Paiement réussi - R&G';
require_once __DIR__ . '/partials/header.php';
?>

<main class="success-container">
    <div class="success-content">
        <?php if ($error): ?>
            <div class="error-section">
                <div class="icon-container error">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h1>Problème de paiement</h1>
                <p><?= h($error) ?></p>
                <div class="actions">
                    <a href="/cart.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Retour au panier
                    </a>
                    <a href="/" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="success-section">
                <div class="icon-container success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Paiement réussi !</h1>
                <p>Merci pour votre commande. Votre paiement a été traité avec succès.</p>
                
                <?php if ($order): ?>
                    <div class="order-summary">
                        <h2>Résumé de votre commande</h2>
                        <div class="order-details">
                            <div class="detail-row">
                                <span class="label">Numéro de commande:</span>
                                <span class="value">#<?= (int)$order['id'] ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Date:</span>
                                <span class="value"><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Total payé:</span>
                                <span class="value total"><?= number_format($order['total'], 2, ',', ' ') ?> €</span>
                            </div>
                            <?php if (!empty($order['customer_email'])): ?>
                                <div class="detail-row">
                                    <span class="label">Email de confirmation:</span>
                                    <span class="value"><?= h($order['customer_email']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="next-steps">
                    <h3>Que se passe-t-il maintenant ?</h3>
                    <ul>
                        <li><i class="fas fa-envelope"></i> Vous recevrez un email de confirmation</li>
                        <li><i class="fas fa-truck"></i> Votre commande sera préparée et expédiée sous 24-48h</li>
                        <li><i class="fas fa-phone"></i> Notre équipe vous contactera si des informations supplémentaires sont nécessaires</li>
                    </ul>
                </div>
                
                <div class="actions">
                    <a href="/" class="btn btn-primary">
                        <i class="fas fa-home"></i> Continuer vos achats
                    </a>
                    <?php if ($order && current_user()): ?>
                        <a href="/compte.php" class="btn btn-secondary">
                            <i class="fas fa-user"></i> Mon compte
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.success-container {
    max-width: 600px;
    margin: 3rem auto;
    padding: 0 1rem;
}

.success-content {
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
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.icon-container.success {
    background: #dcfce7;
    color: #166534;
}

.icon-container.error {
    background: #fee2e2;
    color: #991b1b;
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

.order-summary {
    background: #f8fafc;
    border-radius: 0.75rem;
    padding: 2rem;
    margin: 2rem 0;
    text-align: left;
}

.order-summary h2 {
    color: #374151;
    margin-bottom: 1.5rem;
    text-align: center;
    font-size: 1.25rem;
}

.order-details {
    max-width: 400px;
    margin: 0 auto;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-row .label {
    color: #6b7280;
    font-weight: 500;
}

.detail-row .value {
    color: #374151;
    font-weight: 600;
}

.detail-row .value.total {
    color: #1e3a8a;
    font-size: 1.125rem;
}

.next-steps {
    background: #f0f9ff;
    border-radius: 0.75rem;
    padding: 2rem;
    margin: 2rem 0;
    text-align: left;
}

.next-steps h3 {
    color: #0c4a6e;
    margin-bottom: 1rem;
    text-align: center;
}

.next-steps ul {
    list-style: none;
    padding: 0;
    max-width: 400px;
    margin: 0 auto;
}

.next-steps li {
    padding: 0.5rem 0;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.next-steps li i {
    color: #0ea5e9;
    width: 20px;
    text-align: center;
}

.actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
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
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover {
    background: #e5e7eb;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .success-content {
        padding: 2rem 1.5rem;
    }
    
    .actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 250px;
        justify-content: center;
    }
}
</style>

<?php require_once __DIR__ . '/partials/footer.php'; ?>