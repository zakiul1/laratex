{{-- @once
@push('scripts')
<script>
(function(){
  // delay until DOM ready
  function initDynamicGridCart() {
    // only run if plugin is active
    window.DynamicGridActive = true;
    if (!window.DynamicGridActive) return;

    var cart = [];
    var cartCountEl = document.getElementById('dynamicCartCount');
    var cartBtn     = document.getElementById('dynamicCartBtn');
    if (!cartCountEl || !cartBtn) return;

    // helper to update badge
    function refreshBadge() {
      if (cart.length) {
        cartCountEl.style.display = 'flex';
        cartCountEl.textContent = cart.length;
      } else {
        cartCountEl.style.display = 'none';
      }
    }

    // attach click to each price button
    document.querySelectorAll('.get-price-btn').forEach(function(btn) {
      btn.addEventListener('click', function(evt) {
        console.log('Get Price clicked:', btn.dataset.id);
        var id    = btn.getAttribute('data-id');
        var title = btn.getAttribute('data-title');
        var img   = btn.getAttribute('data-image');
        if (!cart.some(function(i){ return i.id === id; })) {
          cart.push({ id: id, title: title, img: img });
          refreshBadge();
        }
      });
    });

    // show modal
    cartBtn.addEventListener('click', function(){ renderCartModal(); });

    function renderCartModal() {
      var existing = document.getElementById('dynamicCartModal');
      if (existing) existing.remove();

      var modal = document.createElement('div');
      modal.id = 'dynamicCartModal';
      var html = '' +
        '<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">' +
          '<div class="bg-white rounded-lg w-96 p-4 relative">' +
            '<button id="closeCartModal" class="absolute top-2 right-2">✕</button>' +
            '<h2 class="text-xl font-bold mb-4">Your Cart</h2>' +
            '<ul id="cartItems" class="space-y-2 max-h-60 overflow-auto text-sm"></ul>' +
            '<div class="mt-4 text-right">' +
              '<button id="continueCart" class="bg-blue-600 text-white px-4 py-2 rounded">Continue</button>' +
              '<button id="cancelCart" class="ml-2 bg-gray-300 px-4 py-2 rounded">Cancel</button>' +
            '</div>' +
          '</div>' +
        '</div>';
      modal.innerHTML = html;
      document.body.appendChild(modal);

      var list = modal.querySelector('#cartItems');
      cart.forEach(function(item) {
        var li = document.createElement('li');
        li.className = 'flex items-center justify-between';
        li.innerHTML = '' +
          '<div class="flex items-center space-x-2">' +
            '<img src="' + item.img + '" class="w-8 h-8 object-cover rounded">' +
            '<span>' + item.title + '</span>' +
          '</div>' +
          '<button class="removeItem" data-id="' + item.id + '">✕</button>';
        list.appendChild(li);
      });

      modal.querySelector('#closeCartModal').onclick =
      modal.querySelector('#cancelCart').onclick = function(){ modal.remove(); };

      modal.querySelectorAll('.removeItem').forEach(function(xbtn){
        xbtn.addEventListener('click', function(){
          var id = xbtn.getAttribute('data-id');
          var idx = cart.findIndex(function(i){ return i.id === id; });
          if (idx > -1) {
            cart.splice(idx, 1);
            refreshBadge();
            renderCartModal();
          }
        });
      });

      modal.querySelector('#continueCart').addEventListener('click', function(){
        fetch('https://edesk.siatexmail.com/api/send', {
          method: 'POST', headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer KEed6834a78387e15376d4fefe52316Y'
          },
          body: JSON.stringify({ products: cart.map(function(i){ return { id: i.id, title: i.title, image: i.img }; }) })
        })
        .then(function(r){ return r.json(); })
        .then(function(json){ alert('Sent! ' + JSON.stringify(json)); cart = []; refreshBadge(); })
        .catch(function(){ alert('Error sending cart.'); });
        modal.remove();
      });

      refreshBadge();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDynamicGridCart);
  } else {
    initDynamicGridCart();
  }
})();
</script>
@endpush
@endonce
 --}}