<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/csrf.php';
require_admin();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';

if ($action === 'delete' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    product_delete($id);
    header('Location: /admin/products.php?msg=supprime');
    exit;
}
$products = products_list();
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Produits - Admin</title>
  <link rel="stylesheet" href="/styles/main.css">
</head>
<body>
  <div class="admin-container" style="max-width:1100px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1>Produits</h1>
    <p><a href="/admin/product_edit.php?action=create" class="btn btn-primary">Ajouter un produit</a> | <a href="/admin/">Retour</a></p>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th>ID</th><th>Nom</th><th>Prix</th><th>Stock</th><th>Catégorie</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= (int)$p['id'] ?></td>
          <td><?= htmlspecialchars($p['name'] ?? '') ?></td>
          <td><?= isset($p['price']) ? number_format((float)$p['price'], 2, ',', ' ') . ' €' : '' ?></td>
          <td><?= (int)($p['stock'] ?? 0) ?></td>
          <td><?= htmlspecialchars($p['category'] ?? '') ?></td>
          <td>
            <a href="/admin/product_edit.php?action=edit&id=<?= (int)$p['id'] ?>">Modifier</a>
            <form action="/admin/products.php?action=delete&id=<?= (int)$p['id'] ?>" method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce produit ?');">
              <?= csrf_field() ?>
              <button type="submit" style="background:#c00;color:#fff;padding:.25rem .5rem;border:none;border-radius:4px;cursor:pointer;">Supprimer</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>