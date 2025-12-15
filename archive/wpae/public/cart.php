<?php
// Handle both direct access (public/cart.php) and inclusion from root (cart.php)
$auth_path = file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php';
require_once $auth_path;

// Set up page variables for header
$page_title = 'Panier - R&G';
$additional_css = ['styles/panier.css'];
$include_scripts = true;

// Include header
require_once 'partials/header.php';
?>
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h1><i class="fas fa-shopping-cart"></i> Mon Panier</h1>
            
            <div class="cart-container">
                <div class="cart-items-section">
                    <div id="cartItems" class="cart-items">
                        <!-- Les articles du panier seront chargés ici via JavaScript -->
                        <div class="cart-empty" id="cartEmpty">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Votre panier est vide</h3>
                            <p>Découvrez nos collections pour ajouter des articles</p>
                            <a href="index.php" class="continue-shopping-btn">
                                <i class="fas fa-arrow-left"></i>
                                Continuer mes achats
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="cart-summary-section" id="cartSummarySection" style="display: none;">
                    <div class="cart-summary">
                        <h3>Récapitulatif de commande</h3>
                        
                        <div class="summary-row">
                            <span>Sous-total:</span>
                            <span id="cartSubtotal">0,00 €</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Livraison:</span>
                            <span id="cartShipping">Gratuite</span>
                        </div>
                        
                        <div class="summary-row total-row">
                            <span>Total:</span>
                            <span id="cartTotal">0,00 €</span>
                        </div>
                        
                        <div class="cart-actions">
                            <a href="index.php" class="continue-shopping-btn">
                                <i class="fas fa-arrow-left"></i>
                                Continuer mes achats
                            </a>
                            <button class="checkout-btn" id="checkoutBtn">
                                <i class="fas fa-credit-card"></i>
                                Finaliser la commande
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>R&G</h3>
                <p>Votre destination mode et bijoux de luxe</p>
            </div>
            <div class="footer-section">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="pages/femme.html">Vêtements Femme</a></li>
                    <li><a href="pages/homme.html">Vêtements Homme</a></li>
                    <li><a href="pages/bijoux.html">Bijoux</a></li>
                    <li><a href="pages/info.html">Info</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: contact@rg-boutique.fr</p>
                <p>Téléphone: +33 1 23 45 67 89</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 R&G. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Login Modal (if not logged in) -->
    <?php if (!$current_user): ?>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeLogin">&times;</span>
            <h2>Connexion / Inscription</h2>
            <div class="auth-tabs">
                <button class="tab-button active" id="loginTab">Connexion</button>
                <button class="tab-button" id="registerTab">Inscription</button>
            </div>
            
            <form id="loginForm" class="auth-form" action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
            </form>
            
            <form id="registerForm" class="auth-form hidden" action="register.php" method="POST">
                <input type="text" name="first_name" placeholder="Prénom" required>
                <input type="text" name="last_name" placeholder="Nom" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">S'inscrire</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content payment-modal">
            <div class="modal-header">
                <h2><i class="fas fa-credit-card"></i> Finaliser votre commande</h2>
                <span class="close" id="closePayment">&times;</span>
            </div>
            
            <div class="payment-body">
                <div class="order-summary">
                    <h3>Récapitulatif de commande</h3>
                    <div id="orderItems" class="order-items">
                        <!-- Order items will be populated here -->
                    </div>
                    <div class="order-total">
                        <div class="total-row">
                            <span>Total à payer:</span>
                            <span id="orderTotal" class="total-amount">0,00 €</span>
                        </div>
                    </div>
                </div>
                
                <div class="payment-methods">
                    <h3>Choisissez votre mode de paiement</h3>
                    
                    <div class="payment-option" id="paypalOption">
                        <div class="payment-header">
                            <i class="fab fa-paypal"></i>
                            <span>PayPal</span>
                            <span class="payment-badge">Sécurisé</span>
                        </div>
                        <p class="payment-description">Payez rapidement et en toute sécurité avec votre compte PayPal</p>
                        <button class="payment-btn paypal-btn" id="paypalBtn">
                            <i class="fab fa-paypal"></i>
                            Payer avec PayPal
                        </button>
                    </div>
                    
                    <div class="payment-divider">
                        <span>ou</span>
                    </div>
                    
                    <div class="payment-option" id="stripeOption">
                        <div class="payment-header">
                            <i class="fas fa-credit-card"></i>
                            <span>Carte bancaire</span>
                            <span class="payment-badge">SSL</span>
                        </div>
                        <p class="payment-description">Visa, Mastercard, American Express acceptées</p>
                        
                        <div class="card-form">
                            <div class="form-group">
                                <label for="cardNumber">Numéro de carte</label>
                                <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiryDate">Date d'expiration</label>
                                    <input type="text" id="expiryDate" placeholder="MM/AA" maxlength="5">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" placeholder="123" maxlength="4">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cardName">Nom sur la carte</label>
                                <input type="text" id="cardName" placeholder="Jean Dupont">
                            </div>
                        </div>
                        
                        <button class="payment-btn stripe-btn" id="stripeBtn">
                            <i class="fas fa-lock"></i>
                            Payer par carte
                        </button>
                    </div>
                </div>
                
                <div class="security-info">
                    <i class="fas fa-shield-alt"></i>
                    <span>Paiement 100% sécurisé - Vos données sont protégées</span>
                </div>
            </div>
        </div>
    </div>

<?php
// Set additional scripts for this page
$additional_scripts = ['scripts/auth.js', 'scripts/promo.js', 'scripts/orders.js'];

// Include footer
require_once 'partials/footer.php';
?>