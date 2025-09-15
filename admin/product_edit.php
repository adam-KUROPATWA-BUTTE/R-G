<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/csrf.php';
require_admin();

// Compute base path for subdirectory deployments
$base_path = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$base_path = $base_path === '/' ? '' : rtrim($base_path, '');

$action = $_GET['action'] ?? 'edit';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = [
  'name' => '', 'description' => '', 'price' => 0, 'stock' => 0, 'image_url' => '', 'category' => 'bijoux'
];

if ($action === 'edit' && $id > 0) {
    $p = product_get($id);
    if (!$p) { http_response_code(404); exit('Produit introuvable'); }
    $product = $p;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $action === 'create' ? 'Ajouter' : 'Modifier' ?> un produit - Admin</title>
  <link rel="stylesheet" href="<?= $base_path ?>/styles/main.css">
</head>
<body>
  <div class="admin-container" style="max-width:800px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1><?= $action === 'create' ? 'Ajouter' : 'Modifier' ?> un produit</h1>
    <?php if ($msg): ?><div class="alert alert-error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Nom</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>" style="width:100%;">
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="5" style="width:100%;"><?= htmlspecialchars($product['description']) ?></textarea>
      </div>
      <div class="form-group">
        <label>Prix (€)</label>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars((string)$product['price']) ?>">
      </div>
      <div class="form-group">
        <label>Stock</label>
        <input type="number" name="stock" value="<?= (int)$product['stock'] ?>">
      </div>
      <div class="form-group">
        <label>Image URL</label>
        <input type="url" name="image_url" value="<?= htmlspecialchars($product['image_url']) ?>" style="width:100%;">
      </div>
      <div class="form-group">
        <label>Catégorie</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>">
      </div>
      <button type="submit" class="btn btn-primary"><?= $action === 'create' ? 'Créer' : 'Enregistrer' ?></button>
      <a href="<?= $base_path ?>/admin/products.php" class="btn">Annuler</a>
    </form>
  </div>
</body>
</html>