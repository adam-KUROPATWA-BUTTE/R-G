<?php
declare(strict_types=1);

/**
 * Service d'upload pour les images produits.
 * - Vérifie erreur PHP
 * - Vérifie taille (5 Mo max)
 * - Vérifie MIME réel
 * - Crée dossier public/uploads/products/{productId}
 * - Génère un nom de fichier aléatoire
 * - Retourne le chemin relatif à utiliser pour <img src="">
 */
class ImageUploadService
{
    private string $baseUploadDir;

    public function __construct(?string $baseUploadDir = null)
    {
        $this->baseUploadDir = $baseUploadDir
            ? rtrim($baseUploadDir, '/')
            : __DIR__ . '/../../public/uploads/products';
    }

    public function store(array $file, int $productId): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null; // pas de fichier fourni
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erreur upload (code ' . $file['error'] . ').');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Image > 5MB.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Format non supporté.');
        }

        $ext = $allowed[$mime];
        $productDir = $this->baseUploadDir . '/' . $productId;

        if (!is_dir($productDir) && !mkdir($productDir, 0755, true) && !is_dir($productDir)) {
            throw new RuntimeException('Impossible de créer le dossier produit.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $productDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException('Échec déplacement du fichier.');
        }

        return 'uploads/products/' . $productId . '/' . $filename;
    }

    /**
     * Supprime une ancienne image si elle existe (chemin relatif).
     */
    public function deleteOld(?string $relativePath): void
    {
        if (!$relativePath) return;
        $full = dirname($this->baseUploadDir) . '/../' . ltrim($relativePath, '/');
        if (is_file($full)) {
            @unlink($full);
        }
    }
}