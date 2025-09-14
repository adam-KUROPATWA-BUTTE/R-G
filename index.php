<?php
require_once __DIR__ . '/src/auth.php';
$u = current_user();
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>R&G - Accueil</title>
  <link rel="stylesheet" href="/styles/main.css">
  <link rel="stylesheet" href="/styles/auth.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <?php require __DIR__ . '/partials/header.php'; ?>

  <main class="main-content">
    <section class="hero">
      <div class="hero-content">
        <h1>Bienvenue chez R&G</h1>
        <p>Découvrez notre collection de bijoux</p>
        <a class="cta-button" href="/bijoux.php">Voir les bijoux</a>
      </div>
    </section>

    <!-- Ici, gardez ou nettoyez vos blocs existants (carrousels, etc.)
         Aucun listing de produits ici (bijoux.php s’en charge depuis la BDD). -->
  </main>

  <?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>