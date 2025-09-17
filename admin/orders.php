<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/Services/OrderService.php';

// Vérifier que l'utilisateur est admin
require_admin();

$orderService = new OrderService();

try {
    $orders = $orderService->findAll(100); // Limiter à 100 commandes
} catch (Exception $e) {
    $orders = [];
    $error = 'Erreur lors du chargement des commandes : ' . $e->getMessage();
}

function formatStatus(string $status): array {
    $statusMap = [
        'pending' => ['Attente', 'status-pending'],
        'paid' => ['Payée', 'status-paid'],
        'canceled' => ['Annulée', 'status-canceled'],
        'failed' => ['Échouée', 'status-failed']
    ];
    
    return $statusMap[$status] ?? [$status, 'status-unknown'];
}

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commandes - Admin R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            margin: 0;
            color: #1e3a8a;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #1d4ed8;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            color: #1d4ed8;
            border: 2px solid #1d4ed8;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .orders-table {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .table tr:hover {
            background: #f9fafb;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-paid {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-canceled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .view-btn {
            background: #6366f1;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .view-btn:hover {
            background: #4f46e5;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #f87171;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="orders-container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> Gestion des Commandes</h1>
            <a href="/admin/" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Retour Admin
            </a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= h($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="orders-table">
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>Aucune commande trouvée</h3>
                    <p>Les commandes apparaîtront ici une fois que les clients auront passé des commandes.</p>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Paiement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php [$statusLabel, $statusClass] = formatStatus($order['status']); ?>
                            <tr>
                                <td><strong>#<?= (int)$order['id'] ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td><?= h($order['customer_name'] ?? 'N/A') ?></td>
                                <td><?= h($order['customer_email'] ?? 'N/A') ?></td>
                                <td><strong><?= number_format($order['total'], 2, ',', ' ') ?> €</strong></td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= h($statusLabel) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($order['payment_reference'])): ?>
                                        <span title="<?= h($order['payment_reference']) ?>">
                                            <i class="fas fa-check-circle" style="color: #059669;"></i>
                                        </span>
                                    <?php elseif (!empty($order['stripe_session_id'])): ?>
                                        <span title="Session Stripe créée">
                                            <i class="fas fa-clock" style="color: #d97706;"></i>
                                        </span>
                                    <?php else: ?>
                                        <span title="Pas de paiement">
                                            <i class="fas fa-minus-circle" style="color: #6b7280;"></i>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/order_show.php?id=<?= (int)$order['id'] ?>" class="view-btn">
                                        <i class="fas fa-eye"></i>
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>