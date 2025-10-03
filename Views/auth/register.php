<div class="auth-container">
    <div class="auth-card">
        <h1>Inscription</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/register.php" class="auth-form">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="full_name">Nom complet</label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       placeholder="Jean Dupont"
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                       required 
                       autofocus>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="votre@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="••••••••"
                       minlength="6"
                       required>
                <small>Au moins 6 caractères</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" 
                       id="password_confirm" 
                       name="password_confirm" 
                       placeholder="••••••••"
                       minlength="6"
                       required>
            </div>
            
            <button type="submit" class="btn-primary btn-block">
                S'inscrire
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Déjà un compte ? <a href="/login.php">Connectez-vous</a></p>
        </div>
    </div>
</div>
