<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/cart.php';
require_once __DIR__ . '/src/csrf.php';
require_once __DIR__ . '/src/functions.php';

// Start session for cart
session_boot();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

// Handle both form-encoded and JSON input
$input = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
} else {
    $input = $_POST;
}

if (!$input || !isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid input']);
    exit;
}

// Validate CSRF token
try {
    if (isset($input['csrf'])) {
        $_POST['csrf_token'] = $input['csrf'];
    }
    csrf_validate();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$product_id = (int) $input['product_id'];
$quantity = (int) ($input['qty'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid product ID or quantity']);
    exit;
}

try {
    // Verify product exists
    $product = product_get($product_id);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Product not found']);
        exit;
    }
    
    // Add to cart
    cart_add($product_id, $quantity);
    
    // Get updated cart count
    $cart_count = cart_count();
    
    echo json_encode([
        'ok' => true,
        'message' => 'Produit ajouté au panier',
        'count' => $cart_count
    ]);
    
} catch (Exception $e) {
    error_log('Add to cart error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Server error']);
}
?>