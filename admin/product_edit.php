<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/csrf.php';
require_admin();

<<<<<<< HEAD
// Compute base path for subdirectory deployments
$base_path = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$base_path = $base_path === '/' ? '' : rtrim($base_path, '');

$action = $_GET['action'] ?? 'edit';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
=======
// Active (temporairement) plus de logs si besoin
// ini_set('display_errors', '1'); error_reporting(E_ALL);
>>>>>>> 51b0590 (dernier version)

// Base path pour sous-dossiers
$base_path = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base_path === '/') $base_path = '';

/* =========================
   Fonctions inline produits
   ========================= */

// Détecte les colonnes existantes de la table products
if (!function_exists('products_schema')) {
    function products_schema(): array {
        $pdo = db();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $cols = [];
        try {
            if ($driver === 'sqlite') {
                $stmt = $pdo->query("PRAGMA table_info(products)");
                foreach ($stmt->fetchAll() as $row) {
                    $cols[] = is_array($row) ? ($row['name'] ?? $row[1] ?? '') : '';
                }
            } else {
                $stmt = $pdo->query("SHOW COLUMNS FROM products");
                foreach ($stmt->fetchAll() as $row) {
                    $cols[] = is_array($row) ? ($row['Field'] ?? $row[0] ?? '') : '';
                }
            }
            $cols = array_filter($cols);
        } catch (Throwable $e) {
            $cols = ['id','name','description','price','stock_quantity','image','category'];
        }

        $has = fn(string $c) => in_array($c, $cols, true);

        return [
            'id'          => 'id',
            'name'        => $has('name')        ? 'name' : null,
            'description' => $has('description') ? 'description' : null,
            'price'       => $has('price')       ? 'price' : null,
            'stock'       => $has('stock_quantity') ? 'stock_quantity' : ($has('stock') ? 'stock' : null),
            'image'       => $has('image') ? 'image' : ($has('image_url') ? 'image_url' : null),
            'category'    => $has('category')    ? 'category' : null,
            'columns'     => $cols,
        ];
    }
}

