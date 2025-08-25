document.addEventListener('DOMContentLoaded', function () {
  const quickViewButtons = document.querySelectorAll('.quick-view-button');
  const quickViewModal = document.getElementById('quick-view-modal');
  const quickViewContent = document.getElementById('quick-view-content');
  const closeQuickView = document.querySelector('.close-quick-view');

  quickViewButtons.forEach(button => {
    button.addEventListener('click', async function (e) {
      e.preventDefault();
      const productId = this.dataset.productId;
      if (!productId) return;

      try {
        const response = await fetch(`/api/product.php?id=${productId}`);
        const product = await response.json();
        if (product.success) {
          renderQuickView(product.data);
          quickViewModal.style.display = 'block';
        }
      } catch (error) {
        console.error('Failed to load quick view:', error);
      }
    });
  });

  function renderQuickView(product) {
    quickViewContent.innerHTML = `
      <div class="quick-view-product">
        <div class="quick-view-image">
          <img src="${product.image}" alt="${product.title}">
        </div>
        <div class="quick-view-details">
          <h3>${product.title}</h3>
          <p class="price">₹${parseFloat(product.price).toFixed(2)}</p>
          <p class="description">${product.description || ''}</p>
          <button class="btn btn-primary quick-buy-button" data-product-id="${product.id}">
            Quick Buy
          </button>
          <button class="btn btn-secondary add-to-cart-button" data-product-id="${product.id}">
            Add to Cart
          </button>
        </div>
      </div>
    `;

    // Add event listeners for new buttons
    quickViewContent.querySelector('.quick-buy-button').addEventListener('click', function () {
      window.location.href = `/checkout?product=${product.id}`;
    });

    quickViewContent.querySelector('.add-to-cart-button').addEventListener('click', async function () {
      await addToCart(product.id);
      quickViewModal.style.display = 'none';
    });
  }

  if (closeQuickView) {
    closeQuickView.addEventListener('click', () => {
      quickViewModal.style.display = 'none';
    });
  }

  window.addEventListener('click', (e) => {
    if (e.target === quickViewModal) {
      quickViewModal.style.display = 'none';
    }
  });
});
