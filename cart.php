<?php
require_once __DIR__ . '/src/auth.php';
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
                        <h2>Articles dans votre panier</h2>
                        <!-- Cart items would be displayed here -->
                        <p>Fonctionnalité du panier en cours de développement.</p>
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
    </style>

<?php
require __DIR__ . '/partials/footer.php';
?>