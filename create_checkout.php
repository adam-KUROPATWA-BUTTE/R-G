<?php
/**
 * Stripe Checkout Session Creator
 * Crée une session de paiement Stripe pour les articles du panier
 */

declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/csrf.php';
require_once __DIR__ . '/src/CartService.php';
require_once __DIR__ . '/src/Services/OrderService.php';

// Vérification CSRF pour les requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        http_response_code(403);
        $_SESSION['error'] = 'Token CSRF invalide';
        header('Location: /cart.php');
        exit;
    }
}

// Vérifier qu'il y a des articles dans le panier
$cart = cart_get();
if (empty($cart['items'])) {
    $_SESSION['error'] = 'Votre panier est vide';
    header('Location: /cart.php');
    exit;
}

/**
 * Charge les variables d'environnement depuis le fichier .env
 */
function loadEnv(): array {
    $env = [];
    $envFile = __DIR__ . '/.env';
    
    if (!file_exists($envFile)) {
        throw new Exception('Fichier .env non trouvé. Copiez .env.example vers .env et configurez vos clés.');
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Ignorer les commentaires
        
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
    
    return $env;
}

/**
 * Prépare les données client à partir de la session et des données POST
 */
function prepareCustomerData(): array {
    $user = current_user();
    
    return [
        'name' => $_POST['customer_name'] ?? $user['first_name'] ?? $user['name'] ?? '',
        'email' => $_POST['customer_email'] ?? $user['email'] ?? '',
        'address' => $_POST['customer_address'] ?? ''
    ];
}

/**
 * Créer une commande et initier le paiement Stripe
 */
function createCheckoutSession(): void {
    try {
        $env = loadEnv();
        $baseUrl = $env['APP_URL'] ?? 'http://localhost:8000';
        
        // Récupérer le panier
        $cart = cart_get();
        $customer = prepareCustomerData();
        
        // Récupérer la méthode de paiement sélectionnée
        $paymentMethod = $_POST['payment_method'] ?? 'stripe';
        
        // Créer la commande en statut pending
        $orderService = new OrderService();
        $orderId = $orderService->createFromCart($cart, $customer, 'pending');
        
        // Rediriger vers le bon processeur de paiement
        if ($paymentMethod === 'revolut') {
            handleRevolutCheckout($orderId, $cart, $customer, $env);
            return;
        }
        
        // Vérifier si Stripe est configuré
        $stripeKey = $env['STRIPE_SECRET_KEY'] ?? '';
        
        if (empty($stripeKey) || strpos($stripeKey, 'sk_') !== 0) {
            // Mode simulation pour le développement
            handleSimulatedCheckout($orderId, $baseUrl);
            return;
        }
        
        // Mode production - Créer vraie session Stripe
        if (class_exists('\Stripe\Stripe')) {
            handleRealStripeCheckout($orderId, $cart, $customer, $env);
        } else {
            // Stripe PHP SDK non installé, mode simulation
            handleSimulatedCheckout($orderId, $baseUrl);
        }
        
    } catch (Exception $e) {
        error_log('Erreur checkout: ' . $e->getMessage());
        $_SESSION['error'] = 'Erreur lors de la création de la commande: ' . $e->getMessage();
        header('Location: /cart.php');
        exit;
    }
}

/**
 * Gère le checkout simulé pour le développement
 */
function handleSimulatedCheckout(int $orderId, string $baseUrl): void {
    $orderService = new OrderService();
    $order = $orderService->find($orderId);
    
    if (!$order) {
        throw new Exception('Commande introuvable');
    }
    
    // Créer un ID de session simulé
    $sessionId = 'cs_sim_' . $orderId . '_' . time();
    $orderService->setStripeSession($orderId, $sessionId);
    
    // Rediriger vers une page de paiement simulé
    $checkoutUrl = $baseUrl . '/mock_stripe_checkout.php?' . http_build_query([
        'session_id' => $sessionId,
        'order_id' => $orderId,
        'amount' => $order['total'],
        'email' => $order['customer_email']
    ]);
    
    header('Location: ' . $checkoutUrl);
    exit;
}

/**
 * Gère le checkout Revolut Business
 */
function handleRevolutCheckout(int $orderId, array $cart, array $customer, array $env): void {
    $orderService = new OrderService();
    $order = $orderService->find($orderId);
    
    if (!$order) {
        throw new Exception('Commande introuvable');
    }
    
    $baseUrl = $env['APP_URL'] ?? 'http://localhost:8000';
    $revolutApiKey = $env['REVOLUT_API_KEY'] ?? '';
    
    // Si Revolut n'est pas configuré, utiliser le mode simulation
    if (empty($revolutApiKey) || strpos($revolutApiKey, 'pk_') !== 0) {
        // Mode simulation Revolut
        $sessionId = 'rev_sim_' . $orderId . '_' . time();
        $orderService->setStripeSession($orderId, $sessionId);
        
        $checkoutUrl = $baseUrl . '/mock_stripe_checkout.php?' . http_build_query([
            'session_id' => $sessionId,
            'order_id' => $orderId,
            'amount' => $order['total'],
            'email' => $order['customer_email'],
            'method' => 'revolut'
        ]);
        
        header('Location: ' . $checkoutUrl);
        exit;
    }
    
    // Mode production Revolut Business
    // Préparer les données pour l'API Revolut
    $orderData = [
        'amount' => (int)round($order['total'] * 100), // Centimes
        'currency' => 'EUR',
        'description' => 'Commande R&G #' . $orderId,
        'customer_email' => $customer['email'],
        'metadata' => [
            'order_id' => (string)$orderId,
            'source' => 'r-g-boutique'
        ]
    ];
    
    // Appel API Revolut Business
    $ch = curl_init('https://merchant.revolut.com/api/1.0/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $revolutApiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($orderData)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 && $httpCode !== 201) {
        throw new Exception('Erreur lors de la création de l\'ordre Revolut');
    }
    
    $revolutOrder = json_decode($response, true);
    
    if (!isset($revolutOrder['public_id'])) {
        throw new Exception('Réponse invalide de Revolut');
    }
    
    // Stocker l'ID de la session Revolut
    $orderService->setStripeSession($orderId, $revolutOrder['public_id']);
    
    // Rediriger vers Revolut checkout
    $checkoutUrl = 'https://checkout.revolut.com/pay/' . $revolutOrder['public_id'];
    header('Location: ' . $checkoutUrl);
    exit;
}

/**
 * Gère le checkout Stripe réel (nécessite Stripe PHP SDK)
 */
function handleRealStripeCheckout(int $orderId, array $cart, array $customer, array $env): void {
    \Stripe\Stripe::setApiKey($env['STRIPE_SECRET_KEY']);
    
    $baseUrl = $env['APP_URL'] ?? 'http://localhost:8000';
    
    // Préparer les articles pour Stripe
    $lineItems = [];
    foreach ($cart['items'] as $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $item['name'],
                    'description' => $item['size'] ? "Taille: {$item['size']}" : '',
                    'images' => !empty($item['image']) ? [$item['image']] : [],
                ],
                'unit_amount' => (int)round($item['price'] * 100), // Centimes
            ],
            'quantity' => $item['qty'],
        ];
    }
    
    // Créer la session Stripe
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => $baseUrl . '/checkout_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $baseUrl . '/checkout_cancel.php?order_id=' . $orderId,
        'customer_email' => $customer['email'],
        'metadata' => [
            'order_id' => (string)$orderId,
            'source' => 'r-g-boutique'
        ],
    ]);
    
    // Lier la session à la commande
    $orderService = new OrderService();
    $orderService->setStripeSession($orderId, $session->id);
    
    // Rediriger vers Stripe
    header('Location: ' . $session->url);
    exit;
}

// Point d'entrée principal
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Afficher le formulaire de checkout
    require_once __DIR__ . '/checkout_form.php';
} else {
    // Traiter la création de la session
    createCheckoutSession();
}