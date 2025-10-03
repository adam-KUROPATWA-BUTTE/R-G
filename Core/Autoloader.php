<?php

// Autoloader for PSR-4

class Autoloader {
    public static function register() {
        spl_autoload_register(function (
            string $class
        ) {
            $file = __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}
