<?php
/**
 * Application Configuration
 * Central configuration for the R&G application
 */

return [
    // Application name
    'name' => 'R&G',
    
    // Application environment (production, development, testing)
    'env' => $_ENV['APP_ENV'] ?? 'production',
    
    // Debug mode
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    
    // Application URL
    'url' => $_ENV['APP_URL'] ?? 'https://www.rechercheet-grandeur.fr',
    
    // Session configuration
    'session' => [
        'name' => 'rg_session',
        'lifetime' => 120, // minutes
        'path' => '/',
        'domain' => null,
        'secure' => true,
        'http_only' => true,
        'same_site' => 'lax',
    ],
    
    // Timezone
    'timezone' => 'Europe/Paris',
    
    // Locale
    'locale' => 'fr',
    
    // Paths
    'paths' => [
        'uploads' => __DIR__ . '/../public/uploads',
        'logs' => __DIR__ . '/../storage/logs',
    ],
];
