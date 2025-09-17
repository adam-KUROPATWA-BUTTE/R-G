<?php
/**
 * Stripe Checkout Session Creator
 * Crée une session de paiement Stripe pour les articles du panier
 */

declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/csrf.php';
require_once __DIR__ . '/src/CartService.php';

// Vérification CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        http_response_code(403);
        exit(json_encode(['error' => 'Token CSRF invalide']));
    }
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
 * Initialise Stripe avec les clés d'environnement
 */
function initializeStripe(): void {
    // NOTE: Dans un vrai projet, vous installeriez Stripe via Composer:
    // composer require stripe/stripe-php
    
    // Pour ce stub, nous simulons l'initialisation
    // require_once 'vendor/autoload.php';
    
    $env = loadEnv();
    
    if (empty($env['STRIPE_SECRET_KEY'])) {
        throw new Exception('STRIPE_SECRET_KEY non configurée dans .env');
    }
    
    // \Stripe\Stripe::setApiKey($env['STRIPE_SECRET_KEY']);
    
    // Simulation pour le développement
    if (!defined('STRIPE_INITIALIZED')) {
        define('STRIPE_INITIALIZED', true);
        error_log('Stripe initialisé avec la clé: ' . substr($env['STRIPE_SECRET_KEY'], 0, 7) . '...');
    }
}

/**
 * Crée une session Stripe Checkout
 */
function createStripeCheckout(array $items, string $customerEmail, array $options = []): array {
    try {
        initializeStripe();
        $env = loadEnv();
        
        // Validation des articles
        if (empty($items)) {
            throw new InvalidArgumentException('Aucun article fourni');
        }
        
        // Calcul du total
        $totalAmount = 0;
        $lineItems = [];
        
        foreach ($items as $item) {
            if (!isset($item['name'], $item['price'], $item['quantity'])) {
                throw new InvalidArgumentException('Article invalide: nom, prix et quantité requis');
            }
            
            $price = (float)$item['price'];
            $quantity = (int)$item['quantity'];
            
            if ($price <= 0 || $quantity <= 0) {
                throw new InvalidArgumentException('Prix et quantité doivent être positifs');
            }
            
            $totalAmount += $price * $quantity;
            
            // Format pour Stripe (centimes)
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                        'description' => $item['description'] ?? '',
                        'images' => isset($item['image']) ? [$item['image']] : [],
                    ],
                    'unit_amount' => (int)($price * 100), // Convertir en centimes
                ],
                'quantity' => $quantity,
            ];
        }
        
        // Configuration de la session
        $sessionConfig = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => ($options['success_url'] ?? $env['APP_URL']) . '/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => ($options['cancel_url'] ?? $env['APP_URL']) . '/cart.php?cancelled=1',
            'customer_email' => $customerEmail,
            'metadata' => [
                'source' => 'r-g-boutique',
                'timestamp' => time(),
                'total_items' => count($items),
            ],
        ];
        
        // Ajout des options supplémentaires
        if (isset($options['shipping_address_collection'])) {
            $sessionConfig['shipping_address_collection'] = $options['shipping_address_collection'];
        }
        
        // SIMULATION: Dans un vrai projet, vous créeriez la session Stripe ici
        // $session = \Stripe\Checkout\Session::create($sessionConfig);
        
        // Pour le développement, nous simulons une réponse
        $simulatedSession = [
            'id' => 'cs_test_' . uniqid(),
            'url' => $env['APP_URL'] . '/mock_stripe_checkout.php?amount=' . urlencode($totalAmount) . '&email=' . urlencode($customerEmail),
            'amount_total' => (int)($totalAmount * 100),
            'currency' => 'eur',
            'customer_email' => $customerEmail,
            'status' => 'open',
        ];
        
        // Log de la tentative
        logPaymentAttempt(current_user()['id'] ?? 0, $totalAmount, 'session_created', 'Session Stripe créée: ' . $simulatedSession['id']);
        
        return [
            'success' => true,
            'session' => $simulatedSession,
            'checkout_url' => $simulatedSession['url'],
        ];
        
    } catch (Exception $e) {
        logPaymentAttempt(current_user()['id'] ?? 0, $totalAmount ?? 0, 'session_error', $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
        ];
    }
}

