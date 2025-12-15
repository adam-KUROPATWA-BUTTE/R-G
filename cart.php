<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/CartService.php';

$current_user = current_user();

// Récupérer les données du panier
$items = cart_items();
$total = cart_total();
$count = cart_count();

$page_title = 'Mon Panier - R&G';
require __DIR__ . '/partials/header.php';
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
          <form method="post" action="<?= $base_path ?>/cart_clear.php" class="clear-cart-form">
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
                  <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" 
                       alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                       loading="lazy">
                <?php else: ?>
                  <div class="image-placeholder">
                    <i class="fas fa-image"></i>
                  </div>
                <?php endif; ?>
              </div>

              <!-- Détails Produit -->
              <div class="item-details">
                <div class="item-header">
                  <h3 class="item-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>
                  <?php if ($category): ?>
                    <span class="item-category"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span>
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
                      Taille : <strong><?= htmlspecialchars($size, ENT_QUOTES, 'UTF-8') ?></strong>
                    </p>
                  <?php endif; ?>
                </div>

                <!-- Contrôles Quantité -->
                <div class="quantity-section">
                  <form method="post" action="<?= $base_path ?>/cart_update.php" class="quantity-form">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="item_index" value="<?= $index ?>">
                    
                    <label class="quantity-label">Quantité :</label>
                    <div class="quantity-controls">
                      <button type="button" class="qty-btn qty-minus" 
                              onclick="updateQuantity(this, -1)">
                        <i class="fas fa-minus"></i>
                      </button>
                      <input type="number" 
                             name="qty" 
                             class="qty-input" 
                             value="<?= $qty ?>" 
                             min="0" 
                             max="99"
                             onchange="this.form.submit()">
                      <button type="button" class="qty-btn qty-plus" 
                              onclick="updateQuantity(this, 1)">
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
                
                <form method="post" action="<?= $base_path ?>/cart_remove.php" class="remove-form">
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
              
              <a href="<?= $base_path ?>/checkout.php" class="btn btn-primary btn-block btn-checkout">
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

<style>
:root {
  --primary-blue: #1e3a8a;
  --light-blue: #3b82f6;
  --gold: #d4af37;
  --dark-gray: #333;
  --light-gray: #f8f9fa;
  --border-color: #e5e7eb;
  --danger: #dc3545;
  --success: #28a745;
  --white: #ffffff;
  --shadow: 0 2px 8px rgba(0,0,0,0.1);
  --shadow-lg: 0 4px 16px rgba(0,0,0,0.15);
}

/* Hero Header */
.cart-hero {
  background: linear-gradient(135deg, var(--primary-blue) 0%, var(--gold) 100%);
  color: var(--white);
  padding: 4rem 2rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}


.cart-hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%);
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: 0 auto;
}

.hero-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.hero-content h1 {
  font-size: 3.5rem;
  margin-bottom: 0.5rem;
  font-weight: 700;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.hero-subtitle {
  font-size: 1.3rem;
  opacity: 0.95;
}

/* Main Container */
.cart-main {
  padding: 3rem 2rem;
  background: linear-gradient(to bottom, var(--light-gray) 0%, var(--white) 100%);
  min-height: 60vh;
}

.cart-container {
  max-width: 1400px;
  margin: 0 auto;
}

/* Panier Vide */
.cart-empty {
  text-align: center;
  padding: 5rem 2rem;
  background: var(--white);
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
}

.empty-icon {
  font-size: 6rem;
  color: var(--primary-blue);
  margin-bottom: 2rem;
  opacity: 0.3;
}

.cart-empty h2 {
  font-size: 2.5rem;
  color: var(--dark-gray);
  margin-bottom: 1rem;
}

.cart-empty p {
  font-size: 1.2rem;
  color: #6b7280;
  margin-bottom: 2.5rem;
}

/* Cart Header */
.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--border-color);
}

.cart-header h2 {
  font-size: 2rem;
  color: var(--primary-blue);
  margin: 0;
}

