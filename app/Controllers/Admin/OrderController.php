<?php
namespace Controllers\Admin;

use Controllers\Controller;
use Models\Database;
use PDO;

/**
 * Admin Order Controller
 * Handles order management in admin panel
 */
class OrderController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->db = Database::getInstance();
    }

    /**
     * Display all orders
     */
    public function index(): void
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    id,
                    total,
                    statut as status,
                    email_client as customer_email,
                    CONCAT(prenom_client, ' ', nom_client) as customer_name,
                    revolut_order_id as payment_reference,
                    stripe_session_id,
                    date_creation as created_at,
                    date_paiement as paid_at
                FROM commandes
                ORDER BY date_creation DESC
                LIMIT 100
            ");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $orders = [];
            $error = 'Erreur lors du chargement des commandes : ' . $e->getMessage();
        }

        $this->view('admin.orders.index', [
            'orders' => $orders,
            'error' => $error ?? null
        ]);
    }

    /**
     * Display single order details
     */
    public function show(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        
        if ($id <= 0) {
            $this->redirect('/admin/orders');
            return;
        }

        try {
            // Get order details
            $stmt = $this->db->prepare("SELECT * FROM commandes WHERE id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                $this->redirect('/admin/orders');
                return;
            }

            // Get order items
            $stmt = $this->db->prepare("SELECT * FROM commande_items WHERE commande_id = ?");
            $stmt->execute([$id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->view('admin.orders.show', [
                'order' => $order,
                'items' => $items
            ]);
        } catch (\Exception $e) {
            $this->redirect('/admin/orders');
        }
    }
}
