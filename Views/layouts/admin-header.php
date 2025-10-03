<?php
require_admin();
$current_user = current_user();
?>
<header class="admin-header">
    <nav class="admin-navbar">
        <div class="nav-container">
            <a href="/admin/" class="admin-logo">
                <i class="fas fa-cog"></i>
                <span>Administration R&G</span>
            </a>
            
            <ul class="admin-nav-menu">
                <li><a href="/admin/">Dashboard</a></li>
                <li><a href="/admin/products.php">Produits</a></li>
                <li><a href="/admin/orders.php">Commandes</a></li>
                <li><a href="/admin/users.php">Utilisateurs</a></li>
            </ul>
            
            <div class="admin-nav-actions">
                <a href="/" class="nav-link">
                    <i class="fas fa-home"></i> Retour au site
                </a>
                <span class="admin-user">
                    <?= htmlspecialchars($current_user['name'] ?? $current_user['email']) ?>
                </span>
                <a href="/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>
</header>
