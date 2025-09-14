<?php
// Ensure required dependencies are available
if (!function_exists('current_user')) {
    $auth_path = file_exists('../../src/auth.php') ? '../../src/auth.php' : 
                 (file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php');
    require_once $auth_path;
}

if (!function_exists('cart_get')) {
    $functions_path = file_exists('../../src/functions.php') ? '../../src/functions.php' : 
                      (file_exists('../src/functions.php') ? '../src/functions.php' : 'src/functions.php');
    require_once $functions_path;
}

$current_user = current_user();

// Calculate cart count
$cart_count = cart_count();

// Determine base path for assets based on current location
$current_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_path = '';
if (strpos($current_dir, '/partials') !== false) {
    $base_path = '../';
} elseif (strpos($current_dir, '/pages') !== false) {
    $base_path = '../';
} elseif (strpos($current_dir, '/admin') !== false) {
    $base_path = '../public/';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'R&G - Boutique de Mode et Bijoux' ?></title>
    <link rel="stylesheet" href="<?= $base_path ?>styles/main.css">
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css_file): ?>
            <link rel="stylesheet" href="<?= $base_path . htmlspecialchars($css_file) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Icône panier en haut à gauche -->
            <div class="nav-left">
                <a href="<?= $base_path ?>cart.php" class="icon-btn cart-btn" aria-label="Panier">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount"><?= $cart_count ?></span>
                </a>
            </div>
            
            <!-- Logo au centre -->
            <div class="logo-container">
                <a href="<?= $base_path ?>index.php">
                    <img src="<?= $base_path ?>assets/logo.svg" alt="R&G Logo" class="logo">
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
                    <a href="<?= $base_path ?>index.php">Accueil</a>
                    <a href="<?= $base_path ?>pages/femme.html">Vêtements Femme</a>
                    <a href="<?= $base_path ?>pages/homme.html">Vêtements Homme</a>
                    <a href="<?= $base_path ?>pages/bijoux.html">Bijoux</a>
                    <a href="<?= $base_path ?>pages/info.php">Info</a>
                </div>
            </div>
            
            <!-- Icônes utilisateur -->
            <div class="nav-icons">
                <?php if ($current_user): ?>
                    <div class="user-info">
                        <span class="user-greeting">Bonjour, <?= htmlspecialchars($current_user['email']) ?></span>
                        <a href="<?= $base_path ?>logout.php" class="icon-btn" aria-label="Déconnexion">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?= $base_path ?>login.php" class="icon-btn" aria-label="Se connecter">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>