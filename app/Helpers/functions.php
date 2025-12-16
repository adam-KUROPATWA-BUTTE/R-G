<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema.php';

// Si dbh() est déjà défini ailleurs, éviter la redéclaration
if (!function_exists('dbh')) {
    function dbh(): PDO {
        return db();
    }
}

/* =========================
   Produits
   ========================= */

function products_list(?string $category = null): array {
    $available = table_columns('products');       // ex: ['id','name','price','category',...]
    $availableSet = array_flip($available);       // set: ['id'=>0,'name'=>1,...]

    // Adapter cette liste à ton schéma réel
    $wanted = ['id','name','description','price','stock_quantity','image','category','category_id','status','created_at','updated_at'];
    $cols = array_values(array_filter($wanted, fn($c) => isset($availableSet[$c])));
    if (empty($cols)) $cols = ['id'];

    $sql = "SELECT " . implode(',', $cols) . " FROM products";
    $params = [];

    if ($category !== null && $category !== '' && isset($availableSet['category'])) {
        $sql .= " WHERE category = ?";
        $params[] = $category;
    }

    $sql .= " ORDER BY id DESC";
    $stmt = dbh()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function product_get(int $id): ?array {
    $available = table_columns('products');
    $availableSet = array_flip($available);

    $wanted = ['id','name','description','price','stock_quantity','image','category','category_id','status','created_at','updated_at'];
    $cols = array_values(array_filter($wanted, fn($c) => isset($availableSet[$c])));
    if (empty($cols)) $cols = ['id'];

    $sql = "SELECT " . implode(',', $cols) . " FROM products WHERE id = ? LIMIT 1";
    $stmt = dbh()->prepare($sql);
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    return $p ?: null;
}

function product_create(array $d): int {
    // Remplir uniquement les champs existants dans la table
    $available = table_columns('products');
    $availableSet = array_flip($available);

    // Champs autorisés (adapte si besoin)
    $allowed = ['name','description','price','image','stock_quantity','category','category_id','status','created_at','updated_at'];

    $fields = [];
    foreach ($allowed as $field) {
        if (isset($availableSet[$field]) && array_key_exists($field, $d)) {
            $val = $d[$field];
            if (is_string($val)) $val = trim($val);
            if ($val === '' || $val === null) continue;
            // Typage léger
            if ($field === 'stock_quantity' || $field === 'category_id') $val = (int)$val;
            if ($field === 'price') $val = (string)(float)$val; // DECIMAL -> string
            if ($field === 'status') {
                $vv = strtolower((string)$val);
                if (in_array($vv, ['1','true','on','actif','active'], true)) $val = 'active';
                elseif (in_array($vv, ['0','false','off','inactif','inactive'], true)) $val = 'inactive';
            }
            $fields[$field] = $val;
        }
    }

    if (empty($fields)) {
        $received = implode(', ', array_keys($d));
        $expected = implode(', ', array_intersect($allowed, $available));
        throw new RuntimeException("Aucun champ valide pour l'insertion produit. Reçu: {$received}. Attendus (et existant): {$expected}");
    }

    $cols = array_keys($fields);
    $placeholders = array_map(fn($c) => ':' . $c, $cols);
    $sql = "INSERT INTO products (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
    $stmt = dbh()->prepare($sql);
    foreach ($fields as $k => $v) {
        $type = PDO::PARAM_STR;
        if (is_int($v)) $type = PDO::PARAM_INT;
        if (is_bool($v)) { $v = (int)$v; $type = PDO::PARAM_INT; }
        $stmt->bindValue(':' . $k, $v, $type);
    }
    $stmt->execute();
    return (int)dbh()->lastInsertId();
}

function product_update(int $id, array $d): void {
    $available = table_columns('products');
    $availableSet = array_flip($available);

    $allowed = ['name','description','price','image','stock_quantity','category','category_id','status','updated_at'];

    $fields = [];
    foreach ($allowed as $field) {
        if (isset($availableSet[$field]) && array_key_exists($field, $d)) {
            $val = $d[$field];
            if (is_string($val)) $val = trim($val);
            if ($val === '' || $val === null) continue;
            if ($field === 'stock_quantity' || $field === 'category_id') $val = (int)$val;
            if ($field === 'price') $val = (string)(float)$val;
            if ($field === 'status') {
                $vv = strtolower((string)$val);
                if (in_array($vv, ['1','true','on','actif','active'], true)) $val = 'active';
                elseif (in_array($vv, ['0','false','off','inactif','inactive'], true)) $val = 'inactive';
            }
            $fields[$field] = $val;
        }
    }

    if (empty($fields)) return;

    $sets = array_map(fn($c) => $c . ' = :' . $c, array_keys($fields));
    $sql = "UPDATE products SET " . implode(', ', $sets) . " WHERE id = :id";
    $stmt = dbh()->prepare($sql);
    foreach ($fields as $k => $v) {
        $type = PDO::PARAM_STR;
        if (is_int($v)) $type = PDO::PARAM_INT;
        if (is_bool($v)) { $v = (int)$v; $type = PDO::PARAM_INT; }
        $stmt->bindValue(':' . $k, $v, $type);
    }
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
}

function product_delete(int $id): void {
    $stmt = dbh()->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

/* =========================
   Utilisateurs (admin)
   ========================= */

function users_list(): array {
    $available = table_columns('users');
    $availableSet = array_flip($available);

    $wanted = ['id','email','role','first_name','last_name','created_at'];
    $cols = array_values(array_filter($wanted, fn($c) => isset($availableSet[$c])));
    if (empty($cols)) $cols = ['id'];

    $sql = "SELECT " . implode(',', $cols) . " FROM users ORDER BY id DESC";
    $stmt = dbh()->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($rows as &$r) {
        foreach ($wanted as $w) {
            if (!array_key_exists($w, $r)) $r[$w] = null;
        }
    }
    unset($r);

    return $rows;
}


function product_parse_sizes($raw) {
    if (!is_string($raw) || $raw === '') return array();
    $parts = explode(',', $raw);
    $out = array();
    foreach ($parts as $p) {
        $p = strtoupper(trim($p));
        if ($p !== '' && !in_array($p, $out, true)) $out[] = $p;
    }
    return $out;
}

// Cart functions
require_once __DIR__ . '/CartService.php';