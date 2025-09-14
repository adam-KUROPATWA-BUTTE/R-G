// Système de gestion des commandes pour R&G
class OrderManager {
    constructor() {
        this.orders = this.loadOrders();
        this.orderStatuses = {
            'pending': 'En attente',
            'confirmed': 'Confirmée',
            'preparing': 'En préparation',
            'shipped': 'Expédiée',
            'delivered': 'Livrée',
            'cancelled': 'Annulée'
        };
    }
    
    // Charger les commandes depuis localStorage
    loadOrders() {
        const savedOrders = localStorage.getItem('rg_orders');
        return savedOrders ? JSON.parse(savedOrders) : [];
    }
    
    // Sauvegarder les commandes
    saveOrders() {
        localStorage.setItem('rg_orders', JSON.stringify(this.orders));
    }
    
    // Créer une nouvelle commande
    createOrder(orderData) {
        const order = {
            id: this.generateOrderId(),
            userId: orderData.userId || null,
            date: new Date().toISOString(),
            items: orderData.items || [],
            subtotal: orderData.subtotal || 0,
            discount: orderData.discount || 0,
            total: orderData.total || 0,
            paymentMethod: orderData.paymentMethod || '',
            status: 'confirmed',
            deliveryAddress: orderData.deliveryAddress || null,
            promoCode: orderData.promoCode || null,
            customerInfo: orderData.customerInfo || {},
            trackingNumber: this.generateTrackingNumber(),
            estimatedDelivery: this.calculateEstimatedDelivery()
        };
        
        this.orders.push(order);
        this.saveOrders();
        
        // Ajouter à l'historique de l'utilisateur si connecté
        if (window.authManager && window.authManager.isUserLoggedIn()) {
            window.authManager.addOrder(order);
        }
        
        return order;
    }
    
    // Générer un ID de commande unique
    generateOrderId() {
        const timestamp = Date.now().toString(36);
        const random = Math.random().toString(36).substr(2, 5);
        return `RG-${timestamp}-${random}`.toUpperCase();
    }
    
    // Générer un numéro de suivi
    generateTrackingNumber() {
        const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const numbers = '0123456789';
        let tracking = 'RG';
        
        // Ajouter 2 lettres
        for (let i = 0; i < 2; i++) {
            tracking += letters.charAt(Math.floor(Math.random() * letters.length));
        }
        
        // Ajouter 8 chiffres
        for (let i = 0; i < 8; i++) {
            tracking += numbers.charAt(Math.floor(Math.random() * numbers.length));
        }
        
        return tracking;
    }
    
    // Calculer la date de livraison estimée
    calculateEstimatedDelivery() {
        const now = new Date();
        const deliveryDate = new Date(now.getTime() + (3 + Math.floor(Math.random() * 4)) * 24 * 60 * 60 * 1000);
        return deliveryDate.toISOString();
    }
    
    // Obtenir une commande par ID
    getOrder(orderId) {
        return this.orders.find(order => order.id === orderId);
    }
    
    // Obtenir les commandes d'un utilisateur
    getUserOrders(userId) {
        if (!userId) return [];
        return this.orders.filter(order => order.userId === userId)
                         .sort((a, b) => new Date(b.date) - new Date(a.date));
    }
    
    // Mettre à jour le statut d'une commande
    updateOrderStatus(orderId, newStatus) {
        const order = this.getOrder(orderId);
        if (!order) {
            return { success: false, error: 'Commande non trouvée' };
        }
        
        if (!this.orderStatuses[newStatus]) {
            return { success: false, error: 'Statut invalide' };
        }
        
        order.status = newStatus;
        order.lastUpdated = new Date().toISOString();
        
        this.saveOrders();
        
        return { success: true, order: order };
    }
    
    // Annuler une commande
    cancelOrder(orderId) {
        const order = this.getOrder(orderId);
        if (!order) {
            return { success: false, error: 'Commande non trouvée' };
        }
        
        if (['shipped', 'delivered'].includes(order.status)) {
            return { success: false, error: 'Cette commande ne peut plus être annulée' };
        }
        
        order.status = 'cancelled';
        order.cancelledAt = new Date().toISOString();
        
        this.saveOrders();
        
        return { success: true, order: order };
    }
    
    // Obtenir les statistiques des commandes
    getOrderStats() {
        const stats = {
            total: this.orders.length,
            statuses: {},
            totalRevenue: 0,
            averageOrderValue: 0
        };
        
        // Calculer les statistiques par statut
        Object.keys(this.orderStatuses).forEach(status => {
            stats.statuses[status] = this.orders.filter(order => order.status === status).length;
        });
        
        // Calculer le chiffre d'affaires
        const completedOrders = this.orders.filter(order => 
            ['confirmed', 'preparing', 'shipped', 'delivered'].includes(order.status)
        );
        
        stats.totalRevenue = completedOrders.reduce((sum, order) => sum + order.total, 0);
        stats.averageOrderValue = completedOrders.length > 0 ? stats.totalRevenue / completedOrders.length : 0;
        
        return stats;
    }
    
