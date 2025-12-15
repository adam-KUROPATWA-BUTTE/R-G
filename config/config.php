<?php
/**
 * General Application Configuration
 */

return [
    'app' => [
        'name' => 'R&G - Boutique de Mode et Bijoux',
        'env' => 'development',
        'debug' => true,
        'url' => 'http://localhost:8000',
        'timezone' => 'Europe/Paris',
        'locale' => 'fr_FR',
        'session_name' => 'rg_session',
    ],
    
    'db' => require __DIR__ . '/database.php',
    
    'stripe' => [
        'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: '',
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: '',
        'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
    ],
    
    'revolut' => [
        'api_key' => getenv('REVOLUT_API_KEY') ?: '',
    ],
    
    'upload' => [
        'max_size' => 5242880, // 5MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'path' => __DIR__ . '/../public/uploads',
    ],
];
