<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/functions.php';
require_once __DIR__ . '/../../src/csrf.php';

function cart_ensure_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function cart_init(): void {
    cart_ensure_session();
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [],
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

function cart_add(int $productId, int $qty = 1, string $size = ''): void {
    cart_init();
    $qty = max(1, $qty);

    $p = product_get($productId);
    if (!$p) {
        throw new RuntimeException("Produit introuvable (#{$productId}).");
    }
    if (isset($p['status']) && $p['status'] === 'inactive') {
        throw new RuntimeException("Ce produit est inactif.");
    }

    $name = $p['name'] ?? ("Produit #".$productId);
    $price = isset($p['price']) ? (float)$p['price'] : 0.0;
    $image = $p['image'] ?? null;
    $category = $p['category'] ?? null;
    
    $availableSizes = product_parse_sizes($p['sizes'] ?? '');
    if (!empty($availableSizes) && !in_array(strtoupper($size), $availableSizes, true)) {
        throw new RuntimeException("Taille invalide pour ce produit.");
    }

    $cartKey = $size ? $productId . '_' . strtoupper($size) : $productId;

    if (isset($p['stock_quantity'])) {
        $inCart = $_SESSION['cart']['items'][$cartKey]['qty'] ?? 0;
        $maxAddable = max(0, ((int)$p['stock_quantity']) - $inCart);
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

function cart_update(int $productId, int $qty, string $size = ''): void {
    cart_init();
    
    $cartKey = $size ? $productId . '_' . strtoupper($size) : $productId;
    
    if (!isset($_SESSION['cart']['items'][$cartKey])) return;

    $qty = max(0, $qty);

    $p = product_get($productId);
    if ($p && isset($p['stock_quantity'])) {
        $qty = min($qty, (int)$p['stock_quantity']);
    }

    if ($qty === 0) {
        unset($_SESSION['cart']['items'][$cartKey]);
    } else {
        $_SESSION['cart']['items'][$cartKey]['qty'] = $qty;
    }
    $_SESSION['cart']['updated_at'] = time();
}

function cart_remove(int $productId, string $size = ''): void {
    cart_init();
    
    $cartKey = $size ? $productId . '_' . strtoupper($size) : $productId;
    
    unset($_SESSION['cart']['items'][$cartKey]);
    $_SESSION['cart']['updated_at'] = time();
}

function cart_clear(): void {
    cart_init();
    $_SESSION['cart'] = ['items' => [], 'updated_at' => time()];
}

/**
 * ✅ Récupère tous les items du panier avec détails complets depuis la BDD
 */
function cart_get_all(): array {
    $pdo = db();
    $cartItems = cart_items();
    $result = [];
    
    foreach ($cartItems as $cartKey => $item) {
        $productId = (int)$item['id'];
        
        // ✅ Table 'products' (et non 'produits')
        $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$produit) continue;
        
        $result[] = [
            'id' => (int)$produit['id'],
            'nom' => $produit['name'],          // ✅ Mapping 'name' -> 'nom'
            'prix' => (float)$produit['price'], // ✅ Mapping 'price' -> 'prix'
            'image' => $produit['image'],
            'taille' => $item['size'] ?? null,
            'quantite' => (int)$item['qty'],
            'cart_key' => $cartKey
        ];
    }
    
    return $result;
}