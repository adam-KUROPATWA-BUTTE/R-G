<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /checkout/success instead (handled by CheckoutController@success)
 */
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/checkout/success' . ($query ? '?' . $query : '');
header('Location: ' . $url);
exit;
