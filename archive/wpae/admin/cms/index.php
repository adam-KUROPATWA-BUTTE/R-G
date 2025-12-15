<?php
require_once '../../src/auth.php';
require_once '../../src/csrf.php';
require_once '../../src/functions.php';

require_admin();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_info_page':
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                
                if ($title && $content) {
                    $pdo = db();
                    
                    // Ensure the table exists
                    try {
                        $pdo->exec("CREATE TABLE IF NOT EXISTS page_content (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            page_name VARCHAR(50) NOT NULL UNIQUE,
                            title VARCHAR(255) NOT NULL,
                            content TEXT NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            INDEX idx_page_name (page_name)
                        )");
                    } catch (PDOException $e) {
                        // Table creation failed, log error but continue
                    }
                    
                    // Check if info page content exists
                    $stmt = $pdo->prepare('SELECT id FROM page_content WHERE page_name = ?');
                    $stmt->execute(['info']);
                    $existing = $stmt->fetch();
                    
                    if ($existing) {
                        $stmt = $pdo->prepare('UPDATE page_content SET title = ?, content = ?, updated_at = NOW() WHERE page_name = ?');
                        $success = $stmt->execute([$title, $content, 'info']);
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO page_content (page_name, title, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
                        $success = $stmt->execute(['info', $title, $content]);
                    }
                    
                    if ($success) {
                        $message = 'Page Info mise à jour avec succès';
                        $message_type = 'success';
                    } else {
                        $message = 'Erreur lors de la mise à jour';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Veuillez remplir tous les champs';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get current info page content
$pdo = db();
try {
    $stmt = $pdo->prepare('SELECT * FROM page_content WHERE page_name = ?');
    $stmt->execute(['info']);
    $info_page = $stmt->fetch();
} catch (PDOException $e) {
    // Table might not exist yet, use default content
    $info_page = false;
}

// Default content if not in database
if (!$info_page) {
    $info_page = [
        'title' => 'À propos de R&G',
        'content' => 'Bienvenue dans l\'univers de R&G, où chaque pièce est pensée et créée avec soin.

Diplômée en couture, R&G propose des vêtements entièrement faits sur mesure, pensés pour s\'adapter à votre morphologie, à vos envies et à votre style. Ici, pas de production en série ni de stock : chaque création est unique, à votre image.

En parallèle, elle imagine et assemble également une collection de bijoux en acier inoxydable, pour sublimer vos tenues avec des pièces durables, élégantes et accessibles.

Que vous soyez à la recherche d\'un vêtement sur-mesure ou d\'un bijou coup de cœur, vous êtes au bon endroit. Merci de soutenir l\'artisanat local et la création indépendante ❤️'
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion CMS - Admin R&G</title>
    <link rel="stylesheet" href="../../public/styles/main.css">
    <link rel="stylesheet" href="../../public/styles/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group textarea {
            min-height: 300px;
            resize: vertical;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .btn {
            padding: 12px 24px;
            background-color: #1e3a8a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #1e40af;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            display: inline-block;
            padding: 10px 15px;
            margin-right: 10px;
            background-color: #f8f9fa;
            color: #1e3a8a;
            text-decoration: none;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .nav-links a:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-cogs"></i> Gestion CMS - Page Info</h1>
            <div>
                <a href="../products/" class="btn" style="background-color: #6c757d; margin-right: 10px;">
                    <i class="fas fa-box"></i> Produits
                </a>
                <a href="../../public/" class="btn" style="background-color: #28a745;">
                    <i class="fas fa-home"></i> Retour au site
                </a>
            </div>
        </div>

        <div class="nav-links">
            <a href="../products/"><i class="fas fa-box"></i> Gestion des Produits</a>
            <a href="#" style="background-color: #1e3a8a; color: white;"><i class="fas fa-edit"></i> CMS - Page Info</a>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_info_page">
            
            <div class="form-group">
                <label for="title">Titre de la page:</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($info_page['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="content">Contenu de la page:</label>
                <textarea id="content" name="content" required placeholder="Saisissez le contenu de la page Info..."><?= htmlspecialchars($info_page['content']) ?></textarea>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-save"></i> Sauvegarder les modifications
            </button>
        </form>

        <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
            <h3><i class="fas fa-info-circle"></i> Instructions</h3>
            <ul>
                <li>Le titre s'affichera comme titre principal de la page Info</li>
                <li>Le contenu accepte le texte brut - les retours à la ligne seront conservés</li>
                <li>Pour une mise en forme, utilisez des paragraphes séparés par une ligne vide</li>
                <li>Les modifications sont immédiatement visibles sur la page Info du site</li>
            </ul>
        </div>
    </div>
</body>
</html>