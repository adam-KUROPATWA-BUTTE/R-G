<?php
require_once __DIR__ . '/config/config_revolut.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Mode Revolut</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 50px;
            background: #f3f4f6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e3a8a;
            margin-top: 0;
        }
        .sandbox { 
            background: #d4edda; 
            color: #155724; 
            padding: 20px; 
            border-radius: 5px;
            border: 2px solid #28a745;
            margin: 20px 0;
        }
        .production { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 20px; 
            border-radius: 5px;
            border: 2px solid #dc3545;
            margin: 20px 0;
        }
        .info-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-list li {
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-list li:last-child {
            border-bottom: none;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            font-weight: 600;
        }
        .btn:hover {
            background: #1e40af;
        }
        .key-preview {
            font-family: monospace;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Configuration Revolut</h1>
        
        <?php if (REVOLUT_MODE === 'sandbox'): ?>
            <div class="sandbox">
                <h2>✅ MODE TEST (SANDBOX) ACTIVÉ</h2>
                <p><strong>Vous pouvez tester sans risque !</strong></p>
                <p>Les paiements sont simulés et aucun argent réel ne sera débité.</p>
            </div>
        <?php else: ?>
            <div class="production">
                <h2>⚠️ MODE PRODUCTION (RÉEL)</h2>
                <p><strong>ATTENTION : Les paiements seront RÉELS !</strong></p>
                <p>L'argent sera vraiment débité des cartes.</p>
            </div>
        <?php endif; ?>
        
        <ul class="info-list">
            <li>
                <strong>Mode :</strong> 
                <span class="key-preview"><?= REVOLUT_MODE ?></span>
            </li>
            <li>
                <strong>URL API :</strong> 
                <span class="key-preview"><?= REVOLUT_API_URL ?></span>
            </li>
            <li>
                <strong>Clé publique :</strong> 
                <span class="key-preview"><?= substr(REVOLUT_API_KEY_PUBLIC, 0, 20) ?>...</span>
            </li>
            <li>
                <strong>Clé privée :</strong> 
                <span class="key-preview"><?= substr(REVOLUT_API_KEY_PRIVATE, 0, 15) ?>...</span>
            </li>
            <?php if (defined('REVOLUT_WEBHOOK_URL')): ?>
            <li>
                <strong>URL Webhook :</strong> 
                <span class="key-preview" style="font-size: 0.8em;"><?= REVOLUT_WEBHOOK_URL ?></span>
            </li>
            <?php endif; ?>
        </ul>

        <h3>🧪 Cartes de test (mode sandbox uniquement) :</h3>
        <ul class="info-list">
            <li>
                <strong>✅ Paiement réussi :</strong><br>
                <span class="key-preview">4242 4242 4242 4242</span> - Exp: 12/25 - CVV: 123
            </li>
            <li>
                <strong>❌ Paiement refusé :</strong><br>
                <span class="key-preview">4000 0000 0000 0002</span> - Exp: 12/25 - CVV: 123
            </li>
        </ul>
        
        <a href="checkout.php" class="btn">→ Tester le checkout</a>
        <a href="/admin/orders.php" class="btn" style="background: #059669;">📊 Voir les commandes</a>
    </div>
</body>
</html>