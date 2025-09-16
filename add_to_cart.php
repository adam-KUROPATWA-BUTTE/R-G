<?php
declare(strict_types=1);

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/CartService.php';

// Helper pour savoir si la requête est JSON
function is_json_request(): bool {
    $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    return stripos($ct, 'application/json') !== false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Méthode non autorisée';
    exit;
}

$data = $_POST;

// Support optionnel du JSON (SEULEMENT si les cookies de session sont envoyés !)
if (is_json_request()) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        $data = $json + $data; // JSON a priorité
    }
}

// Extraire champs (compat noms anciens: product_id/quantity/csrf_token)
$token = $data['csrf'] ?? $data['csrf_token'] ?? null;
$id    = (int)($data['id'] ?? $data['product_id'] ?? 0);
$qty   = (int)($data['qty'] ?? $data['quantity'] ?? 1);
$size  = trim((string)($data['size'] ?? ''));
$qty   = max(1, $qty);

// Vérif CSRF
if (!csrf_verify($token)) {
    if (is_json_request()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'CSRF invalide']);
        exit;
    }
    http_response_code(400);
    exit('CSRF invalide');
}

try {
    cart_add($id, $qty, $size);

    if (is_json_request()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Produit ajouté au panier',
            'cart_count' => cart_count(),
        ]);
        exit;
    }

    $back = $data['back'] ?? ($_SERVER['HTTP_REFERER'] ?? '/');
    header('Location: ' . $back);
    exit;
} catch (Throwable $e) {
    if (is_json_request()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
        ]);
        exit;
    }
    http_response_code(400);
    echo "Impossible d'ajouter au panier: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}