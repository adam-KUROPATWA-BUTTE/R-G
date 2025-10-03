<?php

namespace App\Models;

use App\Core\Model;

/**
 * Category Model
 */
class Category extends Model {
    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'description', 'created_at'];
    
    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?array {
        return $this->first(['slug' => $slug]);
    }
    
    /**
     * Get category with products
     */
    public function getWithProducts(int $categoryId): ?array {
        $category = $this->find($categoryId);
        if (!$category) {
            return null;
        }
        
        $sql = "SELECT * FROM products WHERE category_id = ? AND status = 'active'";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$categoryId]);
        $category['products'] = $stmt->fetchAll();
        
        return $category;
    }
    
    /**
     * Get all active categories
     */
    public function getActive(): array {
        return $this->all();
    }
}
