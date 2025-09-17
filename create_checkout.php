<?php
/**
 * Revolut Business Checkout Session Creator
 * Crée une session de paiement Revolut Business pour les articles du panier
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
 * Créer une commande et initier le paiement Revolut Business
 */
function createCheckoutSession(): void {
    try {
        $env = loadEnv();
        $baseUrl = $env['APP_BASE_URL'] ?? $env['APP_URL'] ?? 'http://localhost:8000';
        
        // Récupérer le panier
        $cart = cart_get();
        $customer = prepareCustomerData();
        
        // Créer la commande en statut pending
        $orderService = new OrderService();
        $orderId = $orderService->createFromCart($cart, $customer, 'pending');
        
        // Vérifier si Revolut est configuré
        $revolutApiKey = $env['REVOLUT_API_KEY'] ?? '';
        
        if (empty($revolutApiKey)) {
            // Mode simulation pour le développement
            handleSimulatedCheckout($orderId, $baseUrl);
            return;
        }
        
        // Mode production - Créer vraie session Revolut Business
        handleRealRevolutCheckout($orderId, $cart, $customer, $env);
        
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
    $sessionId = 'rev_sim_' . $orderId . '_' . time();
    $orderService->setRevolutSession($orderId, $sessionId);
    
    // Rediriger vers une page de paiement simulé
    $checkoutUrl = $baseUrl . '/mock_revolut_checkout.php?' . http_build_query([
        'session_id' => $sessionId,
        'order_id' => $orderId,
        'amount' => $order['total'],
        'email' => $order['customer_email']
    ]);
    
    header('Location: ' . $checkoutUrl);
    exit;
}

/**
 * Gère le checkout Revolut Business réel
 */
function handleRealRevolutCheckout(int $orderId, array $cart, array $customer, array $env): void {
    $baseUrl = $env['APP_BASE_URL'] ?? $env['APP_URL'] ?? 'http://localhost:8000';
    $apiKey = $env['REVOLUT_API_KEY'];
    $environment = $env['REVOLUT_ENVIRONMENT'] ?? 'sandbox';
    
    // URL de l'API Revolut Business
    $apiUrl = $environment === 'live' 
        ? 'https://b2b.revolut.com/api/1.0'
        : 'https://sandbox-b2b.revolut.com/api/1.0';
    
    // Calculer le total en centimes
    $totalCents = 0;
    $lineItems = [];
    
    foreach ($cart['items'] as $item) {
        $itemTotal = (int)round($item['price'] * $item['qty'] * 100);
        $totalCents += $itemTotal;
        
        $lineItems[] = [
            'name' => $item['name'],
            'amount' => (int)round($item['price'] * 100),
            'quantity' => $item['qty'],
            'description' => $item['size'] ? "Taille: {$item['size']}" : ''
        ];
    }
    
    // Préparer la requête pour Revolut Business
    $paymentData = [
        'request_id' => 'order_' . $orderId . '_' . time(),
        'amount' => $totalCents,
        'currency' => 'EUR',
        'description' => 'Commande R&G #' . $orderId,
        'merchant_order_ext_ref' => 'RG-' . $orderId,
        'customer_info' => [
            'email' => $customer['email']
        ],
        'capture_mode' => 'automatic',
        'settlement_currency' => 'EUR',
        'callback_url' => $baseUrl . '/revolut_webhook.php',
        'redirect_url' => $baseUrl . '/checkout_success.php?session_id={PAYMENT_ID}',
        'cancel_url' => $baseUrl . '/checkout_cancel.php?order_id=' . $orderId
    ];
    
    // Effectuer la requête vers l'API Revolut Business
    $ch = curl_init($apiUrl . '/pay');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($paymentData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception('Erreur API Revolut: HTTP ' . $httpCode . ' - ' . $response);
    }
    
    $result = json_decode($response, true);
    if (!$result || !isset($result['payment_url'])) {
        throw new Exception('Réponse invalide de Revolut Business');
    }
    
    // Lier la session à la commande
    $orderService = new OrderService();
    $orderService->setRevolutSession($orderId, $result['id'] ?? 'rev_' . time());
    
    // Rediriger vers Revolut
    header('Location: ' . $result['payment_url']);
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