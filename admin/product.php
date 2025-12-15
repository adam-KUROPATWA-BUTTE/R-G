<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/csrf.php';

// Get product ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(404);
    exit('Produit non trouvé');
}
$productId = (int)$_GET['id'];

// Get product details
$pdo = db();
$stmt = $pdo->prepare("SELECT id, name, description, price, image, images, stock_quantity, category, sizes FROM products WHERE id = ? LIMIT 1");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    exit('Produit non trouvé');
}

// Parse images for gallery (refs #36)
$productImages = [];
if (!empty($product['images'])) {
    $decoded = json_decode($product['images'], true);
    if (is_array($decoded)) {
        $productImages = $decoded;
    }
}
// Fallback to single image for backward compatibility
if (empty($productImages) && !empty($product['image'])) {
    $productImages = [$product['image']];
}

// Parse sizes
$sizes = product_parse_sizes($product['sizes'] ?? '');
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
        
        /* Image Gallery Styles (refs #36) */
        .product-image-section {
            position: sticky;
            top: 1rem;
        }
        
        .gallery-main-image {
            width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        
        .gallery-thumbnails {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .gallery-thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            cursor: pointer;
            object-fit: cover;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .gallery-thumbnail:hover {
            border-color: var(--gold);
            transform: scale(1.05);
        }
        
        .gallery-thumbnail.active {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px var(--gold);
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
        
        /* Expandable Description Styles (refs #37) */
        .product-description {
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--text-color);
            margin-bottom: 1rem;
            position: relative;
        }
        
        .description-content {
            max-height: 150px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .description-content.expanded {
            max-height: none;
        }
        
        .description-toggle {
            background: none;
            border: none;
            color: var(--primary-blue);
            font-weight: 600;
            cursor: pointer;
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .description-toggle:hover {
            color: var(--gold);
        }
        
        .description-toggle i {
            transition: transform 0.3s ease;
        }
        
        .description-toggle.expanded i {
            transform: rotate(180deg);
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
            
            .product-image-section {
                position: static;
            }
            
            .gallery-thumbnail {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <main class="product-detail-container">
        <div class="product-image-section">
            <?php if (!empty($productImages)): ?>
                <?php $mainImgUrl = $base_path . '/' . ltrim($productImages[0], '/'); ?>
                <img src="<?= htmlspecialchars($mainImgUrl) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="gallery-main-image" id="mainImage">
                
                <?php if (count($productImages) > 1): ?>
                    <div class="gallery-thumbnails">
                        <?php foreach ($productImages as $index => $imgPath): ?>
                            <?php $thumbUrl = $base_path . '/' . ltrim($imgPath, '/'); ?>
                            <img src="<?= htmlspecialchars($thumbUrl) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?> - Image <?= $index + 1 ?>" 
                                 class="gallery-thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                 onclick="changeMainImage('<?= htmlspecialchars($thumbUrl, ENT_QUOTES) ?>', this)">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                    <div class="description-content" id="descriptionContent">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </div>
                    <?php if (strlen($product['description']) > 300): ?>
                        <button class="description-toggle" id="descriptionToggle" onclick="toggleDescription()">
                            <span id="toggleText">Voir plus</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    <?php endif; ?>
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
        // Image Gallery - Change main image when clicking thumbnail (refs #36)
        function changeMainImage(imageUrl, thumbnailElement) {
            const mainImage = document.getElementById('mainImage');
            if (mainImage) {
                mainImage.src = imageUrl;
            }
            
            // Update active state
            const thumbnails = document.querySelectorAll('.gallery-thumbnail');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            if (thumbnailElement) {
                thumbnailElement.classList.add('active');
            }
        }
        
        // Expandable Description Toggle (refs #37)
        function toggleDescription() {
            const content = document.getElementById('descriptionContent');
            const toggle = document.getElementById('descriptionToggle');
            const toggleText = document.getElementById('toggleText');
            
            if (content && toggle && toggleText) {
                content.classList.toggle('expanded');
                toggle.classList.toggle('expanded');
                
                if (content.classList.contains('expanded')) {
                    toggleText.textContent = 'Voir moins';
                } else {
                    toggleText.textContent = 'Voir plus';
                }
            }
        }
        
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
        <?php if (!empty($productImages)): ?>
        ,"image": [
            <?php foreach ($productImages as $index => $imgPath): ?>
                "<?= htmlspecialchars($base_path . '/' . ltrim($imgPath, '/')) ?>"<?= $index < count($productImages) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
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