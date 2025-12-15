<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/bootstrap.php';

// Vérification admin
$u = current_user();
if (!$u) {
    die("❌ Pas d'utilisateur connecté. <a href='/login.php'>Se connecter</a>");
}
if (($u['role'] ?? '') !== 'admin') {
    die("❌ Accès refusé. Votre rôle: " . ($u['role'] ?? 'aucun') . ". Attendu: admin");
}

require_admin();

// Récupérer les commandes
try {
    $pdo = db();
    $stmt = $pdo->query("
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
} catch (Exception $e) {
    $orders = [];
    $error = 'Erreur lors du chargement des commandes : ' . $e->getMessage();
}

function formatStatus(string $status): array {
    $statusMap = [
        'pending' => ['En attente', 'status-pending'],
        'paid' => ['Payée', 'status-paid'],
        'cancelled' => ['Annulée', 'status-canceled'],
        'canceled' => ['Annulée', 'status-canceled'],
        'failed' => ['Échouée', 'status-failed']
    ];
    
    return $statusMap[$status] ?? [$status, 'status-unknown'];
}

function h(string $v): string { 
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commandes - Admin R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .orders-container {
            max-width: 1400px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #1e3a8a;
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
        
        /* Icônes de paiement */
        .payment-icon {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 1.2rem;
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
        
        <?php
        // Calculer les statistiques
        $totalOrders = count($orders);
        $totalRevenue = array_sum(array_map(fn($o) => (float)$o['total'], $orders));
        $paidOrders = count(array_filter($orders, fn($o) => $o['status'] === 'paid'));
        $pendingOrders = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="fas fa-shopping-cart"></i> Total Commandes</h3>
                <div class="value"><?= $totalOrders ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-euro-sign"></i> Chiffre d'affaires</h3>
                <div class="value"><?= number_format((float)$totalRevenue, 2, ',', ' ') ?> €</div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-check-circle"></i> Commandes Payées</h3>
                <div class="value" style="color: #059669;"><?= $paidOrders ?></div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-clock"></i> En Attente</h3>
                <div class="value" style="color: #d97706;"><?= $pendingOrders ?></div>
            </div>
        </div>
        
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
                                <td><strong><?= number_format((float)$order['total'], 2, ',', ' ') ?> €</strong></td>
                                <td>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= h($statusLabel) ?>
                                    </span>
                                </td>
                                <td class="payment-icon">
                                    <?php if (!empty($order['stripe_session_id'])): ?>
                                        <i class="fab fa-stripe" title="Stripe" style="color: #635bff; font-size: 1.5rem;"></i>
                                    <?php elseif (!empty($order['payment_reference'])): ?>
                                        <i class="fas fa-credit-card" title="Revolut" style="color: #0075eb;"></i>
                                    <?php elseif ($order['status'] === 'pending'): ?>
                                        <i class="fas fa-clock" title="En attente de paiement" style="color: #d97706;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-ban" title="Pas de paiement" style="color: #9ca3af;"></i>
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