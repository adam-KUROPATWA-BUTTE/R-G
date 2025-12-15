(function() {
  function $(sel, ctx){ return (ctx||document).querySelector(sel); }
  function createEl(tag,c){ var e=document.createElement(tag); if(c) e.className=c; return e; }
  
  // Handle size selection
  function handleSizes(sizes) {
    var sizesWrap = document.getElementById('pqv-sizes-wrapper');
    var sizesBox = document.getElementById('pqv-sizes');
    var sizeHidden = document.getElementById('pqv-size-selected');
    
    sizesBox.innerHTML = '';
    sizeHidden.value = '';
    
    if (sizes && sizes.length > 0) {
      sizesWrap.style.display = 'block';
      sizes.forEach(function(sz){
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pqv-size-btn';
        btn.textContent = sz;
        btn.onclick = function(){
          // reset
          var all = sizesBox.querySelectorAll('.pqv-size-btn');
          for (var i=0;i<all.length;i++) all[i].classList.remove('active');
          btn.classList.add('active');
          sizeHidden.value = sz;
        };
        sizesBox.appendChild(btn);
      });
    } else {
      sizesWrap.style.display = 'none';
    }
  }

  window.showProductDetails = function(id){
    var modal = $('#productQuickViewModal');
    if(!modal){ console.error('Modal non trouvée'); return; }

    // Reset contenu
    modal.classList.add('open');
    $('body').classList.add('modal-open');
    $('#pqv-image').src = '';
    $('#pqv-image').alt = '';
    $('#pqv-title').textContent = 'Chargement...';
    $('#pqv-desc').textContent = '';
    $('#pqv-price').textContent = '';
    $('#pqv-stock').textContent = '';
    $('#pqv-add-form').style.display = 'none';

    // AJAX (fetch fallback XHR)
    var url = 'product_api.php?id=' + encodeURIComponent(id);
    if (window.fetch) {
      fetch(url).then(function(r){ return r.json(); }).then(fill).catch(errHandler);
    } else {
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function(){
        if(xhr.readyState === 4){
          if(xhr.status === 200){
            try { fill(JSON.parse(xhr.responseText)); } catch(e){ errHandler(e); }
          } else errHandler(xhr.status);
        }
      };
      xhr.open('GET', url, true);
      xhr.send();
    }

    function fill(data){
      if(data.error){
        $('#pqv-title').textContent = 'Produit introuvable';
        return;
      }
      $('#pqv-title').textContent = data.name || 'Produit';
      $('#pqv-desc').textContent = data.description || '';
      if (data.image) {
        $('#pqv-image').src = data.image;
        $('#pqv-image').alt = data.name || '';
      } else {
        $('#pqv-image').src = '';
        $('#pqv-image').alt = '';
      }
      $('#pqv-price').textContent = data.price ? data.price.toFixed(2).replace('.', ',') + ' €' : '';
      var stockEl = $('#pqv-stock');
      stockEl.textContent = data.stock_label || '';
      stockEl.className = 'pqv-stock ' + (data.stock_class || '');


      // Handle sizes
      handleSizes(data.sizes);

      // Formulaire ajout panier
      var form = $('#pqv-add-form');
      form.style.display = data.stock_quantity > 0 ? 'block' : 'none';
      $('#pqv-id').value = data.id;

      // Lien fiche complète (si tu crées product.php)
      var fullLink = $('#pqv-full-link');
      if (fullLink) {
        fullLink.href = 'product.php?id=' + encodeURIComponent(data.id);
      }
    }

    function errHandler(e){
      $('#pqv-title').textContent = 'Erreur de chargement';
      console.error(e);
    }
  };

  window.closeProductModal = function(){
    var modal = document.getElementById('productQuickViewModal');
    if(modal){
      modal.classList.remove('open');
      document.body.classList.remove('modal-open');
    }
  };

  // Fermer sur clic overlay
  document.addEventListener('click', function(e){
    var modal = document.getElementById('productQuickViewModal');
    if(!modal) return;
    if(e.target === modal) closeProductModal();
  });

  // Zoom simple
  var zoomed = false;
  document.addEventListener('click', function(e){
    if(e.target && e.target.id === 'pqv-image' && e.target.src){
      zoomed = !zoomed;
      if(zoomed){
        e.target.classList.add('zoomed');
      } else {
        e.target.classList.remove('zoomed');
      }
    }
  });

  document.addEventListener('keyup', function(e){
    if(e.key === 'Escape') closeProductModal();
  });

})();