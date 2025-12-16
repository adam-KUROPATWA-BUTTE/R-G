<?php
/**
 * PSR-4 Autoloader for R&G Application
 * Maps namespace prefixes to base directories
 */

spl_autoload_register(function ($class) {
    // Namespace prefix to directory mapping
    $prefixes = [
        'Controllers\\' => __DIR__ . '/app/Controllers/',
        'Models\\'      => __DIR__ . '/app/Models/',
        'Services\\'    => __DIR__ . '/app/Services/',
        'Config\\'      => __DIR__ . '/app/Config/',
        'Helpers\\'     => __DIR__ . '/app/Helpers/',
    ];
    
    // Try each prefix
    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        
        // Check if the class uses this namespace prefix
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        // Get the relative class name
        $relativeClass = substr($class, $len);
        
        // Convert namespace separators to directory separators
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        // If the file exists, require it
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});
