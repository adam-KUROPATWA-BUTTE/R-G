<?php
require_once __DIR__ . '/auth.php';

/**
 * Session-based cart helper functions
 */

function cart_init(): void {
    session_boot();
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function cart_add(int $product_id, int $qty = 1): void {
    cart_init();
    $product_id = (string)$product_id; // Use string keys for consistency
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'quantity' => $qty
        ];
    }
}

function cart_remove(int $product_id): void {
    cart_init();
    $product_id = (string)$product_id;
    unset($_SESSION['cart'][$product_id]);
}

function cart_clear(): void {
    cart_init();
    $_SESSION['cart'] = [];
}

function cart_count(): int {
    cart_init();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += (int)($item['quantity'] ?? 1);
    }
    return $count;
}

function cart_get(): array {
    cart_init();
    return $_SESSION['cart'] ?? [];
}

function cart_update_quantity(int $product_id, int $quantity): void {
    if ($quantity <= 0) {
        cart_remove($product_id);
        return;
    }
    
    cart_init();
    $product_id = (string)$product_id;
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
}