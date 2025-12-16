<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /payment/success instead (handled by PaymentController@success)
 */
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/payment/success' . ($query ? '?' . $query : '');
header('Location: ' . $url);
exit;
