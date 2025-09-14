<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/csrf.php';

// Start session for cart
session_boot();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Validate CSRF token if provided
if (isset($input['csrf_token'])) {
    try {
        // Set the token for validation
        $_POST['csrf_token'] = $input['csrf_token'];
        csrf_validate();
    } catch (Exception $e) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

$product_id = (int) $input['product_id'];
$quantity = (int) ($input['quantity'] ?? 1);

if ($product_id <= 0 || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid product ID or quantity']);
    exit;
}

try {
    // Verify product exists
    $product = product_get($product_id);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
    
    // Add to cart
    cart_add($product_id, $quantity);
    
    // Get updated cart count
    $cart_count = cart_count();
    
    echo json_encode([
        'success' => true,
        'message' => 'Produit ajouté au panier',
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    error_log('Add to cart error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>