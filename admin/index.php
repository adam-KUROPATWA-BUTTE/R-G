<?php
require_once __DIR__ . '/../src/auth.php';
require_admin();
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - R&G</title>
  <link rel="stylesheet" href="/styles/main.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="admin-container" style="max-width:1000px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1>Espace Administrateur</h1>
    <ul>
      <li><a href="/admin/products.php">Gérer les produits</a></li>
      <li><a href="/admin/users.php">Liste des utilisateurs</a></li>
      <li><a href="/">Retour au site</a></li>
    </ul>
  </div>
</body>
</html>