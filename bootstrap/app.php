<?php
/**
 * R&G Application Bootstrap
 * Initializes the application with autoloading, configuration, and dependencies
 */

declare(strict_types=1);

// Start output buffering
ob_start();

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Error reporting
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Define base paths
define('APP_ROOT', dirname(__DIR__));
define('APP_PATH', APP_ROOT . '/app');
define('PUBLIC_PATH', APP_ROOT . '/public');
define('CONFIG_PATH', APP_ROOT . '/config');
define('VIEWS_PATH', APP_PATH . '/Views');

// Load PSR-4 Autoloader
require_once APP_ROOT . '/autoload.php';

// Load configuration files
require_once APP_ROOT . '/src/config.php';
require_once APP_ROOT . '/src/db.php';
require_once APP_ROOT . '/src/schema.php';

// Load legacy auth and csrf functions (for compatibility during migration)
require_once APP_ROOT . '/src/auth.php';
require_once APP_ROOT . '/src/csrf.php';

// Load legacy cart functions (for compatibility during migration)
// Note: CartService.php also loads src/functions.php
require_once APP_ROOT . '/src/CartService.php';

return true;
