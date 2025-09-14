<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';

$current_user = current_user();
$cart_count = cart_count();

// Determine display name
$displayName = '';
if ($current_user) {
    $displayName = $current_user['first_name'] ?? '';
    if ($displayName === '' || $displayName === null) {
        $displayName = $current_user['email'] ?? 'Utilisateur';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'R&G - Boutique de Mode et Bijoux' ?></title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/auth.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css_file): ?>
            <link rel="stylesheet" href="/<?= htmlspecialchars($css_file) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- User icon and cart in top left -->
            <div class="nav-left">
                <a href="/login.php" class="icon-btn user-btn" aria-label="Connexion/Profil">
                    <i class="fas fa-user"></i>
                </a>
                <a href="/cart.php" class="icon-btn cart-btn" aria-label="Panier">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount"><?= $cart_count ?></span>
                </a>
            </div>
            
            <!-- Logo au centre -->
            <div class="logo-container">
                <a href="/">
                    <img src="/assets/logo.svg" alt="R&G Logo" class="logo">
                </a>
            </div>
            
            <!-- Menu déroulant avec 3 étoiles -->
            <div class="menu-dropdown">
                <button class="menu-trigger" id="menuTrigger">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </button>
                <div class="dropdown-content" id="dropdownContent">
                    <a href="/">Accueil</a>
                    <a href="/vetements-femme.php">Vêtements Femme</a>
                    <a href="/vetements-homme.php">Vêtements Homme</a>
                    <a href="/bijoux.php">Bijoux</a>
                </div>
            </div>
            
            <!-- User authentication info -->
            <div class="nav-icons">
                <?php if ($current_user): ?>
                    <div class="user-info">
                        <span class="user-greeting">Bonjour, <?= htmlspecialchars($displayName) ?></span>
                        <?php if (($current_user['role'] ?? '') === 'admin'): ?>
                            <a href="/admin/" class="admin-link">Admin</a>
                        <?php endif; ?>
                        <a href="/logout.php" class="logout-link" aria-label="Déconnexion">
                            Déconnexion
                        </a>
                    </div>
                <?php else: ?>
                    <div class="auth-links">
                        <a href="/login.php" class="login-link">Se connecter</a>
                        <a href="/register.php" class="register-link">S'inscrire</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>