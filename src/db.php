<?php
$config_file = __DIR__ . '/config.php';
if (file_exists($config_file)) {
  $config = require $config_file;
} else {
  // Fallback configuration for development when config.php doesn't exist
  $config = [
    'db' => [
      'dsn' => 'sqlite::memory:',
      'user' => '',
      'pass' => '',
      'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ],
    ],
  ];
}

function db(): PDO {
  static $pdo = null;
  global $config;
  if ($pdo === null) {
    $pdo = new PDO(
      $config['db']['dsn'],
      $config['db']['user'],
      $config['db']['pass'],
      $config['db']['options']
    );
  }
  return $pdo;
}