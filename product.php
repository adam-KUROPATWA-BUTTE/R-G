<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/src/bootstrap.php';
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/csrf.php';
require_once __DIR__ . '/src/ProductRepository.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(404);
    exit('Produit non trouvé');
}
$productId = (int)$_GET['id'];

$repo    = new ProductRepository();
$product = $repo->getById($productId);
if (!$product) {
    http_response_code(404);
    exit('Produit non trouvé');
}

// Parse tailles
if (method_exists($repo, 'parseSizes')) {
    $sizes = $repo->parseSizes($product['sizes'] ?? '');
} else {
    $sizes = [];
    $raw = $product['sizes'] ?? '';
    if ($raw !== '') {
        foreach (preg_split('/[,;|]/', $raw) as $p) {
            $p = strtoupper(trim($p));
            if ($p !== '' && !in_array($p, $sizes, true)) $sizes[] = $p;
        }
    }
}

$inStock = (int)($product['stock_quantity'] ?? 0) > 0;

function h(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

$page_title = h($product['name']) . ' - R&G';
$additional_css = ['styles/responsive.css'];
require_once __DIR__ . '/partials/header.php'; // <-- adapte si nécessaire
?>
<div class="page-wrapper">

    <!-- Lien retour -->
    <div class="page-top-bar" style="max-width:1200px;margin:0.8rem auto 0;padding:0 1rem;">
        <a href="javascript:history.back()" class="back-link" style="text-decoration:none;color:#1d4ed8;font-weight:600;font-size:.85rem;">
            &larr; Retour
        </a>
    </div>

    <!-- Messages flash -->
    <div style="max-width:1200px;margin:.5rem auto 0;padding:0 1rem;">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="flash flash-error"><?= h($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="flash flash-success"><?= h($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
    </div>

    <main class="product-detail-container">
        <div class="product-media">
            <?php
            $img = '';
            if (!empty($product['image'])) {
                $raw = $product['image'];
                $isAbs = preg_match('#^(?:https?:)?//#',$raw) || strncmp($raw,'data:',5)===0;
                $img = $isAbs ? $raw : '/'.ltrim($raw,'/');
            }
            ?>
            <?php if ($img): ?>
                <img src="<?= h($img) ?>" alt="<?= h($product['name']) ?>" class="product-image">
            <?php else: ?>
                <div class="product-image product-image--placeholder">
                    Image indisponible
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info-block">
            <h1 class="product-title"><?= h($product['name']) ?></h1>
            <div class="product-price"><?= number_format((float)$product['price'],2,',',' ') ?> €</div>
            <div class="product-stock <?= $inStock ? 'in-stock':'out-of-stock' ?>">
                <?= $inStock ? 'En stock' : 'Rupture de stock' ?>
            </div>

            <?php if (!empty($product['description'])): ?>
                <div class="product-description"><?= nl2br(h($product['description'])) ?></div>
            <?php endif; ?>

            <?php if (!empty($sizes)): ?>
                <section class="sizes-section">
                    <div class="sizes-label">Tailles</div>
                    <div class="sizes-grid" id="sizesGrid" role="radiogroup" aria-label="Sélection de la taille">
                        <?php foreach ($sizes as $s): ?>
                            <button type="button"
                                    class="size-btn"
                                    data-size="<?= h($s) ?>"
                                    role="radio"
                                    aria-checked="false"><?= h($s) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="note-size">Choisissez une taille avant d’ajouter au panier.</div>
                </section>
            <?php endif; ?>

            <form class="add-cart-form" method="post" action="add_to_cart.php">
                <?= function_exists('csrf_input') ? csrf_input() : '' ?>
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <input type="hidden" name="size" id="selectedSize" value="">
                <label class="qty-label">
                    Quantité
                    <input type="number" name="qty" value="1" min="1">
                </label>
                <button type="submit" id="addCartBtn" class="add-btn">
                    <i class="fas fa-shopping-cart"></i>
                    Ajouter au panier
                </button>
            </form>
        </div>
    </main>
</div>

<?php
// (Optionnel) si tu as un footer global
if (file_exists(__DIR__.'/partials/footer.php')) {
    require_once __DIR__.'/partials/footer.php';
} else {
    echo '</body></html>';
}
?>

<!-- Script de sélection des tailles -->
<script>
(function(){
    const grid = document.getElementById('sizesGrid');
    const sizeInput = document.getElementById('selectedSize');
    const addBtn = document.getElementById('addCartBtn');

    if (!addBtn) return;

    if (!grid) {
        addBtn.disabled = false;
        return;
    }

    const buttons = Array.from(grid.querySelectorAll('.size-btn'));
    if (buttons.length === 0) {
        addBtn.disabled = false;
        return;
    }

    if (buttons.length === 1) {
        const b = buttons[0];
        b.classList.add('active');
        b.setAttribute('aria-checked','true');
        sizeInput.value = b.dataset.size;
        addBtn.disabled = false;
    } else {
        addBtn.disabled = true;
    }

    grid.addEventListener('click', e => {
        if (!e.target.classList.contains('size-btn')) return;
        buttons.forEach(btn=>{
            btn.classList.remove('active');
            btn.setAttribute('aria-checked','false');
        });
        e.target.classList.add('active');
        e.target.setAttribute('aria-checked','true');
        sizeInput.value = e.target.dataset.size;
        addBtn.disabled = false;
    });
})();
</script>

<style>
/* Styles spécifiques fiche produit (si tu ne veux pas créer product-inline.css) */
.product-detail-container {
    max-width:1200px;
    margin:1rem auto 3rem;
    display:grid;
    gap:3rem;
    grid-template-columns:1fr 1fr;
    padding:0 1rem;
}
@media (max-width:900px){
    .product-detail-container { grid-template-columns:1fr; gap:2rem; }
}
.product-media {
    width: 100%;
    display: flex;
    align-items: flex-start;
    justify-content: center;
}
.product-image {
    width:100%; 
    min-height:500px;
    max-height:700px; 
    height:auto;
    object-fit:contain;
    border-radius:14px; 
    background:#f1f5f9;
    box-shadow:0 4px 22px rgba(0,0,0,.12);
}
.product-image--placeholder {
    display:flex; align-items:center; justify-content:center;
    color:#475569; font-weight:600; height:500px; width:100%;
}
.product-title { font-size:clamp(1.9rem,4.6vw,2.6rem); margin:0 0 1rem; font-weight:700; line-height:1.1; color:#1e3a8a; }
.product-price { font-size:2rem; font-weight:600; color:#b8860b; margin-bottom:1rem; }
.product-stock { font-weight:600; margin-bottom:1rem; }
.in-stock { color:#15803d; }
.out-of-stock { color:#b91c1c; }
.product-description { line-height:1.55; margin:0 0 1.5rem; color:#334155; }
.sizes-label { font-size:.7rem; font-weight:700; letter-spacing:.7px; text-transform:uppercase; color:#1e3a8a; margin-bottom:.5rem; }
.sizes-grid { display:flex; flex-wrap:wrap; gap:.55rem; }
.size-btn {
    padding:.55rem .9rem; border:2px solid #e2e8f0; background:#fff;
    font-size:.8rem; border-radius:9px; font-weight:600; cursor:pointer; transition:.16s;
}
.size-btn:hover { border-color:#1d4ed8; color:#1d4ed8; }
.size-btn.active { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
.note-size { font-size:.65rem; color:#64748b; margin-top:.4rem; }
.add-cart-form { margin-top:.8rem; }
.add-cart-form .qty-label { display:inline-flex; gap:.5rem; align-items:center; font-size:.75rem; font-weight:600; margin-bottom:.9rem; }
.add-cart-form input[type=number] { width:90px; padding:.45rem .55rem; }
.add-cart-form .add-btn {
    background:#1d4ed8; color:#fff; border:none; padding:.85rem 1.25rem;
    border-radius:11px; font-weight:600; cursor:pointer; display:inline-flex; gap:.5rem; align-items:center; transition:.18s;
}
.add-cart-form .add-btn:hover { background:#1e40af; }
.add-cart-form .add-btn:disabled { opacity:.55; cursor:not-allowed; }
.flash { border-radius:8px; padding:.65rem .95rem; font-size:.8rem; }
.flash-success { background:#ecfdf5; border:1px solid #10b981; color:#065f46; }
.flash-error { background:#fef2f2; border:1px solid #f87171; color:#991b1b; }
</style>