<?php
$page_title = 'Inscription - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<main class="main-content">
  <div class="auth-container" style="max-width:420px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1>Inscription</h1>
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <p><a href="<?= $base_path ?>/login">Se connecter maintenant</a></p>
    <?php else: ?>
      <form method="POST" class="auth-form">
        <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
        <div class="form-group">
          <label for="full_name">Nom complet</label>
          <input id="full_name" type="text" name="full_name" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" type="email" name="email" required>
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
      <p>Déjà un compte ? <a href="<?= $base_path ?>/login">Se connecter</a></p>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
