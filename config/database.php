<?php
/**
 * Database Configuration
 * This file is loaded by the Database model
 */

return [
    'type' => 'sqlite',
    'path' => __DIR__ . '/../database.db',
    'charset' => 'utf8mb4',
    
    // For MySQL/PostgreSQL (uncomment and configure if needed)
    /*
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => 'rg_boutique',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
    */
];
