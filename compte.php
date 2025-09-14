<?php
require_once __DIR__ . '/src/bootstrap.php';  // Start session and CSRF first
require_once __DIR__ . '/src/auth.php';

// Require user to be logged in
require_login();

$current_user = current_user();
$page_title = 'Mon Compte - R&G';
require __DIR__ . '/partials/header.php';
?>

<!-- Page Header -->
<header class="page-header">
    <div class="header-content">
        <h1><i class="fas fa-user-circle"></i> Mon Compte</h1>
        <p>Gestion de votre profil utilisateur</p>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <section class="account-section">
        <div class="account-container">
            <div class="account-info">
                <h2>Informations du compte</h2>
                <div class="user-details">
                    <div class="detail-item">
                        <label>Email :</label>
                        <span><?= htmlspecialchars($current_user['email']) ?></span>
                    </div>
                    
                    <?php if (!empty($current_user['first_name'])): ?>
                    <div class="detail-item">
                        <label>Prénom :</label>
                        <span><?= htmlspecialchars($current_user['first_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($current_user['last_name'])): ?>
                    <div class="detail-item">
                        <label>Nom :</label>
                        <span><?= htmlspecialchars($current_user['last_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($current_user['role'])): ?>
                    <div class="detail-item">
                        <label>Statut :</label>
                        <span class="role-badge role-<?= htmlspecialchars($current_user['role']) ?>">
                            <?= $current_user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur' ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="account-actions">
                <h2>Actions</h2>
                <div class="action-buttons">
                    <?php if (($current_user['role'] ?? '') === 'admin'): ?>
                        <a href="/admin/" class="btn btn-secondary">
                            <i class="fas fa-cog"></i>
                            Administration
                        </a>
                    <?php endif; ?>
                    
                    <a href="/cart.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-cart"></i>
                        Mon Panier
                    </a>
                </div>
                
                <!-- Logout Form with CSRF Protection -->
                <form method="POST" action="/logout.php" class="logout-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--gold) 100%);
    color: var(--white);
    padding: 4rem 2rem;
    text-align: center;
}

.header-content h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.header-content p {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* Account Section */
.account-section {
    padding: 2rem;
    background-color: var(--white);
}

.account-container {
    max-width: 800px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
}

.account-info,
.account-actions {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.account-info h2,
.account-actions h2 {
    color: var(--primary-blue);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    border-bottom: 2px solid var(--gold);
    padding-bottom: 0.5rem;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem;
    background: var(--white);
    border-radius: 8px;
    border-left: 4px solid var(--primary-blue);
}

.detail-item label {
    font-weight: bold;
    color: var(--dark-gray);
}

.detail-item span {
    color: var(--primary-blue);
    font-weight: 500;
}

.role-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: bold;
}

.role-admin {
    background: #dc3545;
    color: white;
}

.role-user {
    background: #28a745;
    color: white;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.8rem 1.5rem;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 1rem;
}

.btn-secondary {
    background: var(--primary-blue);
    color: var(--white);
}

.btn-secondary:hover {
    background: var(--light-blue);
    transform: translateY(-1px);
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.logout-form {
    border-top: 2px solid #e9ecef;
    padding-top: 1.5rem;
}

.logout-btn {
    width: 100%;
    font-size: 1.1rem;
    padding: 1rem;
}

@media (max-width: 768px) {
    .account-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .header-content h1 {
        font-size: 2rem;
    }
    
    .detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<?php
require __DIR__ . '/partials/footer.php';
?>