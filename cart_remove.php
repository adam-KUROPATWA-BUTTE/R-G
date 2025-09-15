<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/CartService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée'); }
if (!csrf_verify($_POST['csrf'] ?? null)) { http_response_code(400); exit('CSRF invalide'); }

$id = (int)($_POST['id'] ?? 0);
cart_remove($id);

// Compute base path for subdirectory deployments
$base_path = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$base_path = $base_path === '/' ? '' : rtrim($base_path, '');
header('Location: ' . $base_path . '/cart.php');