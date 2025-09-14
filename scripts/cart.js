/**
 * Cart management JavaScript
 */
class CartManager {
    constructor() {
        this.csrfToken = this.getCSRFToken();
        this.cartBadge = document.querySelector('[data-cart-count]');
        this.init();
    }

    init() {
        this.setupAddToCartButtons();
        this.initializeCartCount();
    }

    getCSRFToken() {
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        return tokenInput ? tokenInput.value : null;
    }

    setupAddToCartButtons() {
        // Listen for add to cart button clicks
        document.addEventListener('click', (e) => {
            const addToCartBtn = e.target.closest('[data-add-to-cart]');
            if (addToCartBtn) {
                e.preventDefault();
                this.handleAddToCart(addToCartBtn);
            }
        });
    }

    async handleAddToCart(button) {
        const productId = button.dataset.addToCart;
        const quantity = button.dataset.quantity || 1;

        if (!productId) {
            this.showToast('Erreur: ID du produit manquant', 'error');
            return;
        }

        try {
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';

            const response = await fetch('/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    product_id: productId,
                    qty: quantity,
                    csrf: this.csrfToken
                })
            });

            const result = await response.json();

            if (result.ok) {
                this.updateCartBadge(result.count);
                this.showToast(result.message || 'Produit ajouté au panier', 'success');
            } else {
                this.showToast(result.message || 'Erreur lors de l\'ajout au panier', 'error');
            }

        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showToast('Erreur de connexion', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
        }
    }

    updateCartBadge(count) {
        if (this.cartBadge) {
            this.cartBadge.textContent = count;
            this.cartBadge.setAttribute('data-cart-count', count);
            
            // Add bounce animation
            this.cartBadge.classList.add('cart-bounce');
            setTimeout(() => {
                this.cartBadge.classList.remove('cart-bounce');
            }, 300);
        }

        // Update global cart count for other scripts
        if (window.app && window.app.cartCount !== undefined) {
            window.app.cartCount = count;
        }
    }

    initializeCartCount() {
        if (this.cartBadge) {
            const count = this.cartBadge.getAttribute('data-cart-count') || 0;
            this.cartBadge.textContent = count;
        }
    }

    showToast(message, type = 'info') {
        // Create notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const iconMap = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'info': 'fa-info-circle'
        };
        
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${iconMap[type] || iconMap.info}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 1rem;
            z-index: 3000;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease;
            max-width: 300px;
            border-left: 4px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        `;

        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 100);

        // Hide notification
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
}

// Add cart bounce animation CSS
const cartAnimationCSS = `
.cart-bounce {
    animation: cartBounce 0.3s ease-in-out;
}

@keyframes cartBounce {
    0%, 20%, 60%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-3px);
    }
    80% {
        transform: translateY(-1px);
    }
}
`;

// Inject CSS
const style = document.createElement('style');
style.textContent = cartAnimationCSS;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.cartManager = new CartManager();
});