<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administration R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/../layouts/admin-header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <h1>Dashboard Administration</h1>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Produits</h3>
                        <p class="stat-number"><?= $stats['products'] ?? 0 ?></p>
                    </div>
                    <a href="/admin/products.php" class="stat-link">Gérer <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Commandes</h3>
                        <p class="stat-number"><?= $stats['orders'] ?? 0 ?></p>
                    </div>
                    <a href="/admin/orders.php" class="stat-link">Gérer <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Utilisateurs</h3>
                        <p class="stat-number"><?= $stats['users'] ?? 0 ?></p>
                    </div>
                    <a href="/admin/users.php" class="stat-link">Gérer <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Revenus</h3>
                        <p class="stat-number"><?= number_format($stats['revenue'] ?? 0, 2) ?> €</p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($recentOrders)): ?>
                <div class="admin-section">
                    <h2>Commandes récentes</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['user_email'] ?? 'N/A') ?></td>
                                    <td><?= number_format($order['total'], 2) ?> €</td>
                                    <td>
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <a href="/admin/order_show.php?id=<?= $order['id'] ?>" class="btn-small">
                                            Voir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
