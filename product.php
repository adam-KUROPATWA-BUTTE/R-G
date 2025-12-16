<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /product/{id} instead (handled by ProductController@show)
 */
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    header('Location: /product/' . $_GET['id']);
} else {
    header('Location: /');
}
exit;
