<?php
require_once __DIR__ . '/db.php';

if (!function_exists('table_columns')) {
    function table_columns(string $table): array {
        static $cache = [];
        if (!isset($cache[$table])) {
            // Use SQLite-compatible PRAGMA command
            $stmt = db()->prepare("PRAGMA table_info(`$table`)");
            $stmt->execute();
            $cols = [];
            foreach ($stmt->fetchAll() as $row) {
                $cols[$row['name']] = true;
            }
            $cache[$table] = $cols;
        }
        return $cache[$table];
    }
}

if (!function_exists('table_has_column')) {
    function table_has_column(string $table, string $column): bool {
        $cols = table_columns($table);
        return isset($cols[$column]);
    }
}