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
        $orderService->markPaid((int)$orderId, 'rev_' . time());
        header('Location: /checkout_success.php?session_id=' . urlencode($sessionId));
        exit;
    } elseif ($action === 'cancel') {
        // Simuler une annulation
        header('Location: /checkout_cancel.php?order_id=' . urlencode($orderId));
        exit;
    }
}

$page_title = 'Paiement Revolut - R&G';
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
    <div class="revolut-container">
        <div class="revolut-content">
            <div class="revolut-header">
                <div class="logo">
                    <div class="revolut-logo">
                        <span class="logo-circle">R</span>
                        <span class="logo-text">Revolut Business</span>
                    </div>
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
                    <p>Ceci est une simulation de paiement Revolut Business. En production, cette page serait hébergée par Revolut.</p>
                </div>
                
                <div class="payment-methods">
                    <div class="method-card active">
                        <div class="method-header">
                            <i class="fas fa-credit-card"></i>
                            <span>Carte bancaire</span>
                        </div>
                        <div class="method-content">
                            <div class="form-group">
                                <label>Numéro de carte</label>
                                <input type="text" value="4000 0000 0000 0002" readonly class="card-input">
                                <small>Carte de test Revolut</small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Expiration</label>
                                    <input type="text" value="12/25" readonly class="card-input">
                                </div>
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input type="text" value="100" readonly class="card-input">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Nom du titulaire</label>
                                <input type="text" value="<?= h($order['customer_name'] ?? 'Test User') ?>" readonly class="card-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="method-card">
                        <div class="method-header">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Revolut Pay</span>
                        </div>
                        <div class="method-content">
                            <p>Payez instantanément avec votre app Revolut</p>
                            <button class="revolut-pay-btn" disabled>
                                <div class="revolut-logo-small">R</div>
                                Payer avec Revolut
                            </button>
                        </div>
                    </div>
                </div>
                
                <form method="post">
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
                    <div class="revolut-badge">
                        <span class="badge-circle">R</span>
                        <span>Powered by Revolut</span>
                    </div>
                </div>
                <div class="badge">
                    <i class="fas fa-globe"></i>
                    <span>Accepté mondialement</span>
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
            background: linear-gradient(135deg, #00d4ff 0%, #0066ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .revolut-container {
            max-width: 500px;
            width: 100%;
        }
        
        .revolut-content {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .revolut-header {
            background: linear-gradient(135deg, #00d4ff 0%, #0066ff 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .revolut-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .logo-circle {
            width: 32px;
            height: 32px;
            background: white;
            color: #0066ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.2rem;
        }
        
        .logo-text {
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
            color: #1a1a1a;
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
            color: #1a1a1a;
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-weight: 600;
            color: #1a1a1a;
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
            color: #1a1a1a;
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
        
        .payment-methods {
            margin-bottom: 2rem;
        }
        
        .method-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.2s;
        }
        
        .method-card.active {
            border-color: #00d4ff;
        }
        
        .method-header {
            background: #f8fafc;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
        }
        
        .method-card.active .method-header {
            background: #eff6ff;
            color: #0066ff;
        }
        
        .method-content {
            padding: 1rem;
            display: none;
        }
        
        .method-card.active .method-content {
            display: block;
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
            color: #1a1a1a;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        .card-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
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
        
        .revolut-pay-btn {
            width: 100%;
            background: linear-gradient(135deg, #00d4ff 0%, #0066ff 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }
        
        .revolut-pay-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .revolut-logo-small {
            width: 24px;
            height: 24px;
            background: white;
            color: #0066ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 0.875rem;
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
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-pay {
            background: linear-gradient(135deg, #00d4ff 0%, #0066ff 100%);
            color: white;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 100, 255, 0.3);
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
            flex-wrap: wrap;
            gap: 1rem;
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
        
        .revolut-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .badge-circle {
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, #00d4ff 0%, #0066ff 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 0.75rem;
        }
        
        @media (max-width: 480px) {
            .revolut-container {
                margin: 0;
                padding: 0;
            }
            
            .revolut-content {
                border-radius: 0;
                min-height: 100vh;
            }
            
            .trust-badges {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
</body>
</html>