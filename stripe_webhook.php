<?php
/**
 * Revolut Business Webhook Handler
 * Traite les événements Revolut Business (confirmations de paiement, échecs, etc.)
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
 * Vérifie la signature du webhook Revolut Business
 */
function verifyRevolutSignature(string $payload, string $signature, string $secret): bool {
    if (empty($signature) || empty($secret)) {
        return false;
    }
    
    // Revolut utilise HMAC-SHA256 pour signer les webhooks
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expectedSignature, $signature);
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
 * Traite l'événement de paiement terminé
 */
function handlePaymentCompleted(array $event): bool {
    try {
        $payment = $event['data'] ?? [];
        $paymentId = $payment['id'] ?? '';
        $orderRef = $payment['merchant_order_ext_ref'] ?? '';
        $state = $payment['state'] ?? '';
        
        if (empty($paymentId)) {
            throw new Exception('ID de paiement manquant');
        }
        
        // Extraire l'ID de commande depuis la référence
        $orderId = null;
        if (preg_match('/^RG-(\d+)$/', $orderRef, $matches)) {
            $orderId = (int)$matches[1];
        }
        
        if (!$orderId) {
            logWebhookEvent('payment_completed', $paymentId, 'order_not_found', 
                'Impossible d\'extraire l\'ID de commande depuis: ' . $orderRef);
            return false;
        }
        
        $orderService = new OrderService();
        $order = $orderService->find($orderId);
        
        if (!$order) {
            logWebhookEvent('payment_completed', $paymentId, 'order_not_found', 
                'Commande #' . $orderId . ' non trouvée');
            return false;
        }
        
        // Vérifier si déjà marquée comme payée
        if ($order['status'] === 'paid') {
            logWebhookEvent('payment_completed', $paymentId, 'already_processed', 
                'Commande #' . $orderId . ' déjà marquée comme payée');
            return true;
        }
        
        // Vérifier que le paiement est réussi
        if ($state === 'completed') {
            // Marquer la commande comme payée
            $orderService->markPaid($orderId, $paymentId);
            
            logWebhookEvent('payment_completed', $paymentId, 'success', 
                'Commande #' . $orderId . ' marquée comme payée');
            
            return true;
        } else {
            // Marquer comme échouée ou annulée selon l'état
            $newStatus = in_array($state, ['failed', 'declined']) ? 'failed' : 'canceled';
            $orderService->updateStatus($orderId, $newStatus);
            
            logWebhookEvent('payment_completed', $paymentId, 'payment_' . $state, 
                'Commande #' . $orderId . ' marquée comme ' . $newStatus);
            
            return true;
        }
        
    } catch (Exception $e) {
        $errorMsg = 'Erreur traitement payment_completed: ' . $e->getMessage();
        error_log($errorMsg);
        logWebhookEvent('payment_completed', $paymentId ?? 'unknown', 'error', $errorMsg);
        return false;
    }
}

/**
 * Traite l'événement de paiement en attente
 */
function handlePaymentPending(array $event): bool {
    try {
        $payment = $event['data'] ?? [];
        $paymentId = $payment['id'] ?? '';
        $orderRef = $payment['merchant_order_ext_ref'] ?? '';
        
        logWebhookEvent('payment_pending', $paymentId, 'processed', 
            'Paiement en attente pour la référence: ' . $orderRef);
        
        return true;
        
    } catch (Exception $e) {
        $errorMsg = 'Erreur traitement payment_pending: ' . $e->getMessage();
        error_log($errorMsg);
        logWebhookEvent('payment_pending', $paymentId ?? 'unknown', 'error', $errorMsg);
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
    $signature = $_SERVER['HTTP_X_REVOLUT_SIGNATURE'] ?? '';
    
    if (empty($payload)) {
        http_response_code(400);
        exit('Payload vide');
    }
    
    // Charger la configuration
    $env = loadEnv();
    $webhookSecret = $env['REVOLUT_WEBHOOK_SECRET'] ?? '';
    
    // Vérifier la signature (sauf en mode développement sans secret configuré)
    if (!empty($webhookSecret)) {
        if (!verifyRevolutSignature($payload, $signature, $webhookSecret)) {
            http_response_code(400);
            logWebhookEvent('signature_verification', 'unknown', 'failed', 'Signature invalide');
            exit('Signature invalide');
        }
    } else {
        // Mode développement - log un avertissement
        error_log('AVERTISSEMENT: Webhook traité sans vérification de signature (REVOLUT_WEBHOOK_SECRET non configuré)');
    }
    
    // Parser l'événement
    $event = json_decode($payload, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        exit('JSON invalide');
    }
    
    $eventType = $event['event'] ?? '';
    $eventId = $event['data']['id'] ?? '';
    
    // Traiter l'événement selon son type
    $processed = false;
    
    switch ($eventType) {
        case 'PaymentCompleted':
            $processed = handlePaymentCompleted($event);
            break;
            
        case 'PaymentPending':
            $processed = handlePaymentPending($event);
            break;
            
        case 'PaymentFailed':
        case 'PaymentDeclined':
            // Ces événements sont gérés dans handlePaymentCompleted
            $processed = handlePaymentCompleted($event);
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