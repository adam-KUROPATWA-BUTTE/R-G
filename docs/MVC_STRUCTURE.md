# R&G MVC Architecture Guide

## 🎯 Overview

R&G has been successfully migrated to a professional MVC architecture with:
- **PSR-4 Autoloading** - Automatic class loading
- **Centralized Routing** - All routes in `routes/web.php`
- **Single Entry Point** - `public/index.php`
- **Service Layer** - Business logic separation
- **Backward Compatibility** - Legacy code coexistence

## 📁 Directory Structure

```
app/
├── Controllers/        # Request handlers
│   ├── Admin/         # Admin panel controllers
│   └── Api/           # API endpoints
├── Models/            # Data layer
├── Services/          # Business logic
├── Views/             # Templates
│   ├── layouts/       # Shared layouts
│   ├── auth/          # Login/register
│   ├── products/      # Product views
│   ├── cart/          # Cart views
│   ├── checkout/      # Checkout views
│   └── admin/         # Admin views
└── Helpers/           # Helper functions

bootstrap/
└── app.php            # Application initialization

config/
├── app.php            # App configuration
└── database.php       # Database configuration

public/
├── index.php          # Entry point
└── assets/            # CSS, JS, images

routes/
└── web.php            # Route definitions
```

## 🛣️ Routing System

Routes are defined in `routes/web.php`:

```php
// Public routes
$router->get('/', 'HomeController@index');
$router->get('/product/{id}', 'ProductController@show');

// Admin routes
$router->get('/admin', 'Admin\DashboardController@index');

// API routes
$router->post('/api/stripe/create-session', 'Api\StripeController@createSession');
```

## 🎮 Controllers

### Base Controller
All controllers extend `Controllers\Controller` with methods:
- `view($view, $data)` - Render a view
- `redirect($url)` - Redirect to URL
- `json($data, $code)` - Return JSON
- `requireAuth()` - Require authentication
- `requireAdmin()` - Require admin role
- `validateCsrf()` - Validate CSRF token

### Example Controller

```php
namespace Controllers;

class ProductController extends Controller {
    public function show(array $params): void {
        $id = (int)$params['id'];
        $product = (new \Models\Product())->getById($id);
        
        $this->view('products.show', ['product' => $product]);
    }
}
```

## 📊 Models

Models handle database operations:

```php
namespace Models;

class Product {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll(): array {
        return $this->db->query("SELECT * FROM products")->fetchAll();
    }
}
```

## 🔧 Services

Services contain business logic:

### AuthService
```php
$auth = new \Services\AuthService();
$user = $auth->getCurrentUser();
$auth->login($email, $password);
```

### CsrfService
```php
$csrf = new \Services\CsrfService();
$token = $csrf->getToken();
```

### EmailService
```php
$email = new \Services\EmailService();
$email->sendOrderConfirmation($order, $items);
```

## 👁️ Views

Views use dot notation: `products.show` → `app/Views/products/show.php`

```php
<?php
$page_title = 'Product Details';
require __DIR__ . '/../layouts/header.php';
?>

<main>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

## ⚙️ Configuration

Application settings in `config/app.php`:

```php
return [
    'name' => 'R&G',
    'env' => 'production',
    'timezone' => 'Europe/Paris',
    'session' => [
        'name' => 'rg_session',
        'lifetime' => 120,
    ],
];
```

## 🔄 Migration Status

### ✅ Completed
- Core MVC structure
- Controllers for all main features
- Admin panel controllers
- API controllers
- Auth system
- Layouts and partials
- Service layer
- PSR-4 autoloading
- Centralized routing

### ⚙️ In Progress
- Admin panel views
- Payment success/cancel views

### 🔒 Backward Compatibility
Legacy code in `src/` is still loaded for compatibility:
- Old functions coexist with new services
- Gradual migration without breaking changes
- New features use MVC, old code remains functional

## 🚀 Best Practices

1. **Use MVC for new features** - Always follow the pattern
2. **Type declarations** - Use strict types
3. **Validate input** - Sanitize user data
4. **CSRF protection** - Use `validateCsrf()` for forms
5. **Security** - Require auth/admin where needed
6. **Keep controllers thin** - Move logic to models/services
7. **DRY principle** - Reuse code via services

## 📝 Adding New Features

1. Create controller in `app/Controllers/`
2. Create model in `app/Models/` if needed
3. Create views in `app/Views/`
4. Add routes in `routes/web.php`
5. Test thoroughly

---

**Version:** 2.0 (MVC Architecture)  
**Last Updated:** December 2024
