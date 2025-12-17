<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /product/{id} instead (handled by ProductController@show)
 */
$id = $_GET['id'] ?? '';
if (ctype_digit($id)) {
    header('Location: /product/' . $id);
} else {
    header('Location: /');
}
exit;
