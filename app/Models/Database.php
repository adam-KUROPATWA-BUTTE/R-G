<?php
namespace Models;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 * Handles PDO connection and configuration
 */
class Database
{
    private static ?PDO $instance = null;
    private static ?array $config = null;

    /**
     * Get PDO instance (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Connect to database
     */
    private static function connect(): void
    {
        $config = self::getConfig();
        
        try {
            if (isset($config['type']) && $config['type'] === 'sqlite') {
                $dsn = "sqlite:{$config['path']}";
                self::$instance = new PDO($dsn);
            } elseif (isset($config['host']) && $config['host'] === 'sqlite') {
                $dsn = "sqlite:{$config['name']}";
                self::$instance = new PDO($dsn);
            } else {
                $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset={$config['charset']}";
                self::$instance = new PDO($dsn, $config['user'], $config['pass']);
            }
            
            // Set PDO options
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new \RuntimeException("Could not connect to database");
        }
    }

    /**
     * Get database configuration
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            $configFile = __DIR__ . '/../../config/database.php';
            if (!file_exists($configFile)) {
                // Fallback to old config
                $oldConfig = require __DIR__ . '/../../src/config.php';
                self::$config = $oldConfig['db'] ?? [];
            } else {
                self::$config = require $configFile;
            }
        }
        return self::$config;
    }

    /**
     * Execute a query and return results
     */
    public static function query(string $sql, array $params = []): array
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a query and return single row
     */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Execute an insert/update/delete query
     */
    public static function execute(string $sql, array $params = []): bool
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}
