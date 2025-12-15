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
                    <li><a href="<?= $base_path ?>index.php">Accueil</a></li>
                    <li><a href="<?= $base_path ?>pages/femme.html">Vêtements Femme</a></li>
                    <li><a href="<?= $base_path ?>pages/homme.html">Vêtements Homme</a></li>
                    <li><a href="<?= $base_path ?>pages/bijoux.html">Bijoux</a></li>
                    <li><a href="<?= $base_path ?>pages/info.html">Info</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: contact@rg-boutique.fr</p>
                <p>Téléphone: +33 1 23 45 67 89</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 R&G. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <?php if (isset($include_scripts) && $include_scripts): ?>
        <script>
            // Initialize cart count from server
            window.serverCartCount = <?= $cart_count ?>;
        </script>
        <script src="<?= $base_path ?>scripts/app.js"></script>
        <?php if (isset($additional_scripts)): ?>
            <?php foreach ($additional_scripts as $script_file): ?>
                <script src="<?= $base_path . htmlspecialchars($script_file) ?>"></script>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>