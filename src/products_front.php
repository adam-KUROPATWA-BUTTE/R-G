<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Retourne les produits pour le site (image normalisée):
 * - image = COALESCE(image, image_url, '')
 */
function products_front_all(): array {
    $sql = "SELECT id, name, price, 
                   COALESCE(stock_quantity, stock, 0) AS stock_quantity,
                   COALESCE(image, image_url, '') AS image,
                   COALESCE(category, '') AS category
            FROM products
            ORDER BY id DESC";
    return db()->query($sql)->fetchAll();
}

/** Construit l'URL <img src> pour l'image (gère sous-dossier) */
function product_image_url(array $p, string $base_path): string {
    $img = trim((string)($p['image'] ?? ''), " \t\n\r\0\x0B");
    if ($img === '') return '';
    // Évite les doubles slashs
    return rtrim($base_path, '/') . '/' . ltrim($img, '/');
}