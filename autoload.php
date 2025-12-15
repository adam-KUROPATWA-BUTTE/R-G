<?php
/**
 * PSR-4 Autoloader for R&G Application
 * Automatically loads classes from the app/ directory
 */

spl_autoload_register(function ($class) {
    // Base directory for the namespace
    $baseDir = __DIR__ . '/app/';
    
    // Convert namespace to file path
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});
