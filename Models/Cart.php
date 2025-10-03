<?php

namespace App\Models;

use App\Core\Model;

/**
 * Cart Model - Session-based cart management
 */
class Cart extends Model {
    protected $table = 'cart_items';
    
    /**
     * Get cart from session
     */
    public function getCart(): array {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return $_SESSION['cart'] ?? [];
    }
    
    /**
     * Add item to cart
     */
    public function addItem(int $productId, int $quantity = 1, array $options = []): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        $itemKey = $productId;
        
        if (isset($_SESSION['cart'][$itemKey])) {
            $_SESSION['cart'][$itemKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$itemKey] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'options' => $options,
                'added_at' => time()
            ];
        }
    }
    
    /**
     * Update item quantity
     */
    public function updateQuantity(int $productId, int $quantity): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
        }
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem(int $productId): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
    }
    
    /**
     * Clear cart
     */
    public function clear(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['cart'] = [];
    }
    
    /**
     * Get cart with product details
     */
    public function getCartWithProducts(): array {
        $cart = $this->getCart();
        if (empty($cart)) {
            return [];
        }
        
        $productIds = array_keys($cart);
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($productIds);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($products as $product) {
            $productId = $product['id'];
            if (isset($cart[$productId])) {
                $result[] = array_merge($product, [
                    'cart_quantity' => $cart[$productId]['quantity'],
                    'cart_options' => $cart[$productId]['options'] ?? [],
                    'subtotal' => $product['price'] * $cart[$productId]['quantity']
                ]);
            }
        }
        
        return $result;
    }
    
    /**
     * Get cart total
     */
    public function getTotal(): float {
        $items = $this->getCartWithProducts();
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }
    
    /**
     * Get cart count
     */
    public function getCount(): int {
        $cart = $this->getCart();
        $count = 0;
        
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        
        return $count;
    }
}
