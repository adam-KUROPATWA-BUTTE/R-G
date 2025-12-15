<?php
$page_title = 'Mon Panier - R&G';
require __DIR__ . '/../layouts/header.php';

// Helper function
function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function csrf_input(): string { return function_exists('csrf_field') ? csrf_field() : ''; }
?>

<!-- Hero Header -->
<header class="cart-hero">
  <div class="hero-content">
    <div class="hero-icon">
      <i class="fas fa-shopping-cart"></i>
    </div>
    <h1>Mon Panier</h1>
    <p class="hero-subtitle">
      <?php if ($count > 0): ?>
        <?= $count ?> article<?= $count > 1 ? 's' : '' ?> dans votre panier
      <?php else: ?>
        Votre sélection de produits
      <?php endif; ?>
    </p>
  </div>
</header>

<!-- Main Content -->
<main class="cart-main">
  <div class="cart-container">
    
    <?php if (empty($items)): ?>
      <!-- Panier Vide -->
      <div class="cart-empty">
        <div class="empty-icon">
          <i class="fas fa-shopping-basket"></i>
        </div>
        <h2>Votre panier est vide</h2>
        <p>Découvrez nos collections exclusives et trouvez vos produits préférés</p>
        <a href="<?= $base_path ?>/" class="btn btn-primary btn-lg">
          <i class="fas fa-arrow-left"></i>
          Découvrir nos produits
        </a>
      </div>
      
    <?php else: ?>
      <!-- Panier avec Articles -->
      <div class="cart-content">
        
        <!-- Actions Globales -->
        <div class="cart-header">
          <h2>Articles sélectionnés</h2>
          <form method="post" action="<?= $base_path ?>/cart/clear" class="clear-cart-form">
            <?= csrf_input() ?>
            <button type="submit" class="btn btn-outline-danger" 
                    onclick="return confirm('Êtes-vous sûr de vouloir vider votre panier ?')">
              <i class="fas fa-trash-alt"></i>
              Vider le panier
            </button>
          </form>
        </div>

        <!-- Liste des Articles -->
        <div class="cart-items">
          <?php foreach ($items as $index => $item): ?>
            <?php
              $id = (int)$item['id'];
              $name = (string)($item['name'] ?? 'Produit #'.$id);
              $price = (float)($item['price'] ?? 0);
              $qty = (int)($item['qty'] ?? $item['quantity'] ?? 1);
              $img = $item['image'] ?? null;
              $size = $item['size'] ?? null;
              $category = $item['category'] ?? null;
              $subtotal = $price * $qty;
            ?>
            
            <div class="cart-item" data-item-id="<?= $id ?>">
              <!-- Image Produit -->
              <div class="item-image">
                <?php if (!empty($img)): ?>
                  <img src="<?= h($img) ?>" alt="<?= h($name) ?>" loading="lazy">
                <?php else: ?>
                  <div class="image-placeholder">
                    <i class="fas fa-image"></i>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Détails Produit -->
              <div class="item-details">
                <div class="item-header">
                  <h3 class="item-name"><?= h($name) ?></h3>
                  <?php if ($category): ?>
                    <span class="item-category"><?= h($category) ?></span>
                  <?php endif; ?>
                </div>
                
                <div class="item-meta">
                  <p class="item-price">
                    <span class="price-label">Prix unitaire :</span>
                    <span class="price-value"><?= number_format($price, 2, ',', ' ') ?> €</span>
                  </p>
                  
                  <?php if (!empty($size)): ?>
                    <p class="item-size">
                      <i class="fas fa-ruler"></i>
                      Taille : <strong><?= h($size) ?></strong>
                    </p>
                  <?php endif; ?>
                </div>

                <!-- Contrôles Quantité -->
                <div class="quantity-section">
                  <form method="post" action="<?= $base_path ?>/cart/update" class="quantity-form">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="item_index" value="<?= $index ?>">
                    
                    <label class="quantity-label">Quantité :</label>
                    <div class="quantity-controls">
                      <button type="button" class="qty-btn qty-minus" onclick="updateQuantity(this, -1)">
                        <i class="fas fa-minus"></i>
                      </button>
                      <input type="number" name="qty" class="qty-input" value="<?= $qty ?>" 
                             min="0" max="99" onchange="this.form.submit()">
                      <button type="button" class="qty-btn qty-plus" onclick="updateQuantity(this, 1)">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Prix & Actions -->
              <div class="item-summary">
                <div class="item-subtotal">
                  <span class="subtotal-label">Sous-total</span>
                  <span class="subtotal-value"><?= number_format($subtotal, 2, ',', ' ') ?> €</span>
                </div>
                
                <form method="post" action="<?= $base_path ?>/cart/remove" class="remove-form">
                  <?= csrf_input() ?>
                  <input type="hidden" name="id" value="<?= $id ?>">
                  <input type="hidden" name="index" value="<?= $index ?>">
                  <button type="submit" class="btn-remove" 
                          onclick="return confirm('Retirer cet article du panier ?')"
                          title="Retirer du panier">
                    <i class="fas fa-times"></i>
                    Retirer
                  </button>
                </form>
              </div>
            </div>
            
          <?php endforeach; ?>
        </div>

        <!-- Résumé & Actions -->
        <div class="cart-footer">
          <div class="cart-summary-box">
            <div class="summary-content">
              <div class="summary-row">
                <span class="summary-label">Sous-total</span>
                <span class="summary-value"><?= number_format($total, 2, ',', ' ') ?> €</span>
              </div>
              <div class="summary-row summary-total">
                <span class="summary-label">Total</span>
                <span class="summary-value total-amount"><?= number_format($total, 2, ',', ' ') ?> €</span>
              </div>
              <p class="summary-note">
                <i class="fas fa-info-circle"></i>
                Les frais de livraison seront calculés à l'étape suivante
              </p>
            </div>

            <div class="cart-actions">
              <a href="<?= $base_path ?>/" class="btn btn-secondary btn-block">
                <i class="fas fa-arrow-left"></i>
                Continuer mes achats
              </a>
              
              <a href="<?= $base_path ?>/checkout" class="btn btn-primary btn-block btn-checkout">
                Passer commande
                <i class="fas fa-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>

      </div>
    <?php endif; ?>
    
  </div>
</main>

<link rel="stylesheet" href="<?= $base_path ?>/public/styles/panier.css">
<script>
function updateQuantity(button, change) {
  const form = button.closest('.quantity-form');
  const input = form.querySelector('.qty-input');
  const currentValue = parseInt(input.value) || 0;
  const newValue = Math.max(0, currentValue + change);
  
  input.value = newValue;
  form.submit();
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
