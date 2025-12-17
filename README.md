# R&G - Boutique de Mode et Bijoux

Site e-commerce élégant pour la boutique R&G, spécialisée dans les vêtements de luxe et bijoux précieux.

## 🌟 Fonctionnalités

### Navigation
- **Logo centré** : Logo R&G avec design bleu et doré au centre de la barre de navigation
- **Menu déroulant étoilé** : Menu avec 3 étoiles donnant accès aux catégories principales
- **Navigation responsive** : Adaptation parfaite à tous les écrans

### Catégories
- **Vêtements Femme** : Collection élégante et moderne
- **Vêtements Homme** : Style raffiné et sophistiqué  
- **Bijoux** : Pièces précieuses et uniques

### Fonctionnalités E-commerce
- **Système de connexion/inscription** : Authentification utilisateur
- **Panier d'achat** : Gestion complète du panier avec compteur
- **Gestion des stocks** : Distinction entre articles en stock et sur demande
- **Filtres avancés** : Filtrage par catégorie, prix, matériau, stock
- **Design premium** : Interface inspirée des grandes marques de luxe

## 🎨 Design

### Thème Couleurs
- **Bleu Royal** (#1e3a8a) : Couleur principale pour l'élégance
- **Bleu Clair** (#3b82f6) : Accents et dégradés
- **Or** (#d4af37) : Touches de luxe et sophistication
- **Or Foncé** (#b8860b) : Détails et survols

### Caractéristiques Visuelles
- Dégradés bleu-or sophistiqués
- Animations subtiles et élégantes
- Effets de survol raffinés
- Typography professionnelle
- Icônes Font Awesome

## 🏗️ Architecture

Le projet utilise une **architecture MVC (Model-View-Controller)** pour une meilleure organisation et maintenabilité du code.

📖 **Documentation complète** : [Architecture MVC](docs/MVC_ARCHITECTURE.md)

## 📁 Structure du Projet (Architecture MVC)

```
R-G/
├── app/                        # Application MVC
│   ├── Config/                 # Configuration classes
│   │   └── Database.php        # Singleton PDO connection
│   ├── Controllers/            # Contrôleurs (logique métier)
│   │   ├── Admin/              # Admin controllers
│   │   │   ├── DashboardController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ProductController.php
│   │   │   └── UserController.php
│   │   ├── Api/                # API controllers
│   │   ├── Controller.php      # Base controller
│   │   ├── AuthController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   ├── HomeController.php
│   │   ├── PaymentController.php
│   │   ├── ProductController.php
│   │   └── UserController.php
│   ├── Models/                 # Modèles (accès données)
│   │   ├── Database.php        # Database model
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── User.php
│   │   └── Cart.php
│   ├── Views/                  # Vues (présentation HTML)
│   │   ├── layouts/            # Header, footer, etc.
│   │   ├── admin/              # Admin views
│   │   ├── auth/               # Login, register views
│   │   ├── cart/               # Cart views
│   │   ├── checkout/           # Checkout views
│   │   ├── home/               # Home page
│   │   ├── payment/            # Payment views
│   │   ├── products/           # Product views
│   │   └── user/               # User account views
│   ├── Services/               # Business logic services
│   │   ├── AuthService.php
│   │   ├── CartService.php
│   │   ├── CsrfService.php
│   │   └── EmailService.php
│   ├── Helpers/                # Helper functions
│   │   └── functions.php
│   └── Router.php              # Système de routage
├── bootstrap/                  # Application bootstrap
│   └── app.php                 # Initialization
├── public/                     # Point d'entrée public (document root)
│   ├── index.php               # Front controller (single entry point)
│   ├── .htaccess               # URL rewriting for clean URLs
│   ├── assets/                 # Static resources
│   │   ├── css/
│   │   ├── js/
│   │   ├── logo.png
│   │   └── logo.svg
│   ├── scripts/                # JavaScript files
│   ├── styles/                 # CSS files
│   ├── uploads/                # User uploaded files (products)
│   └── webhooks/               # Payment webhooks
├── routes/                     # Route definitions
│   └── web.php                 # Web routes (public + admin)
├── src/                        # Legacy code (for compatibility)
├── config/                     # Legacy configuration
├── database/                   # SQLite database files
├── docs/                       # Documentation
├── .env                        # Environment configuration (not in git)
├── .env.example                # Example environment file
├── autoload.php                # PSR-4 autoloader
├── .htaccess                   # Redirect to public/
└── README.md                   # This file
```

## 🚀 Fonctionnement

### Architecture MVC - Routing

Le projet utilise une **architecture MVC moderne** avec un système de routing centralisé :

#### Point d'entrée unique
- Toutes les requêtes passent par `public/index.php` (front controller)
- `.htaccess` redirige automatiquement vers `public/` 
- Clean URLs sans `.php` (ex: `/product/123` au lieu de `product.php?id=123`)

#### Routes principales
```
GET  /                          → HomeController@index
GET  /bijoux                    → ProductController@bijoux
GET  /vetements-femme           → ProductController@vetementsFemme
GET  /vetements-homme           → ProductController@vetementsHomme
GET  /product/{id}              → ProductController@show
GET  /cart                      → CartController@index
POST /cart/add                  → CartController@add
POST /cart/update               → CartController@update
GET  /checkout                  → CheckoutController@index
GET  /payment/success           → PaymentController@success
GET  /login                     → AuthController@login
POST /register                  → AuthController@register
GET  /admin                     → Admin\DashboardController@index
GET  /admin/products            → Admin\ProductController@index
GET  /admin/orders              → Admin\OrderController@index
```

Voir `routes/web.php` pour la liste complète des routes.

#### Autoloading PSR-4
- Namespace `Controllers\` → `app/Controllers/`
- Namespace `Models\` → `app/Models/`
- Namespace `Services\` → `app/Services/`
- Namespace `Config\` → `app/Config/`

### Navigation
- **Menu principal** : Accès via les 3 étoiles dorées
- **Logo cliquable** : Retour à l'accueil depuis toutes les pages
- **Icônes utilisateur** : Connexion et panier accessibles partout

### Produits
- **Fiches détaillées** : Nom, description, prix, statut stock
- **Images placeholder** : Icônes appropriées par catégorie
- **Ajout au panier** : Un clic pour ajouter un article
- **Filtres intelligents** : Combinaison de plusieurs critères

### Panier
- **Persistance** : Sauvegarde dans le localStorage
- **Compteur dynamique** : Affichage en temps réel
- **Gestion quantités** : Modification et suppression d'articles
- **Calcul total** : Prix total automatique

## 💎 Spécificités par Catégorie

### Vêtements Femme
- Filtres : Robes, Tailleurs, Blouses, Pantalons
- Prix : 0-100€, 100-200€, 200-500€, 500€+
- Icône : Silhouette féminine

### Vêtements Homme  
- Filtres : Costumes, Chemises, Pantalons, Vestes
- Prix : 0-150€, 150-300€, 300-600€, 600€+
- Icône : Homme en costume

### Bijoux
- Filtres : Colliers, Boucles, Bracelets, Bagues, Montres
- Matériaux : Or, Argent, Platine, Diamant
- Prix : 0-500€, 500-1000€, 1000-2000€, 2000€+
- Icône : Gemme avec effets scintillants

## 🔧 Technologies Utilisées

- **HTML5** : Structure sémantique moderne
- **CSS3** : Variables CSS, Flexbox, Grid, animations
- **JavaScript ES6+** : Classes, modules, fonctions fléchées
- **Font Awesome 6** : Icônes vectorielles
- **Design Responsive** : Mobile-first approach

## 📱 Compatibilité

- ✅ Ordinateurs de bureau
- ✅ Tablettes 
- ✅ Smartphones
- ✅ Navigateurs modernes (Chrome, Firefox, Safari, Edge)

## 🎯 Expérience Utilisateur

### Performance
- Chargement rapide avec CSS et JS optimisés
- Images SVG légères pour le logo
- Animations fluides à 60fps

### Accessibilité
- Navigation au clavier
- Contrastes respectés
- Textes alternatifs sur les images
- Structure sémantique claire

### Ergonomie
- Interface intuitive
- Feedback visuel sur toutes les actions
- Messages de confirmation et d'erreur
- Workflow d'achat simplifié

## 🚀 Installation et Déploiement

### Prérequis
- PHP 7.4+ (recommandé: PHP 8.0+)
- Serveur web Apache avec mod_rewrite activé
- SQLite3 ou MySQL
- Composer (optionnel, pour les dépendances futures)

### Installation Locale

1. **Cloner le repository**
   ```bash
   git clone https://github.com/votre-repo/R-G.git
   cd R-G
   ```

2. **Configurer l'environnement**
   ```bash
   cp .env.example .env
   ```
   Éditer `.env` avec vos paramètres :
   - Base de données (SQLite par défaut)
   - Clés API Stripe pour les paiements
   - Configuration SMTP pour les emails

3. **Permissions**
   ```bash
   chmod 755 public/uploads
   chmod 644 database.db
   ```

4. **Lancer le serveur de développement**
   ```bash
   php -S localhost:8000 -t public
   ```
   Accéder à : http://localhost:8000

### Déploiement Production

1. **Configuration Apache**
   - Le document root doit pointer vers `public/`
   - Vérifier que mod_rewrite est activé
   - `.htaccess` est déjà configuré

2. **Variables d'environnement**
   - Copier `.env.example` vers `.env`
   - Configurer avec les vraies clés de production
   - Ne **JAMAIS** commiter `.env` dans Git

3. **Sécurité**
   - Activer HTTPS
   - Configurer les permissions : `755` pour dossiers, `644` pour fichiers
   - Protéger les dossiers sensibles (app/, bootstrap/, config/, src/)

4. **Base de données**
   - Pour SQLite : vérifier les permissions sur `database.db`
   - Pour MySQL : créer la base et configurer dans `.env`

### Structure des URLs

Avec la configuration MVC, toutes les URLs passent par `public/index.php` :
- `http://votresite.com/` → Page d'accueil
- `http://votresite.com/product/123` → Fiche produit
- `http://votresite.com/admin` → Panel admin

Pas besoin de `.php` dans les URLs - tout est géré automatiquement !

## 📞 Contact

**R&G Boutique**
- Email : contact@rg-boutique.fr
- Téléphone : +33 1 23 45 67 89

---

*Développé avec élégance pour R&G - Votre destination mode et bijoux de luxe* ✨