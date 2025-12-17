<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /checkout/cancel instead (handled by CheckoutController@cancel)
 */
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/checkout/cancel' . ($query ? '?' . $query : '');
header('Location: ' . $url);
exit;