if (!function_exists('product_get')) {
    function product_get(int $id): ?array {
        $sch = products_schema();
        $select = [];
        $select[] = "{$sch['id']} AS id";
        if ($sch['name'])        $select[] = "{$sch['name']} AS name";
        if ($sch['description']) $select[] = "{$sch['description']} AS description";
        if ($sch['price'])       $select[] = "{$sch['price']} AS price";
        if ($sch['stock'])       $select[] = "{$sch['stock']} AS stock_quantity";
        if ($sch['image'])       $select[] = "{$sch['image']} AS image";
        if ($sch['category'])    $select[] = "{$sch['category']} AS category";

        $sql = "SELECT ".implode(", ", $select)." FROM products WHERE {$sch['id']} = ? LIMIT 1";
        $stmt = db()->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

if (!function_exists('product_create')) {
    function product_create(array $data): int {
        $sch = products_schema();

        $fields = [];
        $placeholders = [];
        $params = [];

        // Map data keys -> table columns
        $map = [
            'name'        => $sch['name'],
            'description' => $sch['description'],
            'price'       => $sch['price'],
            'stock'       => $sch['stock'],
            'category'    => $sch['category'],
            'image'       => $sch['image'],
        ];

        foreach ($map as $key => $col) {
            if ($col !== null && array_key_exists($key, $data)) {
                $fields[] = $col;
                $placeholders[] = '?';
                $val = $data[$key];
                if ($key === 'price')      $params[] = (float)$val;
                elseif ($key === 'stock')  $params[] = (int)$val;
                else                       $params[] = (string)$val;
            }
        }

        if (!$fields) {
            throw new RuntimeException("Aucun champ insérable détecté pour products (vérifie les colonnes).");
        }

        $sql = "INSERT INTO products (".implode(", ", $fields).") VALUES (".implode(", ", $placeholders).")";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return (int)db()->lastInsertId();
    }
}

if (!function_exists('product_update')) {
    function product_update(int $id, array $data): void {
        $sch = products_schema();
        $sets = [];
        $params = [];

        $map = [
            'name'        => $sch['name'],
            'description' => $sch['description'],
            'price'       => $sch['price'],
            'stock'       => $sch['stock'],
            'category'    => $sch['category'],
            'image'       => $sch['image'],
        ];

        foreach ($map as $key => $col) {
            if ($col !== null && array_key_exists($key, $data)) {
                $sets[] = "$col = ?";
                $val = $data[$key];
                if ($key === 'price')      $params[] = (float)$val;
                elseif ($key === 'stock')  $params[] = (int)$val;
                else                       $params[] = (string)$val;
            }
        }

        if (!$sets) return;

        $sql = "UPDATE products SET ".implode(", ", $sets)." WHERE {$sch['id']} = ?";
        $params[] = $id;

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
    }
}

if (!function_exists('product_image_column')) {
    function product_image_column(): string {
        $sch = products_schema();
        return $sch['image'] ?? 'image';
    }
}

if (!function_exists('product_update_image')) {
    function product_update_image(int $id, string $imagePath): void {
        $col = product_image_column();
        $sql = "UPDATE products SET {$col} = ? WHERE id = ?";
        $stmt = db()->prepare($sql);
        $stmt->execute([$imagePath, $id]);
    }
}

// Upload image minimaliste (stocke sous uploads/products/{id}/)
if (!function_exists('store_image_upload')) {
    function store_image_upload(array $file, int $productId): string {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload invalide.');
        }
        $tmp = $file['tmp_name'] ?? '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Fichier temporaire manquant.');
        }

        // Détection MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Type de fichier non autorisé: ' . $mime);
        }

        // Nom de fichier
        $orig = (string)($file['name'] ?? 'image');
        $base = preg_replace('~[^a-zA-Z0-9_-]+~', '-', pathinfo($orig, PATHINFO_FILENAME));
        $base = trim($base, '-');
        if ($base === '') $base = 'image';
        $ext  = $allowed[$mime];
        $name = $base . '-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;

        // Dossier destination
        $relDir = 'uploads/products/' . $productId;
        $absDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . $relDir;
        if ($absDir === false) {
            $absDir = __DIR__ . '/../' . $relDir;
        }
        if (!is_dir($absDir)) {
            if (!mkdir($absDir, 0775, true) && !is_dir($absDir)) {
                throw new RuntimeException('Impossible de créer le dossier: ' . $relDir);
            }
        }

        $absPath = rtrim($absDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($tmp, $absPath)) {
            throw new RuntimeException('Échec du déplacement du fichier.');
        }

        // Retourne le chemin relatif utilisable côté web
        $relPath = $relDir . '/' . $name; // ex: uploads/products/42/xxx.jpg
        return str_replace('\\', '/', $relPath);
    }
}

/* =========================
   Contrôleur produit
   ========================= */

// Détermine create ou edit
$action = $_GET['action'] ?? 'edit';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Valeurs par défaut
$mode = ($action === 'create' || $id <= 0) ? 'create' : 'edit';
$product = [
    'id' => 0,
    'name' => '',
    'description' => '',
    'price' => 0,
    'stock_quantity' => 0,
    'image' => '',
    'category' => 'bijoux'
];

