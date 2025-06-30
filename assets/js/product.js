const grid      = document.getElementById('grid');
const searchBox = document.getElementById('searchBox');
const catFilter = document.getElementById('catFilter');

let  ALL = [];
let  PAGE = 1;
const PER  = 8;

/* ------------------------------------------------------------------ */
(async function init () {
  const res = await fetch('backend/product/get-products.php');
  ALL = await res.json();
  populateCategories(ALL);
  renderPage();
})();

/* Build category <option> list */
function populateCategories(list){
  [...new Set(list.map(p=>p.category))].sort()
    .forEach(c=>catFilter.insertAdjacentHTML('beforeend',`<option>${c}</option>`));
}

/* Render current page */
function renderPage(){
  const term = searchBox.value.toLowerCase();
  const cat  = catFilter.value;
  const filtered = ALL.filter(p =>
      p.name.toLowerCase().includes(term) &&
      (!cat || p.category===cat));

  const slice = filtered.slice(0, PAGE*PER);
  grid.innerHTML = slice.map(p=>`
    <div class="card">
      <a href="product-details.html?id=${p.id}">
        <img src="assets/images/${encodeURIComponent(p.image)}" alt="${p.name}">
        <h3>${p.name}</h3>
      </a>
      <p class="price">₦${Number(p.price).toLocaleString()}</p>
      <button onclick="addToCart(${p.id})">Add to Cart</button>
    </div>`).join('');

  document.getElementById('moreBtn')?.remove();
  if (slice.length < filtered.length){
    grid.insertAdjacentHTML('afterend',
      `<button id="moreBtn" class="btn-primary load-more">Load more</button>`);
    document.getElementById('moreBtn').onclick = ()=>{ PAGE++; renderPage(); };
  }
}

searchBox.oninput   = renderPage;
catFilter.onchange  = ()=>{ PAGE=1; renderPage(); };

function addToCart(id){
  const cart=JSON.parse(localStorage.getItem('cart')||'{}');
  cart[id]=(cart[id]||0)+1;
  localStorage.setItem('cart', JSON.stringify(cart));
  alert('Added to cart!');
}
// ----------------------------------------------
// 2.  assets/js/product.js  (patch add‑to‑cart & wishlist)
// ----------------------------------------------
// inside your existing product.js add below helper
function addToWishlist(id){
  const wl=JSON.parse(localStorage.getItem('wishlist')||'[]');
  if(!wl.includes(id)) wl.push(id);
  localStorage.setItem('wishlist',JSON.stringify(wl));
  alert('❤️ Added to wishlist');
}
// (ensure cards have a ♥ button calling this)
