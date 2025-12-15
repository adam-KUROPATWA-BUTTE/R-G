<?php
namespace Models;

use PDO;

/**
 * Order Model
 * Handles order management and checkout
 */
class Order
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Create a new order
     */
    public function create(array $data): int
    {
        $cols = ['user_id', 'total', 'status', 'payment_method', 'created_at'];
        $vals = ['?', '?', '?', '?', '?'];
        $params = [
            $data['user_id'] ?? null,
            $data['total'] ?? 0,
            $data['status'] ?? 'pending',
            $data['payment_method'] ?? 'stripe',
            date('Y-m-d H:i:s')
        ];

        // Add optional fields if they exist in the table
        if ($this->hasColumn('payment_status')) {
            $cols[] = 'payment_status';
            $vals[] = '?';
            $params[] = $data['payment_status'] ?? 'pending';
        }
        if ($this->hasColumn('stripe_session_id')) {
            $cols[] = 'stripe_session_id';
            $vals[] = '?';
            $params[] = $data['stripe_session_id'] ?? null;
        }
        if ($this->hasColumn('shipping_address')) {
            $cols[] = 'shipping_address';
            $vals[] = '?';
            $params[] = $data['shipping_address'] ?? null;
        }

        $sql = "INSERT INTO orders (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get order by ID
     */
    public function getById(int $id): ?array
    {
        $cols = ['id', 'user_id', 'total', 'status', 'payment_method', 'created_at'];
        
        if ($this->hasColumn('payment_status')) $cols[] = 'payment_status';
        if ($this->hasColumn('stripe_session_id')) $cols[] = 'stripe_session_id';
        if ($this->hasColumn('shipping_address')) $cols[] = 'shipping_address';
        if ($this->hasColumn('updated_at')) $cols[] = 'updated_at';

        $sql = "SELECT " . implode(',', $cols) . " FROM orders WHERE id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $order ?: null;
    }

    /**
     * Get order by stripe session ID
     */
    public function getByStripeSession(string $sessionId): ?array
    {
        if (!$this->hasColumn('stripe_session_id')) {
            return null;
        }

        $cols = ['id', 'user_id', 'total', 'status', 'payment_method', 'created_at'];
        if ($this->hasColumn('payment_status')) $cols[] = 'payment_status';
        if ($this->hasColumn('stripe_session_id')) $cols[] = 'stripe_session_id';
        if ($this->hasColumn('shipping_address')) $cols[] = 'shipping_address';

        $sql = "SELECT " . implode(',', $cols) . " FROM orders WHERE stripe_session_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $order ?: null;
    }

    /**
     * Get orders for a user
     */
    public function getByUser(int $userId): array
    {
        $cols = ['id', 'user_id', 'total', 'status', 'payment_method', 'created_at'];
        if ($this->hasColumn('payment_status')) $cols[] = 'payment_status';

        $sql = "SELECT " . implode(',', $cols) . " FROM orders WHERE user_id = ? ORDER BY id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all orders
     */
    public function getAll(): array
    {
        $cols = ['id', 'user_id', 'total', 'status', 'payment_method', 'created_at'];
        if ($this->hasColumn('payment_status')) $cols[] = 'payment_status';

        $sql = "SELECT " . implode(',', $cols) . " FROM orders ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update order status
     */
    public function updateStatus(int $id, string $status): void
    {
        $sql = "UPDATE orders SET status = ?";
        $params = [$status];
        
        if ($this->hasColumn('updated_at')) {
            $sql .= ", updated_at = ?";
            $params[] = date('Y-m-d H:i:s');
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $id, string $paymentStatus): void
    {
        if (!$this->hasColumn('payment_status')) return;

        $sql = "UPDATE orders SET payment_status = ?";
        $params = [$paymentStatus];
        
        if ($this->hasColumn('updated_at')) {
            $sql .= ", updated_at = ?";
            $params[] = date('Y-m-d H:i:s');
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Create order items
     */
    public function createItems(int $orderId, array $items): void
    {
        if (empty($items)) return;

        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        foreach ($items as $item) {
            $stmt->execute([
                $orderId,
                $item['id'] ?? $item['product_id'],
                $item['qty'] ?? $item['quantity'] ?? 1,
                $item['price'] ?? 0,
                $item['size'] ?? null
            ]);
        }
    }

    /**
     * Get order items
     */
    public function getItems(int $orderId): array
    {
        $sql = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if column exists
     */
    private function hasColumn(string $column): bool
    {
        if (function_exists('table_has_column')) {
            return table_has_column('orders', $column);
        }
        
        // Fallback
        $stmt = $this->pdo->query("PRAGMA table_info(orders)");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['name'] === $column) return true;
        }
        return false;
    }
}
