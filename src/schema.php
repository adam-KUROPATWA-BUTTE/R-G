<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Retourne le driver PDO actif (mysql, sqlite, pgsql, ...).
 */
function db_driver(): string {
    return db()->getAttribute(PDO::ATTR_DRIVER_NAME);
}

/**
 * Liste les colonnes d'une table de manière sûre et compatible.
 * @return string[] noms de colonnes
 */
function table_columns(string $table): array {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        throw new InvalidArgumentException('Invalid table name');
    }

    $pdo = db();
    $driver = db_driver();

    if ($driver === 'mysql') {
        // MariaDB/MySQL
        $sql = "SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':table' => $table]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } elseif ($driver === 'pgsql') {
        // PostgreSQL
        $sql = "SELECT column_name
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':table' => $table]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    } elseif ($driver === 'sqlite' || $driver === 'sqlite2') {
        // SQLite
        // PRAGMA ne supporte pas les placeholders -> nom déjà validé par regex
        $stmt = $pdo->query("PRAGMA table_info(" . $table . ")");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return array_values(array_filter(array_map(
            static fn(array $r) => isset($r['name']) ? (string)$r['name'] : '',
            $rows
        )));
    }

    // Fallback générique
    $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = :table";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':table' => $table]);
    return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/**
 * Vérifie l'existence d'une colonne dans une table.
 */
function table_has_column(string $table, string $column): bool {
    $cols = table_columns($table);
    return in_array($column, $cols, true);
}

/**
 * Vérifie si une table existe.
 */
function table_exists(string $table): bool {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }

    $pdo = db();
    $driver = db_driver();

    if ($driver === 'mysql') {
        $sql = "SELECT 1
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':table' => $table]);
        return (bool) $stmt->fetchColumn();
    } elseif ($driver === 'pgsql') {
        $sql = "SELECT 1
                FROM information_schema.tables
                WHERE table_schema = current_schema()
                  AND table_name = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':table' => $table]);
        return (bool) $stmt->fetchColumn();
    } elseif ($driver === 'sqlite' || $driver === 'sqlite2') {
        $sql = "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':table' => $table]);
        return (bool) $stmt->fetchColumn();
    }

    return false;
}