<?php
$page_title = 'Mon Compte - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<main class="main-content" style="max-width:1200px;margin:2rem auto;padding:0 1rem;">
  <h1>Mon Compte</h1>
  
  <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);margin-bottom:2rem;">
    <h2>Informations du compte</h2>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
    <?php if (!empty($user['first_name'])): ?>
      <p><strong>Nom:</strong> <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name'] ?? '') ?></p>
    <?php endif; ?>
    <a href="<?= $base_path ?>/logout" class="btn btn-secondary">Se déconnecter</a>
  </div>

  <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 2px 12px rgba(0,0,0,.08);">
    <h2>Mes Commandes</h2>
    <?php if (empty($orders)): ?>
      <p>Vous n'avez pas encore passé de commande.</p>
    <?php else: ?>
      <table style="width:100%;border-collapse:collapse;">
        <thead>
          <tr style="background:#f8f9fa;">
            <th style="padding:1rem;text-align:left;border-bottom:2px solid #dee2e6;">N° Commande</th>
            <th style="padding:1rem;text-align:left;border-bottom:2px solid #dee2e6;">Date</th>
            <th style="padding:1rem;text-align:left;border-bottom:2px solid #dee2e6;">Total</th>
            <th style="padding:1rem;text-align:left;border-bottom:2px solid #dee2e6;">Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr style="border-bottom:1px solid #dee2e6;">
              <td style="padding:1rem;">#<?= $order['id'] ?></td>
              <td style="padding:1rem;"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
              <td style="padding:1rem;"><?= number_format($order['total'], 2, ',', ' ') ?> €</td>
              <td style="padding:1rem;">
                <span style="padding:0.25rem 0.75rem;border-radius:20px;font-size:0.85rem;background:<?= $order['status'] === 'completed' ? '#28a745' : '#ffc107' ?>;color:#fff;">
                  <?= htmlspecialchars($order['status']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
