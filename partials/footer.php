<?php
// Compute base path for subdirectory deployments (consistent with header.php)
if (!isset($base_path)) {
    $base_path = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $base_path = $base_path === '/' ? '' : rtrim($base_path, '');
}
?>
    <!-- Footer -->
    <footer class="footer">
        <link rel="stylesheet" href="/assets/css/product_modal.css">
        <div class="footer-content">
            <div class="footer-section">
                <h3>R&G</h3>
                <p>Votre destination mode et bijoux de luxe</p>
            </div>
            <div class="footer-section">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="<?= $base_path ?>/">Accueil</a></li>
                    <li><a href="<?= $base_path ?>/vetements-femme.php">Vêtements Femme</a></li>
                    <li><a href="<?= $base_path ?>/vetements-homme.php">Vêtements Homme</a></li>
                    <li><a href="<?= $base_path ?>/bijoux.php">Bijoux</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p><a href="mailto:contact@r-and-g.fr">Support !</a></p>
                <p><a href="mailto:support@r-and-g.fr">Nous Contacter !</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> R&G. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Initialize cart count from server
        window.serverCartCount = <?= $cart_count ?? 0 ?>;
        
        // Menu dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const menuTrigger = document.getElementById('menuTrigger');
            const dropdownContent = document.getElementById('dropdownContent');
            
            if (menuTrigger && dropdownContent) {
                menuTrigger.addEventListener('click', function() {
                    dropdownContent.classList.toggle('show');
                    menuTrigger.setAttribute('aria-expanded', 
                        dropdownContent.classList.contains('show'));
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!menuTrigger.contains(event.target) && !dropdownContent.contains(event.target)) {
                        dropdownContent.classList.remove('show');
                        menuTrigger.setAttribute('aria-expanded', 'false');
                    }
                });
            }
            
            // Mobile menu functionality
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                    mobileMenuToggle.setAttribute('aria-expanded', 
                        mobileMenu.classList.contains('active'));
                    
                    // Change icon
                    const icon = mobileMenuToggle.querySelector('i');
                    if (mobileMenu.classList.contains('active')) {
                        icon.className = 'fas fa-times';
                    } else {
                        icon.className = 'fas fa-bars';
                    }
                });
                
                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuToggle.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.remove('active');
                        mobileMenuToggle.setAttribute('aria-expanded', 'false');
                        const icon = mobileMenuToggle.querySelector('i');
                        icon.className = 'fas fa-bars';
                    }
                });
                
                // Close mobile menu when a link is clicked
                const mobileLinks = mobileMenu.querySelectorAll('a');
                mobileLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenu.classList.remove('active');
                        mobileMenuToggle.setAttribute('aria-expanded', 'false');
                        const icon = mobileMenuToggle.querySelector('i');
                        icon.className = 'fas fa-bars';
                    });
                });
            }
        });
    </script>
    <script src="<?= $base_path ?>/scripts/cart.js"></script>
    <?php if (isset($additional_scripts)): ?>
        <?php foreach ($additional_scripts as $script_file): ?>
            <script src="<?= $base_path ?>/<?= htmlspecialchars($script_file) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require __DIR__ . '/_product_modal.php'; ?>
<script src="/assets/js/product_modal.js"></script>
</body>
</html>