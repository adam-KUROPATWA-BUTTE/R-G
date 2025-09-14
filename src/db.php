<?php
function config(): array {
    static $cfg = null;
    if ($cfg === null) $cfg = require __DIR__ . '/config.php';
    return $cfg;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $cfg = config()['db'];
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5, // optionnel
        ];
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
    }
    return $pdo;
}