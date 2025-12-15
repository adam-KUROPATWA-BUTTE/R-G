<?php
namespace Models;

/**
 * Cart Model
 * Handles shopping cart operations
 */
class Cart
{
    private Product $productModel;

    public function __construct()
    {
        $this->ensureSession();
        $this->init();
        $this->productModel = new Product();
    }

    /**
     * Ensure session is started
     */
    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Initialize cart in session
     */
    private function init(): void
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [
                'items' => [],
                'updated_at' => time(),
            ];
        }
    }

    /**
     * Get all cart data
     */
    public function get(): array
    {
        return $_SESSION['cart'];
    }

    /**
     * Get cart items
     */
    public function getItems(): array
    {
        $cart = $this->get();
        return $cart['items'] ?? [];
    }

    /**
     * Get cart item count
     */
    public function getCount(): int
    {
        $count = 0;
        foreach ($this->getItems() as $item) {
            $count += (int)($item['qty'] ?? 0);
        }
        return $count;
    }

    /**
     * Get cart total
     */
    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $qty = (int)($item['qty'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            $total += $price * $qty;
        }
        return round($total, 2);
    }

    /**
     * Add item to cart
     */
    public function add(int $productId, int $qty = 1, string $size = ''): void
    {
        $qty = max(1, $qty);

        $product = $this->productModel->getById($productId);
        if (!$product) {
            throw new \RuntimeException("Product not found (#{$productId}).");
        }
        if (isset($product['status']) && $product['status'] === 'inactive') {
            throw new \RuntimeException("This product is inactive.");
        }

        $name = $product['name'] ?? ("Product #" . $productId);
        $price = isset($product['price']) ? (float)$product['price'] : 0.0;
        $image = $product['image'] ?? null;
        $category = $product['category'] ?? null;
        
        $availableSizes = $this->productModel->parseSizes($product['sizes'] ?? '');
        if (!empty($availableSizes) && !in_array(strtoupper($size), $availableSizes, true)) {
            throw new \RuntimeException("Invalid size for this product.");
        }

        $cartKey = $size ? $productId . '_' . strtoupper($size) : (string)$productId;

        // Check stock
        if (isset($product['stock_quantity'])) {
            $inCart = $_SESSION['cart']['items'][$cartKey]['qty'] ?? 0;
            $maxAddable = max(0, ((int)$product['stock_quantity']) - $inCart);
            $qty = min($qty, $maxAddable);
            if ($qty === 0) {
                return;
            }
        }

        if (!isset($_SESSION['cart']['items'][$cartKey])) {
            $_SESSION['cart']['items'][$cartKey] = [
                'id' => $productId,
                'name' => $name,
                'price' => $price,
                'qty' => 0,
                'image' => $image,
                'category' => $category,
                'size' => $size ? strtoupper($size) : null,
                'cart_key' => $cartKey,
            ];
        }
        $_SESSION['cart']['items'][$cartKey]['qty'] += $qty;
        $_SESSION['cart']['updated_at'] = time();
    }

    /**
     * Update cart item quantity
     */
    public function update(int $productId, int $qty, string $size = ''): void
    {
        $cartKey = $size ? $productId . '_' . strtoupper($size) : (string)$productId;
        
        if (!isset($_SESSION['cart']['items'][$cartKey])) {
            throw new \RuntimeException("Item not found in cart.");
        }

        if ($qty <= 0) {
            unset($_SESSION['cart']['items'][$cartKey]);
        } else {
            $_SESSION['cart']['items'][$cartKey]['qty'] = $qty;
        }
        
        $_SESSION['cart']['updated_at'] = time();
    }

    /**
     * Update cart item by index
     */
    public function updateByIndex(int $index, int $qty): void
    {
        $items = array_values($this->getItems());
        if (!isset($items[$index])) {
            throw new \RuntimeException("Item not found in cart.");
        }

        $cartKey = $items[$index]['cart_key'] ?? null;
        if (!$cartKey) {
            throw new \RuntimeException("Invalid cart item.");
        }

        if ($qty <= 0) {
            unset($_SESSION['cart']['items'][$cartKey]);
        } else {
            $_SESSION['cart']['items'][$cartKey]['qty'] = $qty;
        }
        
        $_SESSION['cart']['updated_at'] = time();
    }

    /**
     * Remove item from cart
     */
    public function remove(int $productId, string $size = ''): void
    {
        $cartKey = $size ? $productId . '_' . strtoupper($size) : (string)$productId;
        unset($_SESSION['cart']['items'][$cartKey]);
        $_SESSION['cart']['updated_at'] = time();
    }

    /**
     * Remove item by index
     */
    public function removeByIndex(int $index): void
    {
        $items = array_values($this->getItems());
        if (!isset($items[$index])) {
            throw new \RuntimeException("Item not found in cart.");
        }

        $cartKey = $items[$index]['cart_key'] ?? null;
        if (!$cartKey) {
            throw new \RuntimeException("Invalid cart item.");
        }

        unset($_SESSION['cart']['items'][$cartKey]);
        $_SESSION['cart']['updated_at'] = time();
    }

    /**
     * Clear cart
     */
    public function clear(): void
    {
        $_SESSION['cart'] = [
            'items' => [],
            'updated_at' => time(),
        ];
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->getItems());
    }
}
