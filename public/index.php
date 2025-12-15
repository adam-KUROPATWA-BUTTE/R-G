<?php
/**
 * R&G Application - Single Entry Point
 * All requests are routed through this file
 */

declare(strict_types=1);

// Start session and error reporting
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

ini_set('display_errors', '1');
error_reporting(E_ALL);

// Load dependencies
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/functions.php';

// Load autoloader
require_once __DIR__ . '/../autoload.php';

// Load Router
require_once __DIR__ . '/../app/Router.php';

// Compute base path for subdirectory deployments
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$basePath = $scriptDir === '/' ? '' : rtrim($scriptDir, '/');

// Create router instance
$router = new Router($basePath);

// Load routes
require __DIR__ . '/../routes/web.php';

// Dispatch the request
try {
    $router->dispatch();
} catch (Exception $e) {
    // Error handling
    if (ini_get('display_errors')) {
        echo "<h1>Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>500 - Internal Server Error</h1>";
    }
}