/* Cart Items */
.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.cart-item {
  display: grid;
  grid-template-columns: 120px 1fr auto;
  gap: 2rem;
  padding: 2rem;
  background: var(--white);
  border-radius: 12px;
  box-shadow: var(--shadow);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.cart-item:hover {
  box-shadow: var(--shadow-lg);
  border-color: var(--light-blue);
  transform: translateY(-2px);
}

/* Item Image */
.item-image {
  width: 120px;
  height: 120px;
  border-radius: 8px;
  overflow: hidden;
  background: var(--light-gray);
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.cart-item:hover .item-image img {
  transform: scale(1.05);
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: var(--primary-blue);
  background: linear-gradient(135deg, #f0f4f8 0%, #e5e7eb 100%);
}

/* Item Details */
.item-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.item-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.item-name {
  font-size: 1.5rem;
  color: var(--primary-blue);
  margin: 0;
  font-weight: 600;
}

.item-category {
  font-size: 0.85rem;
  padding: 0.25rem 0.75rem;
  background: var(--gold);
  color: var(--white);
  border-radius: 20px;
  font-weight: 600;
}

.item-meta {
  display: flex;
  gap: 2rem;
  flex-wrap: wrap;
}

.item-price {
  font-size: 1.1rem;
  margin: 0;
}

.price-label {
  color: #6b7280;
}

.price-value {
  font-weight: 700;
  color: var(--dark-gray);
  margin-left: 0.5rem;
}

.item-size {
  margin: 0;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Quantity Section */
.quantity-section {
  margin-top: 0.5rem;
}

.quantity-form {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.quantity-label {
  font-weight: 600;
  color: var(--dark-gray);
  margin: 0;
}

.quantity-controls {
  display: inline-flex;
  align-items: center;
  gap: 0;
  border: 2px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
  background: var(--white);
}

.qty-btn {
  width: 40px;
  height: 40px;
  background: var(--light-gray);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary-blue);
  font-size: 1rem;
  transition: all 0.2s ease;
}

.qty-btn:hover {
  background: var(--primary-blue);
  color: var(--white);
}

.qty-input {
  width: 70px;
  height: 40px;
  border: none;
  text-align: center;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dark-gray);
}

.qty-input:focus {
  outline: none;
  background: var(--light-gray);
}

/* Item Summary */
.item-summary {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-end;
  min-width: 180px;
}

.item-subtotal {
  text-align: right;
}

.subtotal-label {
  display: block;
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.subtotal-value {
  display: block;
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--gold);
}

.btn-remove {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  background: transparent;
  color: var(--danger);
  border: 2px solid var(--danger);
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-remove:hover {
  background: var(--danger);
  color: var(--white);
  transform: translateY(-1px);
}

/* Cart Footer */
.cart-footer {
  margin-top: 3rem;
}

.cart-summary-box {
  max-width: 500px;
  margin-left: auto;
  background: linear-gradient(135deg, var(--white) 0%, var(--light-gray) 100%);
  border-radius: 16px;
  padding: 2.5rem;
  box-shadow: var(--shadow-lg);
  border: 2px solid var(--gold);
}

.summary-content {
  margin-bottom: 2rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  font-size: 1.1rem;
}

.summary-total {
  border-top: 2px solid var(--gold);
  margin-top: 1rem;
  padding-top: 1.5rem;
  font-size: 1.3rem;
}

.summary-label {
  font-weight: 600;
  color: var(--dark-gray);
}

.summary-value {
  font-weight: 700;
  color: var(--primary-blue);
}

.total-amount {
  font-size: 2rem;
  color: var(--gold);
}

.summary-note {
  margin-top: 1rem;
  padding: 1rem;
  background: rgba(30, 58, 138, 0.05);
  border-radius: 8px;
  font-size: 0.9rem;
  color: #6b7280;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
}

.btn-block {
  width: 100%;
  display: flex;
}

.btn-lg {
  padding: 1.2rem 2.5rem;
  font-size: 1.2rem;
}

.btn-primary {
  background: var(--primary-blue);
  color: var(--white);
}

.btn-primary:hover {
  background: var(--light-blue);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
}

.btn-checkout {
  background: linear-gradient(135deg, var(--gold) 0%, #f4c430 100%);
  color: var(--dark-gray);
  font-size: 1.1rem;
}

.btn-checkout:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
}

.btn-secondary {
  background: #6c757d;
  color: var(--white);
}

.btn-secondary:hover {
  background: #5a6268;
}

.btn-outline-danger {
  background: transparent;
  color: var(--danger);
  border: 2px solid var(--danger);
  padding: 0.7rem 1.5rem;
  font-size: 0.95rem;
}

.btn-outline-danger:hover {
  background: var(--danger);
  color: var(--white);
}

.cart-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .cart-item {
    grid-template-columns: 100px 1fr;
    gap: 1.5rem;
  }
  
  .item-summary {
    grid-column: 1 / -1;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
  }
}

@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 2.5rem;
  }
  
  .hero-icon {
    font-size: 3rem;
  }
  
  .cart-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .cart-item {
    grid-template-columns: 80px 1fr;
    gap: 1rem;
    padding: 1.5rem;
  }
  
  .item-image {
    width: 80px;
    height: 80px;
  }
  
  .item-name {
    font-size: 1.2rem;
  }
  
  .item-meta {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .quantity-form {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .cart-summary-box {
    padding: 1.5rem;
  }
  
  .subtotal-value {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .cart-main {
    padding: 2rem 1rem;
  }
  
  .hero-content h1 {
    font-size: 2rem;
  }
  
  .cart-item {
    padding: 1rem;
  }
  
  .item-summary {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
</style>

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

<?php
require __DIR__ . '/partials/footer.php';
?>