<?php
declare(strict_types=1);

/**
 * Démarre la session le plus tôt possible avec des paramètres sûrs et communs
 * à TOUTES les pages et handlers.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    // Compute base path for subdirectory deployments
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $basePath = $scriptDir === '/' ? '/' : rtrim($scriptDir, '/') . '/';

    // Important: définir les paramètres AVANT session_start()
    $params = [
        'lifetime' => 0,
        'path' => $basePath,          // Set session cookie path to subdirectory
        'domain' => '',               // laisse vide pour host courant
        'secure' => $isHttps,         // true si HTTPS
        'httponly' => true,
        'samesite' => 'Lax',          // ok pour formulaires cross-page
    ];
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($params);
    } else {
        // Compat anciennes versions: pas de samesite propre.
        session_set_cookie_params(
            $params['lifetime'],
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // Si vous avez déjà un nom de session global, décommentez et utilisez-le PARTOUT:
    // session_name('RGSESSID');

    session_start();
}

// Charge le module CSRF
require_once __DIR__ . '/csrf.php';