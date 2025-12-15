<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/CartService.php';

cart_clear();

header('Location: cart.php');
exit;