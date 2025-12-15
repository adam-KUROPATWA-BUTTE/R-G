<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/CartService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$index = isset($_POST['item_index']) ? (int)$_POST['item_index'] : -1;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;

if ($index >= 0) {
    cart_update_quantity($index, $quantity);
}

header('Location: cart.php');
exit;