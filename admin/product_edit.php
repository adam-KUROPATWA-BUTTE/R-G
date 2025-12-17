<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /admin/products/{id}/edit instead (handled by Admin\ProductController@edit)
 */
$id = $_GET['id'] ?? '';
if (ctype_digit($id)) {
    header('Location: /admin/products/' . $id . '/edit');
} else {
    header('Location: /admin/products');
}
exit;
