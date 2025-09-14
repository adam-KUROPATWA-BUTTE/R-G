<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';
require_admin();

$users = users_list();
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Utilisateurs - Admin</title>
  <link rel="stylesheet" href="/styles/main.css">
</head>
<body>
  <div class="admin-container" style="max-width:1100px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1>Utilisateurs</h1>
    <p><a href="/admin/">Retour</a></p>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th>ID</th><th>Email</th><th>Rôle</th><th>Prénom</th><th>Nom</th><th>Créé le</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td><?= htmlspecialchars($u['first_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($u['last_name'] ?? '') ?></td>
          <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>