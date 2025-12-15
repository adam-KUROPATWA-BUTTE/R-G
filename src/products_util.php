<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Détermine la colonne à utiliser pour l'image du produit (image ou image_url).
 */
function product_image_column(): string {
    static $col = null;
    if ($col !== null) return $col;

    $pdo = db();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $columns = [];

    try {
        if ($driver === 'sqlite') {
            $stmt = $pdo->query("PRAGMA table_info(products)");
            foreach ($stmt->fetchAll() as $row) {
                // sqlite renvoie 'name' pour le nom de colonne
                $columns[] = is_array($row) ? ($row['name'] ?? $row[1] ?? null) : null;
            }
        } else {
            // MySQL/MariaDB
            $stmt = $pdo->query("SHOW COLUMNS FROM products");
            foreach ($stmt->fetchAll() as $row) {
                $columns[] = is_array($row) ? ($row['Field'] ?? $row[0] ?? null) : null;
            }
        }
        $columns = array_filter(array_map('strval', $columns));
    } catch (Throwable $e) {
        // fallback si la détection échoue
        $columns = ['image', 'image_url'];
    }

    if (in_array('image', $columns, true)) {
        $col = 'image';
    } elseif (in_array('image_url', $columns, true)) {
        $col = 'image_url';
    } else {
        // Si aucune colonne n'existe, choix par défaut
        $col = 'image';
    }
    return $col;
}

/**
 * Met à jour uniquement l'image d'un produit.
 */
function product_update_image(int $id, string $imagePath): void {
    $col = product_image_column();
    $sql = "UPDATE products SET {$col} = ? WHERE id = ?";
    $stmt = db()->prepare($sql);
    $stmt->execute([$imagePath, $id]);
}