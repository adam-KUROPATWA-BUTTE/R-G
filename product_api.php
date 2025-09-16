<?php
require_once __DIR__ . '/src/bootstrap.php';
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo json_encode(array('error'=>'id manquant')); exit;
}
$id = (int)$_GET['id'];

$pdo = db();
$stmt = $pdo->prepare("SELECT id,name,description,price,image,stock_quantity,category,sizes FROM products WHERE id=? LIMIT 1");
$stmt->execute(array($id));
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) { echo json_encode(array('error'=>'not found')); exit; }

$img = !empty($p['image']) ? '/' . ltrim($p['image'],'/') : '';
$inStock = (int)$p['stock_quantity'] > 0;

$sizesArr = array();
if (!empty($p['sizes'])) {
    $rawParts = explode(',', $p['sizes']);
    foreach ($rawParts as $s) {
        $s = strtoupper(trim($s));
        if ($s !== '' && !in_array($s, $sizesArr, true)) $sizesArr[] = $s;
    }
}

echo json_encode(array(
  'id'=>(int)$p['id'],
  'name'=>$p['name'],
  'description'=>$p['description'],
  'price'=>(float)$p['price'],
  'image'=>$img,
  'stock_quantity'=>(int)$p['stock_quantity'],
  'stock_label'=>$inStock ? 'En stock' : 'Rupture de stock',
  'stock_class'=>$inStock ? 'in-stock':'out-of-stock',
  'category'=>$p['category'],
  'sizes'=>$sizesArr
));