<?php
$page_title = 'Créer un compte - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<main class="main-content">
  <div class="auth-container" style="max-width:420px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1>Créer un compte</h1>
    
    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($success) ?>
        <p><a href="<?= $base_path ?>/login">Se connecter maintenant</a></p>
      </div>
    <?php else: ?>
      <form method="POST" class="auth-form">
        <?= csrf_field() ?>
        
        <div class="form-group">
          <label for="first_name">Prénom</label>
          <input id="first_name" type="text" name="first_name" value="<?= htmlspecialchars($first_name ?? '') ?>">
        </div>
        
        <div class="form-group">
          <label for="last_name">Nom</label>
          <input id="last_name" type="text" name="last_name" value="<?= htmlspecialchars($last_name ?? '') ?>">
        </div>
        
        <div class="form-group">
          <label for="email">Email *</label>
          <input id="email" type="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
        </div>
        
        <div class="form-group">
          <label for="password">Mot de passe * (min. 6 caractères)</label>
          <input id="password" type="password" name="password" required minlength="6">
        </div>
        
        <div class="form-group">
          <label for="password_confirm">Confirmer le mot de passe *</label>
          <input id="password_confirm" type="password" name="password_confirm" required minlength="6">
        </div>
        
        <button type="submit" class="btn btn-primary">Créer mon compte</button>
      </form>
      
      <p>Déjà un compte ? <a href="<?= $base_path ?>/login">Se connecter</a></p>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
