// ----------------------------------------------
// 3.  assets/js/wishlist.js
// ----------------------------------------------
(async()=>{
 const list=JSON.parse(localStorage.getItem('wishlist')||'[]');
 const res=await fetch('backend/product/get-products.php');
 const cat=await res.json();
 const wrap=document.getElementById('wlGrid');
 if(!list.length){wrap.innerHTML='<p>No items yet.</p>';return;}
 list.map(id=>cat.find(p=>p.id==id)).filter(Boolean).forEach(p=>{
  wrap.insertAdjacentHTML('beforeend',`<div class="card">
    <a href="product-details.html?id=${p.id}"><img src="assets/images/${encodeURIComponent(p.image)}"><h3>${p.name}</h3></a>
    <p class="price">₦${Number(p.price).toLocaleString()}</p>
  </div>`);
 });})();
