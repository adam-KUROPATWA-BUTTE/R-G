# Architecture MVC - R&G Boutique

## 📐 Vue d'ensemble

Le projet R&G a été refactorisé en suivant le pattern MVC (Model-View-Controller) pour une meilleure organisation, maintenabilité et séparation des responsabilités.

## 🗂️ Structure des Dossiers

```
R-G/
├── app/                        # Application principale
│   ├── Controllers/           # Contrôleurs (logique métier)
│   │   ├── Controller.php     # Contrôleur de base
│   │   ├── HomeController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   └── UserController.php
│   ├── Models/                # Modèles (accès aux données)
│   │   ├── Database.php       # Gestionnaire de connexion PDO
│   │   ├── Product.php        # Gestion des produits
│   │   ├── Cart.php           # Gestion du panier
│   │   ├── Order.php          # Gestion des commandes
│   │   └── User.php           # Gestion des utilisateurs
│   ├── Views/                 # Vues (présentation HTML)
│   │   ├── layouts/           # Layouts réutilisables
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── home/              # Vues de la page d'accueil
│   │   ├── products/          # Vues des produits
│   │   ├── cart/              # Vues du panier
│   │   ├── checkout/          # Vues du processus de paiement
│   │   └── user/              # Vues utilisateur
│   └── Router.php             # Système de routage
├── public/                     # Point d'entrée public
│   ├── index.php              # Point d'entrée unique
│   ├── assets/                # Ressources statiques
│   ├── styles/                # Fichiers CSS
│   ├── scripts/               # Fichiers JavaScript
│   ├── uploads/               # Fichiers uploadés
│   └── webhooks/              # Webhooks (Stripe, etc.)
├── config/                     # Configuration
│   ├── config.php             # Configuration générale
│   └── database.php           # Configuration base de données
├── routes/                     # Définition des routes
│   └── web.php                # Routes web
├── src/                        # Code legacy (conservé pour compatibilité)
│   ├── bootstrap.php          # Initialisation session
│   ├── auth.php               # Fonctions d'authentification
│   ├── csrf.php               # Protection CSRF
│   ├── functions.php          # Fonctions utilitaires
│   └── ...
├── database/                   # Fichiers de base de données
│   ├── database.sql           # Structure de la base
│   └── database_migration_*.sql
├── dev/                        # Outils de développement
│   ├── test_api.php
│   └── debug_*.php
├── autoload.php               # Autoloader PSR-4
├── .htaccess                  # Configuration Apache
└── README.md                  # Documentation principale
```

## 🔄 Flux de Requête

1. **Requête HTTP** → `public/index.php`
2. **Routage** → `Router.php` trouve la route correspondante
3. **Contrôleur** → Traite la logique métier
4. **Modèle** → Accède/manipule les données
5. **Vue** → Affiche la présentation HTML
6. **Réponse HTTP** → Envoyée au client

## 🎯 Principes MVC

### Models (Modèles)

**Responsabilités :**
- Accès et manipulation des données
- Interaction avec la base de données
- Logique métier liée aux données
- Validation des données

**Exemple :**
```php
// app/Models/Product.php
$productModel = new \Models\Product();
$product = $productModel->getById($id);
$products = $productModel->getAll('bijoux');
```

### Views (Vues)

**Responsabilités :**
- Présentation HTML/CSS
- Affichage des données
- Pas de logique métier
- Templates réutilisables

**Exemple :**
```php
// app/Views/products/show.php
<?php require __DIR__ . '/../layouts/header.php'; ?>
<h1><?= htmlspecialchars($product['name']) ?></h1>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

### Controllers (Contrôleurs)

**Responsabilités :**
- Orchestration entre Models et Views
- Traitement des requêtes HTTP
- Validation des données utilisateur
- Gestion des sessions et redirections

**Exemple :**
```php
// app/Controllers/ProductController.php
public function show(array $params): void
{
    $id = (int)$params['id'];
    $product = $this->productModel->getById($id);
    $this->view('products.show', ['product' => $product]);
}
```

## 🛣️ Système de Routage

Les routes sont définies dans `routes/web.php` :

```php
// Route simple
$router->get('/', 'HomeController@index');

// Route avec paramètre
$router->get('/product/{id}', 'ProductController@show');

// Route POST
$router->post('/cart/add', 'CartController@add');

