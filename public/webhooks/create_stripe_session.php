<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/CartService.php';
require_once __DIR__ . '/../config/config_stripe.php';

// ✅ Charger Stripe manuellement si pas de Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';
}

header('Content-Type: application/json; charset=utf-8');

try {
    error_log("=== STRIPE SESSION START ===");
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $input = file_get_contents('php://input');
    error_log("Input received: " . $input);
    
    $customerData = json_decode($input, true);

    if (!$customerData) {
        throw new Exception('Données JSON invalides');
    }

    // Récupérer le panier
    $pdo = db();
    $cartItems = cart_get_all();
    $sousTotal = cart_total();
    
    // ✅ Frais de livraison
    $fraisLivraison = 4.00;
    $total = $sousTotal + $fraisLivraison;

    if (empty($cartItems) || $sousTotal <= 0) {
        throw new Exception('Panier vide');
    }

    error_log("Sous-total: $sousTotal EUR");
    error_log("Frais livraison: $fraisLivraison EUR");
    error_log("Total: $total EUR");

    // Créer la commande en BDD
    $stmt = $pdo->prepare("
        INSERT INTO commandes 
        (total, statut, email_client, nom_client, prenom_client, telephone, adresse_livraison, frais_livraison, date_creation) 
        VALUES (?, 'pending', ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $adresseLivraison = sprintf(
        "%s\n%s %s\n%s",
        $customerData['adresse'] ?? '',
        $customerData['code_postal'] ?? '',
        $customerData['ville'] ?? '',
        $customerData['pays'] ?? 'France'
    );
    
    $stmt->execute([
        $total,
        $customerData['email'] ?? '',
        $customerData['nom'] ?? '',
        $customerData['prenom'] ?? '',
        $customerData['telephone'] ?? '',
        $adresseLivraison,
        $fraisLivraison
    ]);
    
    $orderId = (int)$pdo->lastInsertId();
    error_log("Order created: #$orderId");
    
    // Ajouter les items
    $stmtItem = $pdo->prepare("
        INSERT INTO commande_items 
        (commande_id, produit_id, nom_produit, quantite, prix_unitaire, taille) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cartItems as $item) {
        $stmtItem->execute([
            $orderId,
            $item['id'],
            $item['nom'],
            $item['quantite'],
            $item['prix'],
            $item['taille'] ?? null
        ]);
    }

    // ✅ Créer la session Stripe
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    $lineItems = [];
    
    // Ajouter les produits
    foreach ($cartItems as $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['nom'] . ($item['taille'] ? ' (Taille: ' . $item['taille'] . ')' : ''),
                ],
                'unit_amount' => (int)($item['prix'] * 100),
            ],
            'quantity' => $item['quantite'],
        ];
    }
    
    // ✅ Ajouter les frais de livraison
    $lineItems[] = [
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => '📦 Frais de livraison',
            ],
            'unit_amount' => (int)($fraisLivraison * 100),
        ],
        'quantity' => 1,
    ];

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => 'https://r-and-g.fr/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://r-and-g.fr/checkout.php?cancelled=1',
        'customer_email' => $customerData['email'] ?? '',
        'metadata' => [
            'order_id' => $orderId,
        ],
        'locale' => 'fr',
    ]);

    error_log("Stripe session created: " . $session->id);
    error_log("Checkout URL: " . $session->url);

    // Mettre à jour la commande
    $stmt = $pdo->prepare("UPDATE commandes SET stripe_session_id = ? WHERE id = ?");
    $stmt->execute([$session->id, $orderId]);

    error_log("=== STRIPE SESSION SUCCESS ===");

    // ✅ Renvoyer l'URL
    echo json_encode([
        'success' => true,
        'orderId' => $orderId,
        'sessionId' => $session->id,
        'url' => $session->url // ✅ Important !
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("=== STRIPE API ERROR: " . $e->getMessage() . " ===");
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur Stripe: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("=== ERROR: " . $e->getMessage() . " ===");
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}
?>