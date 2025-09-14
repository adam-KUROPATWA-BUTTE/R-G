<?php
require_once '../../src/auth.php';
require_once '../../src/csrf.php';
require_once '../../src/functions.php';

require_admin();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_product':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = floatval($_POST['price']);
                $category_id = intval($_POST['category_id']);
                $stock_quantity = intval($_POST['stock_quantity']);
                
                if ($name && $price > 0) {
                    $pdo = db();
                    $stmt = $pdo->prepare('INSERT INTO products (name, description, price, category_id, stock_quantity) VALUES (?, ?, ?, ?, ?)');
                    if ($stmt->execute([$name, $description, $price, $category_id, $stock_quantity])) {
                        $message = 'Produit ajouté avec succès';
                        $message_type = 'success';
                    } else {
                        $message = 'Erreur lors de l\'ajout du produit';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Veuillez remplir tous les champs requis';
                    $message_type = 'error';
                }
                break;
                
            case 'update_product':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = floatval($_POST['price']);
                $category_id = intval($_POST['category_id']);
                $stock_quantity = intval($_POST['stock_quantity']);
                $status = $_POST['status'];
                
                if ($id && $name && $price > 0) {
                    $pdo = db();
                    $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, stock_quantity = ?, status = ? WHERE id = ?');
                    if ($stmt->execute([$name, $description, $price, $category_id, $stock_quantity, $status, $id])) {
                        $message = 'Produit mis à jour avec succès';
                        $message_type = 'success';
                    } else {
                        $message = 'Erreur lors de la mise à jour du produit';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Veuillez remplir tous les champs requis';
                    $message_type = 'error';
                }
                break;
                
            case 'delete_product':
                $id = intval($_POST['id']);
                if ($id) {
                    $pdo = db();
                    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
                    if ($stmt->execute([$id])) {
                        $message = 'Produit supprimé avec succès';
                        $message_type = 'success';
                    } else {
                        $message = 'Erreur lors de la suppression du produit';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'ID produit invalide';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all products for admin (including those with 0 stock)
$products = products_list_admin();
$pdo = db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Admin R&G</title>
    <link rel="stylesheet" href="../../public/styles/main.css">
    <link rel="stylesheet" href="../../public/styles/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #1e3a8a;
            color: white;
        }
        .btn-primary:hover {
            background: #1e40af;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .products-table th, .products-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .products-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #1e3a8a;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-box"></i> Gestion des Produits</h1>
            <div>
                <a href="../cms/" class="btn btn-primary" style="background-color: #6c757d; margin-right: 10px;">
                    <i class="fas fa-edit"></i> CMS
                </a>
                <button class="btn btn-primary" onclick="showAddModal()">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </button>
                <a href="../../public/" class="btn btn-primary">
                    <i class="fas fa-home"></i> Retour au site
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="products-section">
            <h2>Liste des Produits</h2>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Catégorie</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= number_format($product['price'], 2) ?>€</td>
                            <td><?= htmlspecialchars($product['category'] ?? 'Non définie') ?></td>
                            <td><?= $product['stock_quantity'] ?></td>
                            <td>
                                <span class="status-<?= $product['status'] ?>">
                                    <?= ucfirst($product['status']) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn btn-primary" onclick="editProduct(<?= htmlspecialchars(json_encode($product)) ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form style="display:inline;" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addModal')">&times;</span>
            <h2>Ajouter un Produit</h2>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_product">
                
                <div class="form-group">
                    <label>Nom du produit *</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Prix *</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category_id">
                        <option value="">Aucune catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock_quantity" value="0">
                </div>
                
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h2>Modifier le Produit</h2>
            <form method="POST" id="editForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_product">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>Nom du produit *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Prix *</label>
                    <input type="number" step="0.01" name="price" id="edit_price" required>
                </div>
                
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="category_id" id="edit_category_id">
                        <option value="">Aucune catégorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock_quantity" id="edit_stock_quantity">
                </div>
                
                <div class="form-group">
                    <label>Statut</label>
                    <select name="status" id="edit_status">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editProduct(product) {
            document.getElementById('edit_id').value = product.id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_description').value = product.description || '';
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_category_id').value = product.category_id || '';
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('edit_status').value = product.status;
            document.getElementById('editModal').style.display = 'block';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target === addModal) {
                addModal.style.display = 'none';
            }
            if (event.target === editModal) {
                editModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>