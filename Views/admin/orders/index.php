<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - Administration R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/../../layouts/admin-header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <h1>Gestion des Commandes</h1>
            
            <?php if (!empty($orders)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Total</th>
                            <th>Méthode de paiement</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['user_email'] ?? 'N/A') ?></td>
                                <td><?= number_format($order['total'], 2) ?> €</td>
                                <td><?= htmlspecialchars($order['payment_method'] ?? 'card') ?></td>
                                <td>
                                    <select class="status-select" data-order-id="<?= $order['id'] ?>">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                        <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Payé</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expédié</option>
                                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Livré</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Annulé</option>
                                    </select>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/order_show.php?id=<?= $order['id'] ?>" 
                                       class="btn-icon" 
                                       title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Aucune commande pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="/scripts/admin-orders.js"></script>
</body>
</html>
