<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/csrf.php';

// Only process POST requests with CSRF validation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    logout_user();
    header('Location: index.php');
    exit;
}

// For GET requests, redirect to home page
header('Location: index.php');
exit;