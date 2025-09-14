<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/cart.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée'); }
if (!csrf_verify($_POST['csrf'] ?? null)) { http_response_code(400); exit('CSRF invalide'); }

$id = (int)($_POST['id'] ?? 0);
$qty = max(0, (int)($_POST['qty'] ?? 0));
cart_update($id, $qty);
header('Location: /cart.php');