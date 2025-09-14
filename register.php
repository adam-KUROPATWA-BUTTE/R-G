<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/csrf.php';
session_boot(); // idem

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    // ... le reste inchangé
}
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription - R&G</title>
  <link rel="stylesheet" href="styles/main.css">
  <link rel="stylesheet" href="styles/auth.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <?php require __DIR__ . '/partials/header.php'; ?>

  <main class="main-content">
    <div class="auth-container" style="max-width:480px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
      <h1>Créer un compte</h1>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
      <form method="POST" class="auth-form">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="first_name">Prénom (optionnel)</label>
          <input id="first_name" type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="last_name">Nom (optionnel)</label>
          <input id="last_name" type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input id="password" type="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="password_confirm">Confirmer le mot de passe</label>
          <input id="password_confirm" type="password" name="password_confirm" required>
        </div>
        <button type="submit" class="btn btn-primary">S'inscrire</button>
      </form>
      <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
    </div>
  </main>

  <?php require __DIR__ . '/partials/footer.php'; ?>
</body>
</html>