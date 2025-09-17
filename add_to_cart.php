<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/ProductRepository.php';
require_once __DIR__ . '/src/CartService.php';
require_once __DIR__ . '/src/csrf.php';

function json_request(): bool {
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    return stripos($ct, 'application/json') !== false;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Méthode non autorisée';
    exit;
}

$data = $_POST;
if (json_request()) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) $data = $json + $data;
}

$token = $data['csrf'] ?? $data['csrf_token'] ?? null;
$id    = (int)($data['id'] ?? $data['product_id'] ?? 0);
$qty   = max(1, (int)($data['qty'] ?? $data['quantity'] ?? 1));
$size  = trim((string)($data['size'] ?? ''));

if (function_exists('csrf_verify')) {
    if (!csrf_verify($token)) returnError("CSRF invalide", $id);
} elseif (function_exists('csrf_check')) {
    if (!csrf_check()) returnError("CSRF invalide", $id);
}

if ($id <= 0) returnError("Produit invalide", 0);

$repo = new ProductRepository();
$product = $repo->getById($id);
if (!$product) returnError("Produit introuvable", 0);

// Tailles
$availableSizes = [];
if (method_exists($repo, 'parseSizes')) {
    $availableSizes = $repo->parseSizes($product['sizes'] ?? '');
} else {
    $raw = $product['sizes'] ?? '';
    if ($raw !== '') {
        foreach (preg_split('/[,;|]/',$raw) as $p) {
            $p = strtoupper(trim($p));
            if ($p !== '' && !in_array($p,$availableSizes,true)) $availableSizes[]=$p;
        }
    }
}
if (!empty($availableSizes) && ($size === '' || !in_array($size, $availableSizes, true))) {
    returnError("Veuillez sélectionner une taille valide", $id);
} else if (empty($availableSizes)) {
    $size = '';
}

// Panier - Use CartService for consistency
cart_add(
    productId: $id,
    qty: $qty,
    name: $product['name'] ?? '',
    price: (float)($product['price'] ?? 0),
    image: $product['image'] ?? '',
    category: $product['category'] ?? '',
    size: $size
);

if (json_request()) {
    $count = cart_count();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'message' => 'Ajout effectué',
        'cart_count' => $count
    ]);
    exit;
}

$_SESSION['success'] = 'Ajout effectué';
header('Location: product.php?id=' . $id);
exit;

function returnError(string $msg, int $id): void {
    if (json_request()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>$msg]);
        exit;
    }
    $_SESSION['error'] = $msg;
    if ($id > 0) {
        header('Location: product.php?id='.$id);
    } else {
        header('Location: index.php');
    }
    exit;
}