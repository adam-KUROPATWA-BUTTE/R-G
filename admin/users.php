<?php
require_once '../src/auth.php';
require_once '../src/csrf.php';
require_once '../src/functions.php';

require_admin();

// Get all users
$users = users_list();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utilisateurs - Admin R&G</title>
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
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .users-table th, .users-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .users-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #1e3a8a;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .users-table tr:hover {
            background: #f8fafc;
        }
        
        .role-admin {
            color: #dc2626;
            font-weight: 600;
        }
        
        .role-user {
            color: #059669;
            font-weight: 600;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
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
        
        .users-count {
            background: #e0f2fe;
            color: #0277bd;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .no-name {
            color: #9ca3af;
            font-style: italic;
        }
        
        .user-id {
            font-family: monospace;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .users-table {
                font-size: 0.9rem;
            }
            
            .users-table th, .users-table td {
                padding: 10px 8px;
            }
            
            /* Hide less important columns on mobile */
            .hide-mobile {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .users-table {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-users"></i> Liste des Utilisateurs</h1>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour admin
                </a>
                <a href="../public/" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Retour au site
                </a>
            </div>
        </div>

        <div class="users-count">
            <i class="fas fa-info-circle"></i>
            Total: <?= count($users) ?> utilisateur<?= count($users) > 1 ? 's' : '' ?> inscrit<?= count($users) > 1 ? 's' : '' ?>
        </div>

        <?php if (empty($users)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #666;">
                <i class="fas fa-users" style="font-size: 4rem; color: #1e3a8a; margin-bottom: 20px; display: block;"></i>
                <h3>Aucun utilisateur trouvé</h3>
                <p>Il n'y a encore aucun utilisateur inscrit sur le site.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Nom</th>
                            <th>Rôle</th>
                            <th class="hide-mobile">Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <span class="user-id">#<?= $user['id'] ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($user['name']): ?>
                                        <?= htmlspecialchars($user['name']) ?>
                                    <?php else: ?>
                                        <span class="no-name">Non renseigné</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="role-<?= $user['role'] ?>">
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <i class="fas fa-crown"></i> Administrateur
                                        <?php else: ?>
                                            <i class="fas fa-user"></i> Utilisateur
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="hide-mobile">
                                    <?php 
                                    $date = new DateTime($user['created_at']);
                                    echo $date->format('d/m/Y à H:i');
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #1e3a8a;">
            <h4 style="margin: 0 0 10px 0; color: #1e3a8a;">
                <i class="fas fa-info-circle"></i> Information
            </h4>
            <p style="margin: 0; color: #666;">
                Cette page affiche la liste en lecture seule de tous les utilisateurs inscrits. 
                Pour des raisons de sécurité, la modification des utilisateurs n'est pas disponible dans cette interface.
            </p>
        </div>
    </div>
</body>
</html>