<?php
$page_title = 'Connexion - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<main class="main-content">
  <div class="auth-container" style="max-width:420px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1>Connexion</h1>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" class="auth-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
    <p>Pas de compte ? <a href="<?= $base_path ?>/register">Créer un compte</a></p>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
