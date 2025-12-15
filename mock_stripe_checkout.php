<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/Services/OrderService.php';

$sessionId = $_GET['session_id'] ?? '';
$orderId = $_GET['order_id'] ?? '';
$amount = $_GET['amount'] ?? 0;
$email = $_GET['email'] ?? '';

if (empty($sessionId) || empty($orderId)) {
    $_SESSION['error'] = 'Paramètres de session manquants';
    header('Location: /cart.php');
    exit;
}

$orderService = new OrderService();
$order = $orderService->find((int)$orderId);

if (!$order) {
    $_SESSION['error'] = 'Commande introuvable';
    header('Location: /cart.php');
    exit;
}

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

// Traitement du formulaire simulé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'pay') {
        // Simuler un paiement réussi
        $orderService->markPaid((int)$orderId, 'sim_' . time());
        header('Location: /checkout_success.php?session_id=' . urlencode($sessionId));
        exit;
    } elseif ($action === 'cancel') {
        // Simuler une annulation
        header('Location: /checkout_cancel.php?order_id=' . urlencode($orderId));
        exit;
    }
}

$page_title = 'Paiement Simulé - R&G';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($page_title) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="stripe-container">
        <div class="stripe-content">
            <div class="stripe-header">
                <div class="logo">
                    <i class="fab fa-stripe"></i>
                    <span>Stripe</span>
                </div>
                <div class="security">
                    <i class="fas fa-lock"></i>
                    <span>Paiement sécurisé</span>
                </div>
            </div>
            
            <div class="payment-info">
                <h1>Finaliser votre paiement</h1>
                <div class="merchant-info">
                    <strong>R&G Boutique</strong>
                    <span><?= h($email) ?></span>
                </div>
            </div>
            
            <div class="order-summary">
                <h2>Résumé de la commande</h2>
                <div class="summary-item">
                    <span>Commande #<?= (int)$order['id'] ?></span>
                    <span><?= number_format($order['total'], 2, ',', ' ') ?> €</span>
                </div>
                <?php if (!empty($order['items'])): ?>
                    <div class="items-list">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="item">
                                <span class="item-name">
                                    <?= h($item['product_name']) ?>
                                    <?php if ($item['size']): ?>
                                        <small>(<?= h($item['size']) ?>)</small>
                                    <?php endif; ?>
                                </span>
                                <span class="item-qty">×<?= (int)$item['quantity'] ?></span>
                                <span class="item-price"><?= number_format($item['total_price'], 2, ',', ' ') ?> €</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="total">
                    <strong>Total: <?= number_format($order['total'], 2, ',', ' ') ?> €</strong>
                </div>
            </div>
            
            <div class="payment-form">
                <div class="dev-notice">
                    <i class="fas fa-info-circle"></i>
                    <strong>Mode Développement</strong>
                    <p>Ceci est une simulation de paiement Stripe. En production, cette page serait hébergée par Stripe.</p>
                </div>
                
                <form method="post">
                    <div class="form-group">
                        <label>Numéro de carte</label>
                        <input type="text" value="4242 4242 4242 4242" readonly class="card-input">
                        <small>Carte de test Stripe</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiration</label>
                            <input type="text" value="12/25" readonly class="card-input">
                        </div>
                        <div class="form-group">
                            <label>CVC</label>
                            <input type="text" value="123" readonly class="card-input">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nom sur la carte</label>
                        <input type="text" value="<?= h($order['customer_name'] ?? 'Test User') ?>" readonly class="card-input">
                    </div>
                    
                    <div class="actions">
                        <button type="submit" name="action" value="pay" class="btn btn-pay">
                            <i class="fas fa-lock"></i>
                            Payer <?= number_format($order['total'], 2, ',', ' ') ?> €
                        </button>
                        <button type="submit" name="action" value="cancel" class="btn btn-cancel">
                            <i class="fas fa-times"></i>
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="trust-badges">
                <div class="badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Sécurisé SSL</span>
                </div>
                <div class="badge">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                </div>
            </div>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .stripe-container {
            max-width: 500px;
            width: 100%;
        }
        
        .stripe-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .stripe-header {
            background: #6772e5;
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .security {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .payment-info {
            padding: 2rem 2rem 1rem;
            text-align: center;
        }
        
        .payment-info h1 {
            color: #32325d;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .merchant-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            color: #6b7280;
        }
        
        .order-summary {
            padding: 0 2rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .order-summary h2 {
            color: #32325d;
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-weight: 600;
            color: #32325d;
        }
        
        .items-list {
            margin: 1rem 0;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-qty {
            margin: 0 1rem;
        }
        
        .total {
            text-align: right;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            color: #32325d;
            font-size: 1.125rem;
        }
        
        .payment-form {
            padding: 2rem;
        }
        
        .dev-notice {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #9a3412;
        }
        
        .dev-notice i {
            color: #ea580c;
        }
        
        .dev-notice strong {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .dev-notice p {
            font-size: 0.875rem;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #32325d;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .card-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            background: #f9fafb;
            color: #6b7280;
        }
        
        .form-group small {
            color: #6b7280;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        .actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-pay {
            background: #6772e5;
            color: white;
        }
        
        .btn-pay:hover {
            background: #5469d4;
        }
        
        .btn-cancel {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .btn-cancel:hover {
            background: #e5e7eb;
        }
        
        .trust-badges {
            background: #f8fafc;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .badge i {
            color: #9ca3af;
        }
    </style>
</body>
</html>