<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';  // démarre la session tôt + helpers CSRF
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/CartService.php';

$current_user = current_user();

$items = cart_items();      // chaque item: ['id','name','price','qty','image','category']
$total = cart_total();
$count = cart_count();

$page_title = 'Panier - R&G';
require __DIR__ . '/partials/header.php';
?>

<!-- Page Header -->
<header class="page-header">
  <div class="header-content">
    <h1><i class="fas fa-shopping-cart"></i> Panier d'achat</h1>
    <p>Vos articles sélectionnés</p>
  </div>
</header>

<!-- Main Content -->
<main class="main-content">
  <section class="cart-section">
    <div class="cart-container">
      <?php if (!$items): ?>
        <div class="cart-empty">
          <i class="fas fa-shopping-cart"></i>
          <h2>Votre panier est vide</h2>
          <p>Découvrez nos collections pour ajouter des articles</p>
          <a href="<?= $base_path ?>/" class="btn btn-primary">Continuer mes achats</a>
        </div>
      <?php else: ?>
        <div class="cart-items">
          <h2>Articles dans votre panier (<?= (int)$count ?> article<?= $count > 1 ? 's' : '' ?>)</h2>

          <div class="cart-items-list">
            <?php foreach ($items as $it): ?>
              <?php
                $id = (int)$it['id'];
                $name = (string)($it['name'] ?? ('Produit #'.$id));
                $price = (float)($it['price'] ?? 0);
                $qty = (int)($it['qty'] ?? 0);
                $img = $it['image'] ?? null; // déjà normalisé côté cart_add
                $subtotal = $price * $qty;
              ?>
              <div class="cart-item">
                <div class="cart-item-image">
                  <?php if (!empty($img)): ?>
                    <img src="<?= htmlspecialchars((string)$img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                  <?php else: ?>
                    <div class="placeholder-image">
                      <i class="fas fa-image"></i>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="cart-item-details">
                  <h4><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h4>
                  <p class="cart-item-price">Prix unitaire : <?= number_format($price, 2, ',', ' ') ?> €</p>

                  <div class="quantity-controls">
                    <form method="post" action="<?= $base_path ?>/cart_update.php" class="qty-form">
                      <?= csrf_input() ?>
                      <input type="hidden" name="id" value="<?= $id ?>">
                      <button type="button" class="qty-btn" onclick="this.nextElementSibling.stepDown(); this.closest('form').submit();">-</button>
                      <input class="qty-input" type="number" name="qty" min="0" value="<?= $qty ?>">
                      <button type="button" class="qty-btn" onclick="this.previousElementSibling.stepUp(); this.closest('form').submit();">+</button>
                    </form>
                  </div>

                  <p class="cart-item-total">Total : <?= number_format($subtotal, 2, ',', ' ') ?> €</p>
                </div>

                <div class="cart-item-actions">
                  <form method="post" action="<?= $base_path ?>/cart_remove.php">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <button class="remove-btn" type="submit" title="Retirer l'article">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="cart-summary">
            <div class="cart-total">
              <h3>Total : <?= number_format($total, 2, ',', ' ') ?> €</h3>
            </div>
            <div class="cart-actions">
              <a href="<?= $base_path ?>/" class="btn btn-secondary">Continuer mes achats</a>
              <form method="post" action="<?= $base_path ?>/cart_clear.php" style="display:inline">
                <?= csrf_input() ?>
                <button class="btn btn-primary" type="submit">Vider le panier</button>
              </form>
              <!-- Placeholder checkout -->
              <button class="btn btn-primary" type="button" onclick="alert('Fonctionnalité de commande en cours de développement')">Passer commande</button>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<style>
  /* Page Header */
  .page-header {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--gold) 100%);
    color: var(--white);
    padding: 4rem 2rem;
    text-align: center;
  }
  .header-content h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
  }
  .header-content p {
    font-size: 1.2rem;
    opacity: 0.9;
  }

  /* Cart Section */
  .cart-section { padding: 2rem; background-color: var(--white); }
  .cart-container { max-width: 1200px; margin: 0 auto; }

  .cart-empty { text-align: center; padding: 4rem 2rem; color: var(--dark-gray); }
  .cart-empty i { font-size: 4rem; margin-bottom: 1rem; color: var(--primary-blue); }
  .cart-empty h2 { color: var(--primary-blue); margin-bottom: 1rem; }
  .cart-empty p { margin-bottom: 2rem; font-size: 1.1rem; }

  .btn {
    display: inline-block;
    padding: 0.8rem 1.5rem;
    background: var(--primary-blue);
    color: var(--white);
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
  }
  .btn:hover { background: var(--light-blue); transform: translateY(-1px); }
  .btn-primary { background: var(--primary-blue); color: #fff; }
  .btn-secondary { background: #6c757d; color: #fff; }

  /* Items */
  .cart-items-list { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 2rem; }
  .cart-item {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;
  }
  .cart-item-image { width: 80px; height: 80px; border-radius: 8px; overflow: hidden; }
  .cart-item-image img { width: 100%; height: 100%; object-fit: cover; }
  .placeholder-image {
    width: 100%; height: 100%; background: #e9ecef;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: var(--primary-blue);
  }
  .cart-item-details { flex: 1; }
  .cart-item-details h4 { margin: 0 0 0.5rem 0; color: var(--primary-blue); }
  .cart-item-price, .cart-item-total { margin: 0.5rem 0; font-weight: bold; }

  /* Quantity controls (serveur via POST) */
  .quantity-controls { display: flex; align-items: center; gap: .5rem; margin: .5rem 0; }
  .qty-form { display: inline-flex; align-items: center; gap: .5rem; }
  .qty-input { width: 70px; padding: .5rem; text-align: center; }
  .qty-btn {
    background: var(--primary-blue); color: #fff; border: none;
    width: 30px; height: 30px; border-radius: 4px; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
  }

  .cart-item-actions { display: flex; flex-direction: column; gap: 0.5rem; }
  .remove-btn {
    background: #dc3545; color: white; border: none;
    padding: 0.5rem; border-radius: 4px; cursor: pointer;
  }

  .cart-summary { border-top: 2px solid var(--primary-blue); padding-top: 1rem; margin-top: 2rem; }
  .cart-total { text-align: center; margin-bottom: 1rem; }
  .cart-total h3 { color: var(--gold); font-size: 1.5rem; }
  .cart-actions { display: flex; gap: 1rem; justify-content: center; }

  @media (max-width: 768px) {
    .header-content h1 { font-size: 2rem; }
    .cart-item { align-items: flex-start; }
  }
</style>

<?php
require __DIR__ . '/partials/footer.php';