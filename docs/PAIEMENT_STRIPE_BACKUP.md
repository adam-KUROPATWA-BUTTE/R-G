# Guide d'Intégration des Paiements - R&G

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Configuration des Variables d'Environnement](#configuration-des-variables-denvironnement)
3. [Intégration Stripe Checkout](#intégration-stripe-checkout)
4. [Extension PayPal (Future)](#extension-paypal-future)
5. [Sécurité et Bonnes Pratiques](#sécurité-et-bonnes-pratiques)
6. [Tests et Validation](#tests-et-validation)

## Vue d'ensemble

Ce guide détaille l'implémentation d'un système de paiement pour la boutique R&G en utilisant principalement **Stripe Checkout** avec une extension future pour **PayPal**.

### Fonctionnalités supportées
- ✅ Paiement par carte bancaire (Visa, MasterCard, Amex)
- ✅ Checkout sécurisé avec Stripe
- ✅ Webhooks pour validation côté serveur
- 🔄 PayPal (planifié pour version future)
- ✅ Gestion des erreurs et confirmations
- ✅ Historique des commandes

## Configuration des Variables d'Environnement

Créez un fichier `.env` à la racine du projet (voir `.env.example` pour le template) :

```bash
# Stripe Configuration
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# PayPal Configuration (futur)
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox  # ou 'live' pour la production

# Application
APP_ENV=development  # ou 'production'
APP_URL=http://localhost:8000
```

### Obtenir les clés Stripe

1. Créez un compte sur [stripe.com](https://stripe.com)
2. Accédez au tableau de bord développeur
3. Récupérez vos clés publiques et secrètes de test
4. Configurez un webhook endpoint pour votre domaine

## Intégration Stripe Checkout

### 1. Création d'une session de checkout

Le fichier `create_checkout.php` gère la création des sessions Stripe :

```php
<?php
// Exemple d'utilisation
require_once 'create_checkout.php';

$items = [
    [
        'product_id' => 1,
        'name' => 'Robe élégante',
        'price' => 89.99,
        'quantity' => 1
    ],
    [
        'product_id' => 2,
        'name' => 'Collier doré',
        'price' => 45.50,
        'quantity' => 2
    ]
];

$checkout_url = createStripeCheckout($items, $customer_email);
header('Location: ' . $checkout_url);
```

### 2. Traitement des webhooks

Le fichier `stripe_webhook.php` traite les événements Stripe :

- `checkout.session.completed` : Confirmation de paiement
- `payment_intent.succeeded` : Paiement réussi
- `payment_intent.payment_failed` : Paiement échoué

### 3. Intégration dans le panier

Pour intégrer Stripe à votre panier existant :

```php
// Dans cart.php
if ($_POST['action'] === 'checkout') {
    $cart_items = get_cart_items();
    $total = calculate_cart_total($cart_items);
    
    if ($total > 0) {
        $checkout_url = createStripeCheckout($cart_items, $user_email);
        header('Location: ' . $checkout_url);
        exit;
    }
}
```

## Extension PayPal (Future)

### Configuration PayPal

Une fois PayPal implémenté, l'utilisateur pourra choisir entre Stripe et PayPal :

```html
<!-- Sélecteur de méthode de paiement -->
<div class="payment-methods">
    <label>
        <input type="radio" name="payment_method" value="stripe" checked>
        <span>Carte bancaire (Stripe)</span>
    </label>
    <label>
        <input type="radio" name="payment_method" value="paypal">
        <span>PayPal</span>
    </label>
</div>
```

### API PayPal

```javascript
// Intégration PayPal JavaScript SDK
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '89.99'
                }
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            // Traitement du paiement réussi
            window.location.href = '/success.php?order_id=' + details.id;
        });
    }
}).render('#paypal-button-container');
```

## Sécurité et Bonnes Pratiques

### 1. Validation côté serveur

```php
// Toujours valider les données côté serveur
function validatePaymentData($data) {
    $errors = [];
    
    if (empty($data['amount']) || $data['amount'] <= 0) {
        $errors[] = 'Montant invalide';
    }
    
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email invalide';
    }
    
    return $errors;
}
```

### 2. Protection CSRF

```php
// Utiliser les tokens CSRF existants
if (!csrf_validate()) {
    http_response_code(403);
    exit('Token CSRF invalide');
}
```

### 3. Logs de sécurité

```php
// Logger les tentatives de paiement
function logPaymentAttempt($user_id, $amount, $status, $details = '') {
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO payment_logs (user_id, amount, status, details, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $amount, $status, $details]);
}
```

### 4. Gestion des erreurs

```php
try {
    $stripe_session = createStripeCheckout($items, $email);
    return $stripe_session->url;
} catch (Stripe\Exception\CardException $e) {
    // Erreur de carte
    logPaymentAttempt($user_id, $amount, 'card_error', $e->getMessage());
    return ['error' => 'Erreur de carte bancaire'];
} catch (Stripe\Exception\ApiErrorException $e) {
    // Erreur API Stripe
    logPaymentAttempt($user_id, $amount, 'api_error', $e->getMessage());
    return ['error' => 'Erreur de service de paiement'];
} catch (Exception $e) {
    // Erreur générale
    logPaymentAttempt($user_id, $amount, 'general_error', $e->getMessage());
    return ['error' => 'Erreur interne'];
}
```

## Tests et Validation

### 1. Cartes de test Stripe

```bash
# Carte valide
4242424242424242

# Carte refusée
4000000000000002

# Carte nécessitant authentification 3D Secure
4000002760003184

# Carte expirée
4000000000000069
```

### 2. Tests d'intégration

```php
// Test de création de session
function testStripeCheckout() {
    $test_items = [
        ['name' => 'Test Product', 'price' => 10.00, 'quantity' => 1]
    ];
    
    $result = createStripeCheckout($test_items, 'test@example.com');
    assert(!empty($result), 'Session Stripe créée');
    echo "✅ Test checkout Stripe OK\n";
}

// Test de webhook
function testWebhookProcessing() {
    $test_payload = json_encode([
        'type' => 'checkout.session.completed',
        'data' => ['object' => ['id' => 'cs_test_123']]
    ]);
    
    $result = processStripeWebhook($test_payload, 'test_signature');
    assert($result === true, 'Webhook traité correctement');
    echo "✅ Test webhook OK\n";
}
```

### 3. Tests de charge

```bash
# Test avec Apache Bench
ab -n 100 -c 10 http://localhost:8000/create_checkout.php

# Test avec curl
for i in {1..10}; do
    curl -X POST http://localhost:8000/create_checkout.php \
         -d "amount=50.00&email=test$i@example.com"
done
```

## Migration et Déploiement

### 1. Checklist de déploiement

- [ ] Variables d'environnement configurées
- [ ] Webhooks Stripe configurés
- [ ] Base de données mise à jour
- [ ] Tests de paiement en mode test
- [ ] Logs et monitoring activés
- [ ] SSL/TLS configuré (obligatoire pour Stripe)

### 2. Commandes de déploiement

```bash
# Migration base de données
php migration_payments.php

# Vérification configuration
php check_payment_config.php

# Test intégration
php test_payment_integration.php
```

### 3. Monitoring

```php
// Vérification régulière de l'API Stripe
function checkStripeConnection() {
    try {
        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);
        $balance = $stripe->balance->retrieve();
        return ['status' => 'ok', 'balance' => $balance];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
```

## Support et Documentation

### Ressources externes
- [Documentation Stripe Checkout](https://stripe.com/docs/checkout)
- [Guide PayPal Integration](https://developer.paypal.com/docs/)
- [PCI DSS Compliance](https://www.pcisecuritystandards.org/)

### Contact Support R&G
- Email technique : dev@r-and-g.fr
- Documentation interne : `/docs/`
- Issues GitHub : Utilisez les issues pour signaler des bugs

---

*Document mis à jour le : 2024-09-17*
*Version : 1.0*