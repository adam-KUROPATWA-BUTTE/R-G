<?php
require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';

// Compute base path for subdirectory deployments
$base_path = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$base_path = $base_path === '/' ? '' : rtrim($base_path, '');

// Only process POST requests with CSRF validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    logout_user();
    header('Location: ' . $base_path . '/');
    exit;
}

// For GET requests, redirect to home page
header('Location: ' . $base_path . '/');
exit;