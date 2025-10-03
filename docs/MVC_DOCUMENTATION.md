# Documentation MVC - R&G

## Structure MVC Complète

Le projet R&G dispose désormais d'une architecture MVC (Modèle-Vue-Contrôleur) complète avec tous les composants nécessaires.

## Architecture

```
R-G/
├── Core/                   # Classes de base du framework
│   ├── Autoloader.php     # Chargement automatique des classes
│   ├── Controller.php     # Contrôleur de base
│   ├── Model.php          # Modèle de base avec CRUD
│   ├── Router.php         # Routeur d'URLs
│   └── View.php           # Moteur de rendu des vues
│
├── Models/                 # Modèles de données
│   ├── Product.php        # Gestion des produits
│   ├── User.php           # Gestion des utilisateurs
│   ├── Cart.php           # Panier (session)
│   ├── Order.php          # Commandes
│   └── Category.php       # Catégories
│
├── Views/                  # Vues (templates)
│   ├── layouts/           # Layouts réutilisables
│   │   ├── main.php       # Layout principal
│   │   ├── header.php     # En-tête
│   │   ├── footer.php     # Pied de page
│   │   └── admin-header.php # En-tête admin
│   ├── home/              # Page d'accueil
│   ├── shop/              # Boutique
│   ├── products/          # Détails produit
│   ├── cart/              # Panier
│   ├── checkout/          # Commande
│   ├── auth/              # Authentification
│   ├── account/           # Compte utilisateur
│   └── admin/             # Administration
│
└── src/                    # Code backend existant
    ├── auth.php           # Authentification
    ├── db.php             # Base de données
    └── csrf.php           # Protection CSRF
```

## Utilisation des Modèles

### Product Model

```php
use App\Models\Product;

$productModel = new Product();

// Récupérer tous les produits actifs
$products = $productModel->getActive();

// Récupérer les produits par catégorie
$femmeProducts = $productModel->getByCategory('femme');

// Rechercher des produits
$results = $productModel->search('robe');

// Créer un produit
$id = $productModel->create([
    'name' => 'Robe Élégante',
    'price' => 299.99,
    'category' => 'femme',
    'stock_quantity' => 10
]);

// Mettre à jour un produit
$productModel->update($id, ['price' => 249.99]);

// Supprimer un produit
$productModel->delete($id);
```

### User Model

```php
use App\Models\User;

$userModel = new User();

// Créer un utilisateur
$userId = $userModel->createUser(
    'email@example.com',
    'password123',
    ['name' => 'John Doe']
);

// Vérifier les identifiants
$user = $userModel->verifyPassword('email@example.com', 'password123');

// Vérifier si un email existe
if ($userModel->emailExists('email@example.com')) {
    // Email déjà utilisé
}
```

### Cart Model

```php
use App\Models\Cart;

$cartModel = new Cart();

// Ajouter un article au panier
$cartModel->addItem(productId: 1, quantity: 2);

// Récupérer le panier avec les détails des produits
$items = $cartModel->getCartWithProducts();

// Obtenir le total
$total = $cartModel->getTotal();

// Vider le panier
$cartModel->clear();
```

### Order Model

```php
use App\Models\Order;

$orderModel = new Order();

// Créer une commande avec items
$orderId = $orderModel->createWithItems(
    [
        'user_id' => 1,
        'total' => 299.99,
        'payment_method' => 'card',
        'status' => 'pending'
    ],
    [
        ['product_id' => 1, 'quantity' => 2, 'price' => 149.99]
    ]
);

// Récupérer une commande avec ses items
$order = $orderModel->getWithItems($orderId);

// Mettre à jour le statut
$orderModel->updateStatus($orderId, 'paid');
```

## Utilisation des Contrôleurs

### Créer un contrôleur

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller {
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product();
    }
    
    public function index() {
        $products = $this->productModel->getActive();
        
        $this->render('shop/index', [
            'products' => $products,
            'pageTitle' => 'Nos Produits'
        ]);
    }
    
    public function show(int $id) {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $this->redirect('/404.php');
            return;
        }
        
        $this->render('products/show', [
            'product' => $product
        ]);
    }
}
```

### Méthodes utiles du contrôleur

```php
// Rendre une vue
$this->render('view/path', ['data' => $value]);

// Rediriger
$this->redirect('/url');

// Retourner du JSON
$this->json(['success' => true]);

// Récupérer un input
$email = $this->input('email', 'default@email.com');

// Vérifier l'authentification
$this->requireAuth();  // Redirige vers login si non connecté
$this->requireAdmin(); // Erreur 403 si pas admin

// Valider le token CSRF
$this->validateCSRF();
```

## Utilisation des Vues

### Structure d'une vue

Les vues sont des fichiers PHP qui reçoivent des variables extraites du contrôleur.

```php
<!-- Views/products/show.php -->
<div class="product-detail">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p class="price"><?= number_format($product['price'], 2) ?> €</p>
    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
