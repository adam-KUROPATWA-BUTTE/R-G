<?php
require_once __DIR__ . '/db.php';

function current_user(): ?array {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  return $_SESSION['user'] ?? null;
}

function require_login(): void {
  if (!current_user()) {
    header('Location: /login.php');
    exit;
  }
}

function require_admin(): void {
  $u = current_user();
  if (!$u || $u['role'] !== 'admin') {
    http_response_code(403);
    exit('Accès refusé');
  }
}

function register_user(string $email, string $password, string $first_name = '', string $last_name = ''): bool {
  $pdo = db();
  $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
  $stmt->execute([$email]);
  if ($stmt->fetch()) return false;
  $hash = password_hash($password, PASSWORD_DEFAULT);
  
  // Combine first_name and last_name into name field
  $name = trim($first_name . ' ' . $last_name);
  
  $ins = $pdo->prepare('INSERT INTO users(email, password_hash, name) VALUES(?, ?, ?)');
  return $ins->execute([$email, $hash, $name ?: null]);
}

function login_user(string $email, string $password): bool {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $pdo = db();
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  if (!$u || !password_verify($password, $u['password_hash'])) return false;
  
  // Extract first name from full name
  $first_name = '';
  if ($u['name']) {
    $name_parts = explode(' ', trim($u['name']), 2);
    $first_name = $name_parts[0];
  }
  
  $_SESSION['user'] = [
    'id' => $u['id'], 
    'email' => $u['email'], 
    'role' => $u['role'],
    'name' => $u['name'],
    'first_name' => $first_name
  ];
  return true;
}

function logout_user(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  session_destroy();
}