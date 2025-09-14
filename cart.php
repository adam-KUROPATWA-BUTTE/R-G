<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/cart.php';
require_once __DIR__ . '/src/functions.php';
$current_user = current_user();

$cart = cart_get();
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
                <?php if (empty($cart)): ?>
                    <div class="cart-empty">
                        <i class="fas fa-shopping-cart"></i>
                        <h2>Votre panier est vide</h2>
                        <p>Découvrez nos collections pour ajouter des articles</p>
                        <a href="/" class="btn btn-primary">Continuer mes achats</a>
                    </div>
                <?php else: ?>
                    <div class="cart-items">
                        <h2>Articles dans votre panier (<?= count($cart) ?> article<?= count($cart) > 1 ? 's' : '' ?>)</h2>
                        <div class="cart-items-list">
                            <?php 
                            $total = 0;
                            foreach ($cart as $product_id => $item): 
                                $product = product_get((int)$product_id);
                                if ($product): 
                                    $item_total = (float)$product['price'] * (int)$item['quantity'];
                                    $total += $item_total;
                            ?>
                                <div class="cart-item">
                                    <div class="cart-item-image">
                                        <?php if (!empty($product['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                        <?php else: ?>
                                            <div class="placeholder-image">
                                                <i class="fas fa-gem"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-item-details">
                                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                                        <p class="cart-item-price">Prix unitaire : <?= number_format((float)$product['price'], 2, ',', ' ') ?> €</p>
                                        <div class="quantity-controls">
                                            <button onclick="updateCartQuantity('<?= $product_id ?>', <?= (int)$item['quantity'] - 1 ?>)">-</button>
                                            <span>Quantité : <?= (int)$item['quantity'] ?></span>
                                            <button onclick="updateCartQuantity('<?= $product_id ?>', <?= (int)$item['quantity'] + 1 ?>)">+</button>
                                        </div>
                                        <p class="cart-item-total">Total : <?= number_format($item_total, 2, ',', ' ') ?> €</p>
                                    </div>
                                    <div class="cart-item-actions">
                                        <button onclick="removeFromCart('<?= $product_id ?>')" class="remove-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        
                        <div class="cart-summary">
                            <div class="cart-total">
                                <h3>Total : <?= number_format($total, 2, ',', ' ') ?> €</h3>
                            </div>
                            <div class="cart-actions">
                                <a href="/" class="btn btn-secondary">Continuer mes achats</a>
                                <button class="btn btn-primary" onclick="proceedToCheckout()">Passer commande</button>
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
        .cart-section {
            padding: 2rem;
            background-color: var(--white);
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .cart-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--dark-gray);
        }

        .cart-empty i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-blue);
        }

        .cart-empty h2 {
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }

        .cart-empty p {
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: var(--primary-blue);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: var(--light-blue);
            transform: translateY(-1px);
        }

        .btn.btn-primary {
            background: var(--primary-blue);
        }

        @media (max-width: 768px) {
            .header-content h1 {
                font-size: 2rem;
            }
        }

        /* Cart Items Styles */
        .cart-items-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-image {
            width: 100%;
            height: 100%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary-blue);
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-details h4 {
            margin: 0 0 0.5rem 0;
            color: var(--primary-blue);
        }

        .cart-item-price, .cart-item-total {
            margin: 0.5rem 0;
            font-weight: bold;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }

        .quantity-controls button {
            background: var(--primary-blue);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .cart-summary {
            border-top: 2px solid var(--primary-blue);
            padding-top: 1rem;
            margin-top: 2rem;
        }

        .cart-total {
            text-align: center;
            margin-bottom: 1rem;
        }

        .cart-total h3 {
            color: var(--gold);
            font-size: 1.5rem;
        }

        .cart-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }
    </style>

    <script>
        async function updateCartQuantity(productId, newQuantity) {
            if (newQuantity <= 0) {
                removeFromCart(productId);
                return;
            }
            
            try {
                const response = await fetch('/update-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: newQuantity
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload(); // Reload to show updated cart
                } else {
                    alert(result.error || 'Erreur lors de la mise à jour');
                }
                
            } catch (error) {
                console.error('Error updating cart:', error);
                alert('Erreur de connexion');
            }
        }

        async function removeFromCart(productId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
                return;
            }
            
            try {
                const response = await fetch('/remove-from-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload(); // Reload to show updated cart
                } else {
                    alert(result.error || 'Erreur lors de la suppression');
                }
                
            } catch (error) {
                console.error('Error removing from cart:', error);
                alert('Erreur de connexion');
            }
        }

        function proceedToCheckout() {
            alert('Fonctionnalité de commande en cours de développement');
        }
    </script>

<?php
require __DIR__ . '/partials/footer.php';
?>