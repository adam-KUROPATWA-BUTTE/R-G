<?php
// Handle both direct access and routing from root
$auth_path = file_exists('../src/auth.php') ? '../src/auth.php' : 'src/auth.php';
$csrf_path = file_exists('../src/csrf.php') ? '../src/csrf.php' : 'src/csrf.php';
$functions_path = file_exists('../src/functions.php') ? '../src/functions.php' : 'src/functions.php';
require_once $auth_path;
require_once $csrf_path;
require_once $functions_path;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'products':
            if ($method === 'GET') {
                $products = products_list();
                echo json_encode(['success' => true, 'data' => $products]);
            }
            break;
            
        case 'cart_add':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $product_id = intval($input['product_id'] ?? 0);
                $quantity = intval($input['quantity'] ?? 1);
                
                if ($product_id > 0) {
                    cart_add($product_id, $quantity);
                    echo json_encode(['success' => true, 'message' => 'Produit ajouté au panier']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'ID produit invalide']);
                }
            }
            break;
            
        case 'cart_get':
            if ($method === 'GET') {
                $cart = cart_get();
                $total = cart_total();
                echo json_encode(['success' => true, 'cart' => $cart, 'total' => $total]);
            }
            break;
            
        case 'cart_remove':
            if ($method === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $product_id = intval($input['product_id'] ?? 0);
                
                if ($product_id > 0) {
                    cart_remove($product_id);
                    echo json_encode(['success' => true, 'message' => 'Produit retiré du panier']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'ID produit invalide']);
                }
            }
            break;
            
        case 'cart_clear':
            if ($method === 'POST') {
                cart_clear();
                echo json_encode(['success' => true, 'message' => 'Panier vidé']);
            }
            break;
            
        case 'checkout':
            if ($method === 'POST') {
                $user = current_user();
                if (!$user) {
                    echo json_encode(['success' => false, 'error' => 'Connexion requise']);
                    break;
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $cart = cart_get();
                
                if (empty($cart)) {
                    echo json_encode(['success' => false, 'error' => 'Panier vide']);
                    break;
                }
                
                $total = cart_total();
                $payment_method = $input['payment_method'] ?? 'card';
                
                $order_id = create_order($user['id'], $cart, $total, $payment_method);
                cart_clear();
                
                echo json_encode([
                    'success' => true, 
                    'order_id' => $order_id,
                    'message' => 'Commande créée avec succès'
                ]);
            }
            break;
            
        case 'user_orders':
            if ($method === 'GET') {
                $user = current_user();
                if (!$user) {
                    echo json_encode(['success' => false, 'error' => 'Connexion requise']);
                    break;
                }
                
                $orders = get_user_orders($user['id']);
                echo json_encode(['success' => true, 'data' => $orders]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action non supportée']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}