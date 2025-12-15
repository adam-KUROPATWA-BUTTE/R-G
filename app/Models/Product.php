<?php
namespace Models;

use PDO;

/**
 * Product Model
 * Handles all product-related database operations
 */
class Product
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Get all products, optionally filtered by category
     */
    public function getAll(?string $category = null): array
    {
        $available = $this->getTableColumns();
        $availableSet = array_flip($available);

        $wanted = ['id', 'name', 'description', 'price', 'stock_quantity', 'image', 'images', 
                   'category', 'category_id', 'status', 'sizes', 'revolut_payment_link', 
                   'created_at', 'updated_at'];
        $cols = array_values(array_filter($wanted, fn($c) => isset($availableSet[$c])));
        if (empty($cols)) $cols = ['id'];

        $sql = "SELECT " . implode(',', $cols) . " FROM products";
        $params = [];

        if ($category !== null && $category !== '' && isset($availableSet['category'])) {
            $sql .= " WHERE category = ?";
            $params[] = $category;
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get product by ID
     */
    public function getById(int $id): ?array
    {
        $available = $this->getTableColumns();
        $availableSet = array_flip($available);

        $wanted = ['id', 'name', 'description', 'price', 'stock_quantity', 'image', 'images', 
                   'category', 'category_id', 'status', 'sizes', 'revolut_payment_link', 
                   'created_at', 'updated_at'];
        $cols = array_values(array_filter($wanted, fn($c) => isset($availableSet[$c])));
        if (empty($cols)) $cols = ['id'];

        $sql = "SELECT " . implode(',', $cols) . " FROM products WHERE id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

    /**
     * Create a new product
     */
    public function create(array $data): int
    {
        $available = $this->getTableColumns();
        $availableSet = array_flip($available);

        $allowed = ['name', 'description', 'price', 'image', 'images', 'stock_quantity', 
                    'category', 'category_id', 'status', 'sizes', 'created_at', 'updated_at'];

        $fields = [];
        foreach ($allowed as $field) {
            if (isset($availableSet[$field]) && array_key_exists($field, $data)) {
                $val = $data[$field];
                if (is_string($val)) $val = trim($val);
                if ($val === '' || $val === null) continue;
                
                // Type casting
                if ($field === 'stock_quantity' || $field === 'category_id') $val = (int)$val;
                if ($field === 'price') $val = (string)(float)$val;
                if ($field === 'status') {
                    $vv = strtolower((string)$val);
                    if (in_array($vv, ['1', 'true', 'on', 'actif', 'active'], true)) $val = 'active';
                    elseif (in_array($vv, ['0', 'false', 'off', 'inactif', 'inactive'], true)) $val = 'inactive';
                }
                $fields[$field] = $val;
            }
        }

        if (empty($fields)) {
            throw new \InvalidArgumentException('No valid fields provided to create product');
        }

        $keys = array_keys($fields);
        $placeholders = array_map(fn($k) => ':' . $k, $keys);
        $sql = "INSERT INTO products (" . implode(',', $keys) . ") VALUES (" . implode(',', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($fields as $k => $v) {
            $type = PDO::PARAM_STR;
            if (is_int($v)) $type = PDO::PARAM_INT;
            if (is_bool($v)) { $v = (int)$v; $type = PDO::PARAM_INT; }
            $stmt->bindValue(':' . $k, $v, $type);
        }
        $stmt->execute();
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Update an existing product
     */
    public function update(int $id, array $data): void
    {
        $available = $this->getTableColumns();
        $availableSet = array_flip($available);

        $allowed = ['name', 'description', 'price', 'image', 'images', 'stock_quantity', 
                    'category', 'category_id', 'status', 'sizes', 'updated_at'];

        $fields = [];
        foreach ($allowed as $field) {
            if (isset($availableSet[$field]) && array_key_exists($field, $data)) {
                $val = $data[$field];
                if (is_string($val)) $val = trim($val);
                if ($val === '' || $val === null) continue;
                
                if ($field === 'stock_quantity' || $field === 'category_id') $val = (int)$val;
                if ($field === 'price') $val = (string)(float)$val;
                if ($field === 'status') {
                    $vv = strtolower((string)$val);
                    if (in_array($vv, ['1', 'true', 'on', 'actif', 'active'], true)) $val = 'active';
                    elseif (in_array($vv, ['0', 'false', 'off', 'inactif', 'inactive'], true)) $val = 'inactive';
                }
                $fields[$field] = $val;
            }
        }

        if (empty($fields)) return;

        $sets = array_map(fn($c) => $c . ' = :' . $c, array_keys($fields));
        $sql = "UPDATE products SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($fields as $k => $v) {
            $type = PDO::PARAM_STR;
            if (is_int($v)) $type = PDO::PARAM_INT;
            if (is_bool($v)) { $v = (int)$v; $type = PDO::PARAM_INT; }
            $stmt->bindValue(':' . $k, $v, $type);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Delete a product
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Parse sizes from string
     */
    public function parseSizes($raw): array
    {
        if (!is_string($raw) || $raw === '') return [];
        $parts = preg_split('/[,;|]/', $raw);
        $out = [];
        foreach ($parts as $p) {
            $p = strtoupper(trim($p));
            if ($p !== '' && !in_array($p, $out, true)) $out[] = $p;
        }
        return $out;
    }

    /**
     * Get table columns
     */
    private function getTableColumns(): array
    {
        if (function_exists('table_columns')) {
            return table_columns('products');
        }
        
        // Fallback: get columns from database
        $stmt = $this->pdo->query("PRAGMA table_info(products)");
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['name'];
        }
        return $columns;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(int $id): bool
    {
        $product = $this->getById($id);
        if (!$product) return false;
        return (int)($product['stock_quantity'] ?? 0) > 0;
    }

    /**
     * Decrease stock quantity
     */
    public function decreaseStock(int $id, int $quantity): void
    {
        $sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$quantity, $id]);
    }
}
