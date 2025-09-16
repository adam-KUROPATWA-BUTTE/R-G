<?php
declare(strict_types=1);

/** Construit une URL publique d’image à partir d’un chemin DB (supporte http(s) et data:) */
function product_image_public_url(array $p, string $base_path): string {
    $raw = trim((string)($p['image'] ?? ($p['image_url'] ?? '')));
    if ($raw === '') return '';
    $isAbs = preg_match('#^(?:https?:)?//#', $raw) || strncmp($raw, 'data:', 5) === 0;
    return $isAbs ? $raw : (rtrim($base_path, '/') . '/' . ltrim($raw, '/'));
}

/** Retourne [label, class] à partir de la quantité en stock */
function product_stock_ui(array $p): array {
    $q = $p['stock_quantity'] ?? ($p['stock'] ?? null);
    $qty = ($q === null) ? 0 : (int)$q;
    return $qty > 0 ? ['En stock', 'in-stock'] : ['Rupture de stock', 'out-of-stock'];
}