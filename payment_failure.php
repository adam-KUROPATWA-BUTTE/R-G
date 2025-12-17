<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use /payment/failure instead (handled by PaymentController@failure)
 */
$query = $_SERVER['QUERY_STRING'] ?? '';
$url = '/payment/failure' . ($query ? '?' . $query : '');
header('Location: ' . $url);
exit;
