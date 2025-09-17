<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/schema.php';

/**
 * ProductRepository centralise l'accès aux données des produits
 * Refactorisation des fonctions de products dans functions.php
 */
class ProductRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    /**
     * Récupère la liste des produits, optionnellement filtrée par catégorie
     */
    public function getAll(?string $category = null): array
    {
        $available = table_columns('products');
        $availableSet = array_flip($available);

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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un produit par son ID
     */
    public function getById(int $id): ?array
    {
        $available = table_columns('products');
        $availableSet = array_flip($available);

        $wanted = ['id','name','description','price','stock_quantity','image','category','category_id','status','sizes','created_at','updated_at'];
        $cols = array_values(array_filter($wanted, fn($c) => isset($availableSet[$c])));
        if (empty($cols)) $cols = ['id'];

        $sql = "SELECT " . implode(',', $cols) . " FROM products WHERE id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

    /**
     * Crée un nouveau produit
     */
    public function create(array $data): int
    {
        $available = table_columns('products');
        $availableSet = array_flip($available);

        $allowed = ['name','description','price','image','stock_quantity','category','category_id','status','created_at','updated_at'];

        $fields = [];
        foreach ($allowed as $field) {
            if (isset($availableSet[$field]) && array_key_exists($field, $data)) {
                $val = $data[$field];
                if (is_string($val)) $val = trim($val);
                if ($val === '' || $val === null) continue;
                
                // Typage léger
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

        if (empty($fields)) {
            throw new InvalidArgumentException('Aucun champ valide fourni pour créer le produit');
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
     * Met à jour un produit existant
     */
    public function update(int $id, array $data): void
    {
        $available = table_columns('products');
        $availableSet = array_flip($available);

        $allowed = ['name','description','price','image','stock_quantity','category','category_id','status','updated_at'];

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
                    if (in_array($vv, ['1','true','on','actif','active'], true)) $val = 'active';
                    elseif (in_array($vv, ['0','false','off','inactif','inactive'], true)) $val = 'inactive';
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
     * Supprime un produit
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Parse les tailles d'un produit depuis une chaîne
     */
    public function parseSizes($raw): array
    {
        if (!is_string($raw) || $raw === '') return array();
        $parts = explode(',', $raw);
        $out = array();
        foreach ($parts as $p) {
            $p = strtoupper(trim($p));
            if ($p !== '' && !in_array($p, $out, true)) $out[] = $p;
        }
        return $out;
    }
}