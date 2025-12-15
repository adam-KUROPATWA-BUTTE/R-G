<?php
/**
 * Stripe Webhook Handler
 * Traite les événements Stripe (confirmations de paiement, échecs, etc.)
 */

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/Services/OrderService.php';

// Désactiver l'output buffering pour les webhooks
if (ob_get_level()) {
    ob_end_clean();
}

/**
 * Charge les variables d'environnement
 */
function loadEnv(): array {
    $env = [];
    $envFile = __DIR__ . '/.env';
    
    if (!file_exists($envFile)) {
        http_response_code(500);
        exit('Configuration manquante');
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
    
    return $env;
}

/**
 * Vérifie la signature du webhook Stripe
 */
function verifyStripeSignature(string $payload, string $signature, string $secret): bool {
    // Dans un vrai projet avec Stripe installé:
    if (class_exists('\Stripe\Webhook')) {
        try {
            \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            return true;
        } catch (Exception $e) {
            error_log('Erreur signature Stripe: ' . $e->getMessage());
            return false;
        }
    }
    
    // Simulation pour le développement
    if (empty($signature) || empty($secret)) {
        return false;
    }
    
    // Validation basique pour la démo
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expectedSignature, substr($signature, 3)); // Enlever 'v1='
}

/**
 * Log des événements webhook
 */
function logWebhookEvent(string $eventType, string $eventId, string $status, string $details = ''): void {
    try {
        $pdo = db();
        
        // Créer la table si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS webhook_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_type TEXT,
                event_id TEXT,
                status TEXT,
                details TEXT,
                processed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO webhook_logs (event_type, event_id, status, details) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$eventType, $eventId, $status, $details]);
        
    } catch (Exception $e) {
        error_log('Erreur logging webhook: ' . $e->getMessage());
    }
}

/**
 * Traite l'événement checkout.session.completed
 */
function handleCheckoutCompleted(array $event): bool {
    try {
        $session = $event['data']['object'] ?? [];
        $sessionId = $session['id'] ?? '';
        $paymentIntentId = $session['payment_intent'] ?? '';
        
        if (empty($sessionId)) {
            throw new Exception('ID de session manquant');
        }
        
        $orderService = new OrderService();
        
        // Trouver la commande par session Stripe
        $order = $orderService->findByStripeSession($sessionId);
        
        if (!$order) {
            logWebhookEvent('checkout.session.completed', $sessionId, 'order_not_found', 
                'Aucune commande trouvée pour la session: ' . $sessionId);
            return false;
        }
        
        // Vérifier si déjà marquée comme payée
        if ($order['status'] === 'paid') {
            logWebhookEvent('checkout.session.completed', $sessionId, 'already_processed', 
                'Commande #' . $order['id'] . ' déjà marquée comme payée');
            return true;
        }
        
        // Marquer la commande comme payée
        $orderService->markPaid((int)$order['id'], $paymentIntentId);
        
        logWebhookEvent('checkout.session.completed', $sessionId, 'success', 
            'Commande #' . $order['id'] . ' marquée comme payée');
        
        return true;
        
    } catch (Exception $e) {
        $errorMsg = 'Erreur traitement checkout.session.completed: ' . $e->getMessage();
        error_log($errorMsg);
        logWebhookEvent('checkout.session.completed', $sessionId ?? 'unknown', 'error', $errorMsg);
        return false;
    }
}

/**
 * Traite l'événement payment_intent.payment_failed
 */
function handlePaymentFailed(array $event): bool {
    try {
        $paymentIntent = $event['data']['object'] ?? [];
        $paymentIntentId = $paymentIntent['id'] ?? '';
        $failureCode = $paymentIntent['last_payment_error']['code'] ?? '';
        $failureMessage = $paymentIntent['last_payment_error']['message'] ?? '';
        
        // Note: Pour lier un payment_intent à une commande, il faudrait stocker
        // le payment_intent_id dans la table orders lors de la création de la session
        
        logWebhookEvent('payment_intent.payment_failed', $paymentIntentId, 'processed', 
            "Échec paiement: $failureCode - $failureMessage");
        
        return true;
        
    } catch (Exception $e) {
        $errorMsg = 'Erreur traitement payment_intent.payment_failed: ' . $e->getMessage();
        error_log($errorMsg);
        logWebhookEvent('payment_intent.payment_failed', $paymentIntentId ?? 'unknown', 'error', $errorMsg);
        return false;
    }
}

// === POINT D'ENTRÉE ===

try {
    // Vérifier que c'est une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
    
    // Lire le payload
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    
    if (empty($payload)) {
        http_response_code(400);
        exit('Payload vide');
    }
    
    // Charger la configuration
    $env = loadEnv();
    $webhookSecret = $env['STRIPE_WEBHOOK_SECRET'] ?? '';
    
    // Vérifier la signature (sauf en mode développement sans secret configuré)
    if (!empty($webhookSecret)) {
        if (!verifyStripeSignature($payload, $signature, $webhookSecret)) {
            http_response_code(400);
            logWebhookEvent('signature_verification', 'unknown', 'failed', 'Signature invalide');
            exit('Signature invalide');
        }
    } else {
        // Mode développement - log un avertissement
        error_log('AVERTISSEMENT: Webhook traité sans vérification de signature (STRIPE_WEBHOOK_SECRET non configuré)');
    }
    
    // Parser l'événement
    $event = json_decode($payload, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        exit('JSON invalide');
    }
    
    $eventType = $event['type'] ?? '';
    $eventId = $event['id'] ?? '';
    
    // Traiter l'événement selon son type
    $processed = false;
    
    switch ($eventType) {
        case 'checkout.session.completed':
            $processed = handleCheckoutCompleted($event);
            break;
            
        case 'payment_intent.payment_failed':
            $processed = handlePaymentFailed($event);
            break;
            
        default:
            // Événement non géré - logger mais ne pas échouer
            logWebhookEvent($eventType, $eventId, 'ignored', 'Type d\'événement non géré');
            $processed = true;
            break;
    }
    
    if ($processed) {
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Échec du traitement']);
    }
    
} catch (Exception $e) {
    error_log('Erreur webhook: ' . $e->getMessage());
    logWebhookEvent('webhook_error', 'unknown', 'error', $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erreur interne']);
}