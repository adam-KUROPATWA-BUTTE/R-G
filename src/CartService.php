<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php'; // fournit product_get(), table_columns(), db(), etc.
require_once __DIR__ . '/csrf.php';

function cart_ensure_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function cart_init(): void {
    cart_ensure_session();
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [], // productId => ['id','name','price','qty','image','category']
            'updated_at' => time(),
        ];
    }
}

function cart_get(): array {
    cart_init();
    return $_SESSION['cart'];
}

function cart_items(): array {
    $c = cart_get();
    return $c['items'] ?? [];
}

function cart_count(): int {
    $count = 0;
    foreach (cart_items() as $it) {
        $count += (int)($it['qty'] ?? 0);
    }
    return $count;
}

function cart_total(): float {
    $total = 0.0;
    foreach (cart_items() as $it) {
        $qty = (int)($it['qty'] ?? 0);
        $price = (float)($it['price'] ?? 0);
        $total += $price * $qty;
    }
    return round($total, 2);
}

function cart_add(int $productId, int $qty = 1): void {
    cart_init();
    $qty = max(1, $qty);

    $p = product_get($productId);
    if (!$p) {
        throw new RuntimeException("Produit introuvable (#{$productId}).");
    }
    // Bloquer les inactifs si ta table a 'status'
    if (isset($p['status']) && $p['status'] === 'inactive') {
        throw new RuntimeException("Ce produit est inactif.");
    }

    $name = $p['name'] ?? ("Produit #".$productId);
    $price = isset($p['price']) ? (float)$p['price'] : 0.0;
    $image = $p['image'] ?? null;
    $category = $p['category'] ?? null;

    // Respecter le stock si présent
    if (isset($p['stock_quantity'])) {
        $inCart = $_SESSION['cart']['items'][$productId]['qty'] ?? 0;
        $maxAddable = max(0, ((int)$p['stock_quantity']) - $inCart);
        $qty = min($qty, $maxAddable);
        if ($qty === 0) {
            // Pas assez de stock pour en ajouter
            return;
        }
    }

    if (!isset($_SESSION['cart']['items'][$productId])) {
        $_SESSION['cart']['items'][$productId] = [
            'id' => $productId,
            'name' => $name,
            'price' => $price,
            'qty' => 0,
            'image' => $image,
            'category' => $category,
        ];
    }
    $_SESSION['cart']['items'][$productId]['qty'] += $qty;
    $_SESSION['cart']['updated_at'] = time();
}

function cart_update(int $productId, int $qty): void {
    cart_init();
    if (!isset($_SESSION['cart']['items'][$productId])) return;

    $qty = max(0, $qty);

    // Respecter le stock si présent
    $p = product_get($productId);
    if ($p && isset($p['stock_quantity'])) {
        $qty = min($qty, (int)$p['stock_quantity']);
    }

    if ($qty === 0) {
        unset($_SESSION['cart']['items'][$productId]);
    } else {
        $_SESSION['cart']['items'][$productId]['qty'] = $qty;
    }
    $_SESSION['cart']['updated_at'] = time();
}

function cart_remove(int $productId): void {
    cart_init();
    unset($_SESSION['cart']['items'][$productId]);
    $_SESSION['cart']['updated_at'] = time();
}

function cart_clear(): void {
    cart_init();
    $_SESSION['cart'] = ['items' => [], 'updated_at' => time()];
}