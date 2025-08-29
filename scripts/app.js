// Application JavaScript pour R&G
class RGApp {
    constructor() {
        this.cart = [];
        this.cartCount = 0;
        this.isLoggedIn = false;
        this.currentUser = null;
        
        // Carousel state
        this.currentSlide = 0;
        this.totalSlides = 0;
        this.slidesToShow = 3; // Number of slides to show at once
        this.slideWidth = 320; // Width of each slide including gap
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadCartFromStorage();
        this.updateCartDisplay();
        this.initCarousel();
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
        const formData = new FormData(form);
        const email = form.querySelector('input[type="email"]').value;
        const password = form.querySelector('input[type="password"]').value;
        
        // Simulation de connexion
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
    
    // Gestion de l'inscription
    handleRegister(form) {
        const inputs = form.querySelectorAll('input');
        const name = inputs[0].value;
        const email = inputs[1].value;
        const password = inputs[2].value;
        const confirmPassword = inputs[3].value;
        
        if (!name || !email || !password || !confirmPassword) {
            this.showNotification('Veuillez remplir tous les champs', 'error');
            return;
        }
        
        if (password !== confirmPassword) {
            this.showNotification('Les mots de passe ne correspondent pas', 'error');
            return;
        }
        
        // Simulation d'inscription
        this.currentUser = {
            email: email,
            name: name
        };
        this.isLoggedIn = true;
        
        this.updateUserInterface();
        this.hideModal(document.getElementById('loginModal'));
        this.showNotification('Inscription réussie !', 'success');
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
        const cartTotalElement = document.getElementById('cartTotal');
        
        if (!cartItemsContainer || !cartTotalElement) return;
        
        if (this.cart.length === 0) {
            cartItemsContainer.innerHTML = '<p>Votre panier est vide</p>';
            cartTotalElement.textContent = '0,00 €';
            return;
        }
        
        let total = 0;
        let itemsHTML = '';
        
        this.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            itemsHTML += `
                <div class="cart-item">
                    <div>
                        <h4>${item.name}</h4>
                        <p>Quantité: ${item.quantity}</p>
                        <p>${item.price.toFixed(2)} € × ${item.quantity}</p>
                    </div>
                    <button onclick="app.removeFromCart('${item.id}')" class="remove-btn">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        });
        
        cartItemsContainer.innerHTML = itemsHTML;
        cartTotalElement.textContent = `${total.toFixed(2)} €`;
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
        
        if (savedCount) {
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
                    <img src="${product.image}" alt="${product.name}" onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"200\" height=\"200\" viewBox=\"0 0 200 200\"><rect width=\"200\" height=\"200\" fill=\"%23f3f4f6\"/><text x=\"50%\" y=\"50%\" text-anchor=\"middle\" dy=\"0.3em\" fill=\"%23666\">Image</text></svg>'">
                    <div class="product-overlay">
                        <button class="quick-view-btn" onclick="app.showProductDetails('${product.id}')">
                            <i class="fas fa-eye"></i>
                        </button>
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
        this.totalSlides = categoryCards.length;
        
        // Set up responsive slides
        this.updateSlidesToShow();
        
        // Create dots
        this.createCarouselDots();
        
        // Add event listeners
        if (carouselPrev) {
            carouselPrev.addEventListener('click', () => this.prevSlide());
        }
        
        if (carouselNext) {
            carouselNext.addEventListener('click', () => this.nextSlide());
        }
        
        // Add click events to category cards
        categoryCards.forEach(card => {
            card.addEventListener('click', () => {
                const category = card.getAttribute('data-category');
                this.navigateToCategory(category);
            });
        });
        
        // Update initial state
        this.updateCarousel();
        
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
    }
    
    createCarouselDots() {
        const carouselDots = document.getElementById('carouselDots');
        if (!carouselDots) return;
        
        carouselDots.innerHTML = '';
        const totalDots = Math.max(1, this.totalSlides - this.slidesToShow + 1);
        
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
        
        // Calculate transform
        const translateX = -this.currentSlide * this.slideWidth;
        carouselTrack.style.transform = `translateX(${translateX}px)`;
        
        // Update button states
        const maxSlide = Math.max(0, this.totalSlides - this.slidesToShow);
        
        if (carouselPrev) {
            carouselPrev.disabled = this.currentSlide <= 0;
        }
        
        if (carouselNext) {
            carouselNext.disabled = this.currentSlide >= maxSlide;
        }
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentSlide);
        });
    }
    
    nextSlide() {
        const maxSlide = Math.max(0, this.totalSlides - this.slidesToShow);
        if (this.currentSlide < maxSlide) {
            this.currentSlide++;
            this.updateCarousel();
        }
    }
    
    prevSlide() {
        if (this.currentSlide > 0) {
            this.currentSlide--;
            this.updateCarousel();
        }
    }
    
    goToSlide(slideIndex) {
        const maxSlide = Math.max(0, this.totalSlides - this.slidesToShow);
        this.currentSlide = Math.max(0, Math.min(slideIndex, maxSlide));
        this.updateCarousel();
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