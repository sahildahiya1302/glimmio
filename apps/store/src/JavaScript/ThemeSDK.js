/**
 * Theme SDK - Unified JavaScript API for dynamic theme functionality
 * Supports all theme types: Liquid, PHP, HTML
 */

class ThemeSDK {
    constructor(options = {}) {
        this.apiBaseUrl = options.apiBaseUrl || '/api';
        this.debug = options.debug || false;
        this.cart = new CartAPI(this.apiBaseUrl);
        this.products = new ProductAPI(this.apiBaseUrl);
        this.reviews = new ReviewAPI(this.apiBaseUrl);
        this.checkout = new CheckoutAPI(this.apiBaseUrl);
    }
    
    log(message, ...args) {
        if (this.debug) {
            console.log('[ThemeSDK]', message, ...args);
        }
    }
    
    init() {
        this.log('ThemeSDK initialized');
        this.bindEvents();
        this.updateCartCount();
    }
    
    bindEvents() {
        // Add to cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-add-to-cart]')) {
                e.preventDefault();
                const productId = e.target.dataset.productId;
                const variantId = e.target.dataset.variantId || null;
                const quantity = parseInt(e.target.dataset.quantity || 1);
                
                this.cart.add(productId, variantId, quantity)
                    .then(() => this.showToast('Added to cart!'))
                    .catch(err => this.showToast('Error adding to cart', 'error'));
            }
        });
        
        // Remove from cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-remove-from-cart]')) {
                e.preventDefault();
                const itemId = e.target.dataset.itemId;
                
                this.cart.remove(itemId)
                    .then(() => {
                        this.updateCartCount();
                        this.showToast('Removed from cart');
                    })
                    .catch(err => this.showToast('Error removing from cart', 'error'));
            }
        });
        
        // Review form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('[data-review-form]')) {
                e.preventDefault();
                this.handleReviewSubmit(e.target);
            }
        });
        
        // Quantity updates
        document.addEventListener('change', (e) => {
            if (e.target.matches('[data-quantity-input]')) {
                const itemId = e.target.dataset.itemId;
                const quantity = parseInt(e.target.value);
                
                this.cart.update(itemId, quantity)
                    .then(() => this.updateCartCount())
                    .catch(err => this.showToast('Error updating quantity', 'error'));
            }
        });
    }
    
    showToast(message, type = 'success') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `theme-sdk-toast theme-sdk-toast--${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('theme-sdk-toast--show');
        }, 100);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    updateCartCount() {
        this.cart.getCount()
            .then(count => {
                const cartCountElements = document.querySelectorAll('[data-cart-count]');
                cartCountElements.forEach(el => {
                    el.textContent = count;
                    el.style.display = count > 0 ? 'inline' : 'none';
                });
            })
            .catch(err => this.log('Error updating cart count:', err));
    }
    
    handleReviewSubmit(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        this.reviews.add(data)
            .then(() => {
                this.showToast('Review submitted successfully');
                form.reset();
                // Reload reviews section
                this.refreshReviewsSection(data.product_id);
            })
            .catch(err => this.showToast('Error submitting review', 'error'));
    }
    
    refreshReviewsSection(productId) {
        // Refresh reviews section via AJAX
        fetch(`${this.apiBaseUrl}/reviews/list.php?product_id=${productId}`)
            .then(res => res.json())
            .then(reviews => {
                const container = document.querySelector('[data-reviews-container]');
                if (container) {
                    container.innerHTML = this.renderReviews(reviews);
                }
            });
    }
    
    renderReviews(reviews) {
        return reviews.map(review => `
            <div class="review-item">
                <div class="review-rating">${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}</div>
                <div class="review-author">${review.author}</div>
                <div class="review-title">${review.title}</div>
                <div class="review-content">${review.content}</div>
                <div class="review-date">${review.created_at}</div>
            </div>
        `).join('');
    }
}

// API Classes
class CartAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async add(productId, variantId = null, quantity = 1) {
        const response = await fetch(`${this.baseUrl}/cart/add.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity })
        });
        
        if (!response.ok) throw new Error('Failed to add to cart');
        return response.json();
    }
    
    async remove(itemId) {
        const response = await fetch(`${this.baseUrl}/cart/remove.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId })
        });
        
        if (!response.ok) throw new Error('Failed to remove from cart');
        return response.json();
    }
    
    async update(itemId, quantity) {
        const response = await fetch(`${this.baseUrl}/cart/update.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId, quantity })
        });
        
        if (!response.ok) throw new Error('Failed to update cart');
        return response.json();
    }
    
    async getCount() {
        const response = await fetch(`${this.baseUrl}/cart/count.php`);
        if (!response.ok) throw new Error('Failed to get cart count');
        const data = await response.json();
        return data.count || 0;
    }
    
    async get() {
        const response = await fetch(`${this.baseUrl}/cart.php`);
        if (!response.ok) throw new Error('Failed to get cart');
        return response.json();
    }
}

class ProductAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async get(id) {
        const response = await fetch(`${this.baseUrl}/product.php?id=${id}`);
        if (!response.ok) throw new Error('Failed to get product');
        return response.json();
    }
    
    async list(params = {}) {
        const query = new URLSearchParams(params);
        const response = await fetch(`${this.baseUrl}/products.php?${query}`);
        if (!response.ok) throw new Error('Failed to get products');
        return response.json();
    }
}

class ReviewAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async add(data) {
        const response = await fetch(`${this.baseUrl}/reviews/add.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) throw new Error('Failed to add review');
        return response.json();
    }
    
    async list(productId) {
        const response = await fetch(`${this.baseUrl}/reviews/list.php?product_id=${productId}`);
        if (!response.ok) throw new Error('Failed to get reviews');
        return response.json();
    }
}

class CheckoutAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async submit(data) {
        const response = await fetch(`${this.baseUrl}/order/submit.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) throw new Error('Failed to submit order');
        return response.json();
    }
}

// Auto-initialize ThemeSDK
document.addEventListener('DOMContentLoaded', () => {
    window.themeSDK = new ThemeSDK({
        apiBaseUrl: '/api',
        debug: window.location.search.includes('debug=1')
    });
    window.themeSDK.init();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSDK;
}
