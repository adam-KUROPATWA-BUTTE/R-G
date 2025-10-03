<?php

namespace App\Models;

use App\Core\Model;

/**
 * Order Model
 */
class Order extends Model {
    protected $table = 'orders';
    protected $fillable = [
        'user_id', 'total', 'payment_method', 'status',
        'shipping_address', 'billing_address', 'created_at'
    ];
    
    /**
     * Get orders by user
     */
    public function getByUser(int $userId): array {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get order with items
     */
    public function getWithItems(int $orderId): ?array {
        $order = $this->find($orderId);
        if (!$order) {
            return null;
        }
        
        // Get order items
        $sql = "SELECT oi.*, p.name as product_name, p.image as product_image
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll();
        
        return $order;
    }
    
    /**
     * Create order with items
     */
    public function createWithItems(array $orderData, array $items): int {
        // Start transaction
        $this->db()->beginTransaction();
        
        try {
            // Create order
            $orderId = $this->create($orderData);
            
            // Insert order items
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->db()->prepare($sql);
            
            foreach ($items as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }
            
            $this->db()->commit();
            return $orderId;
            
        } catch (\Exception $e) {
            $this->db()->rollBack();
            throw $e;
        }
    }
    
    /**
     * Update order status
     */
    public function updateStatus(int $orderId, string $status): bool {
        return $this->update($orderId, ['status' => $status]);
    }
    
    /**
     * Get recent orders
     */
    public function getRecent(int $limit = 10): array {
        return $this->all([], $limit);
    }
    
    /**
     * Get pending orders
     */
    public function getPending(): array {
        return $this->where(['status' => 'pending']);
    }
}
