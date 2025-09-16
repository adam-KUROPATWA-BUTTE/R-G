<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/csrf.php';
require_admin();

// Base path (ok sous-dossier)
$base_path = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base_path === '/') $base_path = '';

// Utilitaires DB
function column_exists(string $table, string $column): bool {
    try {
        $stmt = db()->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

function get_product(int $id): ?array {
    $sql = "SELECT id, name, description, price, category, image, stock_quantity, sizes
            FROM products
            WHERE id = ? LIMIT 1";
    $st = db()->prepare($sql);
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
}

function create_product(array $d) {
    $sql = "INSERT INTO products (name, description, price, category, stock_quantity, image, sizes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $st = db()->prepare($sql);
    $st->execute(array(
      (string)$d['name'],
      (string)$d['description'],
      (float)$d['price'],
      (string)$d['category'],
      (int)$d['stock_quantity'],
      (string)$d['image'],
      (string)($d['sizes'] ?? '')
    ));
    return db()->lastInsertId();
}
function update_product($id, array $d) {
    $sql = "UPDATE products
            SET name=?, description=?, price=?, category=?, stock_quantity=?, image=?, sizes=?
            WHERE id = ?";
    $st = db()->prepare($sql);
    $st->execute(array(
        $d['name'],$d['description'],$d['price'],$d['category'],
        $d['stock_quantity'],$d['image'],$d['sizes'] ?? '',$id
    ));
}

function store_image_upload(array $file, int $productId): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload invalide.');
    }
    $tmp = $file['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Fichier temporaire manquant.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: '';
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Type non autorisé: '.$mime);

    $orig = (string)($file['name'] ?? 'image');
    $base = preg_replace('~[^a-zA-Z0-9_-]+~','-', pathinfo($orig, PATHINFO_FILENAME));
    $base = $base ? trim($base,'-') : 'image';
    $ext  = $allowed[$mime];
    $name = $base.'-'.date('Ymd-His').'-'.substr(bin2hex(random_bytes(6)),0,12).'.'.$ext;

    $relDir = 'uploads/products/'.$productId;
    $absDir = __DIR__ . '/../' . $relDir;
    if (!is_dir($absDir) && !mkdir($absDir, 0775, true) && !is_dir($absDir)) {
        throw new RuntimeException('Impossible de créer '.$relDir);
    }

    $absPath = $absDir . '/' . $name;
    if (!move_uploaded_file($tmp, $absPath)) throw new RuntimeException('Échec déplacement fichier.');
    return str_replace('\\','/',$relDir.'/'.$name); // chemin relatif web, ex: uploads/products/42/xxx.jpg
}

// Contrôleur
$action = $_GET['action'] ?? 'edit';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode   = ($action === 'create' || $id <= 0) ? 'create' : 'edit';

$product = array(
  'id'=>0,'name'=>'','description'=>'','price'=>0.0,
  'category'=>'femme','image'=>'','stock_quantity'=>0,'sizes'=>''
);

if ($mode === 'edit') {
    $p = get_product($id);
    if (!$p) { http_response_code(404); exit('Produit introuvable'); }
    $product = $p;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_validate();

        $name  = trim((string)($_POST['name'] ?? ''));
        $desc  = trim((string)($_POST['description'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $cat   = trim((string)($_POST['category'] ?? 'femme'));
        $sizes = isset($_POST['sizes']) ? trim($_POST['sizes']) : '';
        $sizes_clean = strtoupper(str_replace(' ', '', $sizes));
        
        
        if ($sizes_clean !== '' && !preg_match('~^[A-Z0-9,/-]+$~', $sizes_clean)) {
                $errors[] = 'Format tailles invalide.';
        }

        if ($name === '') $errors[] = 'Le nom est requis.';
        if (!is_numeric($_POST['price'] ?? null) || $price <= 0) $errors[] = 'Prix invalide.';
        if ($stock < 0) $stock = 0;

        // En création, défaut = 1 (pour être "En stock")
        if ($mode === 'create' && $stock <= 0) $stock = 1;

        // Image actuelle (si pas de nouvel upload)
        $currentImage = (string)($product['image'] ?? '');

        // Upload image si fournie
        if (!empty($_FILES['image']['name'])) {
            try {
                // Si en création, crée d'abord un id temporaire en BDD pour avoir un dossier/id
                if ($mode === 'create') {
                    // On insère un stub pour obtenir un id
                    $tmpId = create_product([
                        'name' => $name ?: 'tmp',
                        'description' => $desc,
                        'price' => $price > 0 ? $price : 1,
                        'category' => $cat,
                        'stock_quantity' => $stock,
                        'image' => '',
                        'sizes' => $sizes_clean
                    ]);
                    $id = $tmpId;
                    $product['id'] = $id;
                    $mode = 'edit';
                }
                $rel = store_image_upload($_FILES['image'], $id);
                $currentImage = $rel;
            } catch (Throwable $e) {
                $errors[] = 'Image non enregistrée: '.$e->getMessage();
            }
        }

        if (!$errors) {
            if ($mode === 'create') {
                $newId = create_product([
                    'name' => $name,
                    'description' => $desc,
                    'price' => $price,
                    'category' => $cat,
                    'stock_quantity' => $stock,
                    'image' => $currentImage,
                    'sizes' => $sizes_clean
                ]);
                $id = $newId;
                $product['id'] = $id;
                $success = 'Produit créé.';
            } else {
                update_product($product['id'], [
                    'name' => $name,
                    'description' => $desc,
                    'price' => $price,
                    'category' => $cat,
                    'stock_quantity' => $stock,
                    'image' => $currentImage,
                    'sizes' => $sizes_clean
                ]);
                $success = 'Produit mis à jour.';
            }
            $product = get_product($id);
        }
    } catch (Throwable $e) {
        $errors[] = 'Erreur: '.$e->getMessage();
    }
}

// DA du site: header/footer front
$page_title = $mode === 'create' ? 'Créer un produit' : 'Modifier le produit #'.(int)$product['id'];
?>
<main class="main-content" style="max-width:960px;margin:1.5rem auto;">
  <h1 style="margin-bottom:1rem;"><?= $mode === 'create' ? 'Nouveau produit' : 'Modifier le produit #'.(int)$product['id'] ?></h1>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($errors): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $er): ?><li><?= htmlspecialchars($er) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="product-form" style="display:flex;flex-direction:column;gap:1rem;">
    <?= csrf_field() ?>
    <label>Nom
      <input type="text" name="name" required value="<?= htmlspecialchars($product['name'] ?? '') ?>">
    </label>
    <label>Description
      <textarea name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
    </label>
    <div style="display:flex;gap:1rem;">
      <label style="flex:1;">Prix (€)
        <input type="number" step="0.01" name="price" required value="<?= htmlspecialchars((string)($product['price'] ?? 0)) ?>">
      </label>
      <label style="flex:1;">Quantité en stock
        <input type="number" name="stock" min="0" value="<?= (int)($product['stock_quantity'] ?? 0) ?>">
        <small><?= ((int)($product['stock_quantity'] ?? 0) > 0) ? 'Actuellement: En stock' : 'Actuellement: Rupture de stock' ?></small>
      </label>
    </div>
    <label>Catégorie (ex: femme, homme, bijoux)
      <input type="text" name="category" value="<?= htmlspecialchars($product['category'] ?? 'femme') ?>">
    </label>
    <label>Tailles disponibles (CSV ex: XS,S,M,L,XL ou TU)
        <input type="text" name="sizes" value="<?= htmlspecialchars($product['sizes']) ?>" placeholder="XS,S,M,L,XL">
        <small style="display:block;font-size:.7rem;color:#555;">Laisser vide si le produit n’a pas de tailles.</small>
    </label>
    <label>Image (jpeg/png/webp/gif)
      <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
    </label>
    <?php if (!empty($product['image'])): ?>
      <?php $imgUrl = $base_path . '/' . ltrim((string)$product['image'], '/'); ?>
      <div style="display:flex;align-items:center;gap:1rem;">
        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="" style="height:80px;border:1px solid #e5e7eb;border-radius:8px;">
        <span>Image actuelle</span>
      </div>
    <?php endif; ?>
    <div style="display:flex;gap:.75rem;">
      <button class="btn-primary" type="submit"><?= $mode === 'create' ? 'Créer' : 'Enregistrer' ?></button>
      <a class="btn" href="<?= $base_path ?>">Annuler</a>
    </div>
  </form>
</main>

<style>
    /* Container principal */
    .main-content {
        max-width: 960px;
        margin: 2rem auto;
        background: var(--white);
        padding: 2rem;
        border-radius: 15px;
        box-shadow: var(--shadow);
    }

    h1 {
        color: var(--primary-blue);
        font-size: 2rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    /* Alerts */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .product-form label {
        display: flex;
        flex-direction: column;
        font-weight: 600;
        color: var(--primary-blue);
        font-size: 1rem;
    }

    .product-form input[type="text"],
    .product-form input[type="number"],
    .product-form input[type="file"],
    .product-form textarea {
        padding: 0.8rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        background-color: var(--white);
        transition: all 0.3s ease;
    }

    .product-form input:focus,
    .product-form textarea:focus {
        border-color: var(--gold);
        outline: none;
        box-shadow: 0 0 5px rgba(211, 170, 54, 0.3);
    }

    .product-form textarea {
        resize: vertical;
    }

    small {
        font-size: 0.85rem;
        color: var(--dark-gray);
    }

    /* Boutons */
    .btn-primary {
        background: var(--primary-blue);
        color: var(--white);
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: var(--light-blue);
        transform: translateY(-1px);
    }

    .btn {
        background: #f3f4f6;
        color: var(--primary-blue);
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: #e5e7eb;
    }

    img {
        border-radius: 8px;
        max-height: 100px;
        object-fit: cover;
    }

    @media (max-width: 768px) {
        .product-form {
            gap: 1rem;
        }

        h1 {
            font-size: 1.5rem;
        }
    }
</style>
