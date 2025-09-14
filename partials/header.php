<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/cart.php';

$current_user = current_user();
$cart_count = cart_count();

// Compute base path dynamically from script name for subdirectory support
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_path = '';
if ($script_dir !== '/') {
    $base_path = $script_dir;
}
// Ensure base_path ends without slash for consistency
$base_path = rtrim($base_path, '/');

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
    <link rel="stylesheet" href="<?= $base_path ?>/styles/main.css">
    <link rel="stylesheet" href="<?= $base_path ?>/styles/auth.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css_file): ?>
            <link rel="stylesheet" href="<?= $base_path ?>/<?= htmlspecialchars($css_file) ?>">
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
                <?php if ($current_user): ?>
                    <a href="<?= $base_path ?>/compte.php" class="icon-btn user-btn user-icon" aria-label="Mon Compte">
                        <i class="fas fa-user-circle"></i>
                    </a>
                <?php else: ?>
                    <a href="<?= $base_path ?>/login.php" class="icon-btn user-btn user-icon" aria-label="Connexion">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
                <a href="<?= $base_path ?>/cart.php" class="icon-btn cart-btn" aria-label="Panier">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount" data-cart-count="<?= $cart_count ?>"><?= $cart_count ?></span>
                </a>
            </div>
            
            <!-- Logo au centre -->
            <div class="logo-container">
                <a href="<?= $base_path ?>/">
                    <img src="<?= $base_path ?>/assets/logo.svg" alt="R&G Logo" class="logo">
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
                    <a href="<?= $base_path ?>/">Accueil</a>
                    <a href="<?= $base_path ?>/vetements-femme.php">Vêtements Femme</a>
                    <a href="<?= $base_path ?>/vetements-homme.php">Vêtements Homme</a>
                    <a href="<?= $base_path ?>/bijoux.php">Bijoux</a>
                    <?php if ($current_user && ($current_user['role'] ?? '') === 'admin'): ?>
                        <a href="<?= $base_path ?>/admin/">Administration</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>