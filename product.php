<?php
declare(strict_types=1);
require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/csrf.php';
require_once __DIR__ . '/src/ProductRepository.php';

// Get product ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(404);
    exit('Produit non trouvé');
}
$productId = (int)$_GET['id'];

// Get product details using ProductRepository
$productRepo = new ProductRepository();
$product = $productRepo->getById($productId);

if (!$product) {
    http_response_code(404);
    exit('Produit non trouvé');
}

// Parse sizes
$sizes = $productRepo->parseSizes($product['sizes'] ?? '');
$inStock = (int)$product['stock_quantity'] > 0;

// Base path for assets
$base_path = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base_path === '/') $base_path = '';

$page_title = htmlspecialchars($product['name']) . ' - R&G';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="<?= $base_path ?>/styles/global.css">
    <style>
        .product-detail-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }
        
        .product-image {
            width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .product-info {
            padding: 1rem 0;
        }
        
        .product-title {
            font-size: 2.5rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .product-price {
            font-size: 1.8rem;
            color: var(--gold);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .product-description {
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--text-color);
            margin-bottom: 2rem;
        }
        
        .product-stock {
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .in-stock {
            color: #28a745;
        }
        
        .out-of-stock {
            color: #dc3545;
        }
        
        .sizes-section {
            margin-bottom: 2rem;
        }
        
        .sizes-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
        }
        
        .sizes-grid {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .size-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            min-width: 50px;
            text-align: center;
        }
        
        .size-btn:hover {
            border-color: var(--gold);
        }
        
        .size-btn.active {
            background: var(--gold);
            border-color: var(--gold);
            color: white;
        }
        
        .add-to-cart-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .quantity-input {
            width: 80px;
            padding: 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
        }
        
        .btn-add-cart {
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-add-cart:hover {
            background: var(--light-blue);
            transform: translateY(-1px);
        }
        
        .btn-add-cart:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .product-detail-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <main class="product-detail-container">
        <div class="product-image-section">
            <?php if (!empty($product['image'])): ?>
                <?php $imgUrl = $base_path . '/' . ltrim($product['image'], '/'); ?>
                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
            <?php else: ?>
                <div class="product-image" style="background: #f5f5f5; display: flex; align-items: center; justify-content: center; min-height: 400px; color: #666;">
                    Aucune image disponible
                </div>
            <?php endif; ?>
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product-price">
                <?= number_format((float)$product['price'], 2, ',', ' ') ?> €
            </div>
            
            <?php if (!empty($product['description'])): ?>
                <div class="product-description">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
            <?php endif; ?>
            
            <div class="product-stock <?= $inStock ? 'in-stock' : 'out-of-stock' ?>">
                <?= $inStock ? 'En stock' : 'Rupture de stock' ?>
            </div>
            
            <?php if ($inStock): ?>
                <form method="post" action="<?= $base_path ?>/add_to_cart.php" class="add-to-cart-form" id="add-to-cart-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $productId ?>">
                    <input type="hidden" name="size" id="selected-size" value="">
                    
                    <?php if (!empty($sizes)): ?>
                        <div class="sizes-section">
                            <div class="sizes-label">Taille:</div>
                            <div class="sizes-grid">
                                <?php foreach ($sizes as $size): ?>
                                    <button type="button" class="size-btn" data-size="<?= htmlspecialchars($size) ?>">
                                        <?= htmlspecialchars($size) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <label>
                        Quantité:
                        <input type="number" name="qty" value="1" min="1" max="<?= (int)$product['stock_quantity'] ?>" class="quantity-input">
                    </label>
                    
                    <button type="submit" class="btn-add-cart" <?= (!empty($sizes)) ? 'disabled id="add-cart-btn"' : '' ?>>
                        Ajouter au panier
                    </button>
                </form>
            <?php endif; ?>
            
            <div style="margin-top: 2rem;">
                <a href="<?= $base_path ?>/" style="color: var(--primary-blue); text-decoration: none;">
                    ← Retour à la boutique
                </a>
            </div>
        </div>
    </main>

    <script>
        // Handle size selection
        document.addEventListener('DOMContentLoaded', function() {
            const sizeButtons = document.querySelectorAll('.size-btn');
            const selectedSizeInput = document.getElementById('selected-size');
            const addCartBtn = document.getElementById('add-cart-btn');
            
            sizeButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    sizeButtons.forEach(function(b) {
                        b.classList.remove('active');
                    });
                    
                    // Add active class to clicked button
                    btn.classList.add('active');
                    
                    // Set selected size
                    const size = btn.getAttribute('data-size');
                    selectedSizeInput.value = size;
                    
                    // Enable add to cart button
                    if (addCartBtn) {
                        addCartBtn.disabled = false;
                    }
                });
            });
            
            // Form submission
            const form = document.getElementById('add-to-cart-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const hasSizes = sizeButtons.length > 0;
                    const selectedSize = selectedSizeInput.value;
                    
                    if (hasSizes && !selectedSize) {
                        e.preventDefault();
                        alert('Veuillez sélectionner une taille');
                        return false;
                    }
                });
            }
        });
    </script>
    
    <!-- Structured Data for SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "<?= htmlspecialchars($product['name']) ?>",
        "description": "<?= htmlspecialchars($product['description'] ?? '') ?>",
        "offers": {
            "@type": "Offer",
            "url": "<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>",
            "priceCurrency": "EUR",
            "price": "<?= (float)$product['price'] ?>",
            "availability": "<?= $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>"
        }
        <?php if (!empty($product['image'])): ?>
        ,"image": "<?= htmlspecialchars($base_path . '/' . ltrim($product['image'], '/')) ?>"
        <?php endif; ?>
        <?php if (!empty($sizes)): ?>
        ,"additionalProperty": [
            {
                "@type": "PropertyValue",
                "name": "Tailles disponibles",
                "value": "<?= htmlspecialchars(implode(', ', $sizes)) ?>"
            }
        ]
        <?php endif; ?>
    }
    </script>
</body>
</html>