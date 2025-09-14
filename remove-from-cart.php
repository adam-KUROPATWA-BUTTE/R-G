<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/functions.php';

session_boot();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$product_id = (int) $input['product_id'];

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    cart_remove($product_id);
    $cart_count = cart_count();
    
    echo json_encode([
        'success' => true,
        'message' => 'Article supprimé du panier',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    error_log('Remove from cart error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>