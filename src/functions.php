<?php
require_once __DIR__ . '/db.php';

function products_list(): array {
  $pdo = db();
  return $pdo->query('SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.stock_quantity > 0 ORDER BY p.created_at DESC')->fetchAll();
}

function products_list_admin(): array {
  $pdo = db();
  return $pdo->query('SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.created_at DESC')->fetchAll();
}

function product_find(int $id): ?array {
  $pdo = db();
  $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
  $stmt->execute([$id]);
  return $stmt->fetch() ?: null;
}

function cart_add(int $product_id, int $qty = 1): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $_SESSION['cart'] ??= [];
  $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + max(1, $qty);
}

function cart_remove(int $product_id): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  unset($_SESSION['cart'][$product_id]);
}

function cart_clear(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $_SESSION['cart'] = [];
}

function cart_get(): array {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  return $_SESSION['cart'] ?? [];
}

function cart_count(): int {
  $cart = cart_get();
  return array_sum($cart);
}

function cart_total(): float {
  $cart = cart_get();
  $total = 0;
  $pdo = db();
  foreach ($cart as $product_id => $qty) {
    $stmt = $pdo->prepare('SELECT price FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    if ($product) {
      $total += $product['price'] * $qty;
    }
  }
  return $total;
}

function create_order(int $user_id, array $items, float $total, string $payment_method = 'card'): int {
  $pdo = db();
  $pdo->beginTransaction();
  
  try {
    // Create order
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, payment_method, status, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$user_id, $total, $payment_method, 'pending']);
    $order_id = $pdo->lastInsertId();
    
    // Add order items
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    foreach ($items as $product_id => $qty) {
      $product_stmt = $pdo->prepare('SELECT price FROM products WHERE id = ?');
      $product_stmt->execute([$product_id]);
      $product = $product_stmt->fetch();
      if ($product) {
        $stmt->execute([$order_id, $product_id, $qty, $product['price']]);
      }
    }
    
    $pdo->commit();
    return $order_id;
  } catch (Exception $e) {
    $pdo->rollback();
    throw $e;
  }
}

function users_list(): array {
  $pdo = db();
  return $pdo->query('SELECT id, email, role, name, created_at FROM users ORDER BY created_at DESC')->fetchAll();
}

function product_create(string $name, string $description = '', float $price = 0, int $stock = 0, string $image_url = '', string $category = ''): bool {
  $pdo = db();
  $stmt = $pdo->prepare('INSERT INTO products (name, description, price, stock_quantity, image_url, category) VALUES (?, ?, ?, ?, ?, ?)');
  return $stmt->execute([$name, $description, $price, $stock, $image_url, $category]);
}

function product_update(int $id, string $name, string $description = '', float $price = 0, int $stock = 0, string $image_url = '', string $category = ''): bool {
  $pdo = db();
  $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, image_url = ?, category = ? WHERE id = ?');
  return $stmt->execute([$name, $description, $price, $stock, $image_url, $category, $id]);
}

function product_delete(int $id): bool {
  $pdo = db();
  $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
  return $stmt->execute([$id]);
}

function product_get(int $id): ?array {
  return product_find($id); // Alias for consistency with naming convention
}

function get_user_orders(int $user_id): array {
  $pdo = db();
  $stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
  $stmt->execute([$user_id]);
  return $stmt->fetchAll();
}

function get_order_items(int $order_id): array {
  $pdo = db();
  $stmt = $pdo->prepare('
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON p.id = oi.product_id 
    WHERE oi.order_id = ?
  ');
  $stmt->execute([$order_id]);
  return $stmt->fetchAll();
}