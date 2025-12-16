<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /checkout instead (handled by CheckoutController@index)
 */
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/checkout' . ($query ? '?' . $query : '');
header('Location: ' . $url);
exit;
$total = cart_total();

if (empty($items) || $total <= 0) {
    header('Location: cart.php');
    exit;
}

$pageTitle = 'Paiement - R&G';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <!-- ✅ SUPPRIMÉ : script Revolut -->
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
            color: var(--primary-blue);
            border-bottom: 2px solid var(--gold);
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
        }
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        .btn-pay {
            width: 100%;
            padding: 15px;
            background: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-pay:hover {
            background: var(--light-blue);
        }
        .btn-pay:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .alert {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="checkout-container">
        <?php if (!empty($cancelMessage)): ?>
            <div class="alert">
                ⚠️ <?= htmlspecialchars($cancelMessage) ?>
            </div>
        <?php endif; ?>

        <h1>Finaliser votre commande</h1>

        <div class="checkout-grid">
            <!-- Formulaire de livraison -->
            <div class="checkout-section">
                <h2>Informations de livraison</h2>
                <form id="checkoutForm">
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone *</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    <div class="form-group">
                        <label for="adresse">Adresse *</label>
                        <textarea id="adresse" name="adresse" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville *</label>
                        <input type="text" id="ville" name="ville" required>
                    </div>
                    <div class="form-group">
                        <label for="code_postal">Code postal *</label>
                        <input type="text" id="code_postal" name="code_postal" required>
                    </div>
                    <div class="form-group">
                        <label for="pays">Pays *</label>
                        <input type="text" id="pays" name="pays" value="France" required>
                    </div>
                </form>
            </div>

            <!-- Récapitulatif commande -->
            <div class="checkout-section">
                <h2>Récapitulatif de votre commande</h2>
                <div class="order-summary">
    <?php 
    $sousTotal = 0;
    foreach ($items as $item): 
        $itemTotal = (float)$item['prix'] * (int)$item['quantite'];
        $sousTotal += $itemTotal;
    ?>
        <div class="order-item">
            <div>
                <strong><?= htmlspecialchars($item['nom']) ?></strong>
                <?php if (!empty($item['taille'])): ?>
                    <br><small>Taille: <?= htmlspecialchars($item['taille']) ?></small>
                <?php endif; ?>
                <br><small>Quantité: <?= (int)$item['quantite'] ?></small>
            </div>
            <div>
                <?= number_format($itemTotal, 2, ',', ' ') ?> €
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- ✅ SOUS-TOTAL -->
    <div class="order-item" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">
        <div>Sous-total</div>
        <div><?= number_format($sousTotal, 2, ',', ' ') ?> €</div>
    </div>
    
    <!-- ✅ FRAIS DE LIVRAISON -->
    <div class="order-item">
        <div>📦 <strong>Frais de livraison</strong></div>
        <div><?= number_format(4.99, 2, ',', ' ') ?> €</div>
    </div>
    
    <!-- ✅ TOTAL AVEC LIVRAISON -->
    <div class="order-total">
        <span>Total à payer</span>
        <span><?= number_format($sousTotal + 4.99, 2, ',', ' ') ?> €</span>
    </div>
</div>

                <button type="button" id="payButton" class="btn-pay">
                    🔒 Payer avec Stripe
                </button>
                <p style="text-align: center; margin-top: 15px; color: #666; font-size: 14px;">
                    <i class="fas fa-lock"></i> Paiement 100% sécurisé
                </p>
            </div>
        </div>
    </div>

    <script>
console.log('🚀 Checkout script loaded');

document.getElementById('payButton').addEventListener('click', async function(e) {
    e.preventDefault();
    console.log('💳 Pay button clicked');
    
    const form = document.getElementById('checkoutForm');
    
    // Valider le formulaire
    if (!form.checkValidity()) {
        console.warn('⚠️ Form validation failed');
        form.reportValidity();
        return;
    }
    
    console.log('✅ Form is valid');

    // Désactiver le bouton
    this.disabled = true;
    this.textContent = 'Création de la session de paiement...';

    try {
        // Récupérer les données du formulaire
        const formData = new FormData(form);
        const customerData = Object.fromEntries(formData);
        
        console.log('📦 Customer data:', customerData);

        // ✅ Appeler l'API Stripe
        console.log('🌐 Calling API: /api/create_stripe_session.php');
        
        const response = await fetch('/api/create_stripe_session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(customerData)
        });

        console.log('📡 Response status:', response.status);
        
        const text = await response.text();
        console.log('📄 API Response (raw):', text);

        let result;
        try {
            result = JSON.parse(text);
            console.log('✅ Parsed result:', result);
        } catch (e) {
            console.error('❌ JSON Parse Error:', e);
            alert('Erreur serveur:\n\n' + text.substring(0, 500));
            this.disabled = false;
            this.textContent = '🔒 Payer avec Stripe';
            return;
        }

        if (!result.success) {
            console.error('❌ API returned error:', result.error);
            alert('Erreur: ' + result.error);
            this.disabled = false;
            this.textContent = '🔒 Payer avec Stripe';
            return;
        }

        if (!result.url) {
            console.error('❌ No URL in response:', result);
            alert('Erreur: Pas de lien de paiement reçu');
            this.disabled = false;
            this.textContent = '🔒 Payer avec Stripe';
            return;
        }

        console.log('✅ Got Stripe Checkout URL:', result.url);
        console.log('📝 OrderId:', result.orderId);
        console.log('🔑 SessionId:', result.sessionId);
        
        // Sauvegarder l'orderId
        sessionStorage.setItem('lastOrderId', result.orderId);
        
        // ✅ Rediriger vers Stripe Checkout
        console.log('🚀 Redirecting to Stripe...');
        window.location.href = result.url;
        
    } catch (error) {
        console.error('❌ Error:', error);
        alert('Erreur de connexion: ' + error.message);
        this.disabled = false;
        this.textContent = '🔒 Payer avec Stripe';
    }
});

console.log('✅ Event listener attached to Pay button');
</script>

    <?php include __DIR__ . '/partials/footer.php'; ?>
</body>
</html>