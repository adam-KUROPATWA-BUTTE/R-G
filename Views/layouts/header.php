<?php
$current_user = current_user();
$base_path = config()['app']['base_url'] ?? '/';
?>
<header class="main-header">
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?= $base_path ?>" class="logo">
                <img src="/assets/logo.svg" alt="R&G Logo">
                <span>R&G</span>
            </a>
            
            <ul class="nav-menu">
                <li><a href="<?= $base_path ?>">Accueil</a></li>
                <li><a href="/vetements-femme.php">Femme</a></li>
                <li><a href="/vetements-homme.php">Homme</a></li>
                <li><a href="/bijoux.php">Bijoux</a></li>
            </ul>
            
            <div class="nav-actions">
                <?php if ($current_user): ?>
                    <a href="/compte.php" class="nav-icon" title="Mon compte">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($current_user['name'] ?? $current_user['email']) ?></span>
                    </a>
                    <?php if (($current_user['role'] ?? '') === 'admin'): ?>
                        <a href="/admin/" class="nav-icon" title="Administration">
                            <i class="fas fa-cog"></i>
                        </a>
                    <?php endif; ?>
                    <a href="/logout.php" class="nav-icon" title="Déconnexion">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                <?php else: ?>
                    <a href="/login.php" class="nav-icon" title="Connexion">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
                
                <a href="/cart.php" class="nav-icon cart-icon" title="Panier">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>
</header>
