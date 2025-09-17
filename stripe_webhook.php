<?php
/**
 * Stripe Webhook Handler
 * Traite les événements Stripe (confirmations de paiement, échecs, etc.)
 */

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/functions.php';

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
    // NOTE: Dans un vrai projet avec Stripe installé:
    // return \Stripe\Webhook::constructEvent($payload, $signature, $secret);
    
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
                event_type VARCHAR(100),
                event_id VARCHAR(100),
                status VARCHAR(50),
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
function handleCheckoutCompleted(array $session): bool {
    try {
        $sessionId = $session['id'] ?? '';
        $customerEmail = $session['customer_email'] ?? '';
        $amountTotal = ($session['amount_total'] ?? 0) / 100; // Convertir des centimes
        
        if (empty($sessionId)) {
            throw new Exception('ID de session manquant');
        }
        
        // Vérifier si déjà traité
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE stripe_session_id = ?");
        $stmt->execute([$sessionId]);
        
        if ($stmt->fetch()) {
            logWebhookEvent('checkout.session.completed', $sessionId, 'already_processed', 'Commande déjà traitée');
            return true; // Déjà traité, mais ce n'est pas une erreur
        }
        
        // Créer la table des commandes si nécessaire
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                stripe_session_id VARCHAR(100) UNIQUE,
                customer_email VARCHAR(255),
                total_amount DECIMAL(10,2),
                status VARCHAR(50) DEFAULT 'paid',
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Créer la commande
        $stmt = $pdo->prepare("
            INSERT INTO orders (stripe_session_id, customer_email, total_amount, status, metadata) 
            VALUES (?, ?, ?, 'paid', ?)
        ");
        
        $metadata = json_encode([
            'payment_method' => 'stripe',
            'session_data' => $session,
            'processed_at' => date('c'),
        ]);
        
        $stmt->execute([$sessionId, $customerEmail, $amountTotal, $metadata]);
        $orderId = $pdo->lastInsertId();
        
        // Envoyer email de confirmation (simulation)
        sendOrderConfirmationEmail($customerEmail, $orderId, $amountTotal);
        
        logWebhookEvent('checkout.session.completed', $sessionId, 'success', "Commande $orderId créée");
        
        return true;
        
    } catch (Exception $e) {
        logWebhookEvent('checkout.session.completed', $sessionId ?? 'unknown', 'error', $e->getMessage());
        return false;
    }
}

/**
 * Traite l'événement payment_intent.succeeded
 */
function handlePaymentSucceeded(array $paymentIntent): bool {
    try {
        $intentId = $paymentIntent['id'] ?? '';
        $amount = ($paymentIntent['amount'] ?? 0) / 100;
        
        logWebhookEvent('payment_intent.succeeded', $intentId, 'success', "Paiement confirmé: $amount €");
        
        // Ici vous pourriez mettre à jour le statut de la commande, déclencher l'expédition, etc.
        
        return true;
        
    } catch (Exception $e) {
        logWebhookEvent('payment_intent.succeeded', $intentId ?? 'unknown', 'error', $e->getMessage());
        return false;
    }
}

/**
 * Traite l'événement payment_intent.payment_failed
 */
function handlePaymentFailed(array $paymentIntent): bool {
    try {
        $intentId = $paymentIntent['id'] ?? '';
        $amount = ($paymentIntent['amount'] ?? 0) / 100;
        $failureReason = $paymentIntent['last_payment_error']['message'] ?? 'Raison inconnue';
        
        logWebhookEvent('payment_intent.payment_failed', $intentId, 'failed', "Paiement échoué: $failureReason");
        
        // Ici vous pourriez notifier le client, annuler la commande, etc.
        
        return true;
        
    } catch (Exception $e) {
        logWebhookEvent('payment_intent.payment_failed', $intentId ?? 'unknown', 'error', $e->getMessage());
        return false;
    }
}

/**
 * Envoie un email de confirmation de commande (simulation)
 */
function sendOrderConfirmationEmail(string $email, int $orderId, float $amount): void {
    // Simulation d'envoi d'email
    // Dans un vrai projet, vous utiliseriez PHPMailer, SendGrid, etc.
    
    $subject = "Confirmation de commande #$orderId - R&G";
    $message = "
        Bonjour,
        
        Votre commande #$orderId a été confirmée.
        Montant: " . number_format($amount, 2) . " €
        
        Merci pour votre achat !
        
        L'équipe R&G
    ";
    
    // mail($email, $subject, $message); // Décommentez pour un vrai envoi
    
    // Log pour la démo
    error_log("Email envoyé à $email: $subject");
}

/**
 * Traite un événement webhook
 */
function processWebhookEvent(array $event): bool {
    $eventType = $event['type'] ?? '';
    $eventId = $event['id'] ?? uniqid();
    
    switch ($eventType) {
        case 'checkout.session.completed':
            return handleCheckoutCompleted($event['data']['object'] ?? []);
            
        case 'payment_intent.succeeded':
            return handlePaymentSucceeded($event['data']['object'] ?? []);
            
        case 'payment_intent.payment_failed':
            return handlePaymentFailed($event['data']['object'] ?? []);
            
        default:
            logWebhookEvent($eventType, $eventId, 'ignored', 'Type d\'événement non géré');
            return true; // Ignorer les événements non gérés n'est pas une erreur
    }
}

/**
 * Point d'entrée principal du webhook
 */
function handleWebhookRequest(): void {
    // Vérifier la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
    
    try {
        $env = loadEnv();
        $webhookSecret = $env['STRIPE_WEBHOOK_SECRET'] ?? '';
        
        if (empty($webhookSecret)) {
            throw new Exception('STRIPE_WEBHOOK_SECRET non configuré');
        }
        
        // Récupérer le payload et la signature
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        
        if (empty($payload)) {
            throw new Exception('Payload vide');
        }
        
        // Vérifier la signature (désactivé en mode développement)
        if (($env['APP_ENV'] ?? 'development') !== 'development') {
            if (!verifyStripeSignature($payload, $signature, $webhookSecret)) {
                throw new Exception('Signature invalide');
            }
        }
        
        // Décoder l'événement
        $event = json_decode($payload, true);
        if (!$event) {
            throw new Exception('JSON invalide');
        }
        
        // Traiter l'événement
        $success = processWebhookEvent($event);
        
        if ($success) {
            http_response_code(200);
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Erreur de traitement']);
        }
        
    } catch (Exception $e) {
        error_log('Erreur webhook Stripe: ' . $e->getMessage());
        logWebhookEvent('unknown', 'error', 'exception', $e->getMessage());
        
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Interface de test pour les développeurs
if (isset($_GET['test']) && $_GET['test'] === '1') {
    require_once __DIR__ . '/src/auth.php';
    $current_user = current_user();
    
    if (!$current_user || ($current_user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Accès réservé aux administrateurs');
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test Webhook Stripe - R&G</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 2rem auto; padding: 1rem; }
            .form-group { margin: 1rem 0; }
            label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
            select, textarea { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #1D3557; color: white; padding: 1rem 2rem; border: none; border-radius: 4px; cursor: pointer; }
            button:hover { background: #2c5282; }
            .result { margin-top: 2rem; padding: 1rem; border-radius: 4px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
            pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <h1>Test Webhook Stripe</h1>
        <p><strong>Interface de test pour développeurs uniquement</strong></p>
        
        <form id="testForm">
            <div class="form-group">
                <label for="eventType">Type d'événement:</label>
                <select id="eventType" onchange="updatePayload()">
                    <option value="checkout.session.completed">checkout.session.completed</option>
                    <option value="payment_intent.succeeded">payment_intent.succeeded</option>
                    <option value="payment_intent.payment_failed">payment_intent.payment_failed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="payload">Payload JSON:</label>
                <textarea id="payload" rows="15"></textarea>
            </div>
            
            <button type="submit">Simuler Webhook</button>
        </form>
        
        <div id="result"></div>
        
        <script>
            const payloadTemplates = {
                'checkout.session.completed': {
                    "id": "evt_test_webhook",
                    "type": "checkout.session.completed",
                    "data": {
                        "object": {
                            "id": "cs_test_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
                            "amount_total": 8999,
                            "currency": "eur",
                            "customer_email": "client@example.com",
                            "payment_status": "paid"
                        }
                    }
                },
                'payment_intent.succeeded': {
                    "id": "evt_test_webhook",
                    "type": "payment_intent.succeeded",
                    "data": {
                        "object": {
                            "id": "pi_test_1234567890",
                            "amount": 8999,
                            "currency": "eur",
                            "status": "succeeded"
                        }
                    }
                },
                'payment_intent.payment_failed': {
                    "id": "evt_test_webhook",
                    "type": "payment_intent.payment_failed",
                    "data": {
                        "object": {
                            "id": "pi_test_failed_1234567890",
                            "amount": 8999,
                            "currency": "eur",
                            "status": "failed",
                            "last_payment_error": {
                                "message": "Your card was declined."
                            }
                        }
                    }
                }
            };
            
            function updatePayload() {
                const eventType = document.getElementById('eventType').value;
                const payload = payloadTemplates[eventType];
                document.getElementById('payload').value = JSON.stringify(payload, null, 2);
            }
            
            // Initialiser avec le premier template
            updatePayload();
            
            document.getElementById('testForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const payload = document.getElementById('payload').value;
                const resultDiv = document.getElementById('result');
                
                try {
                    const response = await fetch('stripe_webhook.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: payload
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        resultDiv.innerHTML = `
                            <div class="result success">
                                <h3>✅ Webhook traité avec succès!</h3>
                                <pre>${JSON.stringify(result, null, 2)}</pre>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="result error">
                                <h3>❌ Erreur</h3>
                                <pre>${JSON.stringify(result, null, 2)}</pre>
                            </div>
                        `;
                    }
                    
                } catch (error) {
                    resultDiv.innerHTML = `
                        <div class="result error">
                            <h3>❌ Erreur</h3>
                            <p>Erreur réseau: ${error.message}</p>
                        </div>
                    `;
                }
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Traitement normal du webhook
handleWebhookRequest();
?>