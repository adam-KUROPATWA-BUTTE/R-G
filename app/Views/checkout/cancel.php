<?php
$page_title = 'Paiement Annulé - R&G';
require __DIR__ . '/../layouts/header.php';
?>

<div class="cancel-container">
    <div class="cancel-card">
        <div class="cancel-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        
        <h1>Paiement annulé</h1>
        <p class="cancel-message">Votre paiement n'a pas été traité.</p>
        <p>Aucun montant n'a été débité de votre compte.</p>
        
        <div class="action-buttons">
            <a href="<?= $base_path ?>/cart" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i>
                Retour au panier
            </a>
            <a href="<?= $base_path ?>/" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<style>
.cancel-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
}

.cancel-card {
    background: white;
    padding: 3rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
    max-width: 600px;
    width: 100%;
}

.cancel-icon {
    font-size: 5rem;
    color: #ef4444;
    margin-bottom: 1.5rem;
}

.cancel-card h1 {
    color: #1e3a8a;
    margin-bottom: 1rem;
}

.cancel-message {
    font-size: 1.2rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: #1e3a8a;
    color: white;
}

.btn-primary:hover {
    background: #1e40af;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
