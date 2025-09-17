// Application JavaScript pour R&G
class RGApp {
    constructor() {
        this.cart = [];
        this.cartCount = 0;
        this.isLoggedIn = false;
        this.currentUser = null;
        
        // Initialize new systems
        this.authManager = null;
        this.promoManager = null;
        this.orderManager = null;
        
        // Carousel state
        this.currentSlide = 0;
        this.totalSlides = 0;
        this.slidesToShow = 3; // Number of slides to show at once
        this.slideWidth = 320; // Width of each slide including gap
        this.originalTotalSlides = 0; // Original number of slides (without clones)
        this.isInfinite = true; // Enable infinite carousel
        this.autoPlay = true; // Enable auto-play
        this.autoPlayInterval = 4000; // Auto-play interval in ms
        this.autoPlayTimer = null; // Timer reference
        this.isTransitioning = false; // Prevent multiple transitions
        
        this.init();
    }
    
    init() {
        this.initializeManagers();
        this.setupEventListeners();
        this.loadCartFromStorage();
        this.updateCartDisplay();
        this.initCarousel();
        this.updateUserInterface();
    }
    
    initializeManagers() {
        // Initialize managers if available
        if (typeof AuthManager !== 'undefined') {
            this.authManager = new AuthManager();
            this.isLoggedIn = this.authManager.isUserLoggedIn();
            this.currentUser = this.authManager.getCurrentUser();
        }
        
        if (typeof PromoCodeManager !== 'undefined') {
            this.promoManager = new PromoCodeManager();
        }
        
        if (typeof OrderManager !== 'undefined') {
            this.orderManager = new OrderManager();
        }
        
        // Make managers globally accessible
        window.authManager = this.authManager;
        window.promoManager = this.promoManager;
        window.orderManager = this.orderManager;
    }
    
    setupEventListeners() {
        // Menu déroulant
        const menuTrigger = document.getElementById('menuTrigger');
        const dropdownContent = document.getElementById('dropdownContent');
        
        if (menuTrigger && dropdownContent) {
            menuTrigger.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdownContent.classList.toggle('show');
            });
            
            // Fermer le menu si on clique ailleurs
            document.addEventListener('click', () => {
                dropdownContent.classList.remove('show');
            });
            
            dropdownContent.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
        
        // Modal de connexion
        const loginBtn = document.getElementById('loginBtn');
        const loginModal = document.getElementById('loginModal');
        const closeLogin = document.getElementById('closeLogin');
        
        if (loginBtn && loginModal && closeLogin) {
            loginBtn.addEventListener('click', () => {
                this.showModal(loginModal);
            });
            
            closeLogin.addEventListener('click', () => {
                this.hideModal(loginModal);
            });
        }
        
        // Onglets de connexion/inscription
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        
        if (loginTab && registerTab && loginForm && registerForm) {
            loginTab.addEventListener('click', () => {
                this.switchAuthTab('login');
            });
            
            registerTab.addEventListener('click', () => {
                this.switchAuthTab('register');
            });
        }
        
