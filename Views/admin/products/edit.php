<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Modifier' : 'Ajouter' ?> un produit - Administration R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/../../layouts/admin-header.php'; ?>
    
    <main class="admin-main">
        <div class="admin-container">
            <h1><?= $isEdit ? 'Modifier' : 'Ajouter' ?> un produit</h1>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <?= csrf_field() ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nom du produit *</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Catégorie *</label>
                        <select id="category" name="category" required>
                            <option value="">Sélectionner...</option>
                            <option value="femme" <?= ($product['category'] ?? '') === 'femme' ? 'selected' : '' ?>>Femme</option>
                            <option value="homme" <?= ($product['category'] ?? '') === 'homme' ? 'selected' : '' ?>>Homme</option>
                            <option value="bijoux" <?= ($product['category'] ?? '') === 'bijoux' ? 'selected' : '' ?>>Bijoux</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Prix (€) *</label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               step="0.01" 
                               min="0"
                               value="<?= htmlspecialchars($product['price'] ?? '') ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">Quantité en stock *</label>
                        <input type="number" 
                               id="stock_quantity" 
                               name="stock_quantity" 
                               min="0"
                               value="<?= htmlspecialchars($product['stock_quantity'] ?? 0) ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="sizes">Tailles (séparées par des virgules)</label>
                    <input type="text" 
                           id="sizes" 
                           name="sizes" 
                           placeholder="S, M, L, XL"
                           value="<?= htmlspecialchars($product['sizes'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="image">Image principale <?= !$isEdit ? '*' : '' ?></label>
                    <input type="file" 
                           id="image" 
                           name="image" 
                           accept="image/*"
                           <?= !$isEdit ? 'required' : '' ?>>
                    <?php if ($isEdit && !empty($product['image'])): ?>
                        <p class="form-help">Image actuelle: <?= htmlspecialchars($product['image']) ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="images">Images supplémentaires</label>
                    <input type="file" 
                           id="images" 
                           name="images[]" 
                           accept="image/*"
                           multiple>
                    <p class="form-help">Vous pouvez sélectionner plusieurs images</p>
                </div>
                
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="/admin/products.php" class="btn-secondary">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
