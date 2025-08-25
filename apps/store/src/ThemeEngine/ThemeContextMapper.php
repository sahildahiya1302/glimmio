<?php
declare(strict_types=1);

class ThemeContextMapper {
    private $apiBaseUrl;
    
    public function __construct(string $apiBaseUrl = '/api') {
        $this->apiBaseUrl = $apiBaseUrl;
    }
    
    public function mapProductContext(array $product): array {
        return [
            'product' => [
                'id' => $product['id'] ?? '',
                'title' => $product['title'] ?? '',
                'handle' => $product['handle'] ?? '',
                'description' => $product['description'] ?? '',
                'price' => $this->formatPrice($product['price'] ?? 0),
                'price_raw' => $product['price'] ?? 0,
                'compare_at_price' => $this->formatPrice($product['compare_at_price'] ?? 0),
                'images' => $product['images'] ?? [],
                'featured_image' => $product['images'][0] ?? '',
                'variants' => $product['variants'] ?? [],
                'options' => $product['options'] ?? [],
                'tags' => $product['tags'] ?? [],
                'vendor' => $product['vendor'] ?? '',
                'type' => $product['type'] ?? '',
                'available' => $product['available'] ?? true,
                'inventory_quantity' => $product['inventory_quantity'] ?? 0,
                'url' => '/product/' . ($product['handle'] ?? ''),
                'add_to_cart_url' => $this->apiBaseUrl . '/cart/add.php',
                'reviews' => $this->mapReviews($product['reviews'] ?? [])
            ]
        ];
    }
    
    public function mapCartContext(array $cart): array {
        return [
            'cart' => [
                'item_count' => count($cart['items'] ?? []),
                'total_price' => $this->formatPrice($cart['total_price'] ?? 0),
                'total_price_raw' => $cart['total_price'] ?? 0,
                'items' => array_map([$this, 'mapCartItem'], $cart['items'] ?? []),
                'checkout_url' => $this->apiBaseUrl . '/order/submit.php',
                'update_url' => $this->apiBaseUrl . '/cart/update.php',
                'clear_url' => $this->apiBaseUrl . '/cart/clear.php'
            ]
        ];
    }
    
    public function mapCollectionContext(array $collection): array {
        return [
            'collection' => [
                'id' => $collection['id'] ?? '',
                'title' => $collection['title'] ?? '',
                'handle' => $collection['handle'] ?? '',
                'description' => $collection['description'] ?? '',
                'image' => $collection['image'] ?? '',
                'products' => array_map([$this, 'mapProductSummary'], $collection['products'] ?? []),
                'url' => '/collection/' . ($collection['handle'] ?? '')
            ]
        ];
    }
    
    public function mapGlobalContext(): array {
        return [
            'shop' => [
                'name' => getStoreName(),
                'domain' => $_SERVER['HTTP_HOST'],
                'url' => 'https://' . $_SERVER['HTTP_HOST'],
                'currency' => getStoreCurrency(),
                'money_format' => getStoreCurrencySymbol() . '{{amount}}',
                'money_with_currency_format' => getStoreCurrencySymbol() . '{{amount}} ' . getStoreCurrency()
            ],
            'settings' => $this->getThemeSettings(),
            'request' => [
                'host' => $_SERVER['HTTP_HOST'],
                'path' => $_SERVER['REQUEST_URI'],
                'query_string' => $_SERVER['QUERY_STRING'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD']
            ]
        ];
    }
    
    private function mapProductSummary(array $product): array {
        return [
            'id' => $product['id'] ?? '',
            'title' => $product['title'] ?? '',
            'handle' => $product['handle'] ?? '',
            'price' => $this->formatPrice($product['price'] ?? 0),
            'price_raw' => $product['price'] ?? 0,
            'image' => $product['images'][0] ?? '',
            'url' => '/product/' . ($product['handle'] ?? '')
        ];
    }
    
    private function mapCartItem(array $item): array {
        return [
            'id' => $item['id'] ?? '',
            'product_id' => $item['product_id'] ?? '',
            'variant_id' => $item['variant_id'] ?? '',
            'title' => $item['title'] ?? '',
            'price' => $this->formatPrice($item['price'] ?? 0),
            'price_raw' => $item['price'] ?? 0,
            'line_price' => $this->formatPrice(($item['price'] ?? 0) * ($item['quantity'] ?? 1)),
            'line_price_raw' => ($item['price'] ?? 0) * ($item['quantity'] ?? 1),
            'quantity' => $item['quantity'] ?? 1,
            'image' => $item['image'] ?? '',
            'url' => '/product/' . ($item['handle'] ?? ''),
            'remove_url' => $this->apiBaseUrl . '/cart/remove.php?id=' . ($item['id'] ?? '')
        ];
    }
    
    private function mapReviews(array $reviews): array {
        return array_map(function($review) {
            return [
                'id' => $review['id'] ?? '',
                'author' => $review['author'] ?? '',
                'rating' => $review['rating'] ?? 0,
                'title' => $review['title'] ?? '',
                'content' => $review['content'] ?? '',
                'created_at' => $review['created_at'] ?? '',
                'verified' => $review['verified'] ?? false
            ];
        }, $reviews);
    }
    
    private function formatPrice(float $price): string {
        return getStoreCurrencySymbol() . number_format($price, 2);
    }
    
    private function getThemeSettings(): array {
        // Load global theme settings
        return [
            'colors' => [
                'primary' => getThemeSetting('primary_color', '#007bff'),
                'secondary' => getThemeSetting('secondary_color', '#6c757d'),
                'success' => getThemeSetting('success_color', '#28a745'),
                'danger' => getThemeSetting('danger_color', '#dc3545')
            ],
            'typography' => [
                'font_family' => getThemeSetting('font_family', 'Arial, sans-serif'),
                'font_size' => getThemeSetting('font_size', '16px')
            ],
            'layout' => [
                'container_width' => getThemeSetting('container_width', '1200px'),
                'sidebar_position' => getThemeSetting('sidebar_position', 'right')
            ]
        ];
    }
}
