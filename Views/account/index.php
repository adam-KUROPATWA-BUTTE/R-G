<?php if (!$currentUser): ?>
    <div class="container">
        <div class="error-message">
            <h2>Accès refusé</h2>
            <p>Vous devez être connecté pour accéder à cette page.</p>
            <a href="/login.php" class="btn-primary">Se connecter</a>
        </div>
    </div>
<?php else: ?>
    <div class="account-container">
        <h1>Mon compte</h1>
        
        <div class="account-content">
            <div class="account-info">
                <h2>Informations personnelles</h2>
                
                <div class="info-row">
                    <span class="label">Nom:</span>
                    <span class="value"><?= htmlspecialchars($currentUser['name'] ?? 'Non renseigné') ?></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value"><?= htmlspecialchars($currentUser['email']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Rôle:</span>
                    <span class="value"><?= htmlspecialchars($currentUser['role'] ?? 'user') ?></span>
                </div>
                
                <?php if (!empty($currentUser['created_at'])): ?>
                    <div class="info-row">
                        <span class="label">Membre depuis:</span>
                        <span class="value"><?= date('d/m/Y', strtotime($currentUser['created_at'])) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($orders)): ?>
                <div class="account-orders">
                    <h2>Mes commandes</h2>
                    
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= number_format($order['total'], 2) ?> €</td>
                                    <td>
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/order.php?id=<?= $order['id'] ?>" class="btn-small">
                                            Voir détails
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Vous n'avez pas encore passé de commande.</p>
                    <a href="/" class="btn-primary">Découvrir nos produits</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
