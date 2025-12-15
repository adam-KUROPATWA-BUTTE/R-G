<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/functions.php';
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

$users = users_list();
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Utilisateurs - Admin</title>
  <link rel="stylesheet" href="/styles/main.css">
  <link rel="stylesheet" href="/styles/admin.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="admin-container">
    <div class="admin-header">
      <h1><i class="fas fa-users"></i> Liste des Utilisateurs</h1>
      <a href="/admin/" class="btn btn-outline">Retour</a>
    </div>
    
    <div class="table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Créé le</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="6" class="text-center">
              <i class="fas fa-user-slash"></i>
              <p>Aucun utilisateur trouvé.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td>
                <span class="role-badge role-<?= htmlspecialchars($u['role']) ?>">
                  <?= htmlspecialchars($u['role']) ?>
                </span>
              </td>
              <td><?= htmlspecialchars($u['first_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($u['last_name'] ?? '') ?></td>
              <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>