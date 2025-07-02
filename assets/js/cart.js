// assets/js/cart.js
document.addEventListener('DOMContentLoaded', async () => {
  // DOM Elements
  const cartBody = document.getElementById('cartBody');
  const emptyMsg = document.getElementById('emptyMsg');
  const cartTable = document.getElementById('cartTable');
  const cartSummary = document.getElementById('cartSummary');
  const subTotalEl = document.getElementById('subTotal');
  const shippingEl = document.getElementById('shipping');
  const grandTotalEl = document.getElementById('grandTotal');
  const clearCartBtn = document.getElementById('clearCart');
  const checkoutBtn = document.getElementById('checkoutBtn');

  // Product catalog cache
  let productCatalog = {};

  // Initialize cart
  async function initCart() {
    try {
      // Load product catalog
      const response = await fetch('backend/product/get-products.php');
      const products = await response.json();
      products.forEach(product => {
        productCatalog[product.id] = product;
      });

      // Render cart
      renderCart();
    } catch (error) {
      console.error('Error initializing cart:', error);
      emptyMsg.textContent = 'Error loading cart. Please refresh the page.';
      emptyMsg.style.display = 'block';
    }
  }

  // Get current cart
  function getCart() {
    return JSON.parse(localStorage.getItem('cart')) || {};
  }

  // Save cart to storage
  function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
  }

  // Render cart contents
  function renderCart() {
    const cart = getCart();
    cartBody.innerHTML = '';
    
    // Calculate totals
    let subtotal = 0;
    let itemCount = 0;

    // Build cart rows
    for (const [productId, quantity] of Object.entries(cart)) {
      const product = productCatalog[productId];
      if (!product) continue;

      itemCount++;
      const itemTotal = product.price * quantity;
      subtotal += itemTotal;

      cartBody.innerHTML += `
        <tr>
          <td>
            <div style="display: flex; align-items: center; gap: 10px;">
              <img src="assets/images/${encodeURIComponent(product.image)}" 
                   alt="${product.name}" 
                   class="cart-img">
              <div>
                <h4>${product.name}</h4>
                <small>${product.category}</small>
              </div>
            </div>
          </td>
          <td>₦${product.price.toLocaleString()}</td>
          <td>
            <button class="qty-btn" onclick="updateQuantity(${productId}, -1)">-</button>
            ${quantity}
            <button class="qty-btn" onclick="updateQuantity(${productId}, 1)">+</button>
          </td>
          <td>₦${itemTotal.toLocaleString()}</td>
          <td><button class="remove-btn" onclick="removeItem(${productId})">Remove</button></td>
        </tr>
      `;
    }

    // Calculate shipping (free over ₦10,000)
    const shipping = subtotal >= 10000 ? 0 : 1500;
    const total = subtotal + shipping;

    // Update UI
    if (itemCount > 0) {
      emptyMsg.style.display = 'none';
      cartTable.style.display = 'table';
      cartSummary.style.display = 'block';
      
      subTotalEl.textContent = `₦${subtotal.toLocaleString()}`;
      shippingEl.textContent = `₦${shipping.toLocaleString()}`;
      grandTotalEl.textContent = `₦${total.toLocaleString()}`;
    } else {
      emptyMsg.style.display = 'block';
      cartTable.style.display = 'none';
      cartSummary.style.display = 'none';
    }
  }

  // Update item quantity
  window.updateQuantity = (productId, change) => {
    const cart = getCart();
    const newQty = (cart[productId] || 0) + change;
    
    if (newQty <= 0) {
      delete cart[productId];
    } else {
      cart[productId] = newQty;
    }
    
    saveCart(cart);
    renderCart();
  };

  // Remove item from cart
  window.removeItem = (productId) => {
    if (confirm('Remove this item from your cart?')) {
      const cart = getCart();
      delete cart[productId];
      saveCart(cart);
      renderCart();
    }
  };

  // Clear entire cart
  clearCartBtn.addEventListener('click', () => {
    if (Object.keys(getCart()).length > 0 && confirm('Are you sure you want to clear your cart?')) {
      localStorage.removeItem('cart');
      renderCart();
    }
  });

  // Proceed to checkout
  checkoutBtn.addEventListener('click', () => {
    if (Object.keys(getCart()).length === 0) {
      alert('Your cart is empty!');
      return;
    }
    window.location.href = 'checkout.html';
  });

  // Update cart count in header
  function updateCartCount() {
    const cart = getCart();
    const count = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
    document.querySelectorAll('.cart-count').forEach(el => {
      el.textContent = count;
    });
  }

  // Initialize
  initCart();
});