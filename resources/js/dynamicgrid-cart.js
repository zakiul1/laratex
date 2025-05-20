/* // resources/js/dynamicgrid-cart.js
;(function(){
    //
    // Alpine component for the multi-step request form
    //
    window.requestCartForm = function(cartItems) {
      return {
        step: 1,
        name: '',
        whatsapp: '',
        email: '',
        message: '',
        cart: cartItems,
        submit() {
          const payload = {
            name:     this.name,
            whatsapp: this.whatsapp,
            email:    this.email,
            message:  this.message,
            products: this.cart
          };
          fetch('/dynamicgrid/request-price', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
              'Content-Type':  'application/json',
              'Accept':        'application/json'
            },
            body: JSON.stringify(payload)
          })
          .then(r => r.json())
          .then(json => {
            alert(json.message || 'Request sent!');
            document.getElementById('dynamicRequestModal').remove();
            // clear global cart
            cart = [];
            updateBadge();
          })
          .catch(() => alert('Error sending request.'));
        }
      }
    };
  
    //
    // Main cart initialization
    //
    function initCart(){
      window.DynamicGridActive = true;
      if(!window.DynamicGridActive) return;
  
      // global cart state
      window.cart = [];
  
      // DOM elements
      const badge  = document.getElementById('dynamicCartCount');
      const cartBtn = document.getElementById('dynamicCartBtn');
      if(!badge || !cartBtn) return;
  
      // update badge display
      window.updateBadge = function(){
        badge.textContent = cart.length;
        badge.style.display = cart.length ? 'flex' : 'none';
      };
  
      // 1) collect on "Get Price" buttons
      document.querySelectorAll('.get-price-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const { id, title, image: img } = btn.dataset;
          if(!cart.some(x=>x.id===id)){
            cart.push({ id, title, img });
            updateBadge();
          }
        });
      });
  
      // 2) show cart overview modal
      cartBtn.addEventListener('click', showCartOverviewModal);
  
      function showCartOverviewModal(){
        document.getElementById('dynamicCartModal')?.remove();
        const m = document.createElement('div');
        m.id = 'dynamicCartModal';
        m.innerHTML = `
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 p-4 relative">
      <button id="closeCart" class="absolute top-2 right-2">✕</button>
      <h2 class="text-xl font-bold mb-4">Your Cart</h2>
      <ul id="cartList" class="space-y-2 max-h-60 overflow-auto text-sm"></ul>
      <div class="mt-4 text-right">
        <button id="continueCart" class="bg-blue-600 text-white px-4 py-2 rounded">Continue</button>
        <button id="cancelCart"   class="ml-2 bg-gray-300 px-4 py-2 rounded">Cancel</button>
      </div>
    </div>
  </div>`;
        document.body.appendChild(m);
  
        // populate list
        const list = m.querySelector('#cartList');
        cart.forEach(item=>{
          const li = document.createElement('li');
          li.className = 'flex items-center justify-between';
          li.innerHTML = `
  <div class="flex items-center space-x-2">
    <img src="${item.img}" class="w-8 h-8 object-cover rounded">
    <span>${item.title}</span>
  </div>
  <button class="removeItem" data-id="${item.id}">✕</button>`;
          list.appendChild(li);
        });
  
        // handlers
        m.querySelector('#closeCart').onclick =
        m.querySelector('#cancelCart').onclick = ()=>m.remove();
  
        m.querySelectorAll('.removeItem').forEach(x=>{
          x.addEventListener('click', ()=>{
            cart = cart.filter(i=>i.id!==x.dataset.id);
            updateBadge();
            showCartOverviewModal();
          });
        });
  
        m.querySelector('#continueCart').addEventListener('click', ()=>{
          m.remove();
          showRequestFormModal();
        });
  
        updateBadge();
      }
  
      //
      // 3) show multi-step Alpine form
      //
      function showRequestFormModal(){
        document.getElementById('dynamicRequestModal')?.remove();
        const f = document.createElement('div');
        f.id = 'dynamicRequestModal';
        f.innerHTML = `
  <div x-data="requestCartForm(${JSON.stringify(cart)})" x-cloak
       class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg w-96 p-6 relative">
      <button @click="$el.closest('#dynamicRequestModal').remove()"
              class="absolute top-2 right-2">✕</button>
      <form @submit.prevent="submit" class="space-y-6 text-sm">
        <!-- Step 1 -->
        <div x-show="step===1">
          <h2 class="text-lg font-medium">Step 1: Your Info</h2>
          <input type="text"     x-model="name"     placeholder="Name"     class="w-full border p-2 rounded" required>
          <input type="text"     x-model="whatsapp" placeholder="WhatsApp" class="w-full border p-2 rounded">
          <button type="button" @click="step=2"
                  class="mt-4 w-full bg-blue-600 text-white py-2 rounded">Next</button>
        </div>
        <!-- Step 2 -->
        <div x-show="step===2" style="display:none">
          <h2 class="text-lg font-medium">Step 2: Contact</h2>
          <input type="email"    x-model="email"   placeholder="Email"   class="w-full border p-2 rounded" required>
          <textarea x-model="message"
                    placeholder="Write your Message Here"
                    class="w-full border p-2 rounded h-24" required></textarea>
          <div class="flex justify-between">
            <button type="button" @click="step=1"
                    class="bg-gray-300 py-2 px-4 rounded">Previous</button>
            <button type="button" @click="step=3"
                    class="bg-blue-600 text-white py-2 px-4 rounded">Next</button>
          </div>
        </div>
        <!-- Step 3 -->
        <div x-show="step===3" style="display:none">
          <h2 class="text-lg font-medium">Step 3: Confirm</h2>
          <p><strong>Name:</strong>      <span x-text="name"></span></p>
          <p><strong>WhatsApp:</strong>  <span x-text="whatsapp"></span></p>
          <p><strong>Email:</strong>     <span x-text="email"></span></p>
          <p><strong>Message:</strong>   <span x-text="message"></span></p>
          <div class="flex justify-between">
            <button type="button" @click="step=2"
                    class="bg-gray-300 py-2 px-4 rounded">Previous</button>
            <button type="submit"
                    class="bg-blue-600 text-white py-2 px-4 rounded">Send</button>
          </div>
        </div>
      </form>
    </div>
  </div>`;
        document.body.appendChild(f);
        window.Alpine.initTree(f);
      }
  
      updateBadge();
    }
  
    if(document.readyState==='loading'){
      document.addEventListener('DOMContentLoaded', initCart);
    } else {
      initCart();
    }
  })();
   */