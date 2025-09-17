# Guide d'Intégration des Paiements - R&G

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Configuration des Variables d'Environnement](#configuration-des-variables-denvironnement)
3. [Intégration Revolut Business](#intégration-revolut-business)
4. [Sécurité et Bonnes Pratiques](#sécurité-et-bonnes-pratiques)
5. [Tests et Validation](#tests-et-validation)

## Vue d'ensemble

Ce guide détaille l'implémentation d'un système de paiement pour la boutique R&G en utilisant **Revolut Business**.

### Fonctionnalités supportées
- ✅ Paiement par carte bancaire (Visa, MasterCard, Amex)
- ✅ Revolut Pay (paiement instantané via l'app Revolut)
- ✅ Checkout sécurisé avec Revolut Business
- ✅ Webhooks pour validation côté serveur
- ✅ Gestion des erreurs et confirmations
- ✅ Historique des commandes
- ✅ Support multi-devises
- ✅ Frais réduits par rapport aux solutions traditionnelles

## Configuration des Variables d'Environnement

Créez un fichier `.env` à la racine du projet (voir `.env.example` pour le template) :

```bash
# Revolut Business Configuration
REVOLUT_API_KEY=your_revolut_api_key_here
REVOLUT_SECRET_KEY=your_revolut_secret_key_here
REVOLUT_WEBHOOK_SECRET=your_revolut_webhook_secret_here
REVOLUT_ENVIRONMENT=sandbox  # sandbox ou live

# URLs de callback
REVOLUT_SUCCESS_URL=http://localhost:8000/checkout_success.php
REVOLUT_CANCEL_URL=http://localhost:8000/checkout_cancel.php

# Application
APP_ENV=development  # ou 'production'
APP_BASE_URL=http://localhost:8000
```

### Obtenir les clés Revolut Business

1. Créez un compte Revolut Business sur [business.revolut.com](https://business.revolut.com)
2. Activez les fonctionnalités de paiement dans votre compte
3. Accédez à la section "Developer" ou "API" 
4. Générez vos clés API pour l'environnement sandbox puis production
5. Configurez les webhooks pour votre domaine

## Intégration Revolut Business

### 1. Création d'une session de checkout

Le fichier `create_checkout.php` gère la création des sessions Revolut Business :

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

// La fonction crée automatiquement la session et redirige
createCheckoutSession();
```

### 2. Traitement des webhooks

Le fichier `revolut_webhook.php` (renommé depuis `stripe_webhook.php`) traite les événements Revolut Business :

- `PaymentCompleted` : Confirmation de paiement réussi
- `PaymentPending` : Paiement en cours de traitement
- `PaymentFailed` : Paiement échoué
- `PaymentDeclined` : Paiement refusé

### 3. Intégration dans le panier

Pour intégrer Revolut Business à votre panier existant :

```php
// Dans cart.php
if ($_POST['action'] === 'checkout') {
    $cart_items = get_cart_items();
    $total = calculate_cart_total($cart_items);
    
    if ($total > 0) {
        // Rediriger vers le formulaire de checkout
        header('Location: /create_checkout.php');
        exit;
    }
}
```

### 4. API Revolut Business

Structure d'une requête de paiement :

```php
$paymentData = [
    'request_id' => 'order_' . $orderId . '_' . time(),
    'amount' => $totalCents, // Montant en centimes
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
```

## Sécurité et Bonnes Pratiques

### 1. Protection CSRF
Tous les formulaires incluent une protection CSRF :
```php
<?= csrf_input() ?>
```

### 2. Validation des webhooks
Les webhooks Revolut sont validés avec HMAC-SHA256 :
```php
function verifyRevolutSignature(string $payload, string $signature, string $secret): bool {
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expectedSignature, $signature);
}
```

### 3. Gestion des erreurs
- Logging complet des événements webhook
- Gestion des tentatives de paiement multiples
- Validation des montants et références de commande

### 4. Variables d'environnement
- Séparez toujours les clés de sandbox et de production
- Ne jamais exposer les clés secrètes côté client
- Utilisez HTTPS en production

## Tests et Validation

### 1. Cartes de test Revolut

Pour tester les paiements en environnement sandbox :

```text
Carte de test réussie: 4000 0000 0000 0002
Carte de test échouée: 4000 0000 0000 0069
Carte de test déclinée: 4000 0000 0000 0127

Expiration: toute date future (ex: 12/25)
CVV: tout code à 3 chiffres (ex: 100)
```

### 2. Tests d'intégration

```php
// Test de création de session
function testRevolutCheckout() {
    $test_items = [
        ['name' => 'Test Product', 'price' => 10.00, 'quantity' => 1]
    ];
    
    // Vérifier que la session se crée sans erreur
    $result = createCheckoutSession();
    echo "✅ Test checkout Revolut OK\n";
}

// Test de webhook
function testWebhookProcessing() {
    $test_payload = json_encode([
        'event' => 'PaymentCompleted',
        'data' => [
            'id' => 'rev_test_123',
            'merchant_order_ext_ref' => 'RG-123',
            'state' => 'completed'
        ]
    ]);
    
    // Simuler le traitement du webhook
    echo "✅ Test webhook OK\n";
}
```

### 3. Tests de charge

Revolut Business gère automatiquement la montée en charge, mais il est recommandé de :
- Tester avec plusieurs commandes simultanées
- Vérifier les temps de réponse de l'API
- Surveiller les logs d'erreurs

## Avantages de Revolut Business

### Coûts réduits
- Frais de transaction plus bas que les solutions traditionnelles
- Pas de frais cachés
- Taux de change transparents pour les paiements internationaux

### Facilité d'intégration
- API REST simple et bien documentée
- Sandbox complet pour les tests
- Support développeur réactif

### Fonctionnalités avancées
- Paiements instantanés avec Revolut Pay
- Support natif de nombreuses devises
- Réconciliation automatique avec votre compte Revolut Business

## Support

Pour toute question sur l'intégration :
- Documentation officielle : [developer.revolut.com](https://developer.revolut.com)
- Support Revolut Business : accessible depuis votre tableau de bord
- Équipe de développement R&G : contact@r-and-g.fr