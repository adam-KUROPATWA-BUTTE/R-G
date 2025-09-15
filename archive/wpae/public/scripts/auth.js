// Système d'authentification amélioré pour R&G
class AuthManager {
    constructor() {
        this.currentUser = null;
        this.isLoggedIn = false;
        this.users = this.loadUsers();
        this.maxLoginAttempts = 3;
        this.loadCurrentSession();
    }
    
    // Charger les utilisateurs depuis localStorage
    loadUsers() {
        const savedUsers = localStorage.getItem('rg_users');
        return savedUsers ? JSON.parse(savedUsers) : {};
    }
    
    // Sauvegarder les utilisateurs
    saveUsers() {
        localStorage.setItem('rg_users', JSON.stringify(this.users));
    }
    
    // Charger la session actuelle
    loadCurrentSession() {
        const savedSession = localStorage.getItem('rg_current_user');
        if (savedSession) {
            const sessionData = JSON.parse(savedSession);
            // Vérifier si la session n'a pas expiré (24h)
            if (new Date() - new Date(sessionData.loginTime) < 24 * 60 * 60 * 1000) {
                this.currentUser = sessionData.user;
                this.isLoggedIn = true;
            } else {
                this.logout();
            }
        }
    }
    
    // Sauvegarder la session
    saveCurrentSession() {
        if (this.isLoggedIn && this.currentUser) {
            const sessionData = {
                user: this.currentUser,
                loginTime: new Date().toISOString()
            };
            localStorage.setItem('rg_current_user', JSON.stringify(sessionData));
        } else {
            localStorage.removeItem('rg_current_user');
        }
    }
    