    // Formater une commande pour l'affichage
    formatOrderForDisplay(order) {
        return {
            ...order,
            formattedDate: this.formatDate(order.date),
            formattedTotal: this.formatCurrency(order.total),
            statusLabel: this.orderStatuses[order.status],
            formattedEstimatedDelivery: this.formatDate(order.estimatedDelivery)
        };
    }
    
    // Formater une date
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    // Formater un montant
    formatCurrency(amount) {
        return `${amount.toFixed(2)} €`;
    }
}

// Interface utilisateur pour les commandes
class OrderUI {
    constructor(orderManager) {
        this.orderManager = orderManager;
    }
    
    // Créer l'interface de commande
    createOrderSummary(orderData) {
        const order = this.orderManager.formatOrderForDisplay(orderData);
        
        return `
            <div class="order-summary">
                <div class="order-header">
                    <h3>Commande ${order.id}</h3>
                    <span class="order-status status-${order.status}">${order.statusLabel}</span>
                </div>
                
                <div class="order-details">
                    <div class="order-date">
                        <strong>Date :</strong> ${order.formattedDate}
                    </div>
                    <div class="order-total">
                        <strong>Total :</strong> ${order.formattedTotal}
                    </div>
                    ${order.trackingNumber ? `
                        <div class="tracking-number">
                            <strong>Suivi :</strong> ${order.trackingNumber}
                        </div>
                    ` : ''}
                    ${order.estimatedDelivery ? `
                        <div class="estimated-delivery">
                            <strong>Livraison estimée :</strong> ${order.formattedEstimatedDelivery}
                        </div>
                    ` : ''}
                </div>
                
                <div class="order-items">
                    <h4>Articles commandés :</h4>
                    ${order.items.map(item => `
                        <div class="order-item">
                            <span class="item-name">${item.name}</span>
                            <span class="item-quantity">x${item.quantity}</span>
                            <span class="item-price">${this.orderManager.formatCurrency(item.price * item.quantity)}</span>
                        </div>
                    `).join('')}
                </div>
                
                ${order.promoCode ? `
                    <div class="order-promo">
                        <span class="promo-code">Code utilisé : ${order.promoCode}</span>
                        <span class="promo-discount">-${this.orderManager.formatCurrency(order.discount)}</span>
                    </div>
                ` : ''}
                
                ${order.deliveryAddress ? `
                    <div class="delivery-address">
                        <h4>Adresse de livraison :</h4>
                        <p>
                            ${order.deliveryAddress.name}<br>
                            ${order.deliveryAddress.street}<br>
                            ${order.deliveryAddress.postalCode} ${order.deliveryAddress.city}<br>
                            ${order.deliveryAddress.country}
                        </p>
                    </div>
                ` : ''}
            </div>
        `;
    }
    
    // Créer la liste des commandes
    createOrdersList(orders) {
        if (!orders.length) {
            return '<div class="no-orders">Aucune commande trouvée</div>';
        }
        
        return orders.map(order => {
            const formattedOrder = this.orderManager.formatOrderForDisplay(order);
            return `
                <div class="order-card" data-order-id="${order.id}">
                    <div class="order-card-header">
                        <div class="order-id">Commande ${order.id}</div>
                        <div class="order-date">${formattedOrder.formattedDate}</div>
                    </div>
                    
                    <div class="order-card-body">
                        <div class="order-status">
                            <span class="status-badge status-${order.status}">
                                ${formattedOrder.statusLabel}
                            </span>
                        </div>
                        
                        <div class="order-items-summary">
                            ${order.items.length} article${order.items.length > 1 ? 's' : ''}
                        </div>
                        
                        <div class="order-total">
                            ${formattedOrder.formattedTotal}
                        </div>
                    </div>
                    
                    <div class="order-card-actions">
                        <button class="btn-view-order" onclick="viewOrderDetails('${order.id}')">
                            Voir détails
                        </button>
                        ${order.status === 'confirmed' || order.status === 'preparing' ? `
                            <button class="btn-cancel-order" onclick="cancelOrder('${order.id}')">
                                Annuler
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Afficher les détails d'une commande
    showOrderDetails(orderId) {
        const order = this.orderManager.getOrder(orderId);
        if (!order) {
            this.showMessage('Commande non trouvée', 'error');
            return;
        }
        
        const orderHTML = this.createOrderSummary(order);
        
        // Créer et afficher une modal avec les détails
        const modal = document.createElement('div');
        modal.className = 'modal order-details-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Détails de la commande</h2>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    ${orderHTML}
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        modal.style.display = 'block';
        
        // Fermer la modal
        modal.querySelector('.close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    
    showMessage(message, type) {
        if (window.app && window.app.showNotification) {
            window.app.showNotification(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
}

// Fonctions globales pour les interactions
window.viewOrderDetails = function(orderId) {
    if (window.orderUI) {
        window.orderUI.showOrderDetails(orderId);
    }
};

window.cancelOrder = function(orderId) {
    if (confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) {
        const result = window.orderManager.cancelOrder(orderId);
        if (result.success) {
            window.orderUI.showMessage('Commande annulée avec succès', 'success');
            // Recharger la liste des commandes
            location.reload();
        } else {
            window.orderUI.showMessage(result.error, 'error');
        }
    }
};

// Initialisation globale
window.OrderManager = OrderManager;
window.OrderUI = OrderUI;