// Récupération du produit si édition
if ($mode === 'edit') {
    $p = product_get($id);
    if (!$p) { http_response_code(404); exit('Produit introuvable'); }
    $product = $p;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD
    csrf_validate();
    $data = [
      'name' => trim($_POST['name'] ?? ''),
      'description' => trim($_POST['description'] ?? ''),
      'price' => (float)($_POST['price'] ?? 0),
      'stock' => (int)($_POST['stock'] ?? 0),
      'image_url' => trim($_POST['image_url'] ?? ''),
      'category' => trim($_POST['category'] ?? 'bijoux'),
    ];
    if ($data['name'] === '') {
      $msg = 'Le nom est requis.';
    } else {
      if ($action === 'create') {
        $newId = product_create($data);
        header('Location: ' . $base_path . '/admin/products.php');
        exit;
      } else {
        product_update($id, $data);
        header('Location: ' . $base_path . '/admin/products.php');
        exit;
      }
=======
    try {
        csrf_validate();

        // Lecture des champs
        $name  = trim($_POST['name'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $cat   = trim($_POST['category'] ?? 'bijoux');

        // Validations
        if ($name === '') $errors[] = 'Le nom est requis.';
        if (!is_numeric($_POST['price'] ?? null) || $price <= 0) $errors[] = 'Prix invalide.';
        if ($stock < 0) $stock = 0;

        // En création, si stock <= 0, on met 1 par défaut
        if ($mode === 'create' && $stock <= 0) $stock = 1;

        if (!$errors) {
            if ($mode === 'create') {
                $newId = product_create([
                    'name'        => $name,
                    'description' => $desc,
                    'price'       => $price,
                    'stock'       => $stock,
                    'category'    => $cat,
                    'image'       => ''
                ]);
                $id = $newId;
                $product['id'] = $id;
                $mode = 'edit';
            } else {
                product_update($product['id'], [
                    'name'        => $name,
                    'description' => $desc,
                    'price'       => $price,
                    'stock'       => $stock,
                    'category'    => $cat,
                    'image'       => $product['image'], // inchangé si pas de nouvel upload
                ]);
            }

            // Upload image si fournie
            if (!empty($_FILES['image']['name'])) {
                try {
                    $imgPath = store_image_upload($_FILES['image'], $id);
                    product_update_image($id, $imgPath);
                    $product['image'] = $imgPath;
                } catch (Throwable $e) {
                    $errors[] = 'Image non enregistrée: ' . $e->getMessage();
                }
            }

            if (!$errors) {
                $success = ($action === 'create' ? 'Produit créé.' : 'Produit mis à jour.');
                // Recharge les données pour refléter l’image et le stock
                $product = product_get($id);
            }
        }
    } catch (Throwable $e) {
        $errors[] = 'Erreur: ' . $e->getMessage();
>>>>>>> 51b0590 (dernier version)
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<<<<<<< HEAD
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $action === 'create' ? 'Ajouter' : 'Modifier' ?> un produit - Admin</title>
  <link rel="stylesheet" href="<?= $base_path ?>/styles/main.css">
=======
  <meta charset="UTF-8">
  <title><?= $mode === 'create' ? 'Créer' : 'Modifier' ?> un produit</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="<?= $base_path ?>/styles/main.css">
  <link rel="stylesheet" href="<?= $base_path ?>/styles/admin.css">
>>>>>>> 51b0590 (dernier version)
</head>
<body class="admin-body">
  <div class="admin-header">
    <h1><?= $mode === 'create' ? 'Nouveau produit' : 'Modifier produit #'.(int)$product['id'] ?></h1>
    <nav>
      <a href="<?= $base_path ?>/admin/index.php">← Retour liste</a>
    </nav>
  </div>

  <div class="admin-content">
    <?php if ($success): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert error">
        <ul><?php foreach ($errors as $er): ?><li><?= htmlspecialchars($er) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="product-form">
      <?= csrf_field() ?>
      <div class="form-row">
        <label>Nom
          <input type="text" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
        </label>
      </div>
      <div class="form-row">
        <label>Description
          <textarea name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </label>
      </div>
      <div class="two-cols">
        <label>Prix (€)
          <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars((string)($product['price'] ?? 0)) ?>">
        </label>
        <label>Stock
          <input type="number" name="stock" min="0" value="<?= (int)($product['stock_quantity'] ?? 0) ?>">
        </label>
      </div>
      <div class="form-row">
        <label>Catégorie
          <input type="text" name="category" value="<?= htmlspecialchars($product['category'] ?? 'bijoux') ?>">
        </label>
      </div>
      <div class="form-row">
        <label>Image (jpeg/png/webp/gif)
          <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
        </label>
        <?php if (!empty($product['image'])): ?>
          <div class="current-image">
            <img src="<?= $base_path . '/' . ltrim(htmlspecialchars($product['image']), '/') ?>" alt="">
            <span class="hint">Image actuelle</span>
          </div>
        <?php endif; ?>
      </div>
      <div class="form-actions">
        <button class="btn-primary" type="submit"><?= $mode === 'create' ? 'Créer' : 'Enregistrer' ?></button>
        <a class="btn" href="<?= $base_path ?>">Annuler</a>
      </div>
<<<<<<< HEAD
      <button type="submit" class="btn btn-primary"><?= $action === 'create' ? 'Créer' : 'Enregistrer' ?></button>
      <a href="<?= $base_path ?>/admin/products.php" class="btn">Annuler</a>
=======
>>>>>>> 51b0590 (dernier version)
    </form>
  </div>
</body>
</html>