// Route acceptant GET et POST
$router->any('/login', 'UserController@login');
```

### Routes Disponibles

| Méthode | Route | Contrôleur | Description |
|---------|-------|------------|-------------|
| GET | `/` | HomeController@index | Page d'accueil |
| GET | `/products` | ProductController@index | Liste des produits |
| GET | `/product/{id}` | ProductController@show | Détail produit |
| GET | `/bijoux` | ProductController@bijoux | Catégorie bijoux |
| GET | `/vetements-femme` | ProductController@vetementsFemme | Vêtements femme |
| GET | `/vetements-homme` | ProductController@vetementsHomme | Vêtements homme |
| GET | `/cart` | CartController@index | Afficher panier |
| POST | `/cart/add` | CartController@add | Ajouter au panier |
| POST | `/cart/update` | CartController@update | Mettre à jour panier |
| POST | `/cart/remove` | CartController@remove | Retirer du panier |
| POST | `/cart/clear` | CartController@clear | Vider le panier |
| GET | `/checkout` | CheckoutController@index | Page de paiement |
| GET | `/checkout/success` | CheckoutController@success | Paiement réussi |
| GET | `/checkout/cancel` | CheckoutController@cancel | Paiement annulé |
| ANY | `/login` | UserController@login | Connexion |
| ANY | `/register` | UserController@register | Inscription |
| GET | `/logout` | UserController@logout | Déconnexion |
| GET | `/compte` | UserController@account | Compte utilisateur |

## 🔧 Configuration

### Base de données
Configurez la connexion dans `config/database.php` :

```php
return [
    'type' => 'sqlite',
    'path' => __DIR__ . '/../database.db',
];
```

### Application
Configurez l'application dans `config/config.php` :

```php
return [
    'app' => [
        'name' => 'R&G - Boutique',
        'env' => 'development',
        'debug' => true,
    ],
    // ...
];
```

## 🔐 Sécurité

- **Protection CSRF** : Tous les formulaires POST
- **Requêtes préparées** : PDO avec paramètres liés
- **Échappement des sorties** : `htmlspecialchars()` dans les vues
- **Sessions sécurisées** : Configuration stricte
- **Validation des données** : Côté serveur et modèles

## 📝 Bonnes Pratiques

### Création d'un nouveau contrôleur

```php
<?php
namespace Controllers;

class MonController extends Controller
{
    public function index(): void
    {
        $this->view('mon_dossier.index');
    }
    
    public function show(array $params): void
    {
        $id = (int)$params['id'];
        // Logique...
        $this->view('mon_dossier.show', ['data' => $data]);
    }
}
```

### Création d'un nouveau modèle

```php
<?php
namespace Models;

use PDO;

class MonModel
{
    private PDO $pdo;
    
    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }
    
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM ma_table");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### Création d'une nouvelle vue

```php
<?php
// app/Views/mon_dossier/ma_vue.php
$page_title = 'Mon Titre - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<main class="main-content">
    <h1><?= htmlspecialchars($data['titre']) ?></h1>
    <!-- Contenu -->
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

### Ajout d'une nouvelle route

Dans `routes/web.php` :

```php
$router->get('/ma-route', 'MonController@maMethode');
$router->post('/ma-route/action', 'MonController@monAction');
```

## 🔄 Migration depuis l'Ancienne Structure

Les anciens fichiers PHP à la racine sont conservés pour compatibilité. Le système fonctionne en parallèle :

- **Nouvelle architecture** : `/product/{id}` → MVC
- **Ancienne architecture** : `/product.php?id=1` → Fichiers legacy

Le `.htaccess` redirige intelligemment vers le bon système.

## 🚀 Déploiement

1. Configurez Apache pour pointer vers le dossier racine
2. Le `.htaccess` redirige toutes les requêtes vers `public/index.php`
3. Configurez la base de données dans `config/database.php`
4. Définissez les variables d'environnement pour Stripe, etc.

## 📚 Ressources

- [Pattern MVC](https://fr.wikipedia.org/wiki/Mod%C3%A8le-vue-contr%C3%B4leur)
- [PSR-4 Autoloader](https://www.php-fig.org/psr/psr-4/)
- [Documentation PHP PDO](https://www.php.net/manual/fr/book.pdo.php)

## 🤝 Contribution

Pour contribuer au projet :

1. Suivez l'architecture MVC
2. Utilisez les namespaces appropriés
3. Respectez les conventions de nommage
4. Documentez votre code
5. Testez vos modifications

## 📞 Support

Pour toute question sur l'architecture MVC du projet, consultez cette documentation ou contactez l'équipe de développement.
