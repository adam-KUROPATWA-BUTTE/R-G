<?php
require_once 'src/auth.php';
require_once 'src/csrf.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if ($email && $password && $password_confirm) {
        if ($password !== $password_confirm) {
            $error = 'Les mots de passe ne correspondent pas';
        } elseif (strlen($password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères';
        } else {
            if (register_user($email, $password)) {
                $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            } else {
                $error = 'Cette adresse email est déjà utilisée';
            }
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - R&G</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="stylesheet" href="styles/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="/" class="logo-link">
                    <img src="assets/logo.svg" alt="R&G Logo" class="logo">
                </a>
                <h1>Inscription</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                    <p><a href="login.php">Se connecter maintenant</a></p>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    <small>Au moins 6 caractères</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    S'inscrire
                </button>
            </form>
            
            <div class="auth-links">
                <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
                <p><a href="/">Retour à l'accueil</a></p>
            </div>
        </div>
    </div>
</body>
</html>