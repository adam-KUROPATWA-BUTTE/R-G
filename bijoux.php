<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/functions.php';
$u = current_user();

// Charger les produits depuis la BDD (catégorie 'bijoux' si vous l'utilisez)
try {
    // Si votre table products possède une colonne "category", décommentez:
    // $products = products_list('bijoux');
    $products = products_list(); // sinon, liste complète
} catch (Throwable $e) {
    $products = [];
    $error = 'Erreur lors du chargement des produits.';
}
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bijoux - R&G</title>
  <link rel="stylesheet" href="/styles/main.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <?php require __DIR__ . '/partials/header.php'; ?>

  <main class="main-content">
    <section class="products-section">
      <div class="products-container">
        <h2>Nos Bijoux</h2>
        <?php if (!empty($error)): ?>
          <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
          <p>Aucun produit disponible pour le moment.</p>
        <?php else: ?>
          <div class="products-grid">
            <?php foreach ($products as $p): ?>
              <article class="product-card">
                <div class="product-image" style="text-align:center;padding:1rem;background:#f7f7f7;">
                  <?php if (!empty($p['image_url'])): ?>
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name'] ?? 'Produit') ?>" style="max-width:100%;height:auto;">
                  <?php else: ?>
                    <i class="fas fa-gem" style="font-size:48px;color:#d1b000;"></i>
                  <?php endif; ?>
                </div>
                <div class="product-content" style="padding:1rem;">
                  <h3><?= htmlspecialchars($p['name'] ?? 'Produit') ?></h3>
                  <?php if (isset($p['price'])): ?>
                    <p class="price" style="font-weight:bold;color:#1e3a8a;"><?= number_format((float)$p['price'], 2, ',', ' ') ?> €</p>
                  <?php endif; ?>
                  <?php if (!empty($p['description'])): ?>
                    <p><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                  <?php endif; ?>
                  <?php if (isset($p['stock'])): ?>
                    <p style="font-size:.9rem;color:#666;">Stock: <?= (int)$p['stock'] ?></p>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>