    // Chiffrement simple pour les mots de passe (simulation)
    hashPassword(password) {
        // Dans une vraie application, utilisez bcrypt ou similar
        let hash = 0;
        for (let i = 0; i < password.length; i++) {
            const char = password.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return hash.toString();
    }
    
    // Valider l'email
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Valider le mot de passe
    validatePassword(password) {
        return password.length >= 6;
    }
    
    // Inscription
    register(userData) {
        const { name, email, password, confirmPassword } = userData;
        
        // Validations
        if (!name || name.trim().length < 2) {
            return { success: false, error: 'Le nom doit contenir au moins 2 caractères' };
        }
        
        if (!this.validateEmail(email)) {
            return { success: false, error: 'Email invalide' };
        }
        
        if (!this.validatePassword(password)) {
            return { success: false, error: 'Le mot de passe doit contenir au moins 6 caractères' };
        }
        
        if (password !== confirmPassword) {
            return { success: false, error: 'Les mots de passe ne correspondent pas' };
        }
        
        // Vérifier si l'utilisateur existe déjà
        if (this.users[email]) {
            return { success: false, error: 'Un compte existe déjà avec cet email' };
        }
        
        // Créer le nouvel utilisateur
        const newUser = {
            id: Date.now().toString(),
            name: name.trim(),
            email: email.toLowerCase(),
            passwordHash: this.hashPassword(password),
            createdAt: new Date().toISOString(),
            profile: {
                phone: '',
                birthDate: '',
                addresses: []
            },
            orders: [],
            preferences: {
                newsletter: true,
                notifications: true
            }
        };
        
        this.users[email] = newUser;
        this.saveUsers();
        
        return { success: true, user: newUser };
    }
    
    // Connexion
    login(email, password) {
        email = email.toLowerCase();
        const user = this.users[email];
        
        if (!user) {
            return { success: false, error: 'Email ou mot de passe incorrect' };
        }
        
        // Vérifier les tentatives de connexion
        const now = new Date();
        if (user.lastFailedLogin) {
            const timeSinceLastAttempt = now - new Date(user.lastFailedLogin);
            if (user.failedLoginAttempts >= this.maxLoginAttempts && timeSinceLastAttempt < 15 * 60 * 1000) {
                return { 
                    success: false, 
                    error: 'Compte temporairement bloqué. Réessayez dans 15 minutes.' 
                };
            }
        }
        
        // Vérifier le mot de passe
        if (user.passwordHash !== this.hashPassword(password)) {
            user.failedLoginAttempts = (user.failedLoginAttempts || 0) + 1;
            user.lastFailedLogin = now.toISOString();
            this.saveUsers();
            
            return { success: false, error: 'Email ou mot de passe incorrect' };
        }
        
        // Connexion réussie
        user.failedLoginAttempts = 0;
        user.lastLogin = now.toISOString();
        this.saveUsers();
        
        this.currentUser = user;
        this.isLoggedIn = true;
        this.saveCurrentSession();
        
        return { success: true, user: user };
    }
    
    // Déconnexion
    logout() {
        this.currentUser = null;
        this.isLoggedIn = false;
        this.saveCurrentSession();
    }
    
    // Mettre à jour le profil
    updateProfile(profileData) {
        if (!this.isLoggedIn) {
            return { success: false, error: 'Vous devez être connecté' };
        }
        
        const allowedFields = ['name', 'phone', 'birthDate'];
        const updatedData = {};
        
        // Valider et filtrer les données
        allowedFields.forEach(field => {
            if (profileData[field] !== undefined) {
                if (field === 'name' && profileData[field].trim().length < 2) {
                    return { success: false, error: 'Le nom doit contenir au moins 2 caractères' };
                }
                updatedData[field] = profileData[field];
            }
        });
        
        // Mettre à jour l'utilisateur
        if (updatedData.name) this.currentUser.name = updatedData.name;
        if (updatedData.phone) this.currentUser.profile.phone = updatedData.phone;
        if (updatedData.birthDate) this.currentUser.profile.birthDate = updatedData.birthDate;
        
        // Sauvegarder
        this.users[this.currentUser.email] = this.currentUser;
        this.saveUsers();
        this.saveCurrentSession();
        
        return { success: true, user: this.currentUser };
    }
    
    // Ajouter une adresse
    addAddress(addressData) {
        if (!this.isLoggedIn) {
            return { success: false, error: 'Vous devez être connecté' };
        }
        
        const requiredFields = ['name', 'street', 'city', 'postalCode', 'country'];
        for (const field of requiredFields) {
            if (!addressData[field] || addressData[field].trim() === '') {
                return { success: false, error: `Le champ ${field} est requis` };
            }
        }
        
        const newAddress = {
            id: Date.now().toString(),
            name: addressData.name.trim(),
            street: addressData.street.trim(),
            city: addressData.city.trim(),
            postalCode: addressData.postalCode.trim(),
            country: addressData.country.trim(),
            isDefault: this.currentUser.profile.addresses.length === 0
        };
        
        this.currentUser.profile.addresses.push(newAddress);
        this.users[this.currentUser.email] = this.currentUser;
        this.saveUsers();
        this.saveCurrentSession();
        
        return { success: true, address: newAddress };
    }
    
    // Supprimer une adresse
    removeAddress(addressId) {
        if (!this.isLoggedIn) {
            return { success: false, error: 'Vous devez être connecté' };
        }
        
        const addressIndex = this.currentUser.profile.addresses.findIndex(addr => addr.id === addressId);
        if (addressIndex === -1) {
            return { success: false, error: 'Adresse non trouvée' };
        }
        
        this.currentUser.profile.addresses.splice(addressIndex, 1);
        this.users[this.currentUser.email] = this.currentUser;
        this.saveUsers();
        this.saveCurrentSession();
        
        return { success: true };
    }
    
    // Changer de mot de passe
    changePassword(currentPassword, newPassword) {
        if (!this.isLoggedIn) {
            return { success: false, error: 'Vous devez être connecté' };
        }
        
        if (this.currentUser.passwordHash !== this.hashPassword(currentPassword)) {
            return { success: false, error: 'Mot de passe actuel incorrect' };
        }
        
        if (!this.validatePassword(newPassword)) {
            return { success: false, error: 'Le nouveau mot de passe doit contenir au moins 6 caractères' };
        }
        
        this.currentUser.passwordHash = this.hashPassword(newPassword);
        this.users[this.currentUser.email] = this.currentUser;
        this.saveUsers();
        
        return { success: true };
    }
    
    // Obtenir l'utilisateur actuel
    getCurrentUser() {
        return this.currentUser;
    }
    
    // Vérifier si connecté
    isUserLoggedIn() {
        return this.isLoggedIn;
    }
    
    // Ajouter une commande à l'historique
    addOrder(orderData) {
        if (!this.isLoggedIn) {
            return { success: false, error: 'Vous devez être connecté' };
        }
        
        const order = {
            id: Date.now().toString(),
            date: new Date().toISOString(),
            items: orderData.items,
            total: orderData.total,
            paymentMethod: orderData.paymentMethod,
            status: 'confirmed',
            deliveryAddress: orderData.deliveryAddress || null,
            promoCode: orderData.promoCode || null
        };
        
        this.currentUser.orders.push(order);
        this.users[this.currentUser.email] = this.currentUser;
        this.saveUsers();
        this.saveCurrentSession();
        
        return { success: true, order: order };
    }
    
    // Obtenir l'historique des commandes
    getOrderHistory() {
        if (!this.isLoggedIn) {
            return [];
        }
        
        return this.currentUser.orders.sort((a, b) => new Date(b.date) - new Date(a.date));
    }
}

// Interface utilisateur pour l'authentification
class AuthUI {
    constructor(authManager) {
        this.authManager = authManager;
        this.setupEventListeners();
        this.updateUI();
    }
    
