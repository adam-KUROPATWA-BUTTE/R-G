    <!-- Footer -->
    <footer class="footer">
        <link rel="stylesheet" href="<?= $base_path ?>/public/assets/css/product_modal.css">
        <div class="footer-content">
            <div class="footer-section">
                <h3>R&G</h3>
                <p>Votre destination mode et bijoux</p>
            </div>
            <div class="footer-section">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="<?= $base_path ?>/">Accueil</a></li>
                    <li><a href="<?= $base_path ?>/vetements-femme">Vêtements Femme</a></li>
                    <li><a href="<?= $base_path ?>/vetements-homme">Vêtements Homme</a></li>
                    <li><a href="<?= $base_path ?>/bijoux">Bijoux</a></li>
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
        });
    </script>
    <script src="<?= $base_path ?>/public/scripts/cart.js"></script>
    <?php if (isset($additional_scripts)): ?>
        <?php foreach ($additional_scripts as $script_file): ?>
            <script src="<?= $base_path ?>/<?= htmlspecialchars($script_file) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <script src="<?= $base_path ?>/public/assets/js/product_modal.js"></script>
</body>
</html>
