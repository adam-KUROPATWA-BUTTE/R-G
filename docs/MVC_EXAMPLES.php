<?php
/**
 * Example: Using the MVC structure in existing pages
 * 
 * This file demonstrates how to integrate the new MVC components
 * into the existing R&G codebase.
 */

// Example 1: Product listing page using MVC
// File: pages/products-mvc-example.php

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../Core/Autoloader.php';
Autoloader::register();

use App\Models\Product;
use App\Core\View;

// Get products from the model
$productModel = new Product();
$products = $productModel->getActive(10); // Get 10 active products

// Render the view
$view = new View();
$view->render('shop/index', [
    'products' => $products,
    'pageTitle' => 'Nos Produits',
    'categoryDescription' => 'Découvrez notre sélection de produits'
]);

/* 
 * Example 2: Admin product management
 * File: admin/products-mvc.php
 */

require_admin();

use App\Models\Product;
use App\Core\View;

$productModel = new Product();

// Handle product creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    
    if (isset($_GET['id'])) {
        // Update existing product
        $productModel->update((int)$_GET['id'], [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => (float)$_POST['price'],
            'category' => $_POST['category'],
            'stock_quantity' => (int)$_POST['stock_quantity']
        ]);
        $success = "Produit mis à jour avec succès";
    } else {
        // Create new product
        $productId = $productModel->create([
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => (float)$_POST['price'],
            'category' => $_POST['category'],
            'stock_quantity' => (int)$_POST['stock_quantity'],
            'image' => 'default.jpg',
            'status' => 'active'
        ]);
        $success = "Produit créé avec succès (ID: $productId)";
    }
}

// Get product for editing or create new
$product = isset($_GET['id']) 
    ? $productModel->find((int)$_GET['id']) 
    : null;

$view = new View();
$view->render('admin/products/edit', [
    'product' => $product,
    'isEdit' => $product !== null,
    'success' => $success ?? null
], null); // null = no layout, view has its own HTML

/*
 * Example 3: Cart management
 * File: api/cart-add.php
 */

use App\Models\Cart;
use App\Models\Product;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

csrf_validate();

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

// Verify product exists and is in stock
$productModel = new Product();
$product = $productModel->find($productId);

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit;
}

if ($product['stock_quantity'] < $quantity) {
    http_response_code(400);
    echo json_encode(['error' => 'Insufficient stock']);
    exit;
}

// Add to cart
$cartModel = new Cart();
$cartModel->addItem($productId, $quantity);

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => $cartModel->getCount(),
    'cart_total' => $cartModel->getTotal()
]);

/*
 * Example 4: Order processing
 * File: process-order.php
 */

use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;

require_login();
csrf_validate();

$cartModel = new Cart();
$cartItems = $cartModel->getCartWithProducts();

if (empty($cartItems)) {
    header('Location: /cart.php?error=empty');
    exit;
}

// Prepare order items
$orderItems = [];
foreach ($cartItems as $item) {
    $orderItems[] = [
        'product_id' => $item['id'],
        'quantity' => $item['cart_quantity'],
        'price' => $item['price']
    ];
}

// Create the order
$orderModel = new Order();
$currentUser = current_user();

try {
    $orderId = $orderModel->createWithItems([
        'user_id' => $currentUser['id'],
        'total' => $cartModel->getTotal(),
        'payment_method' => $_POST['payment_method'] ?? 'card',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ], $orderItems);
    
    // Clear the cart
    $cartModel->clear();
    
    // Redirect to success page
    header("Location: /checkout_success.php?order=$orderId");
    
} catch (Exception $e) {
    error_log("Order creation failed: " . $e->getMessage());
    header('Location: /checkout_form.php?error=processing');
}

/*
 * Example 5: User account with orders
 * File: account-mvc.php
 */

use App\Models\Order;

require_login();

$currentUser = current_user();
$orderModel = new Order();
$orders = $orderModel->getByUser($currentUser['id']);

$view = new View();
$view->render('account/index', [
    'currentUser' => $currentUser,
    'orders' => $orders
]);
