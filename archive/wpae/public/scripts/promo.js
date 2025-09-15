// Système de codes de réduction pour R&G
class PromoCodeManager {
    constructor() {
        this.promoCodes = {
            'WELCOME10': { 
                type: 'percentage', 
                value: 10, 
                minAmount: 50,
                description: 'Réduction de 10% pour les nouveaux clients',
                validUntil: null // null = illimité
            },
            'SUMMER20': { 
                type: 'percentage', 
                value: 20, 
                minAmount: 100,
                description: 'Réduction estivale de 20%',
                validUntil: '2024-12-31'
            },
            'NEWCLIENT': { 
                type: 'fixed', 
                value: 15, 
                minAmount: 30,
                description: 'Réduction de 15€ pour les nouveaux clients',
                validUntil: null
            },
            'LUXURY50': { 
                type: 'fixed', 
                value: 50, 
                minAmount: 200,
                description: 'Réduction de 50€ sur les achats de luxe',
                validUntil: null
            }
        };
        
        this.appliedPromo = null;
        this.loadAppliedPromo();
    }
    
    // Valider un code promo
    validatePromoCode(code, cartTotal) {
        const promoCode = code.toUpperCase();
        const promo = this.promoCodes[promoCode];
        
        if (!promo) {
            return { valid: false, error: 'Code promo invalide' };
        }
        
        // Vérifier la date d'expiration
        if (promo.validUntil) {
            const expiryDate = new Date(promo.validUntil);
            const now = new Date();
            if (now > expiryDate) {
                return { valid: false, error: 'Ce code promo a expiré' };
            }
        }
        
        // Vérifier le montant minimum
        if (cartTotal < promo.minAmount) {
            return { 
                valid: false, 
                error: `Montant minimum de ${promo.minAmount}€ requis pour ce code` 
            };
        }
        
        return { valid: true, promo: promo };
    }
    
    // Appliquer un code promo
    applyPromoCode(code, cartTotal) {
        const validation = this.validatePromoCode(code, cartTotal);
        
        if (!validation.valid) {
            return validation;
        }
        
        const promo = validation.promo;
        let discount = 0;
        
        if (promo.type === 'percentage') {
            discount = (cartTotal * promo.value) / 100;
        } else if (promo.type === 'fixed') {
            discount = Math.min(promo.value, cartTotal);
        }
        
        this.appliedPromo = {
            code: code.toUpperCase(),
            ...promo,
            discount: discount
        };
        
        this.saveAppliedPromo();
        
        return {
            valid: true,
            promo: this.appliedPromo,
            discount: discount,
            newTotal: cartTotal - discount
        };
    }
    
    // Retirer le code promo appliqué
    removePromoCode() {
        this.appliedPromo = null;
        this.saveAppliedPromo();
    }
    
    // Calculer le total avec réduction
    calculateTotal(cartTotal) {
        if (!this.appliedPromo) {
            return {
                subtotal: cartTotal,
                discount: 0,
                total: cartTotal
            };
        }
        
        // Revalider le code avec le nouveau total
        const validation = this.validatePromoCode(this.appliedPromo.code, cartTotal);
        if (!validation.valid) {
            this.removePromoCode();
            return {
                subtotal: cartTotal,
                discount: 0,
                total: cartTotal,
                error: validation.error
            };
        }
        
        let discount = 0;
        if (this.appliedPromo.type === 'percentage') {
            discount = (cartTotal * this.appliedPromo.value) / 100;
        } else if (this.appliedPromo.type === 'fixed') {
            discount = Math.min(this.appliedPromo.value, cartTotal);
        }
        
        this.appliedPromo.discount = discount;
        this.saveAppliedPromo();
        
        return {
            subtotal: cartTotal,
            discount: discount,
            total: cartTotal - discount,
            promoCode: this.appliedPromo.code
        };
    }
    
    // Obtenir le code promo actuel
    getAppliedPromo() {
        return this.appliedPromo;
    }
    
    // Sauvegarder dans localStorage
    saveAppliedPromo() {
        if (this.appliedPromo) {
            localStorage.setItem('rg_applied_promo', JSON.stringify(this.appliedPromo));
        } else {
            localStorage.removeItem('rg_applied_promo');
        }
    }
    
    // Charger depuis localStorage
    loadAppliedPromo() {
        const savedPromo = localStorage.getItem('rg_applied_promo');
        if (savedPromo) {
            this.appliedPromo = JSON.parse(savedPromo);
        }
    }
    
