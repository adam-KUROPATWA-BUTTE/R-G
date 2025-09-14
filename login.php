<?php
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/csrf.php';
session_boot(); // Assure l'utilisation du cookie de session unique (rg_session)

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
        if (login_user($email, $password)) {
            header('Location: /');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}

$page_title = 'Connexion - R&G';
require __DIR__ . '/partials/header.php';
?>

<main class="main-content">
  <div class="auth-container" style="max-width:420px;margin:2rem auto;background:#fff;padding:1.5rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h1>Connexion</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" class="auth-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
    <p>Pas de compte ? <a href="/register.php">Créer un compte</a></p>
  </div>
</main>

<?php require __DIR__ . '/partials/footer.php'; ?>