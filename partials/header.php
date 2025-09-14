<?php
require_once __DIR__ . '/../src/auth.php';
$u = current_user();
$displayName = '';
if ($u) {
    $displayName = $u['first_name'] ?? '';
    if ($displayName === '' || $displayName === null) {
        $displayName = $u['email'] ?? 'Utilisateur';
    }
}
?>
<nav class="navbar">
  <div class="nav-container" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;">
    <!-- Haut gauche: zone connexion -->
    <div class="left-auth" style="display:flex;align-items:center;gap:.75rem;">
      <?php if ($u): ?>
        <span class="user-name">Bonjour, <?= htmlspecialchars($displayName) ?></span>
        <?php if (($u['role'] ?? '') === 'admin'): ?>
          <a href="/admin/" class="btn-admin">Admin</a>
        <?php endif; ?>
        <a href="/logout.php" class="btn-logout">Déconnexion</a>
      <?php else: ?>
        <a href="/login.php" class="btn-login">Se connecter</a>
        <a href="/register.php" class="btn-register">S'inscrire</a>
      <?php endif; ?>
    </div>

    <!-- Logo au centre -->
    <div class="logo-container" style="text-align:center;">
      <a href="/"><img src="/assets/logo.svg" alt="R&G" class="logo" style="height:48px;"></a>
    </div>

    <!-- Menu déroulant à droite -->
    <div class="menu-dropdown">
      <button class="menu-trigger" id="menuTrigger" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
      </button>
      <div class="dropdown-content" id="dropdownContent">
        <a href="/">Accueil</a>
        <a href="/bijoux.php">Bijoux</a>
        <!-- autres pages à venir -->
      </div>
    </div>
  </div>
</nav>