</div>
```

### Utiliser un layout

Les vues sont automatiquement enveloppées dans le layout `main.php` par défaut. Le contenu de la vue est disponible dans `$content`.

Pour utiliser un layout différent ou aucun layout :

```php
// Dans le contrôleur
$this->view->render('view/path', ['data' => $value], 'custom-layout');
$this->view->render('view/path', ['data' => $value], null); // Sans layout
```

## Intégration avec le code existant

### Authentification

Le système MVC s'intègre avec les fonctions d'authentification existantes :

```php
// Dans un contrôleur
$currentUser = current_user();
if (!$currentUser) {
    $this->redirect('/login.php');
}

// Dans une vue
<?php $currentUser = current_user(); ?>
<?php if ($currentUser): ?>
    <p>Bienvenue <?= htmlspecialchars($currentUser['email']) ?></p>
<?php endif; ?>
```

### Base de données

Les modèles utilisent la connexion PDO existante via `db()` :

```php
// Déjà disponible dans tous les modèles via $this->db()
$pdo = $this->db();
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([1]);
```

### CSRF Protection

Tous les formulaires doivent inclure le token CSRF :

```php
<form method="POST" action="/action.php">
    <?= csrf_field() ?>
    <!-- Champs du formulaire -->
    <button type="submit">Envoyer</button>
</form>
```

Dans le contrôleur, valider le token :

```php
public function save() {
    $this->validateCSRF();
    // Traiter le formulaire
}
```

## Routage

Le routeur (`Core/Router.php`) permet de définir des routes :

```php
$router = new App\Core\Router();

// Routes GET
$router->get('/', 'HomeController@index');
$router->get('/products', 'ProductController@index');

// Routes POST
$router->post('/cart/add', 'CartController@add');

// Dispatcher
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
```

Les routes dynamiques sont supportées :

```php
// Dans Router.php, la route product/{id} est déjà définie
// Elle appelle ProductController@show avec l'ID en paramètre
```

## Exemples complets

### Page de produits avec panier

```php
<?php
// vetements-femme.php

require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/Core/Autoloader.php';
Autoloader::register();

use App\Models\Product;

$productModel = new Product();
$products = $productModel->getByCategory('femme');

$view = new App\Core\View();
$view->render('shop/index', [
    'products' => $products,
    'pageTitle' => 'Vêtements Femme',
    'categoryDescription' => 'Découvrez notre collection pour femme'
]);
```

### Page de détail produit

```php
<?php
// product.php

require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/Core/Autoloader.php';
Autoloader::register();

use App\Models\Product;

$productId = (int)($_GET['id'] ?? 0);

$productModel = new Product();
$product = $productModel->find($productId);

$view = new App\Core\View();
$view->render('products/show', [
    'product' => $product,
    'title' => $product ? $product['name'] . ' - R&G' : 'Produit non trouvé'
]);
```

### Admin - Liste des produits

```php
<?php
// admin/products.php

require_once __DIR__ . '/../src/auth.php';
require_admin();

require_once __DIR__ . '/../Core/Autoloader.php';
Autoloader::register();

use App\Models\Product;

$productModel = new Product();
$products = $productModel->all();

$view = new App\Core\View();
$view->render('admin/products/index', [
    'products' => $products
], null); // null = pas de layout (la vue a son propre HTML complet)
```

## Sécurité

1. **CSRF Protection** : Tous les formulaires utilisent `csrf_field()`
2. **Échappement HTML** : Utiliser `htmlspecialchars()` pour toutes les sorties
3. **Requêtes préparées** : Les modèles utilisent PDO avec prepared statements
4. **Validation** : Toujours valider les entrées utilisateur
5. **Authentification** : Utiliser `requireAuth()` et `requireAdmin()`

## Migration depuis l'ancien code

Pour migrer une page existante vers le MVC :

1. Extraire la logique métier dans un modèle
2. Extraire le HTML dans une vue
3. Créer ou réutiliser un contrôleur
4. Mettre à jour les chemins et variables

Exemple :

**Avant :**
```php
<?php
// old-page.php
require 'src/auth.php';
$pdo = db();
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>
<html>
<body>
<?php foreach ($products as $p): ?>
    <div><?= $p['name'] ?></div>
<?php endforeach; ?>
</body>
</html>
```

**Après :**
```php
<?php
// new-page.php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/Core/Autoloader.php';
Autoloader::register();

use App\Models\Product;
use App\Core\View;

$productModel = new Product();
$products = $productModel->getActive();

$view = new View();
$view->render('shop/index', ['products' => $products]);
```

## Support et Maintenance

Pour toute question ou problème avec l'architecture MVC :

1. Vérifier la syntaxe PHP : `php -l fichier.php`
2. Vérifier l'autoloader : Les classes doivent être dans le namespace `App\`
3. Vérifier les chemins : Les vues doivent être dans `Views/`
4. Vérifier les permissions : Les uploads nécessitent des permissions d'écriture

## Prochaines étapes

- Ajouter des tests unitaires pour les modèles
- Implémenter un système de validation
- Ajouter un système de migration de base de données
- Créer des helpers pour les vues (pagination, formulaires)
