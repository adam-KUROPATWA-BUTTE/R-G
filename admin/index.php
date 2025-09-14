<?php
require_once '../src/auth.php';
require_once '../src/csrf.php';

require_admin();

$current_user = current_user();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - R&G</title>
    <link rel="stylesheet" href="../public/styles/main.css">
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
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
        }
        
        .admin-welcome {
            color: #1e3a8a;
        }
        
        .admin-welcome h1 {
            margin: 0;
            font-size: 2rem;
        }
        
        .admin-welcome p {
            margin: 5px 0 0 0;
            color: #666;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .admin-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.1);
            border-color: #1e3a8a;
        }
        
        .admin-card i {
            font-size: 3rem;
            color: #1e3a8a;
            margin-bottom: 20px;
            display: block;
        }
        
        .admin-card h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #1e3a8a;
        }
        
        .admin-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #1e3a8a;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e40af;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1e3a8a;
            display: block;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            
            .admin-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-welcome">
                <h1><i class="fas fa-cog"></i> Administration R&G</h1>
                <p>Bienvenue, <?= htmlspecialchars($current_user['first_name'] ?: $current_user['email']) ?></p>
            </div>
            <div>
                <a href="../public/" class="btn btn-secondary">
                    <i class="fas fa-home"></i>
                    Retour au site
                </a>
            </div>
        </div>

        <?php
        // Get some basic stats
        try {
            $pdo = db();
            $total_products = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
            $active_products = $pdo->query('SELECT COUNT(*) FROM products WHERE status = "active"')->fetchColumn();
            $total_users = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            $total_orders = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        } catch (Exception $e) {
            $total_products = 0;
            $active_products = 0;
            $total_users = 0;
            $total_orders = 0;
        }
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?= $total_products ?></span>
                <div class="stat-label">Produits total</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= $active_products ?></span>
                <div class="stat-label">Produits actifs</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= $total_users ?></span>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= $total_orders ?></span>
                <div class="stat-label">Commandes</div>
            </div>
        </div>

        <div class="admin-grid">
            <a href="products/" class="admin-card">
                <i class="fas fa-box"></i>
                <h3>Gestion des Produits</h3>
                <p>Ajouter, modifier et supprimer des produits. Gérer les stocks, les prix et les catégories.</p>
            </a>
            
            <a href="users.php" class="admin-card">
                <i class="fas fa-users"></i>
                <h3>Liste des Utilisateurs</h3>
                <p>Consulter la liste des utilisateurs inscrits sur le site et leurs informations.</p>
            </a>
            
            <a href="cms/" class="admin-card">
                <i class="fas fa-edit"></i>
                <h3>Gestion de Contenu</h3>
                <p>Modifier le contenu des pages, les informations de l'entreprise et les textes du site.</p>
            </a>
        </div>
    </div>
</body>
</html>