    setupEventListeners() {
        // Écouter les soumissions de formulaires
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'loginForm') {
                e.preventDefault();
                this.handleLogin(e.target);
            } else if (e.target.id === 'registerForm') {
                e.preventDefault();
                this.handleRegister(e.target);
            }
        });
        
        // Écouter les clics sur les boutons
        document.addEventListener('click', (e) => {
            if (e.target.id === 'logoutBtn') {
                this.handleLogout();
            }
        });
    }
    
    handleLogin(form) {
        const email = form.querySelector('input[type="email"]').value;
        const password = form.querySelector('input[type="password"]').value;
        
        const result = this.authManager.login(email, password);
        
        if (result.success) {
            this.showMessage('Connexion réussie !', 'success');
            this.updateUI();
            this.closeModal();
        } else {
            this.showMessage(result.error, 'error');
        }
    }
    
    handleRegister(form) {
        const formData = new FormData(form);
        const userData = {
            name: form.querySelector('input[placeholder="Nom complet"]').value,
            email: form.querySelector('input[type="email"]').value,
            password: form.querySelector('input[type="password"]').value,
            confirmPassword: form.querySelectorAll('input[type="password"]')[1].value
        };
        
        const result = this.authManager.register(userData);
        
        if (result.success) {
            this.showMessage('Inscription réussie !', 'success');
            // Connecter automatiquement l'utilisateur
            this.authManager.login(userData.email, userData.password);
            this.updateUI();
            this.closeModal();
        } else {
            this.showMessage(result.error, 'error');
        }
    }
    
    handleLogout() {
        this.authManager.logout();
        this.showMessage('Déconnexion réussie', 'info');
        this.updateUI();
    }
    
    updateUI() {
        const loginBtn = document.getElementById('loginBtn');
        if (loginBtn) {
            if (this.authManager.isUserLoggedIn()) {
                const user = this.authManager.getCurrentUser();
                loginBtn.innerHTML = `<i class="fas fa-user-check"></i>`;
                loginBtn.title = `Connecté en tant que ${user.name}`;
            } else {
                loginBtn.innerHTML = `<i class="fas fa-user"></i>`;
                loginBtn.title = 'Se connecter';
            }
        }
        
        // Mettre à jour les liens de navigation si connecté
        this.updateNavigationForUser();
    }
    
    updateNavigationForUser() {
        if (!this.authManager.isUserLoggedIn()) return;
        
        // Ajouter des liens vers le profil et les commandes dans le menu
        const dropdownContent = document.getElementById('dropdownContent');
        if (dropdownContent && !dropdownContent.querySelector('.user-links')) {
            const userLinks = document.createElement('div');
            userLinks.className = 'user-links';
            userLinks.innerHTML = `
                <hr>
                <a href="pages/profil.html">Mon Profil</a>
                <a href="pages/commandes.html">Mes Commandes</a>
                <a href="#" id="logoutBtn">Déconnexion</a>
            `;
            dropdownContent.appendChild(userLinks);
        }
    }
    
    closeModal() {
        const loginModal = document.getElementById('loginModal');
        if (loginModal && window.app && window.app.hideModal) {
            window.app.hideModal(loginModal);
        }
    }
    
    showMessage(message, type) {
        if (window.app && window.app.showNotification) {
            window.app.showNotification(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
}

// Initialisation globale
window.AuthManager = AuthManager;
window.AuthUI = AuthUI;