<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/cart.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Méthode non autorisée'); }
if (!csrf_verify($_POST['csrf'] ?? null)) { http_response_code(400); exit('CSRF invalide'); }

cart_clear();
header('Location: /cart.php');