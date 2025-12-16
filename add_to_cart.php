<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use POST /cart/add instead (handled by CartController@add)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Forward POST data to new route
    header('Location: /cart/add');
    exit;
}
header('Location: /cart');
exit;
