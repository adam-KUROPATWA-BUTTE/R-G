<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Administration R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/../../layouts/admin-header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-header-section">
                <h1>Gestion des Produits</h1>
                <a href="/admin/product_edit.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
            </div>
            
            <?php if (!empty($products)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="/uploads/<?= htmlspecialchars($product['image']) ?>" 
                                         alt="<?= htmlspecialchars($product['name']) ?>"
                                         class="product-thumb"
                                         onerror="this.src='data:image/svg+xml,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;50&quot; height=&quot;50&quot;><rect fill=&quot;%23f3f4f6&quot; width=&quot;50&quot; height=&quot;50&quot;/></svg>'">
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['category'] ?? 'N/A') ?></td>
                                <td><?= number_format($product['price'], 2) ?> €</td>
                                <td><?= $product['stock_quantity'] ?? 0 ?></td>
                                <td>
                                    <span class="status-badge status-<?= $product['status'] ?? 'active' ?>">
                                        <?= htmlspecialchars($product['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="/admin/product_edit.php?id=<?= $product['id'] ?>" 
                                       class="btn-icon" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn-icon btn-delete" 
                                            data-product-id="<?= $product['id'] ?>" 
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>Aucun produit pour le moment.</p>
                    <a href="/admin/product_edit.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Ajouter le premier produit
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="/scripts/admin-products.js"></script>
</body>
</html>
