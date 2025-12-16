<div id="productQuickViewModal" class="pqv-modal">
  <div class="pqv-dialog">
    <button type="button" class="pqv-close" onclick="closeProductModal()">&times;</button>
    <div class="pqv-left">
      <img id="pqv-image" src="" alt="" class="pqv-main-img">
      <div class="pqv-zoom-hint">Clique sur l'image pour zoomer</div>
    </div>
    <div class="pqv-right">
      <h2 id="pqv-title">...</h2>
      <div id="pqv-stock" class="pqv-stock"></div>

      <div id="pqv-sizes-wrapper" class="pqv-sizes" style="display:none;">
        <div class="pqv-sizes-label">Tailles :</div>
        <div id="pqv-sizes" class="pqv-sizes-list"></div>
      </div>

      <div id="pqv-price" class="pqv-price"></div>

      <p id="pqv-desc" class="pqv-desc"></p>

      <form id="pqv-add-form" method="post" action="/cart/add" class="pqv-form">
        <?php if (function_exists('csrf_input')) echo csrf_input(); ?>
        <input type="hidden" name="id" id="pqv-id" value="">
        <input type="hidden" name="back" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
        <div class="pqv-qty-row">
          <label>Qté
            <input type="number" name="qty" value="1" min="1">
            <input type="hidden" name="size" id="pqv-size-selected" value="">
          </label>
          <button type="submit" class="pqv-btn-add">
            <i class="fas fa-shopping-cart"></i> Ajouter
          </button>
        </div>
      </form>

      <a id="pqv-full-link" href="#" class="pqv-full-link">Voir la fiche complète</a>
    </div>
  </div>
</div>