<?php
require_once __DIR__ . '/src/bootstrap.php';

try {
    $pdo = db();
    
    // Récupérer les 5 dernières commandes
    $stmt = $pdo->query("
        SELECT 
            id,
            total,
            statut,
            email_client,
            nom_client,
            prenom_client,
            revolut_order_id,
            date_creation
        FROM commandes
        ORDER BY id DESC
        LIMIT 5
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>🔍 Dernières commandes</h1>";
    
    if (empty($orders)) {
        echo "<p style='color: red;'>❌ Aucune commande trouvée dans la base de données !</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr>
                <th>ID</th>
                <th>Date</th>
                <th>Client</th>
                <th>Email</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Revolut ID</th>
              </tr>";
        
        foreach ($orders as $order) {
            echo "<tr>";
            echo "<td><strong>#" . $order['id'] . "</strong></td>";
            echo "<td>" . $order['date_creation'] . "</td>";
            echo "<td>" . htmlspecialchars($order['prenom_client'] . ' ' . $order['nom_client']) . "</td>";
            echo "<td>" . htmlspecialchars($order['email_client']) . "</td>";
            echo "<td>" . number_format($order['total'], 2) . " €</td>";
            echo "<td><strong>" . $order['statut'] . "</strong></td>";
            echo "<td>" . ($order['revolut_order_id'] ?? '<em>vide</em>') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Afficher les items de la dernière commande
        $lastOrderId = $orders[0]['id'];
        $stmtItems = $pdo->prepare("SELECT * FROM commande_items WHERE commande_id = ?");
        $stmtItems->execute([$lastOrderId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h2>📦 Items de la commande #$lastOrderId</h2>";
        if (empty($items)) {
            echo "<p style='color: orange;'>⚠️ Aucun item trouvé pour cette commande !</p>";
        } else {
            echo "<pre>" . print_r($items, true) . "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>";
}
?>