<?php
require_once __DIR__ . '/db.php';

if (!function_exists('table_columns')) {
    function table_columns(string $table): array {
        static $cache = [];
        if (!isset($cache[$table])) {
            $stmt = db()->prepare("SHOW COLUMNS FROM `$table`");
            $stmt->execute();
            $cols = [];
            foreach ($stmt->fetchAll() as $row) {
                $cols[$row['Field']] = true;
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