<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/bootstrap.php';

// Vérification admin
$u = current_user();
if (!$u) {
    die("❌ Pas d'utilisateur connecté. <a href='/login.php'>Se connecter</a>");
}
if (($u['role'] ?? '') !== 'admin') {
    die("❌ Accès refusé. Votre rôle: " . ($u['role'] ?? 'aucun') . ". Attendu: admin");
}

require_admin();

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(404);
    exit('Commande non trouvée');
}

$orderId = (int)$_GET['id'];

try {
    $pdo = db();
    
    // Récupérer la commande
    $stmt = $pdo->prepare("
        SELECT 
            id,
            total,
            statut as status,
            email_client as customer_email,
            nom_client,
            prenom_client,
            CONCAT(prenom_client, ' ', nom_client) as customer_name,
            telephone,
            adresse_livraison as customer_address,
            revolut_order_id as payment_reference,
            date_creation as created_at,
            date_paiement as updated_at
        FROM commandes
        WHERE id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        http_response_code(404);
        exit('Commande non trouvée');
    }
    
    // Récupérer les items de la commande
    $stmtItems = $pdo->prepare("
        SELECT 
            produit_id as product_id,
            nom_produit as product_name,
            quantite as quantity,
            prix_unitaire as unit_price,
            taille as size,
            (quantite * prix_unitaire) as total_price
        FROM commande_items
        WHERE commande_id = ?
    ");
    $stmtItems->execute([$orderId]);
    $order['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $order = null;
    $error = 'Erreur lors du chargement de la commande : ' . $e->getMessage();
}

if (!$order) {
    http_response_code(404);
    exit('Commande non trouvée');
}

function formatStatus(string $status): array {
    $statusMap = [
        'pending' => ['En attente', 'status-pending'],
        'paid' => ['Payée', 'status-paid'],
        'cancelled' => ['Annulée', 'status-canceled'],
        'canceled' => ['Annulée', 'status-canceled'],
        'failed' => ['Échouée', 'status-failed']
    ];
    
    return $statusMap[$status] ?? [$status, 'status-unknown'];
}

function h(string $v): string { 
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); 
}

[$statusLabel, $statusClass] = formatStatus($order['status']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commande #<?= $orderId ?> - Admin R&G</title>
    <link rel="stylesheet" href="/styles/main.css">
    <link rel="stylesheet" href="/styles/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .order-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-header h1 {
            margin: 0;
            color: #1e3a8a;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-outline {
            background: transparent;
            color: #1d4ed8;
            border: 2px solid #1d4ed8;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .order-info {
                grid-template-columns: 1fr;
            }
        }
        
        .info-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .info-card h3 {
            margin: 0 0 1rem 0;
            color: #374151;
            font-size: 1.125rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6b7280;
            min-width: 120px;
        }
        
        .info-value {
            color: #374151;
            text-align: right;
            flex: 1;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-paid {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-canceled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .items-card {
            background: white;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .items-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .items-header h3 {
            margin: 0;
            color: #374151;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .total-row {
            background: #f8fafc;
            font-weight: 600;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #f87171;
            color: #991b1b;
        }
        
        .payment-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .payment-info h4 {
            margin: 0 0 0.5rem 0;
            color: #0c4a6e;
        }
        
        .payment-detail {
            font-family: monospace;
            font-size: 0.875rem;
            color: #374151;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="order-container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> Commande #<?= $orderId ?></h1>
            <a href="/admin/orders.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Retour aux commandes
            </a>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= h($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($order['payment_reference'])): ?>
            <div class="payment-info">
                <h4><i class="fas fa-credit-card"></i> Informations de Paiement Revolut</h4>
                <p><strong>ID Transaction Revolut:</strong> <span class="payment-detail"><?= h($order['payment_reference']) ?></span></p>
                <?php if ($order['status'] === 'paid' && !empty($order['updated_at'])): ?>
                    <p><strong>Payé le:</strong> <?= date('d/m/Y à H:i', strtotime($order['updated_at'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="order-info">
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Informations Commande</h3>
                <div class="info-row">
                    <span class="info-label">ID Commande</span>
                    <span class="info-value">#<?= (int)$order['id'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date</span>
                    <span class="info-value"><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Statut</span>
                    <span class="info-value">
                        <span class="status-badge <?= $statusClass ?>">
                            <?= h($statusLabel) ?>
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total</span>
                    <span class="info-value"><strong style="font-size: 1.25rem; color: #059669;"><?= number_format((float)$order['total'], 2, ',', ' ') ?> €</strong></span> <!-- ✅ CORRECTION 1 -->
                </div>
            </div>
            
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Informations Client</h3>
                <div class="info-row">
                    <span class="info-label">Nom</span>
                    <span class="info-value"><?= h($order['customer_name'] ?? 'Non renseigné') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">
                        <a href="mailto:<?= h($order['customer_email'] ?? '') ?>" style="color: #2563eb;">
                            <?= h($order['customer_email'] ?? 'Non renseigné') ?>
                        </a>
                    </span>
                </div>
                <?php if (!empty($order['telephone'])): ?>
                    <div class="info-row">
                        <span class="info-label">Téléphone</span>
                        <span class="info-value">
                            <a href="tel:<?= h($order['telephone']) ?>" style="color: #2563eb;">
                                <?= h($order['telephone']) ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($order['customer_address'])): ?>
                    <div class="info-row">
                        <span class="info-label">Adresse</span>
                        <span class="info-value" style="white-space: pre-line;"><?= h($order['customer_address']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="items-card">
            <div class="items-header">
                <h3><i class="fas fa-list"></i> Articles Commandés (<?= count($order['items']) ?>)</h3>
            </div>
            
            <?php if (empty($order['items'])): ?>
                <div style="padding: 2rem; text-align: center; color: #6b7280;">
                    <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p>Aucun article trouvé pour cette commande.</p>
                </div>
            <?php else: ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th style="text-align: center;">Taille</th>
                            <th style="text-align: right;">Prix unitaire</th>
                            <th style="text-align: center;">Quantité</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= h($item['product_name']) ?></strong>
                                    <?php if ($item['product_id']): ?>
                                        <br><small style="color: #6b7280;">Réf: #<?= (int)$item['product_id'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?= !empty($item['size']) ? h($item['size']) : '<span style="color: #9ca3af;">-</span>' ?>
                                </td>
                                <td style="text-align: right;"><?= number_format((float)$item['unit_price'], 2, ',', ' ') ?> €</td> <!-- ✅ CORRECTION 2 -->
                                <td style="text-align: center;"><strong><?= (int)$item['quantity'] ?></strong></td>
                                <td style="text-align: right;"><strong><?= number_format((float)$item['total_price'], 2, ',', ' ') ?> €</strong></td> <!-- ✅ CORRECTION 3 -->
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right; padding: 1.5rem;"><strong style="font-size: 1.125rem;">TOTAL DE LA COMMANDE:</strong></td>
                            <td style="text-align: right; padding: 1.5rem;">
                                <strong style="font-size: 1.25rem; color: #059669;">
                                    <?= number_format((float)$order['total'], 2, ',', ' ') ?> € <!-- ✅ CORRECTION 4 -->
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>