<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use POST /cart/update instead (handled by CartController@update)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: /cart/update');
    exit;
}
header('Location: /cart');
exit;

if ($index >= 0) {
    cart_update_quantity($index, $quantity);
}

header('Location: cart.php');
exit;