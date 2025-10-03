<?php

namespace App\Models;

use App\Core\Model;

/**
 * Product Model
 */
class Product extends Model {
    protected $table = 'products';
    protected $fillable = [
        'name', 'description', 'price', 'category', 'category_id',
        'image', 'images', 'sizes', 'stock_quantity', 'status'
    ];
    
    /**
     * Get products by category
     */
    public function getByCategory(string $category): array {
        $sql = "SELECT * FROM {$this->table} WHERE category = ? AND status = 'active' ORDER BY created_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get products by category ID
     */
    public function getByCategoryId(int $categoryId): array {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = ? AND status = 'active' ORDER BY created_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active products
     */
    public function getActive(int $limit = 0): array {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY created_at DESC";
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Search products
     */
    public function search(string $query): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (name LIKE ? OR description LIKE ?) AND status = 'active'
                ORDER BY created_at DESC";
        $searchTerm = "%$query%";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if product is in stock
     */
    public function isInStock(int $id): bool {
        $product = $this->find($id);
        return $product && ($product['stock_quantity'] ?? 0) > 0;
    }
    
    /**
     * Decrease stock quantity
     */
    public function decreaseStock(int $id, int $quantity): bool {
        $sql = "UPDATE {$this->table} SET stock_quantity = stock_quantity - ? 
                WHERE {$this->primaryKey} = ? AND stock_quantity >= ?";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([$quantity, $id, $quantity]);
    }
    
    /**
     * Get featured products
     */
    public function getFeatured(int $limit = 6): array {
        return $this->getActive($limit);
    }
}
