<?php
/**
 * R&G Application - Single Entry Point
 * All requests are routed through this file
 */

declare(strict_types=1);

// Bootstrap the application
require_once __DIR__ . '/../bootstrap/app.php';

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
