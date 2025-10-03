<?php

// Autoloader for PSR-4

class Autoloader {
    public static function register() {
        spl_autoload_register(function (string $class) {
            // Remove 'App\' prefix if present
            $prefix = 'App\\';
            if (strpos($class, $prefix) === 0) {
                $class = substr($class, strlen($prefix));
            }
            
            $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}
