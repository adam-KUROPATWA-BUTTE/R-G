<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema.php';

// Produits
function products_list(?string $category = null): array {
    $available = table_columns('products');
    $wanted = ['id','name','description','price','stock','image_url','category','created_at'];
    $cols = array_values(array_filter($wanted, fn($c) => isset($available[$c])));
    if (empty($cols)) $cols = ['id'];

    $sql = "SELECT " . implode(',', $cols) . " FROM products";
    $params = [];
    if ($category && isset($available['category'])) {
        $sql .= " WHERE category = ?";
        $params[] = $category;
    }
    $sql .= " ORDER BY id DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function product_get(int $id): ?array {
    $available = table_columns('products');
    $wanted = ['id','name','description','price','stock','image_url','category','created_at'];
    $cols = array_values(array_filter($wanted, fn($c) => isset($available[$c])));
    if (empty($cols)) $cols = ['id'];

    $sql = "SELECT " . implode(',', $cols) . " FROM products WHERE id = ? LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    return $p ?: null;
}

function product_create(array $d): int {
    $available = table_columns('products');
    $fields = [];
    foreach (['name','description','price','stock','image_url','category'] as $field) {
        if (isset($available[$field]) && array_key_exists($field, $d)) {
            $fields[$field] = $d[$field];
        }
    }
    if (isset($available['created_at'])) {
        $fields['created_at'] = date('Y-m-d H:i:s');
    }
    if (empty($fields)) {
        throw new RuntimeException("Aucun champ valide pour l'insertion produit.");
    }
    $cols = array_keys($fields);
    $placeholders = array_fill(0, count($fields), '?');
    $sql = "INSERT INTO products (".implode(',', $cols).") VALUES (".implode(',', $placeholders).")";
    $stmt = db()->prepare($sql);
    $stmt->execute(array_values($fields));
    return (int)db()->lastInsertId();
}

function product_update(int $id, array $d): void {
    $available = table_columns('products');
    $sets = [];
    $params = [];
    foreach (['name','description','price','stock','image_url','category'] as $field) {
        if (isset($available[$field]) && array_key_exists($field, $d)) {
            $sets[] = "$field = ?";
            $params[] = $d[$field];
        }
    }
    if (empty($sets)) return;
    $params[] = $id;
    $sql = "UPDATE products SET ".implode(',', $sets)." WHERE id = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
}

function product_delete(int $id): void {
    $stmt = db()->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

// Utilisateurs (admin)
function users_list(): array {
    $available = table_columns('users');
    $wanted = ['id','email','role','first_name','last_name','created_at'];
    $cols = array_values(array_filter($wanted, fn($c) => isset($available[$c])));
    if (!in_array('id', $cols, true)) $cols[] = 'id';
    if (!in_array('email', $cols, true)) $cols[] = 'email';
    $sql = "SELECT " . implode(',', $cols) . " FROM users ORDER BY id DESC";
    $stmt = db()->query($sql);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) {
        foreach ($wanted as $w) {
            if (!array_key_exists($w, $r)) $r[$w] = null;
        }
    }
    return $rows;
}