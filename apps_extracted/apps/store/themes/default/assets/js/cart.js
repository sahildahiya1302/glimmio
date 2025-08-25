document.addEventListener('DOMContentLoaded', function () {
  const addToCartButtons = document.querySelectorAll('.add-to-cart-button');
  const cartCountElement = document.getElementById('cart-count');

  async function updateCartCount() {
    try {
      const response = await fetch('/api/cart.php');
      const data = await response.json();
      if (data.items) {
        const count = Object.values(data.items).reduce((a, b) => a + b, 0);
        if (cartCountElement) {
          cartCountElement.textContent = count;
        }
      }
    } catch (error) {
      console.error('Failed to update cart count:', error);
    }
  }

  async function addToCart(productId, quantity = 1) {
    try {
      const response = await fetch('/api/cart.php?action=add', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({product_id: productId, quantity: quantity})
      });
      const data = await response.json();
      if (data.success) {
        updateCartCount();
        alert('Product added to cart');
      } else {
        alert('Failed to add product to cart: ' + (data.message || 'Unknown error'));
      }
    } catch (error) {
      alert('Error adding product to cart: ' + error.message);
    }
  }

  addToCartButtons.forEach(button => {
    button.addEventListener('click', function () {
      const productId = parseInt(this.dataset.productId, 10);
      if (productId) {
        addToCart(productId);
      }
    });
  });

  updateCartCount();
});
