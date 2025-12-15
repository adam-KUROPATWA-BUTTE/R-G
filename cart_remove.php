<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/CartService.php';

$index = isset($_GET['index']) ? (int)$_GET['index'] : -1;

if ($index >= 0) {
    cart_remove($index);
}

header('Location: cart.php');
exit;