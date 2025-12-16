<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /payment/failure instead (handled by PaymentController@failure)
 */
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/payment/failure' . ($query ? '?' . $query : '');
header('Location: ' . $url);
exit;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement échoué - R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <style>
        .failure-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            text-align: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .failure-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }
        .btn:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <div class="failure-container">
        <div class="failure-icon">❌</div>
        <h1>Paiement échoué</h1>
        <p>Votre paiement n'a pas pu être traité.</p>
        <?php if ($orderId > 0): ?>
            <p>Commande #<?= $orderId ?></p>
        <?php endif; ?>
        <p>Aucun montant n'a été débité de votre compte.</p>
        
        <a href="/checkout.php" class="btn">Réessayer</a>
        <a href="/cart.php" class="btn" style="background: #6b7280;">Retour au panier</a>
    </div>
</body>
</html>