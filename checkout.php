<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /checkout instead (handled by CheckoutController@index)
 */
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/checkout' . ($query ? '?' . $query : '');
header('Location: ' . $url);
exit;
