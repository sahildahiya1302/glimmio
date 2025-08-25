<?php
if (!isset($product)) return;

// Load global settings
$settings = json_decode(file_get_contents(__DIR__ . '/../config/settings_data.json'), true);

// Load schema for editor UI
$schema = [
    'name' => 'Product Card',
    'settings' => [
        ['type' => 'text', 'id' => 'product_slug', 'label' => 'Product Slug', 'default' => ''],
        ['type' => 'image_picker', 'id' => 'product_image', 'label' => 'Product Image'],
        ['type' => 'text', 'id' => 'product_name', 'label' => 'Product Name', 'default' => 'Product Name'],
        ['type' => 'number', 'id' => 'product_price', 'label' => 'Product Price', 'default' => 0],
    ],
];

// Extract settings or fallback to product data
$productSlug = $settings['product_slug'] ?? ($product['slug'] ?? $product['id']);
$productImage = $settings['product_image'] ?? ($product['image'] ?? '');
$productName = $settings['product_name'] ?? ($product['name'] ?? '');
$productPrice = $settings['product_price'] ?? ($product['price'] ?? 0);

?>
<div class="product-card">
    <a href="/products/<?php echo htmlspecialchars($productSlug); ?>">
        <?php if (!empty($productImage)): ?>
            <img src="/uploads/products/<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($productName); ?>" loading="lazy" />
        <?php endif; ?>
        <h3><?php echo htmlspecialchars($productName); ?></h3>
        <?php 
        $price = $productPrice; 
        include __DIR__ . '/price.php'; 
        ?>
    </a>
    <button class="wishlist-btn" data-product="<?php echo (int)$product['id']; ?>" aria-label="Add to wishlist">❤</button>
    <button class="compare-btn" data-product="<?php echo (int)$product['id']; ?>" aria-label="Add to compare">⇄</button>
</div>
