<?php
namespace Config;

use PDO;
use PDOException;

/**
 * Database Configuration and Connection Manager
 * Singleton pattern for PDO connection
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
     * Connect to database using configuration
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
            throw new \RuntimeException("Could not connect to database: " . $e->getMessage());
        }
    }

    /**
     * Get database configuration
     * First tries .env variables, then falls back to config files
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            // Try to load from environment variables first
            if (getenv('DB_CONNECTION') !== false || isset($_ENV['DB_CONNECTION'])) {
                $dbConnection = getenv('DB_CONNECTION') ?: $_ENV['DB_CONNECTION'];
                
                if ($dbConnection === 'sqlite') {
                    $dbPath = getenv('DB_PATH') ?: $_ENV['DB_PATH'] ?? './database.db';
                    self::$config = [
                        'type' => 'sqlite',
                        'path' => $dbPath
                    ];
                } else {
                    self::$config = [
                        'host' => getenv('DB_HOST') ?: $_ENV['DB_HOST'] ?? 'localhost',
                        'name' => getenv('DB_DATABASE') ?: $_ENV['DB_DATABASE'] ?? '',
                        'user' => getenv('DB_USERNAME') ?: $_ENV['DB_USERNAME'] ?? '',
                        'pass' => getenv('DB_PASSWORD') ?: $_ENV['DB_PASSWORD'] ?? '',
                        'charset' => 'utf8mb4'
                    ];
                }
            } else {
                // Fallback to config file
                $configFile = __DIR__ . '/../../src/config.php';
                if (file_exists($configFile)) {
                    $oldConfig = require $configFile;
                    self::$config = $oldConfig['db'] ?? [];
                } else {
                    // Default SQLite configuration
                    self::$config = [
                        'type' => 'sqlite',
                        'path' => __DIR__ . '/../../database.db'
                    ];
                }
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

    /**
     * Begin a transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Rollback a transaction
     */
    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }
}
