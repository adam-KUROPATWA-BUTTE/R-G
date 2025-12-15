<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';

/**
 * OrderService - Gestion des commandes
 * Crée et gère les commandes avec leurs items
 */
class OrderService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    /**
     * Créer une commande à partir du panier
     * 
     * @param array $cart Panier avec structure ['items' => [...]]
     * @param array $customer Informations client ['name', 'email', 'address']
     * @param string $status Statut initial de la commande
     * @return int ID de la commande créée
     */
    public function createFromCart(array $cart, array $customer, string $status = 'pending'): int
    {
        $items = $cart['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Le panier est vide');
        }

        $totalCents = 0;
        $orderItems = [];

        // Calculer le total et préparer les items
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            
            $qty = (int)($item['qty'] ?? 0);
            $price = (float)($item['price'] ?? 0);
            
            if ($qty <= 0 || $price < 0) continue;
            
            $itemTotalCents = (int)round($price * $qty * 100);
            $totalCents += $itemTotalCents;
            
            $orderItems[] = [
                'product_id' => (int)($item['id'] ?? $item['product_id'] ?? 0),
                'product_name' => (string)($item['name'] ?? 'Produit sans nom'),
                'size' => !empty($item['size']) ? (string)$item['size'] : null,
                'unit_price_cents' => (int)round($price * 100),
                'quantity' => $qty
            ];
        }

        if (empty($orderItems)) {
            throw new InvalidArgumentException('Aucun item valide dans le panier');
        }

        try {
            $this->pdo->beginTransaction();

            // Créer la commande
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (total_cents, customer_name, customer_email, customer_address, status) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $totalCents,
                $customer['name'] ?? null,
                $customer['email'] ?? null,  
                $customer['address'] ?? null,
                $status
            ]);

            $orderId = (int)$this->pdo->lastInsertId();

            // Créer les items de commande
            $stmt = $this->pdo->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, size, unit_price_cents, quantity)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($orderItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['product_name'],
                    $item['size'],
                    $item['unit_price_cents'],
                    $item['quantity']
                ]);
            }

            $this->pdo->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Récupérer toutes les commandes
     * 
     * @param int $limit Limite du nombre de résultats
     * @return array Liste des commandes
     */
    public function findAll(int $limit = 200): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, created_at, updated_at, status, total_cents, 
                   customer_name, customer_email, stripe_session_id, payment_reference
            FROM orders 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir les centimes en euros pour l'affichage
        foreach ($orders as &$order) {
            $order['total'] = $order['total_cents'] / 100;
        }
        
        return $orders;
    }

    /**
     * Récupérer une commande avec ses items
     * 
     * @param int $id ID de la commande
     * @return array|null Commande avec items ou null si non trouvée
     */
    public function find(int $id): ?array
    {
        // Récupérer la commande
        $stmt = $this->pdo->prepare("
            SELECT id, created_at, updated_at, status, total_cents,
                   customer_name, customer_email, customer_address,
                   stripe_session_id, payment_reference
            FROM orders 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return null;
        }

        // Convertir les centimes en euros
        $order['total'] = $order['total_cents'] / 100;

        // Récupérer les items
        $stmt = $this->pdo->prepare("
            SELECT id, product_id, product_name, size, unit_price_cents, quantity
            FROM order_items 
            WHERE order_id = ?
            ORDER BY id
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir les prix des items en euros
        foreach ($items as &$item) {
            $item['unit_price'] = $item['unit_price_cents'] / 100;
            $item['total_price'] = ($item['unit_price_cents'] * $item['quantity']) / 100;
        }

        $order['items'] = $items;
        return $order;
    }

    /**
     * Marquer une commande comme payée
     * 
     * @param int $id ID de la commande
     * @param string|null $paymentRef Référence de paiement (ex: Stripe Payment Intent)
     */
    public function markPaid(int $id, ?string $paymentRef = null): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE orders 
            SET status = 'paid', payment_reference = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$paymentRef, $id]);
    }

    /**
     * Mettre à jour le statut d'une commande
     * 
     * @param int $id ID de la commande
     * @param string $status Nouveau statut
     */
    public function updateStatus(int $id, string $status): void
    {
        $allowedStatuses = ['pending', 'paid', 'canceled', 'failed'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new InvalidArgumentException("Statut invalide: $status");
        }

        $stmt = $this->pdo->prepare("
            UPDATE orders 
            SET status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$status, $id]);
    }

    /**
     * Lier une session Stripe à une commande
     * 
     * @param int $id ID de la commande
     * @param string $sessionId ID de la session Stripe
     */
    public function setStripeSession(int $id, string $sessionId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE orders 
            SET stripe_session_id = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$sessionId, $id]);
    }

    /**
     * Trouver une commande par session Stripe
     * 
     * @param string $sessionId ID de la session Stripe
     * @return array|null Commande ou null si non trouvée
     */
    public function findByStripeSession(string $sessionId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, created_at, updated_at, status, total_cents,
                   customer_name, customer_email, customer_address,
                   stripe_session_id, payment_reference
            FROM orders 
            WHERE stripe_session_id = ?
        ");
        $stmt->execute([$sessionId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            $order['total'] = $order['total_cents'] / 100;
        }
        
        return $order ?: null;
    }
}