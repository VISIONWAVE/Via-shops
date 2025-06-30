// ----------------------------------------------
// 1.  assets/js/cart.js  (replace ENTIRE file)
// ----------------------------------------------
const tbody   = document.getElementById('cartBody');
const subTot  = document.getElementById('subTotal');
const emptyMsg= document.getElementById('emptyMsg');

let CATALOG = {};
(async () => {
  const res = await fetch('backend/product/get-products.php');
  (await res.json()).forEach(p => CATALOG[p.id] = p);
  render();
})();

function cart(){return JSON.parse(localStorage.getItem('cart')||'{}');}
function save(c){localStorage.setItem('cart',JSON.stringify(c));}

function render(){
  const c = cart(); tbody.innerHTML=''; let tot=0, rows=0;
  for(const [id,qty] of Object.entries(c)){
    const p=CATALOG[id]; if(!p)continue; rows++; const line=p.price*qty; tot+=line;
    tbody.insertAdjacentHTML('beforeend',`
    <tr>
      <td><img src="assets/images/${encodeURIComponent(p.image)}"/></td>
      <td>${p.name}</td>
      <td>
        <button onclick="chg(${id},-1)">−</button>
        ${qty}
        <button onclick="chg(${id},1)">+</button>
      </td>
      <td>₦${Number(line).toLocaleString()}</td>
      <td><button onclick="rm(${id})">❌</button></td>
    </tr>`);
  }
  emptyMsg.style.display = rows? 'none':'';
  subTot.textContent='₦'+Number(tot).toLocaleString();
}
function chg(id,d){ const c=cart(); c[id]=(c[id]||0)+d; if(c[id]<=0)delete c[id]; save(c); render(); }
function rm(id){ const c=cart(); delete c[id]; save(c); render(); }
function clearCart(){ localStorage.removeItem('cart'); render(); }
