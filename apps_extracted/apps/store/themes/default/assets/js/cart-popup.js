document.addEventListener('DOMContentLoaded', function () {
  const cartPopup = document.getElementById('cart-popup');
  const cartItems = document.getElementById('cart-items');
  const cartTotal = document.getElementById('cart-total');
  const cartCount = document.getElementById('cart-count');
  const cartIcon = document.querySelector('.cart-icon');
  const closeCart = document.querySelector('.close-cart');

  async function loadCart() {
    try {
      const response = await fetch('/api/cart.php');
      const data = await response.json();
      
      if (data.items && Object.keys(data.items).length > 0) {
        const productIds = Object.keys(data.items);
        const productsResponse = await fetch('/api/products.php?ids=' + productIds.join(','));
        const productsData = await productsResponse.json();
        
        if (productsData.success) {
          renderCartItems(productsData.data, data.items);
          calculateTotal(productsData.data, data.items);
          loadUpsellProducts(productsData.data);
        }
      } else {
        cartItems.innerHTML = '<p>Your cart is empty</p>';
      }
    } catch (error) {
      console.error('Failed to load cart:', error);
    }
  }

  function renderCartItems(products, quantities) {
    cartItems.innerHTML = '';
    products.forEach(product => {
      const quantity = quantities[product.id] || 0;
      const item = document.createElement('div');
      item.className = 'cart-item';
      item.innerHTML = `
        <img src="${product.image}" alt="${product.title}" width="50">
        <div>
          <h4>${product.title}</h4>
          <p>₹${parseFloat(product.price).toFixed(2)} x ${quantity}</p>
        </div>
        <button class="remove-item" data-product-id="${product.id}">×</button>
      `;
      cartItems.appendChild(item);
    });

    // Add remove event listeners
    document.querySelectorAll('.remove-item').forEach(button => {
      button.addEventListener('click', async function () {
        const productId = this.dataset.productId;
        await removeFromCart(productId);
        loadCart();
      });
    });
  }

  function calculateTotal(products, quantities) {
    let total = 0;
    products.forEach(product => {
      total += parseFloat(product.price) * (quantities[product.id] || 0);
    });
    cartTotal.textContent = `₹${total.toFixed(2)}`;
  }

  async function loadUpsellProducts(currentProducts) {
    const upsellSection = document.getElementById('upsell-products');
    if (!upsellSection) return;

    try {
      const response = await fetch('/api/upsell-products.php');
      const data = await response.json();
      
      if (data.success && data.products.length > 0) {
        upsellSection.innerHTML = '<h4>You might also like</h4>';
        data.products.forEach(product => {
          const item = document.createElement('div');
          item.className = 'upsell-item';
          item.innerHTML = `
            <img src="${product.image}" alt="${product.title}" width="40">
            <div>
              <h5>${product.title}</h5>
              <p>₹${parseFloat(product.price).toFixed(2)}</p>
            </div>
            <button class="add-upsell" data-product-id="${product.id}">+</button>
          `;
          upsellSection.appendChild(item);
        });

        document.querySelectorAll('.add-upsell').forEach(button => {
          button.addEventListener('click', async function () {
            const productId = this.dataset.productId;
            await addToCart(productId);
            loadCart();
          });
        });
      }
    } catch (error) {
      console.error('Failed to load upsell products:', error);
    }
  }

  async function removeFromCart(productId) {
    try {
      await fetch(`/api/cart.php?action=remove&product_id=${productId}`);
    } catch (error) {
      console.error('Failed to remove item:', error);
    }
  }

  async function addToCart(productId) {
    try {
      await fetch('/api/cart.php?action=add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({product_id: productId, quantity: 1})
      });
    } catch (error) {
      console.error('Failed to add item:', error);
    }
  }

  if (cartIcon) {
    cartIcon.addEventListener('click', function (e) {
      e.preventDefault();
      loadCart();
      cartPopup.style.display = 'block';
    });
  }

  if (closeCart) {
    closeCart.addEventListener('click', () => {
      cartPopup.style.display = 'none';
    });
  }

  window.addEventListener('click', (e) => {
    if (e.target === cartPopup) {
      cartPopup.style.display = 'none';
    }
  });
});
