<?php
declare(strict_types=1);

class HtmlRenderer {
    private $themePath;
    private $apiBaseUrl;
    
    public function __construct(string $themePath, string $apiBaseUrl = '/api') {
        $this->themePath = $themePath;
        $this->apiBaseUrl = $apiBaseUrl;
    }
    
    public function render(string $templatePath, array $context = []): string {
        $templateContent = file_get_contents($templatePath);
        
        // Inject ThemeSDK and context data
        $processed = $this->injectThemeSDK($templateContent);
        $processed = $this->injectContextData($processed, $context);
        $processed = $this->injectApiEndpoints($processed);
        
        return $processed;
    }
    
    private function injectThemeSDK(string $content): string {
        $sdkScript = '<script src="/src/JavaScript/ThemeSDK.js"></script>';
        
        // Inject SDK before closing head tag
        if (stripos($content, '</head>') !== false) {
            $content = str_ireplace('</head>', $sdkScript . "\n</head>", $content);
        } else {
            // Fallback: inject at the beginning
            $content = $sdkScript . "\n" . $content;
        }
        
        return $content;
    }
    
    private function injectContextData(string $content, array $context): string {
        $contextScript = '<script>';
        $contextScript .= 'window.themeContext = ' . json_encode($context) . ';';
        $contextScript .= '</script>';
        
        // Inject context after SDK
        if (stripos($content, '</head>') !== false) {
            $content = str_ireplace('</head>', $contextScript . "\n</head>", $content);
        } else {
            $content = $contextScript . "\n" . $content;
        }
        
        return $content;
    }
    
    private function injectApiEndpoints(string $content): string {
        $endpointsScript = '<script>';
        $endpointsScript .= 'window.apiEndpoints = ' . json_encode([
            'cart' => [
                'add' => $this->apiBaseUrl . '/cart/add.php',
                'remove' => $this->apiBaseUrl . '/cart/remove.php',
                'update' => $this->apiBaseUrl . '/cart/update.php',
                'get' => $this->apiBaseUrl . '/cart.php',
                'count' => $this->apiBaseUrl . '/cart/count.php'
            ],
            'products' => [
                'get' => $this->apiBaseUrl . '/product.php',
                'list' => $this->apiBaseUrl . '/products.php',
                'search' => $this->apiBaseUrl . '/products/search.php'
            ],
            'reviews' => [
                'add' => $this->apiBaseUrl . '/reviews/add.php',
                'list' => $this->apiBaseUrl . '/reviews/list.php'
            ],
            'checkout' => [
                'submit' => $this->apiBaseUrl . '/order/submit.php'
            ]
        ]) . ';';
        $endpointsScript .= '</script>';
        
        // Inject endpoints after context
        if (stripos($content, '</head>') !== false) {
            $content = str_ireplace('</head>', $endpointsScript . "\n</head>", $content);
        } else {
            $content = $endpointsScript . "\n" . $content;
        }
        
        return $content;
    }
    
    public function renderSection(string $sectionName, array $context = []): string {
        $sectionPath = $this->themePath . "/sections/{$sectionName}.html";
        
        if (!file_exists($sectionPath)) {
            return '';
        }
        
        return $this->render($sectionPath, $context);
    }
    
    public function renderSnippet(string $snippetName, array $context = []): string {
        $snippetPath = $this->themePath . "/snippets/{$snippetName}.html";
        
        if (!file_exists($snippetPath)) {
            return '';
        }
        
        return $this->render($snippetPath, $context);
    }
    
    public function createPlaceholderContent(string $type, array $data = []): string {
        switch ($type) {
            case 'product':
                return $this->createProductPlaceholder($data);
            case 'cart':
                return $this->createCartPlaceholder($data);
            case 'collection':
                return $this->createCollectionPlaceholder($data);
            case 'reviews':
                return $this->createReviewsPlaceholder($data);
            default:
                return '<div class="placeholder">Loading...</div>';
        }
    }
    
    private function createProductPlaceholder(array $data): string {
        return '
        <div class="product-placeholder" data-product-id="' . ($data['id'] ?? '') . '">
            <div class="product-image-placeholder">
                <img src="' . ($data['image'] ?? '/assets/images/placeholder.jpg') . '" alt="' . ($data['title'] ?? 'Product') . '">
            </div>
            <div class="product-info">
                <h2 class="product-title">' . ($data['title'] ?? 'Product Title') . '</h2>
                <div class="product-price">' . ($data['price'] ?? '$0.00') . '</div>
                <button class="add-to-cart-btn" data-product-id="' . ($data['id'] ?? '') . '">
                    Add to Cart
                </button>
            </div>
        </div>';
    }
    
    private function createCartPlaceholder(array $data): string {
        return '
        <div class="cart-placeholder">
            <h3>Shopping Cart</h3>
            <div class="cart-items" data-cart-items>
                <div class="cart-item-placeholder">
                    <p>Loading cart items...</p>
                </div>
            </div>
            <div class="cart-total">
                <strong>Total: <span data-cart-total>$0.00</span></strong>
            </div>
            <button class="checkout-btn" data-checkout>
                Proceed to Checkout
            </button>
        </div>';
    }
    
    private function createCollectionPlaceholder(array $data): string {
        return '
        <div class="collection-placeholder">
            <h1>' . ($data['title'] ?? 'Collection') . '</h1>
            <div class="collection-description">' . ($data['description'] ?? '') . '</div>
            <div class="collection-products" data-collection-products>
                <div class="product-grid-placeholder">
                    <p>Loading products...</p>
                </div>
            </div>
        </div>';
    }
    
    private function createReviewsPlaceholder(array $data): string {
        return '
        <div class="reviews-placeholder">
            <h3>Customer Reviews</h3>
            <div class="reviews-list" data-reviews-container>
                <div class="review-placeholder">
                    <p>Loading reviews...</p>
                </div>
            </div>
            <form class="review-form" data-review-form>
                <h4>Write a Review</h4>
                <div class="form-group">
                    <label>Rating</label>
                    <select name="rating" required>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Review</label>
                    <textarea name="content" required></textarea>
                </div>
                <button type="submit">Submit Review</button>
            </form>
        </div>';
    }
}
