<div class="auth-container">
    <div class="auth-card">
        <h1>Connexion</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/login.php" class="auth-form">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="votre@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="••••••••"
                       required>
            </div>
            
            <button type="submit" class="btn-primary btn-block">
                Se connecter
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Pas encore de compte ? <a href="/register.php">Inscrivez-vous</a></p>
        </div>
    </div>
</div>