        // Formulaires d'authentification
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin(loginForm);
            });
        }
        
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegister(registerForm);
            });
        }
        
        // Modal panier
        const cartBtn = document.getElementById('cartBtn');
        const cartModal = document.getElementById('cartModal');
        const closeCart = document.getElementById('closeCart');
        
        if (cartBtn && cartModal && closeCart) {
            cartBtn.addEventListener('click', () => {
                this.showCart();
            });
            
            closeCart.addEventListener('click', () => {
                this.hideModal(cartModal);
            });
        }
        
        // Enhanced cart buttons
        const continueShoppingBtn = document.getElementById('continueShoppingBtn');
        const checkoutBtn = document.getElementById('checkoutBtn');
        
        if (continueShoppingBtn) {
            continueShoppingBtn.addEventListener('click', () => {
                this.hideModal(document.getElementById('cartModal'));
                this.scrollToCategories();
            });
        }
        
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => {
                this.proceedToCheckout();
            });
        }
        
        // Payment modal handlers
        const paypalBtn = document.getElementById('paypalBtn');
        const stripeBtn = document.getElementById('stripeBtn');
        const closePayment = document.getElementById('closePayment');
        
        if (paypalBtn) {
            paypalBtn.addEventListener('click', () => {
                this.processPayment('paypal');
            });
        }
        
        if (stripeBtn) {
            stripeBtn.addEventListener('click', () => {
                this.processPayment('stripe');
            });
        }
        
        if (closePayment) {
            closePayment.addEventListener('click', () => {
                this.hideModal(document.getElementById('paymentModal'));
            });
        }
        
        // Card input formatting
        this.setupCardInputFormatting();
        
        // Fermer les modals en cliquant à l'extérieur
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.hideModal(e.target);
            }
        });
        
        // CTA Button
        const ctaButton = document.querySelector('.cta-button');
        if (ctaButton) {
            ctaButton.addEventListener('click', () => {
                this.scrollToCategories();
            });
        }
    }
    
    // Gestion des modals
    showModal(modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    hideModal(modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Gestion des onglets d'authentification
    switchAuthTab(tab) {
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        
        if (tab === 'login') {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
        } else {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        }
    }
    
    // Gestion de la connexion
    handleLogin(form) {
        const email = form.querySelector('input[type="email"]').value;
        const password = form.querySelector('input[type="password"]').value;
        
        if (this.authManager) {
            const result = this.authManager.login(email, password);
            
            if (result.success) {
                this.currentUser = result.user;
                this.isLoggedIn = true;
                this.updateUserInterface();
                this.hideModal(document.getElementById('loginModal'));
                this.showNotification('Connexion réussie !', 'success');
            } else {
                this.showNotification(result.error, 'error');
            }
        } else {
            // Fallback to original simulation
            if (email && password) {
                this.currentUser = {
                    email: email,
                    name: email.split('@')[0]
                };
                this.isLoggedIn = true;
                
                this.updateUserInterface();
                this.hideModal(document.getElementById('loginModal'));
                this.showNotification('Connexion réussie !', 'success');
            } else {
                this.showNotification('Veuillez remplir tous les champs', 'error');
            }
        }
    }
    
    // Gestion de l'inscription
    handleRegister(form) {
        const inputs = form.querySelectorAll('input');
        const name = inputs[0].value;
        const email = inputs[1].value;
        const password = inputs[2].value;
        const confirmPassword = inputs[3].value;
        
        if (this.authManager) {
            const userData = { name, email, password, confirmPassword };
            const result = this.authManager.register(userData);
            
            if (result.success) {
                this.showNotification('Inscription réussie !', 'success');
                // Connecter automatiquement l'utilisateur
                const loginResult = this.authManager.login(email, password);
                if (loginResult.success) {
                    this.currentUser = loginResult.user;
                    this.isLoggedIn = true;
                    this.updateUserInterface();
                    this.hideModal(document.getElementById('loginModal'));
                }
            } else {
                this.showNotification(result.error, 'error');
            }
        } else {
            // Fallback to original simulation
            if (!name || !email || !password || !confirmPassword) {
                this.showNotification('Veuillez remplir tous les champs', 'error');
                return;
            }
            
            if (password !== confirmPassword) {
                this.showNotification('Les mots de passe ne correspondent pas', 'error');
                return;
            }
            
            this.currentUser = { email, name };
            this.isLoggedIn = true;
            this.updateUserInterface();
            this.hideModal(document.getElementById('loginModal'));
            this.showNotification('Inscription réussie !', 'success');
        }
    }
    
    // Mise à jour de l'interface utilisateur
    updateUserInterface() {
        const loginBtn = document.getElementById('loginBtn');
        if (loginBtn && this.isLoggedIn) {
            loginBtn.innerHTML = `<i class="fas fa-user-check"></i>`;
            loginBtn.title = `Connecté en tant que ${this.currentUser.name}`;
        }
    }
    
    // Gestion du panier
    addToCart(item) {
        const existingItem = this.cart.find(cartItem => cartItem.id === item.id);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                ...item,
                quantity: 1
            });
        }
        
        this.cartCount++;
        this.updateCartDisplay();
        this.saveCartToStorage();
        this.showNotification(`${item.name} ajouté au panier`, 'success');
    }
    
    removeFromCart(itemId) {
        const itemIndex = this.cart.findIndex(item => item.id === itemId);
        if (itemIndex > -1) {
            const item = this.cart[itemIndex];
            this.cartCount -= item.quantity;
            this.cart.splice(itemIndex, 1);
            this.updateCartDisplay();
            this.saveCartToStorage();
            this.showNotification('Article retiré du panier', 'info');
        }
    }
    
    updateQuantity(itemId, newQuantity) {
        if (newQuantity <= 0) {
            this.removeFromCart(itemId);
            return;
        }
        
        const item = this.cart.find(item => item.id === itemId);
        if (item) {
            const oldQuantity = item.quantity;
            item.quantity = newQuantity;
            this.cartCount = this.cartCount - oldQuantity + newQuantity;
            this.updateCartDisplay();
            this.saveCartToStorage();
        }
    }
    
    addToCart(product, options = {}) {
        const existingItem = this.cart.find(item => 
            item.id === product.id && 
            item.size === options.size && 
            item.color === options.color
        );
        
        if (existingItem) {
            existingItem.quantity += options.quantity || 1;
            this.cartCount += options.quantity || 1;
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                category: product.category,
                size: options.size,
                color: options.color,
                quantity: options.quantity || 1
            });
            this.cartCount += options.quantity || 1;
        }
        
        this.updateCartDisplay();
        this.saveCartToStorage();
        this.showNotification('Article ajouté au panier', 'success');
    }
    
    updateCartDisplay() {
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            cartCountElement.textContent = this.cartCount;
        }
    }
    
    showCart() {
        this.renderCartItems();
        this.showModal(document.getElementById('cartModal'));
    }
    
    renderCartItems() {
        const cartItemsContainer = document.getElementById('cartItems');
        const cartEmpty = document.getElementById('cartEmpty');
        const cartSummary = document.getElementById('cartSummary');
        const cartFooter = document.getElementById('cartFooter');
        const cartSubtotal = document.getElementById('cartSubtotal');
        const cartTotal = document.getElementById('cartTotal');
        
        if (!cartItemsContainer) return;
        
        if (this.cart.length === 0) {
            cartEmpty.style.display = 'block';
            cartSummary.style.display = 'none';
            cartFooter.style.display = 'none';
            cartItemsContainer.innerHTML = '';
            cartItemsContainer.appendChild(cartEmpty);
            return;
        }
        
        cartEmpty.style.display = 'none';
        cartSummary.style.display = 'block';
        cartFooter.style.display = 'flex';
        
        let total = 0;
        let itemsHTML = '';
        
        this.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            // Generate icon based on category or use a default
            const categoryIcons = {
                'femme': 'fas fa-female',
                'homme': 'fas fa-male',
                'bijoux': 'fas fa-gem',
                'accessoires': 'fas fa-shopping-bag'
            };
            const itemIcon = categoryIcons[item.category] || 'fas fa-shopping-bag';
            
            itemsHTML += `
                <div class="cart-item">
                    <div class="cart-item-image">
                        <i class="${itemIcon}"></i>
                    </div>
                    <div class="cart-item-details">
                        <h4 class="cart-item-name">${item.name}</h4>
                        <div class="cart-item-options">
                            ${item.size ? `Taille: ${item.size}` : ''}
                            ${item.color ? ` • Couleur: ${item.color}` : ''}
                        </div>
                        <div class="cart-item-price">${item.price.toFixed(2)} €</div>
                    </div>
                    <div class="cart-item-controls">
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="app.updateQuantity('${item.id}', ${item.quantity - 1})">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="quantity-display">${item.quantity}</span>
                            <button class="quantity-btn" onclick="app.updateQuantity('${item.id}', ${item.quantity + 1})">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <button class="remove-item-btn" onclick="app.removeFromCart('${item.id}')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        cartItemsContainer.innerHTML = itemsHTML;
        
        // Update summary
        if (cartSubtotal) cartSubtotal.textContent = `${total.toFixed(2)} €`;
        if (cartTotal) cartTotal.textContent = `${total.toFixed(2)} €`;
    }
    
    // Stockage local
    saveCartToStorage() {
        localStorage.setItem('rg_cart', JSON.stringify(this.cart));
        localStorage.setItem('rg_cart_count', this.cartCount.toString());
    }
    
    loadCartFromStorage() {
        const savedCart = localStorage.getItem('rg_cart');
        const savedCount = localStorage.getItem('rg_cart_count');
        
        if (savedCart) {
            this.cart = JSON.parse(savedCart);
        }
        
        // Use server cart count if available, otherwise use localStorage
        if (typeof window.serverCartCount !== 'undefined') {
            this.cartCount = window.serverCartCount;
        } else if (savedCount) {
            this.cartCount = parseInt(savedCount);
        }
    }
    
    // Notifications
    showNotification(message, type = 'info') {
        // Créer une notification temporaire
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 3000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Suppression automatique
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Scroll vers les catégories
    scrollToCategories() {
        const categoriesSection = document.querySelector('.categories-preview');
        if (categoriesSection) {
            categoriesSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    // Méthode utilitaire pour formater les prix
    formatPrice(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(price);
    }
    
    // Gestion des articles (pour les pages de catégories)
    createProductCard(product) {
    const stockStatus = product.inStock ? 'En stock' : 'Sur demande';
    const stockClass = product.inStock ? 'in-stock' : 'on-demand';
    
    return `
        <div class="product-card" data-product-id="${product.id}">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}"
                     onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;200&quot; height=&quot;200&quot; viewBox=&quot;0 0 200 200&quot;><rect width=&quot;200&quot; height=&quot;200&quot; fill=&quot;%23f3f4f6&quot;/><text x=&quot;50%&quot; y=&quot;50%&quot; text-anchor=&quot;middle&quot; dy=&quot;0.3em&quot; fill=&quot;%23666&quot;>Image</text></svg>'">
                <div class="product-overlay">
                    <a class="quick-view-btn" href="product.php?id=${product.id}" aria-label="Voir ${product.name}">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-price">${this.formatPrice(product.price)}</div>
                <div class="product-status ${stockClass}">${stockStatus}</div>
                <button class="add-to-cart-btn" onclick="app.addToCart({
                    id: '${product.id}',
                    name: '${product.name}',
                    price: ${product.price},
                    image: '${product.image}'
                })">
                    <i class="fas fa-shopping-cart"></i>
                    Ajouter au panier
                </button>
            </div>
        </div>
    `;
}

    
    // Affichage des détails d'un produit
    showProductDetails(productId) {
        // Cette méthode serait implémentée pour afficher un modal avec les détails du produit
        console.log('Affichage des détails pour le produit:', productId);
    }
    
    // Carousel Methods
    initCarousel() {
        const carouselTrack = document.getElementById('carouselTrack');
        const carouselPrev = document.getElementById('carouselPrev');
        const carouselNext = document.getElementById('carouselNext');
        const carouselDots = document.getElementById('carouselDots');
        
        if (!carouselTrack) return;
        
        const categoryCards = carouselTrack.querySelectorAll('.category-card');
        this.originalTotalSlides = categoryCards.length;
        this.totalSlides = categoryCards.length;
        
        // Set up responsive slides
        this.updateSlidesToShow();
        
        // Clone slides for infinite carousel
        if (this.isInfinite) {
            this.cloneSlides(carouselTrack);
        }
        
        // Create dots
        this.createCarouselDots();
        
        // Add event listeners
        if (carouselPrev) {
            carouselPrev.addEventListener('click', () => this.prevSlide());
        }
        
        if (carouselNext) {
            carouselNext.addEventListener('click', () => this.nextSlide());
        }
        
        // Add hover events for auto-play pause
        if (this.autoPlay) {
            carouselTrack.addEventListener('mouseenter', () => this.pauseAutoPlay());
            carouselTrack.addEventListener('mouseleave', () => this.startAutoPlay());
        }
        
        // Add click events to category cards (including clones)
        this.updateCategoryCardEvents();
        
        // Update initial state
        this.updateCarousel();
        
        // Start auto-play
        if (this.autoPlay) {
            this.startAutoPlay();
        }
        
        // Handle window resize
        window.addEventListener('resize', () => {
            this.updateSlidesToShow();
            this.updateCarousel();
        });
    }
    
    updateSlidesToShow() {
        const width = window.innerWidth;
        if (width < 768) {
            this.slidesToShow = 1;
            this.slideWidth = 300;
        } else if (width < 1024) {
            this.slidesToShow = 2;
            this.slideWidth = 320;
        } else {
            this.slidesToShow = 3;
            this.slideWidth = 320;
        }
        
        // Calculate actual slide width based on card width + gap
        const carouselTrack = document.getElementById('carouselTrack');
        if (carouselTrack) {
            const cards = carouselTrack.querySelectorAll('.category-card');
            if (cards.length > 0) {
                const firstCard = cards[0];
                const rect = firstCard.getBoundingClientRect();
                const trackStyle = getComputedStyle(carouselTrack);
                const gap = parseInt(trackStyle.gap) || 32; // fallback to 32px
                this.slideWidth = rect.width + gap;
            }
        }
    }
    
    createCarouselDots() {
        const carouselDots = document.getElementById('carouselDots');
        if (!carouselDots) return;
        
        carouselDots.innerHTML = '';
        
        // For infinite carousel, use original slides count for dots
        // For non-infinite, use the traditional calculation
        const totalDots = this.isInfinite ? 
            this.originalTotalSlides : 
            Math.max(1, this.totalSlides - this.slidesToShow + 1);
        
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('button');
            dot.className = 'carousel-dot';
            if (i === 0) dot.classList.add('active');
            dot.addEventListener('click', () => this.goToSlide(i));
            carouselDots.appendChild(dot);
        }
    }
    
    updateCarousel() {
        const carouselTrack = document.getElementById('carouselTrack');
        const carouselPrev = document.getElementById('carouselPrev');
        const carouselNext = document.getElementById('carouselNext');
        const dots = document.querySelectorAll('.carousel-dot');
        
        if (!carouselTrack) return;
        
        this.isTransitioning = true;
        
        // Calculate transform
        const translateX = -this.currentSlide * this.slideWidth;
        carouselTrack.style.transform = `translateX(${translateX}px)`;
        
        // For infinite carousel, buttons are never disabled
        if (this.isInfinite) {
            if (carouselPrev) carouselPrev.disabled = false;
            if (carouselNext) carouselNext.disabled = false;
        } else {
            // Original button state logic for non-infinite carousel
            const maxSlide = Math.max(0, this.totalSlides - this.slidesToShow);
            
            if (carouselPrev) {
                carouselPrev.disabled = this.currentSlide <= 0;
            }
            
            if (carouselNext) {
                carouselNext.disabled = this.currentSlide >= maxSlide;
            }
        }
        
        // Update dots (show real position, not clones)
        if (this.isInfinite) {
            const realPosition = ((this.currentSlide - this.slidesToShow) % this.originalTotalSlides + this.originalTotalSlides) % this.originalTotalSlides;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === realPosition);
            });
        } else {
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === this.currentSlide);
            });
        }
        
        // Handle infinite loop teleportation
        if (this.isInfinite) {
            setTimeout(() => {
                this.resetToRealSlide();
                this.isTransitioning = false;
            }, 500); // Match CSS transition duration
        } else {
            this.isTransitioning = false;
        }
    }
    
    nextSlide() {
        if (this.isTransitioning) return;
        
        if (this.isInfinite) {
            this.currentSlide++;
            this.updateCarousel();
        } else {
            const maxSlide = Math.max(0, this.totalSlides - this.slidesToShow);
            if (this.currentSlide < maxSlide) {
                this.currentSlide++;
                this.updateCarousel();
            }
        }
    }
    
    prevSlide() {
        if (this.isTransitioning) return;
        
        if (this.isInfinite) {
            this.currentSlide--;
            this.updateCarousel();
        } else {
            if (this.currentSlide > 0) {
                this.currentSlide--;
                this.updateCarousel();
            }
        }
    }
    
    goToSlide(slideIndex) {
        if (this.isTransitioning) return;
        
        if (this.isInfinite) {
            // Convert real slide index to current slide position (accounting for clones)
            this.currentSlide = slideIndex + this.slidesToShow;
            this.updateCarousel();
        } else {
            const maxSlide = Math.max(0, this.totalSlides - this.slidesToShow);
            this.currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
            this.updateCarousel();
        }
    }
    
    navigateToCategory(category) {
        const categoryMap = {
            'femme': 'pages/femme.html',
            'homme': 'pages/homme.html',
            'bijoux': 'pages/bijoux.html',
            'accessoires': 'pages/accessoires.html',
            'nouvelle-collection': 'pages/nouvelle-collection.html'
        };
        
        const url = categoryMap[category];
        if (url) {
            window.location.href = url;
        }
    }

    // Infinite carousel helper methods
    cloneSlides(carouselTrack) {
        const originalCards = Array.from(carouselTrack.querySelectorAll('.category-card'));
        const slidesToClone = this.slidesToShow;
        
        // Clone first slides and append to end
        for (let i = 0; i < slidesToClone; i++) {
            const clone = originalCards[i].cloneNode(true);
            clone.classList.add('carousel-clone');
            carouselTrack.appendChild(clone);
        }
        
        // Clone last slides and prepend to beginning
        for (let i = originalCards.length - slidesToClone; i < originalCards.length; i++) {
            const clone = originalCards[i].cloneNode(true);
            clone.classList.add('carousel-clone');
            carouselTrack.insertBefore(clone, carouselTrack.firstChild);
        }
        
        // Update total slides count (including clones)
        this.totalSlides = carouselTrack.querySelectorAll('.category-card').length;
        
        // Set initial position to account for prepended clones
        this.currentSlide = slidesToClone;
    }

    updateCategoryCardEvents() {
        const carouselTrack = document.getElementById('carouselTrack');
        if (!carouselTrack) return;
        
        const allCards = carouselTrack.querySelectorAll('.category-card');
        allCards.forEach(card => {
            // Remove existing listeners by cloning the element
            const newCard = card.cloneNode(true);
            card.parentNode.replaceChild(newCard, card);
            
            // Add new event listener
            newCard.addEventListener('click', () => {
                const category = newCard.getAttribute('data-category');
                this.navigateToCategory(category);
            });
        });
    }

    startAutoPlay() {
        if (!this.autoPlay || this.autoPlayTimer) return;
        
        this.autoPlayTimer = setInterval(() => {
            if (!this.isTransitioning) {
                this.nextSlide();
            }
        }, this.autoPlayInterval);
    }

    pauseAutoPlay() {
        if (this.autoPlayTimer) {
            clearInterval(this.autoPlayTimer);
            this.autoPlayTimer = null;
        }
    }

    resetToRealSlide() {
        const carouselTrack = document.getElementById('carouselTrack');
        if (!carouselTrack || !this.isInfinite) return;
        
        const slidesToClone = this.slidesToShow;
        
        // If we're at the beginning clones, jump to the real end
        if (this.currentSlide < slidesToClone) {
            this.currentSlide = this.originalTotalSlides + slidesToClone - (slidesToClone - this.currentSlide);
            carouselTrack.style.transition = 'none';
            const translateX = -this.currentSlide * this.slideWidth;
            carouselTrack.style.transform = `translateX(${translateX}px)`;
            // Re-enable transition after a brief delay
            setTimeout(() => {
                carouselTrack.style.transition = 'transform 0.5s ease-in-out';
            }, 50);
        }
        
        // If we're at the end clones, jump to the real beginning
        if (this.currentSlide >= this.originalTotalSlides + slidesToClone) {
            this.currentSlide = slidesToClone + (this.currentSlide - this.originalTotalSlides - slidesToClone);
            carouselTrack.style.transition = 'none';
            const translateX = -this.currentSlide * this.slideWidth;
            carouselTrack.style.transform = `translateX(${translateX}px)`;
            // Re-enable transition after a brief delay
            setTimeout(() => {
                carouselTrack.style.transition = 'transform 0.5s ease-in-out';
            }, 50);
        }
    }
    
    // Notification system
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Hide notification
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Checkout process
    proceedToCheckout() {
        if (this.cart.length === 0) {
            this.showNotification('Votre panier est vide', 'info');
            return;
        }
        
        // Hide cart modal and show payment modal
        this.hideModal(document.getElementById('cartModal'));
        this.showPaymentModal();
    }
    
    showPaymentModal() {
        const paymentModal = document.getElementById('paymentModal');
        const orderItems = document.getElementById('orderItems');
        const orderTotal = document.getElementById('orderTotal');
        
        // Populate order summary
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        let itemsHTML = '';
        this.cart.forEach(item => {
            itemsHTML += `
                <div class="order-item">
                    <div class="order-item-details">
                        <div class="order-item-name">${item.name}</div>
                        <div class="order-item-options">
                            Quantité: ${item.quantity}
                            ${item.size ? ` • Taille: ${item.size}` : ''}
                            ${item.color ? ` • Couleur: ${item.color}` : ''}
                        </div>
                    </div>
                    <div class="order-item-price">${(item.price * item.quantity).toFixed(2)} €</div>
                </div>
            `;
        });
        
        orderItems.innerHTML = itemsHTML;
        orderTotal.textContent = `${total.toFixed(2)} €`;
        
        this.showModal(paymentModal);
    }
    
    processPayment(method) {
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        
        if (method === 'paypal') {
            // In a real implementation, this would integrate with PayPal SDK
            this.showNotification(`Redirection vers PayPal pour ${total.toFixed(2)}€...`, 'info');
            
            // Simulate PayPal processing
            setTimeout(() => {
                this.completeOrder('PayPal', total);
            }, 2000);
            
        } else if (method === 'stripe') {
            // Validate card fields
            const cardNumber = document.getElementById('cardNumber').value;
            const expiryDate = document.getElementById('expiryDate').value;
            const cvv = document.getElementById('cvv').value;
            const cardName = document.getElementById('cardName').value;
            
            if (!cardNumber || !expiryDate || !cvv || !cardName) {
                this.showNotification('Veuillez remplir tous les champs de la carte', 'info');
                return;
            }
            
            // In a real implementation, this would use Stripe Elements
            this.showNotification(`Traitement du paiement par carte pour ${total.toFixed(2)}€...`, 'info');
            
            // Simulate Stripe processing
            setTimeout(() => {
                this.completeOrder('Carte bancaire', total);
            }, 2000);
        }
    }
    
    completeOrder(paymentMethod, amount) {
        // Calculate totals with promo codes if available
        let subtotal = amount;
        let discount = 0;
        let promoCode = null;
        
        if (this.promoManager) {
            const calculation = this.promoManager.calculateTotal(amount);
            subtotal = calculation.subtotal;
            discount = calculation.discount;
            amount = calculation.total;
            promoCode = calculation.promoCode;
        }
        
        // Create order if OrderManager is available
        if (this.orderManager) {
            const orderData = {
                userId: this.isLoggedIn && this.currentUser ? this.currentUser.id : null,
                items: [...this.cart],
                subtotal: subtotal,
                discount: discount,
                total: amount,
                paymentMethod: paymentMethod,
                promoCode: promoCode
            };
            
            const order = this.orderManager.createOrder(orderData);
            console.log('Order created:', order);
        }
        
        // Clear cart
        this.cart = [];
        this.cartCount = 0;
        this.updateCartDisplay();
        this.saveCartToStorage();
        
        // Clear applied promo code
        if (this.promoManager) {
            this.promoManager.removePromoCode();
        }
        
        // Hide payment modal
        this.hideModal(document.getElementById('paymentModal'));
        
        // Show success notification
        this.showNotification(`Commande confirmée! Paiement de ${amount.toFixed(2)}€ via ${paymentMethod} réussi.`, 'success');
        
        // In a real app, this would redirect to an order confirmation page
        console.log('Order completed:', { paymentMethod, amount, timestamp: new Date() });
    }
    
    setupCardInputFormatting() {
        const cardNumber = document.getElementById('cardNumber');
        const expiryDate = document.getElementById('expiryDate');
        const cvv = document.getElementById('cvv');
        
        if (cardNumber) {
            cardNumber.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
                value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
                e.target.value = value;
            });
        }
        
        if (expiryDate) {
            expiryDate.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
        }
        
        if (cvv) {
            cvv.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/\D/g, '');
            });
        }
    }
}

// Initialisation de l'application
document.addEventListener('DOMContentLoaded', () => {
    window.app = new RGApp();
});

// Données d'exemple pour les produits
const sampleProducts = {
    femme: [
        {
            id: 'f1',
            name: 'Robe Élégante Bleu Marine',
            description: 'Robe sophistiquée pour occasions spéciales',
            price: 189.99,
            image: 'assets/products/robe-bleue.jpg',
            inStock: true
        },
        {
            id: 'f2',
            name: 'Ensemble Tailleur Doré',
            description: 'Tailleur chic avec finitions dorées',
            price: 299.99,
            image: 'assets/products/tailleur-dore.jpg',
            inStock: false
        }
    ],
    homme: [
        {
            id: 'h1',
            name: 'Costume Bleu Royal',
            description: 'Costume élégant coupe ajustée',
            price: 449.99,
            image: 'assets/products/costume-bleu.jpg',
            inStock: true
        },
        {
            id: 'h2',
            name: 'Chemise Luxe Dorée',
            description: 'Chemise premium avec détails dorés',
            price: 129.99,
            image: 'assets/products/chemise-doree.jpg',
            inStock: true
        }
    ],
    bijoux: [
        {
            id: 'b1',
            name: 'Collier Or et Saphir',
            description: 'Collier précieux or 18k avec saphirs',
            price: 899.99,
            image: 'assets/products/collier-saphir.jpg',
            inStock: false
        },
        {
            id: 'b2',
            name: 'Boucles d\'Oreilles Diamant',
            description: 'Boucles élégantes avec diamants',
            price: 1299.99,
            image: 'assets/products/boucles-diamant.jpg',
            inStock: true
        }
    ]
};