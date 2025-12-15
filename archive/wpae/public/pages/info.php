<?php
// Handle both direct access (public/pages/info.php) and inclusion from root
$auth_path = file_exists('../../src/auth.php') ? '../../src/auth.php' : 
             (file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php');
$functions_path = file_exists('../../src/functions.php') ? '../../src/functions.php' : 
                  (file_exists('../src/functions.php') ? '../src/functions.php' : 'src/functions.php');

require_once $auth_path;
require_once $functions_path;

// Get info page content from database
$pdo = db();
try {
    $stmt = $pdo->prepare('SELECT * FROM page_content WHERE page_name = ?');
    $stmt->execute(['info']);
    $info_page = $stmt->fetch();
} catch (PDOException $e) {
    // Table might not exist yet, use default content
    $info_page = false;
}

// Default content if not in database
if (!$info_page) {
    $info_page = [
        'title' => 'À propos de R&G',
        'content' => 'Bienvenue dans l\'univers de R&G, où chaque pièce est pensée et créée avec soin.

Diplômée en couture, R&G propose des vêtements entièrement faits sur mesure, pensés pour s\'adapter à votre morphologie, à vos envies et à votre style. Ici, pas de production en série ni de stock : chaque création est unique, à votre image.

En parallèle, elle imagine et assemble également une collection de bijoux en acier inoxydable, pour sublimer vos tenues avec des pièces durables, élégantes et accessibles.

Que vous soyez à la recherche d\'un vêtement sur-mesure ou d\'un bijou coup de cœur, vous êtes au bon endroit. Merci de soutenir l\'artisanat local et la création indépendante ❤️'
    ];
}

// Set up page variables for header
$page_title = htmlspecialchars($info_page['title']) . ' - R&G';

// Include header
require_once '../partials/header.php';
?>

    <!-- Page Header -->
    <header class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-info-circle"></i> <?= htmlspecialchars($info_page['title']) ?></h1>
            <p>Découvrez l'univers unique de notre atelier de création</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <section class="info-section">
            <div class="info-container">
                <div class="info-content">
                    <div class="info-text">
                        <?php 
                        // Convert line breaks to paragraphs
                        $content = htmlspecialchars($info_page['content']);
                        $paragraphs = explode("\n\n", $content);
                        foreach ($paragraphs as $paragraph) {
                            $paragraph = trim($paragraph);
                            if (!empty($paragraph)) {
                                echo '<p>' . nl2br($paragraph) . '</p>' . "\n";
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-stats">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Fait avec passion</h3>
                            <p>Chaque pièce est créée avec soin et attention</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Sur-mesure</h3>
                            <p>Adapté à votre morphologie et votre style</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="stat-content">
                            <h3>Qualité premium</h3>
                            <p>Matériaux nobles et finitions d'exception</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-section">
            <div class="contact-container">
                <h2>Contactez-nous</h2>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>contact@rg-boutique.fr</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+33 1 23 45 67 89</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Sur rendez-vous uniquement</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php
// Include footer
require_once '../partials/footer.php';
?>