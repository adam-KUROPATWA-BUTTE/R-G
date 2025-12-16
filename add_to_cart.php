<?php
/**
 * DEPRECATED - Redirect to MVC route
 * Use POST /cart/add instead (handled by CartController@add)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Forward POST data to new route
    header('Location: /cart/add');
    exit;
}
header('Location: /cart');
exit;

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
cart_add($id, $qty, $size);

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