    // Obtenir la liste des codes disponibles (pour l'admin/debug)
    getAvailableCodes() {
        return Object.keys(this.promoCodes).map(code => ({
            code,
            ...this.promoCodes[code]
        }));
    }
}

// Interface utilisateur pour les codes promo
class PromoCodeUI {
    constructor(promoManager) {
        this.promoManager = promoManager;
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Event listener pour le formulaire de code promo
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'promoForm') {
                e.preventDefault();
                this.handlePromoSubmission(e.target);
            }
        });
        
        // Event listener pour retirer le code promo
        document.addEventListener('click', (e) => {
            if (e.target.id === 'removePromoBtn') {
                this.removePromoCode();
            }
        });
    }
    
    handlePromoSubmission(form) {
        const codeInput = form.querySelector('input[name="promoCode"]');
        const code = codeInput.value.trim();
        
        if (!code) {
            this.showMessage('Veuillez entrer un code promo', 'error');
            return;
        }
        
        // Obtenir le total du panier depuis l'app principale
        const cartTotal = this.getCartTotal();
        
        const result = this.promoManager.applyPromoCode(code, cartTotal);
        
        if (result.valid) {
            codeInput.value = '';
            this.showMessage(
                `Code "${result.promo.code}" appliqué ! Économie de ${result.discount.toFixed(2)}€`,
                'success'
            );
            this.updatePromoDisplay();
            this.updateTotalDisplay();
        } else {
            this.showMessage(result.error, 'error');
        }
    }
    
    removePromoCode() {
        this.promoManager.removePromoCode();
        this.showMessage('Code promo retiré', 'info');
        this.updatePromoDisplay();
        this.updateTotalDisplay();
    }
    
    updatePromoDisplay() {
        const promoDisplay = document.getElementById('promoDisplay');
        const appliedPromo = this.promoManager.getAppliedPromo();
        
        if (promoDisplay) {
            if (appliedPromo) {
                promoDisplay.innerHTML = `
                    <div class="applied-promo">
                        <div class="promo-info">
                            <span class="promo-code">${appliedPromo.code}</span>
                            <span class="promo-discount">-${appliedPromo.discount.toFixed(2)}€</span>
                        </div>
                        <button type="button" id="removePromoBtn" class="remove-promo-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                promoDisplay.style.display = 'block';
            } else {
                promoDisplay.style.display = 'none';
            }
        }
    }
    
    updateTotalDisplay() {
        const cartTotal = this.getCartTotal();
        const calculation = this.promoManager.calculateTotal(cartTotal);
        
        // Mettre à jour l'affichage des totaux
        const subtotalElement = document.getElementById('cartSubtotal');
        const discountElement = document.getElementById('cartDiscount');
        const totalElement = document.getElementById('cartTotal');
        
        if (subtotalElement) subtotalElement.textContent = `${calculation.subtotal.toFixed(2)} €`;
        if (discountElement) {
            discountElement.textContent = calculation.discount > 0 ? `-${calculation.discount.toFixed(2)} €` : '';
            discountElement.style.display = calculation.discount > 0 ? 'block' : 'none';
        }
        if (totalElement) totalElement.textContent = `${calculation.total.toFixed(2)} €`;
        
        // Notifier l'app principale du changement de total
        if (window.app && window.app.updateCartDisplay) {
            window.app.updateCartDisplay();
        }
    }
    
    getCartTotal() {
        if (window.app && window.app.cart) {
            return window.app.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }
        return 0;
    }
    
    showMessage(message, type) {
        if (window.app && window.app.showNotification) {
            window.app.showNotification(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
    
    // Créer l'interface HTML pour les codes promo
    createPromoInterface() {
        return `
            <div class="promo-section">
                <h4>Code de réduction</h4>
                <form id="promoForm" class="promo-form">
                    <div class="promo-input-group">
                        <input 
                            type="text" 
                            name="promoCode" 
                            placeholder="Entrez votre code promo"
                            maxlength="20"
                        >
                        <button type="submit" class="apply-promo-btn">
                            Appliquer
                        </button>
                    </div>
                </form>
                <div id="promoDisplay" class="promo-display" style="display: none;"></div>
            </div>
        `;
    }
}

// Initialisation globale
window.PromoCodeManager = PromoCodeManager;
window.PromoCodeUI = PromoCodeUI;