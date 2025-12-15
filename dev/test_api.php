<?php
// Ajouter des items au panier pour le test
session_start();
require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/CartService.php';

// Simuler un panier avec un produit
if (empty(cart_items())) {
    echo "<p>Ajout d'un produit test au panier...</p>";
    cart_add(1, 1, 'M'); // ID 1, quantité 1, taille M
}

echo "<h2>Panier actuel:</h2>";
echo "<pre>";
print_r(cart_items());
echo "</pre>";
echo "<p>Total: " . cart_total() . " €</p>";

// Maintenant tester l'API
echo "<hr><h2>Test de l'API create_order.php</h2>";

$testData = [
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'email' => 'jean.dupont@test.fr',
    'telephone' => '0612345678',
    'adresse' => '123 rue de la Paix',
    'ville' => 'Paris',
    'code_postal' => '75001',
    'pays' => 'France'
];

// Appeler directement le fichier
ob_start();
$_SERVER['REQUEST_METHOD'] = 'POST';
file_put_contents('php://input', json_encode($testData));

include __DIR__ . '/api/create_order.php';

$output = ob_get_clean();

echo "<h3>Réponse de l'API:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Vérifier si c'est du JSON valide
$json = json_decode($output, true);
if ($json) {
    echo "<p style='color:green;'>✅ JSON valide!</p>";
    echo "<pre>" . print_r($json, true) . "</pre>";
} else {
    echo "<p style='color:red;'>❌ Pas du JSON - il y a une erreur PHP</p>";
}
?>