<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/ProductRepository.php';

/**
 * Revolut Payment Handler
 * Redirects to the product's Revolut payment link
 */

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['error'] = 'Produit invalide';
    header('Location: index.php');
    exit;
}

$productId = (int)$_GET['id'];

$repo = new ProductRepository();
$product = $repo->getById($productId);

if (!$product) {
    $_SESSION['error'] = 'Produit non trouvé';
    header('Location: index.php');
    exit;
}

// Check if product has a Revolut payment link
$revolutLink = $product['revolut_payment_link'] ?? '';

if (empty($revolutLink)) {
    $_SESSION['error'] = 'Paiement Revolut non disponible pour ce produit';
    header('Location: product.php?id=' . $productId);
    exit;
}

// Validate that it's a valid Revolut checkout URL
if (!preg_match('#^https://checkout\.revolut\.com/pay/[a-f0-9\-]+$#i', $revolutLink)) {
    $_SESSION['error'] = 'Lien de paiement Revolut invalide';
    header('Location: product.php?id=' . $productId);
    exit;
}

// Redirect to Revolut checkout
header('Location: ' . $revolutLink);
exit;
