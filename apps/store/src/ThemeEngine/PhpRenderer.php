<?php
declare(strict_types=1);

class PhpRenderer {
    private $themePath;
    
    public function __construct(string $themePath) {
        $this->themePath = $themePath;
    }
    
    public function render(string $templatePath, array $context = []): string {
        // Extract context variables for use in template
        extract($context);
        
        // Start output buffering
        ob_start();
        
        try {
            // Include the PHP template
            if (file_exists($templatePath)) {
                include $templatePath;
            } else {
                throw new Exception("Template not found: {$templatePath}");
            }
            
            // Get the output
            $output = ob_get_clean();
            return $output;
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }
    
    public function renderSection(string $sectionName, array $context = []): string {
        $sectionPath = $this->themePath . "/sections/{$sectionName}.php";
        
        if (!file_exists($sectionPath)) {
            return '';
        }
        
        return $this->render($sectionPath, $context);
    }
    
    public function renderSnippet(string $snippetName, array $context = []): string {
        $snippetPath = $this->themePath . "/snippets/{$snippetName}.php";
        
        if (!file_exists($snippetPath)) {
            return '';
        }
        
        return $this->render($snippetPath, $context);
    }
}

// Helper functions for PHP templates
function wc_price($price) {
    return getStoreCurrencySymbol() . number_format((float)$price, 2);
}

function wc_get_product($product_id) {
    // Get product by ID
    return getProductById($product_id);
}

function wc_get_products($args = []) {
    // Get products based on arguments
    return getProducts($args);
}

function wc_get_cart() {
    // Get current cart
    return getCart();
}

function wc_get_cart_item_count() {
    // Get cart item count
    return getCartItemCount();
}

function wc_get_cart_total() {
    // Get cart total
    return getCartTotal();
}

function wc_get_product_categories($product_id) {
    // Get product categories
    return getProductCategories($product_id);
}

function wc_get_product_tags($product_id) {
    // Get product tags
    return getProductTags($product_id);
}

function wc_get_product_reviews($product_id) {
    // Get product reviews
    return getProductReviews($product_id);
}

function wc_get_related_products($product_id, $limit = 4) {
    // Get related products
    return getRelatedProducts($product_id, $limit);
}

function wc_get_upsell_products($product_id, $limit = 4) {
    // Get upsell products
    return getUpsellProducts($product_id, $limit);
}

function wc_get_cross_sell_products($product_id, $limit = 4) {
    // Get cross-sell products
    return getCrossSellProducts($product_id, $limit);
}

function wc_get_product_image($product, $size = 'medium') {
    // Get product image URL
    return getProductImage($product, $size);
}

function wc_get_product_gallery($product) {
    // Get product gallery images
    return getProductGallery($product);
}

function wc_get_product_variations($product) {
    // Get product variations
    return getProductVariations($product);
}

function wc_get_product_attributes($product) {
    // Get product attributes
    return getProductAttributes($product);
}

function wc_get_product_stock_status($product) {
    // Get product stock status
    return getProductStockStatus($product);
}

function wc_get_product_availability($product) {
    // Get product availability
    return getProductAvailability($product);
}

function wc_get_product_rating($product) {
    // Get product rating
    return getProductRating($product);
}

function wc_get_product_rating_count($product) {
    // Get product rating count
    return getProductRatingCount($product);
}

function wc_get_product_review_count($product) {
    // Get product review count
    return getProductReviewCount($product);
}

function wc_get_shop_url() {
    // Get shop URL
    return getShopUrl();
}

function wc_get_shop_name() {
    // Get shop name
    return getShopName();
}

function wc_get_shop_description() {
    // Get shop description
    return getShopDescription();
}

function wc_get_shop_logo() {
    // Get shop logo
    return getShopLogo();
}

function wc_get_shop_currency() {
    // Get shop currency
    return getShopCurrency();
}

function wc_get_shop_currency_symbol() {
    // Get shop currency symbol
    return getShopCurrencySymbol();
}

function wc_get_shop_countries() {
    // Get shop countries
    return getShopCountries();
}

function wc_get_shop_states() {
    // Get shop states
    return getShopStates();
}

function wc_get_shop_payment_methods() {
    // Get shop payment methods
    return getShopPaymentMethods();
}

function wc_get_shop_shipping_methods() {
    // Get shop shipping methods
    return getShopShippingMethods();
}

function wc_get_shop_tax_rates() {
    // Get shop tax rates
    return getShopTaxRates();
}

function wc_get_shop_coupons() {
    // Get shop coupons
    return getShopCoupons();
}

function wc_get_shop_discounts() {
    // Get shop discounts
    return getShopDiscounts();
}

function wc_get_shop_sales() {
    // Get shop sales
    return getShopSales();
}

function wc_get_shop_featured_products($limit = 4) {
    // Get shop featured products
    return getShopFeaturedProducts($limit);
}

function wc_get_shop_new_products($limit = 4) {
    // Get shop new products
    return getShopNewProducts($limit);
}

function wc_get_shop_best_selling_products($limit = 4) {
    // Get shop best selling products
    return getShopBestSellingProducts($limit);
}

function wc_get_shop_on_sale_products($limit = 4) {
    // Get shop on sale products
    return getShopOnSaleProducts($limit);
}

function wc_get_shop_categories() {
    // Get shop categories
    return getShopCategories();
}

function wc_get_shop_tags() {
    // Get shop tags
    return getShopTags();
}

function wc_get_shop_brands() {
    // Get shop brands
    return getShopBrands();
}

function wc_get_shop_vendors() {
    // Get shop vendors
    return getShopVendors();
}

function wc_get_shop_collections() {
    // Get shop collections
    return getShopCollections();
}

function wc_get_shop_pages() {
    // Get shop pages
    return getShopPages();
}

function wc_get_shop_blogs() {
    // Get shop blogs
    return getShopBlogs();
}

function wc_get_shop_posts() {
    // Get shop posts
    return getShopPosts();
}

function wc_get_shop_comments() {
    // Get shop comments
    return getShopComments();
}

function wc_get_shop_reviews() {
    // Get shop reviews
    return getShopReviews();
}

function wc_get_shop_ratings() {
    // Get shop ratings
    return getShopRatings();
}

function wc_get_shop_search_results($query) {
    // Get shop search results
    return getShopSearchResults($query);
}

function wc_get_shop_search_suggestions($query) {
    // Get shop search suggestions
    return getShopSearchSuggestions($query);
}

function wc_get_shop_search_products($query) {
    // Get shop search products
    return getShopSearchProducts($query);
}

function wc_get_shop_search_categories($query) {
    // Get shop search categories
    return getShopSearchCategories($query);
}

function wc_get_shop_search_tags($query) {
    // Get shop search tags
    return getShopSearchTags($query);
}

function wc_get_shop_search_brands($query) {
    // Get shop search brands
    return getShopSearchBrands($query);
}

function wc_get_shop_search_vendors($query) {
    // Get shop search vendors
    return getShopSearchVendors($query);
}

function wc_get_shop_search_collections($query) {
    // Get shop search collections
    return getShopSearchCollections($query);
}

function wc_get_shop_search_pages($query) {
    // Get shop search pages
    return getShopSearchPages($query);
}

function wc_get_shop_search_blogs($query) {
    // Get shop search blogs
    return getShopSearchBlogs($query);
}

function wc_get_shop_search_posts($query) {
    // Get shop search posts
    return getShopSearchPosts($query);
}

function wc_get_shop_search_comments($query) {
    // Get shop search comments
    return getShopSearchComments($query);
}

function wc_get_shop_search_reviews($query) {
    // Get shop search reviews
    return getShopSearchReviews($query);
}

function wc_get_shop_search_ratings($query) {
    // Get shop search ratings
    return getShopSearchRatings($query);
}
