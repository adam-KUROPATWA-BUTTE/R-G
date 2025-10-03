<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'R&G - Boutique de Mode et Bijoux' ?></title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php if (!empty($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>
    
    <main class="main-content">
        <?= $content ?? '' ?>
    </main>
    
    <?php include __DIR__ . '/footer.php'; ?>
    
    <script src="/scripts/app.js"></script>
    <script src="/scripts/auth.js"></script>
    <script src="/scripts/cart.js"></script>
    <?php if (!empty($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
