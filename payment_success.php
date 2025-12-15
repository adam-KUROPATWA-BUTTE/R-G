<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/CartService.php';
require_once __DIR__ . '/config/config_stripe.php';
// ✅ REMPLACEZ PAR :
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/stripe/stripe-php/init.php')) {
    require_once __DIR__ . '/vendor/stripe/stripe-php/init.php';
} else {
    error_log("❌ Stripe library not found!");
    // Continuer sans Stripe (pour affichage basique)
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$sessionId = $_GET['session_id'] ?? '';

$order = null;
$session = null;

if ($sessionId) {
    try {
        // Récupérer la session Stripe
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        
        $orderId = $session->metadata->order_id ?? null;
        
        if ($orderId) {
    $pdo = db();
    
    // ✅ MISE À JOUR FORCÉE du statut si payé
    if ($session->payment_status === 'paid') {
        $stmt = $pdo->prepare("
            UPDATE commandes 
            SET statut = 'paid', date_paiement = NOW() 
            WHERE id = ? AND statut = 'pending'
        ");
        $stmt->execute([$orderId]);
        
        error_log("✅ Order #$orderId marked as PAID (from success page)");
        
        // ✅ VIDER LE PANIER
        cart_clear();
        error_log("🛒 Cart cleared after payment");
        
        // ✅ ENVOYER LES EMAILS
        require_once __DIR__ . '/src/EmailService.php';
        
        // Récupérer les détails complets de la commande
        $stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ?");
        $stmt->execute([$orderId]);
        $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Récupérer les articles
        $stmt = $pdo->prepare("SELECT * FROM commande_items WHERE commande_id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($orderDetails && $orderItems) {
            // Email au client
            send_order_confirmation_email($orderDetails, $orderItems);
            
            // Notification à l'admin
            send_admin_order_notification($orderDetails);
        }
    }
    
    // Récupérer la commande pour l'affichage
    $stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}
    } catch (Exception $e) {
        error_log("Error retrieving session: " . $e->getMessage());
    }
}

$pageTitle = 'Paiement réussi - R&G';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="css/style.css">
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
            animation: checkmark 0.8s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .success-title { 
            color: var(--primary-blue); 
            font-size: 2rem; 
            margin-bottom: 10px; 
        }
        .success-message { 
            color: #6b7280; 
            margin-bottom: 30px; 
            line-height: 1.6; 
        }
        .order-details {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            text-align: left;
        }
        .order-details h3 {
            margin-top: 0;
            color: var(--primary-blue);
        }
        .order-details p {
            margin: 10px 0;
        }
        .btn-home {
            display: inline-block;
            padding: 15px 40px;
            background: var(--primary-blue);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-home:hover {
            background: var(--light-blue);
            transform: translateY(-2px);
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1 class="success-title">Paiement réussi !</h1>
        <p class="success-message">
            Merci pour votre commande. Vous allez recevoir un email de confirmation sous peu.
        </p>

        <?php if ($order): ?>
    <div class="order-details">
        <h3>📦 Détails de votre commande</h3>
        <p><strong>Numéro :</strong> #<?= (int)$order['id'] ?></p>
        
        <?php 
        $fraisLivraison = (float)($order['frais_livraison'] ?? 4.99);
        $sousTotal = (float)$order['total'] - $fraisLivraison;
        ?>
        
        <p><strong>Sous-total produits :</strong> <?= number_format($sousTotal, 2, ',', ' ') ?> €</p>
        <p><strong>Frais de livraison :</strong> <?= number_format($fraisLivraison, 2, ',', ' ') ?> €</p>
        <p style="font-size: 1.2em; color: var(--primary-blue);">
            <strong>Total payé :</strong> <?= number_format((float)$order['total'], 2, ',', ' ') ?> €
        </p>
        
        <p><strong>Email :</strong> <?= htmlspecialchars($order['email_client']) ?></p>
        <p>
            <strong>Statut :</strong> 
            <?php if ($order['statut'] === 'paid'): ?>
                <span class="status-badge status-paid">✓ Payée</span>
            <?php else: ?>
                <span style="color: #f59e0b;">⏳ En cours de validation</span>
            <?php endif; ?>
        </p>
    </div>
<?php else: ?>
            <div style="background: #fee2e2; padding: 20px; border-radius: 10px; color: #991b1b;">
                ⚠️ Commande introuvable
            </div>
        <?php endif; ?>

        <p style="color: #6b7280; font-size: 0.9rem; margin-top: 20px;">
            📧 Un email de confirmation a été envoyé à votre adresse.<br>
            📦 Votre commande sera traitée dans les plus brefs délais.
        </p>

        <a href="/" class="btn-home">🏠 Retour à la boutique</a>
    </div>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>