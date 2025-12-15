<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';
require_once __DIR__ . '/../src/csrf.php';
require_once __DIR__ . '/../src/bootstrap.php';


// Debug temporaire
$u = current_user();
if (!$u) {
    die("❌ Pas d'utilisateur connecté. <a href='/login.php'>Se connecter</a>");
}
if (($u['role'] ?? '') !== 'admin') {
    die("❌ Accès refusé. Votre rôle: " . ($u['role'] ?? 'aucun') . ". Attendu: admin");
}
// Fin debug

require_admin();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = $_GET['msg'] ?? '';

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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Produits - Admin</title>
  <link rel="stylesheet" href="/styles/main.css">
  <link rel="stylesheet" href="/styles/admin.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="admin-container">
    <div class="admin-header">
      <h1><i class="fas fa-box"></i> Gestion des Produits</h1>
      <a href="/admin/" class="btn btn-outline">Retour</a>
    </div>
    
    <?php if ($msg === 'supprime'): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Produit supprimé avec succès.
      </div>
    <?php endif; ?>
    
    <div class="admin-actions">
      <a href="/admin/product_edit.php?action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Ajouter un produit
      </a>
    </div>
    
    <div class="table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Catégorie</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
          <tr>
            <td colspan="6" class="text-center">
              <i class="fas fa-inbox"></i>
              <p>Aucun produit trouvé.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><?= (int)$p['id'] ?></td>
              <td><?= htmlspecialchars($p['name'] ?? '') ?></td>
              <td><?= isset($p['price']) ? number_format((float)$p['price'], 2, ',', ' ') . ' €' : '' ?></td>
              <td>
                <span class="stock-badge <?= (int)($p['stock'] ?? 0) > 0 ? 'stock-available' : 'stock-empty' ?>">
                  <?= (int)($p['stock'] ?? 0) ?>
                </span>
              </td>
              <td>
                <?php if (!empty($p['category'])): ?>
                  <span class="category-badge"><?= htmlspecialchars($p['category']) ?></span>
                <?php else: ?>
                  <span class="text-muted">Non définie</span>
                <?php endif; ?>
              </td>
              <td class="actions">
                <a href="/admin/product_edit.php?action=edit&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-secondary">
                  <i class="fas fa-edit"></i> Modifier
                </a>
                <form action="/admin/products.php?action=delete&id=<?= (int)$p['id'] ?>" method="POST" style="display:inline" onsubmit="return confirm('Supprimer ce produit ?');">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i> Supprimer
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>