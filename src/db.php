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
        if ($cfg['host'] === 'sqlite') {
            $dsn = "sqlite:{$cfg['name']}";
            $pdo = new PDO($dsn);
        } else {
            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}";
            $pdo = new PDO($dsn, $cfg['user'], $cfg['pass']);
        }
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        foreach ($options as $key => $value) {
            $pdo->setAttribute($key, $value);
        }
    }
    return $pdo;
}