<?php
session_start();
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/csrf.php';
$current_user = current_user();

// Set up page variables for header
$page_title = 'R&G - Boutique de Mode et Bijoux';
$include_scripts = true;
$additional_css = ['styles/carousel-fixes.css'];

require __DIR__ . '/partials/header.php';
?>
    <!-- Main Content -->
    <main class="main-content">
        <section class="hero">
            <div class="hero-content">
                <h1>Bienvenue chez R&G</h1>
                <p>Découvrez notre collection exclusive de vêtements et bijoux</p>
                <a href="<?= $base_path ?>" class="cta-button">Découvrir nos collections</a>
            </div>
        </section>

        <section class="categories-preview">
            <h2>Nos Collections</h2>
            <div class="carousel-container">
                <button class="carousel-btn carousel-btn-prev" id="carouselPrev">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="carousel-wrapper">
                    <div class="carousel-track" id="carouselTrack">
                        <a href="<?= $base_path ?>/vetements-femme.php" class="category-card clickable" data-category="femme">
                            <div class="category-image">
                                <i class="fas fa-female"></i>
                            </div>
                            <h3>Vêtements Femme</h3>
                            <p>Collections élégantes et modernes</p>
                        </a>
                        
                        <a href="<?= $base_path ?>/vetements-homme.php" class="category-card clickable" data-category="homme">
                            <div class="category-image">
                                <i class="fas fa-male"></i>
                            </div>
                            <h3>Vêtements Homme</h3>
                            <p>Style raffiné et sophistiqué</p>
                        </a>
                        
                        <a href="<?= $base_path ?>/bijoux.php" class="category-card clickable" data-category="bijoux">
                            <div class="category-image">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h3>Bijoux</h3>
                            <p>Pièces précieuses et uniques</p>
                        </a>
                        
                        <!-- Modular: Easy to add new categories -->
                        <a href="<?= $base_path ?>/bijoux.php" class="category-card clickable" data-category="accessoires">
                            <div class="category-image">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3>Accessoires</h3>
                            <p>Compléments de style</p>
                        </a>
                        
                        <a href="<?= $base_path ?>/bijoux.php" class="category-card clickable" data-category="nouvelle-collection">
                            <div class="category-image">
                                <i class="fas fa-star"></i>
                            </div>
                            <h3>Nouvelle Collection</h3>
                            <p>Dernières tendances</p>
                        </a>
                    </div>
                </div>
                
                <button class="carousel-btn carousel-btn-next" id="carouselNext">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="carousel-dots" id="carouselDots">
                <!-- Dots will be generated dynamically -->
            </div>
        </section>
    </main>

    <!-- Modal de connexion -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeLogin">&times;</span>
            <h2>Connexion / Inscription</h2>
            <div class="auth-tabs">
                <button class="tab-button active" id="loginTab">Connexion</button>
                <button class="tab-button" id="registerTab">Inscription</button>
            </div>
            
            <form id="loginForm" class="auth-form" action="<?= $base_path ?>/login.php" method="POST">
                <?= csrf_field() ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
            </form>
            
            <form id="registerForm" class="auth-form hidden" action="<?= $base_path ?>/register.php" method="POST">
                <?= csrf_field() ?>
                <input type="text" name="full_name" placeholder="Nom complet" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe" required>
                <button type="submit">S'inscrire</button>
            </form>
        </div>
    </div>

<?php
// Set additional scripts for this page
$additional_scripts = ['scripts/carousel-init.js', 'scripts/app.js', 'scripts/auth.js', 'scripts/promo.js', 'scripts/orders.js'];

// Include footer
require __DIR__ . '/partials/footer.php';
?>