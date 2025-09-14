<?php
// Handle both direct access (public/login.php) and routing from root
$auth_path = file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php';
$csrf_path = file_exists('../src/csrf.php') ? '../src/csrf.php' : 'src/csrf.php';
require_once $auth_path;
require_once $csrf_path;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        if (login_user($email, $password)) {
            header('Location: /');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect';
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}

// Set up page variables for header
$page_title = 'Connexion - R&G';
$additional_css = ['styles/auth.css'];

// Include header
require_once 'partials/header.php';
?>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="/" class="logo-link">
                    <img src="assets/logo.svg" alt="R&G Logo" class="logo">
                </a>
                <h1>Connexion</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
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
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </button>
            </form>
            
            <div class="auth-links">
                <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                <p><a href="/">Retour à l'accueil</a></p>
            </div>
        </div>
    </div>

<?php
// Include footer
require_once 'partials/footer.php';
?>