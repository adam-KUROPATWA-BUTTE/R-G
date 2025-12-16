<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - R&G</title>
  <link rel="stylesheet" href="<?= $base_path ?>/styles/main.css">
  <link rel="stylesheet" href="<?= $base_path ?>/styles/admin.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="admin-container">
    <div class="admin-header">
      <h1><i class="fas fa-cog"></i> Espace Administrateur</h1>
      <a href="<?= $base_path ?>/" class="btn btn-outline">Retour au site</a>
    </div>
    
    <div class="admin-navigation">
      <div class="nav-cards">
        <a href="<?= $base_path ?>/admin/products" class="nav-card">
          <div class="nav-card-icon">
            <i class="fas fa-box"></i>
          </div>
          <div class="nav-card-content">
            <h3>Gérer les produits</h3>
            <p>Ajouter, modifier et supprimer des produits</p>
          </div>
        </a>
        
        <a href="<?= $base_path ?>/admin/orders" class="nav-card">
          <div class="nav-card-icon">
            <i class="fas fa-shopping-bag"></i>
          </div>
          <div class="nav-card-content">
            <h3>Commandes</h3>
            <p>Visualiser et gérer les commandes clients</p>
          </div>
        </a>
        
        <a href="<?= $base_path ?>/admin/users" class="nav-card">
          <div class="nav-card-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="nav-card-content">
            <h3>Liste des utilisateurs</h3>
            <p>Voir et gérer les comptes utilisateurs</p>
          </div>
        </a>
      </div>
    </div>
  </div>
</body>
</html>
