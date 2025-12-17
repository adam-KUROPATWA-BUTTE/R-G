<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /admin/orders/{id} instead (handled by Admin\OrderController@show)
 */
$id = $_GET['id'] ?? '';
if (ctype_digit($id)) {
    header('Location: /admin/orders/' . $id);
} else {
    header('Location: /admin/orders');
}
exit;
