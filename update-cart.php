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

if (!$input || !isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$product_id = (int) $input['product_id'];
$quantity = (int) $input['quantity'];

if ($product_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
    exit;
}

try {
    if ($quantity <= 0) {
        cart_remove($product_id);
    } else {
        // Update cart by removing and re-adding
        cart_remove($product_id);
        cart_add($product_id, $quantity);
    }
    
    $cart_count = cart_count();
    
    echo json_encode([
        'success' => true,
        'message' => 'Panier mis à jour',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    error_log('Update cart error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>