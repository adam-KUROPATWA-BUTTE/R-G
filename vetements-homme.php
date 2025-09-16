<?php
require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/functions.php';
$current_user = current_user();

// Base path
$base_path = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base_path === '/') $base_path = '';

try {
    $products = products_list('homme');
} catch (Throwable $e) {
    $products = [];
    $error = 'Erreur lors du chargement des produits.';
}

$page_title = 'Vêtements Homme - R&G';
require __DIR__ . '/partials/header.php';
?>
<header class="page-header">
  <div class="header-content">
    <h1><i class="fas fa-male"></i> Vêtements Homme</h1>
    <p>Style raffiné et sophistiqué pour l'homme moderne</p>
  </div>
</header>
<main class="main-content">
  <section class="products-section">
    <div class="products-container">
      <?php if (!empty($error ?? '')): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <div class="products-grid" id="productsGrid">
      <?php if (empty($products)): ?>
        <div class="no-products">
          <i class="fas fa-user-tie"></i>
          <p>Aucun produit disponible pour le moment dans cette catégorie.</p>
        </div>
      <?php else: foreach ($products as $product):
          $rawImg = (string)($product['image'] ?? ($product['image_url'] ?? ''));
          $imgUrl = '';
          if ($rawImg !== '') {
              $isAbs = preg_match('#^(?:https?:)?//#', $rawImg) || strncmp($rawImg, 'data:', 5) === 0;
              $imgUrl = $isAbs ? $rawImg : ($base_path . '/' . ltrim($rawImg, '/'));
          }
          $stockRaw = $product['stock_quantity'] ?? ($product['stock'] ?? null);
          $stockVal = ($stockRaw === null) ? 1 : (int)$stockRaw;
      ?>
        <div class="product-card" data-product-id="<?= (int)$product['id'] ?>">
          <div class="product-image">
            <?php if ($imgUrl): ?>
              <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($product['name'] ?? 'Produit') ?>">
            <?php else: ?>
              <div class="placeholder-image"><i class="fas fa-user-tie"></i></div>
            <?php endif; ?>
            <div class="product-overlay">
              <button class="quick-view-btn" onclick="showProductDetails('<?= (int)$product['id'] ?>')"><i class="fas fa-eye"></i></button>
            </div>
<<<<<<< HEAD
        </section>

        <!-- Products Grid -->
        <section class="products-section">
            <div class="products-container">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="products-grid" id="productsGrid">
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <i class="fas fa-user-tie"></i>
                            <p>Aucun produit disponible pour le moment dans cette catégorie.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card" data-product-id="<?= (int)$product['id'] ?>">
                                <div class="product-image">
                                    <?php $img = $product['image'] ?? ($product['image_url'] ?? null); ?>
                                    <?php if (!empty($img)): ?>
                                        <img src="<?= htmlspecialchars((string)$img) ?>" alt="<?= htmlspecialchars($product['name'] ?? 'Produit') ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-overlay">
                                        <button class="quick-view-btn" onclick="showProductDetails('<?= (int)$product['id'] ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3><?= htmlspecialchars($product['name'] ?? 'Produit') ?></h3>
                                    <?php if (!empty($product['description'])): ?>
                                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                    <?php endif; ?>
                                    <?php if (isset($product['price'])): ?>
                                        <div class="product-price"><?= number_format((float)$product['price'], 2, ',', ' ') ?> €</div>
                                    <?php endif; ?>
                                    <?php $stock = $product['stock_quantity'] ?? ($product['stock'] ?? null); ?>
                                    <?php if ($stock !== null): ?>
                                        <div class="product-status <?= (int)$stock > 0 ? 'in-stock' : 'on-demand' ?>">
                                            <?= (int)$stock > 0 ? 'En stock' : 'Sur demande' ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-actions">
                                        <form method="post" action="<?= $base_path ?>/add_to_cart.php">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                                            <input type="hidden" name="back" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?>">
                                            <div class="qty-row">
                                                <input type="number" name="qty" min="1" value="1" class="qty-input">
                                                <button type="submit" class="add-to-cart-btn">
                                                    <i class="fas fa-shopping-cart"></i>
                                                    Ajouter au panier
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
=======
          </div>
          <div class="product-info">
            <h3><?= htmlspecialchars($product['name'] ?? 'Produit') ?></h3>
            <?php if (!empty($product['description'])): ?><p class="product-description"><?= htmlspecialchars($product['description']) ?></p><?php endif; ?>
            <?php if (isset($product['price'])): ?><div class="product-price"><?= number_format((float)$product['price'], 2, ',', ' ') ?> €</div><?php endif; ?>
            <div class="product-status <?= $stockVal > 0 ? 'in-stock' : 'on-demand' ?>"><?= $stockVal > 0 ? 'En stock' : 'Sur demande' ?></div>
            <div class="product-actions">
              <form method="post" action="<?= $base_path ?>/add_to_cart.php">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <input type="hidden" name="back" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/') ?>">
                <div class="qty-row">
                  <input type="number" name="qty" min="1" value="1" class="qty-input">
                  <button type="submit" class="add-to-cart-btn"><i class="fas fa-shopping-cart"></i> Ajouter au panier</button>
>>>>>>> 51b0590 (dernier version)
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
      </div>
    </div>
  </section>
</main>
<style>
/* ... ton CSS existant inchangé ... */
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

        /* Filters Section */
        .filters-section {
            padding: 2rem;
            background-color: var(--light-gray);
        }

        .filters-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .filters-container h3 {
            color: var(--primary-blue);
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .filters select {
            padding: 0.8rem;
            border: 2px solid var(--primary-blue);
            border-radius: 5px;
            background-color: var(--white);
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filters select:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 5px rgba(211, 170, 54, 0.3);
        }

        /* Products Section */
        .products-section {
            padding: 2rem;
            background-color: var(--white);
        }

        .products-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: var(--gold);
        }

        .product-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-image {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--light-gray) 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-blue);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(30, 58, 138, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .quick-view-btn {
            background: var(--gold);
            color: var(--primary-blue);
            border: none;
            padding: 1rem;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-view-btn:hover {
            background: var(--white);
            transform: scale(1.1);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-info h3 {
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .product-description {
            color: var(--dark-gray);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--gold);
            margin-bottom: 0.5rem;
        }

        .product-status {
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            display: inline-block;
        }

        .product-status.in-stock {
            background: #d4edda;
            color: #155724;
        }

        .product-status.on-demand {
            background: #fff3cd;
            color: #856404;
        }

        .product-actions .qty-row {
            display: flex;
            gap: .5rem;
            align-items: center;
        }

        .qty-input {
            width: 70px;
            padding: .5rem;
        }

        .add-to-cart-btn {
            padding: 0.8rem;
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .add-to-cart-btn:hover {
            background-color: var(--light-blue);
            transform: translateY(-1px);
        }

        .no-products {
            text-align: center;
            padding: 3rem;
            color: var(--dark-gray);
            grid-column: 1 / -1;
        }

        .no-products i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-blue);
        }

        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
            }
            
            .filters select {
                width: 100%;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content h1 {
                font-size: 2rem;
            }
        }
</style>
<script>
function showProductDetails(productId){ console.log('Show details for product:', productId); }
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>
        /* Page Header */