/**
 * Log des tentatives de paiement
 */
function logPaymentAttempt(int $userId, float $amount, string $status, string $details = ''): void {
    try {
        $pdo = db();
        
        // Créer la table si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS payment_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                amount DECIMAL(10,2),
                status VARCHAR(50),
                details TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $stmt = $pdo->prepare("
            INSERT INTO payment_logs (user_id, amount, status, details, created_at) 
            VALUES (?, ?, ?, ?, datetime('now'))
        ");
        $stmt->execute([$userId, $amount, $status, $details]);
        
    } catch (Exception $e) {
        error_log('Erreur lors du logging de paiement: ' . $e->getMessage());
    }
}

/**
 * Point d'entrée principal
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new InvalidArgumentException('Données JSON invalides');
        }
        
        $items = $input['items'] ?? [];
        $email = $input['email'] ?? (current_user()['email'] ?? '');
        $options = $input['options'] ?? [];
        
        if (empty($email)) {
            throw new InvalidArgumentException('Email client requis');
        }
        
        $result = createStripeCheckout($items, $email, $options);
        echo json_encode($result);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
        ]);
    }
    
    exit;
}

// Interface de test pour les développeurs
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['test'] ?? '') === '1') {
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
        <title>Test Stripe Checkout - R&G</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 2rem auto; padding: 1rem; }
            .form-group { margin: 1rem 0; }
            label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
            input, textarea { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #1D3557; color: white; padding: 1rem 2rem; border: none; border-radius: 4px; cursor: pointer; }
            button:hover { background: #2c5282; }
            .result { margin-top: 2rem; padding: 1rem; border-radius: 4px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        </style>
    </head>
    <body>
        <h1>Test Stripe Checkout</h1>
        <p><strong>Interface de test pour développeurs uniquement</strong></p>
        
        <form id="testForm">
            <div class="form-group">
                <label for="email">Email client:</label>
                <input type="email" id="email" value="<?= htmlspecialchars($current_user['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="items">Articles (JSON):</label>
                <textarea id="items" rows="10" placeholder="Exemple d'articles...">[
    {
        "name": "Robe élégante",
        "description": "Belle robe pour soirée",
        "price": 89.99,
        "quantity": 1,
        "image": "https://example.com/image.jpg"
    },
    {
        "name": "Collier doré",
        "description": "Bijou en or 18 carats",
        "price": 45.50,
        "quantity": 2
    }
]</textarea>
            </div>
            
            <button type="submit">Créer Session Checkout</button>
        </form>
        
        <div id="result"></div>
        
        <script>
            document.getElementById('testForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const itemsText = document.getElementById('items').value;
                const resultDiv = document.getElementById('result');
                
                try {
                    const items = JSON.parse(itemsText);
                    
                    const response = await fetch('create_checkout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': '<?= csrf_token() ?>'
                        },
                        body: JSON.stringify({
                            items: items,
                            email: email
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        resultDiv.innerHTML = `
                            <div class="result success">
                                <h3>✅ Session créée avec succès!</h3>
                                <p><strong>ID Session:</strong> ${result.session.id}</p>
                                <p><strong>Montant total:</strong> ${(result.session.amount_total / 100).toFixed(2)} €</p>
                                <p><strong>URL Checkout:</strong> <a href="${result.checkout_url}" target="_blank">${result.checkout_url}</a></p>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="result error">
                                <h3>❌ Erreur</h3>
                                <p>${result.error}</p>
                            </div>
                        `;
                    }
                    
                } catch (error) {
                    resultDiv.innerHTML = `
                        <div class="result error">
                            <h3>❌ Erreur</h3>
                            <p>Erreur JSON ou réseau: ${error.message}</p>
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

// Page normale - redirection vers le panier
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: cart.php');
    exit;
}
?>