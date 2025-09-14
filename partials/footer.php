    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>R&G</h3>
                <p>Votre destination mode et bijoux de luxe</p>
            </div>
            <div class="footer-section">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="/">Accueil</a></li>
                    <li><a href="/vetements-femme.php">Vêtements Femme</a></li>
                    <li><a href="/vetements-homme.php">Vêtements Homme</a></li>
                    <li><a href="/bijoux.php">Bijoux</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: contact@rg-boutique.fr</p>
                <p>Téléphone: +33 1 23 45 67 89</p>
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
    <script src="/scripts/cart.js"></script>
    <?php if (isset($additional_scripts)): ?>
        <?php foreach ($additional_scripts as $script_file): ?>
            <script src="/<?= htmlspecialchars($script_file) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>