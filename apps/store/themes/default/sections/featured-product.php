<?php
$id = $id ?? basename(__FILE__, '.php') . '-' . uniqid();
/**
 * featured-product.php
 * Highlight a specific product with add to cart button
 */

// Load schema for editor UI
$schema = [
    'name' => 'Featured Product',
    'settings' => [
        ['type' => 'product', 'id' => 'product', 'label' => 'Select Product'],
        ['type' => 'checkbox', 'id' => 'show_price', 'label' => 'Show Price', 'default' => true],
        ['type' => 'checkbox', 'id' => 'show_add_to_cart', 'label' => 'Show Add to Cart Button', 'default' => true],
    ],
];

// Extract settings with defaults
$productId = $settings['product'] ?? null;
if (!$productId && !empty($context['product']['id'])) {
    $productId = $context['product']['id'];
}
$showPrice = $settings['show_price'] ?? true;
$showAddToCart = $settings['show_add_to_cart'] ?? true;
$buttonBg = $settings['button_bg'] ?? '#007bff';
$buttonColor = $settings['button_color'] ?? '#fff';
$textAlign = $settings['text_align'] ?? 'center';

$product = null;
if ($productId) {
    $product = getProduct((int)$productId);
}
?>

<section id="<?php echo htmlspecialchars($id); ?>" class="featured-product" style="text-align: <?= htmlspecialchars($textAlign) ?>;">
    <?php if (!$product): ?>
        <p>No product selected or product not found.</p>
    <?php else: ?>
        <div class="product-card">
            <a href="/products/<?php echo e($product['handle']); ?>">
                <img src="<?php echo e($product['image'] ?? asset('images/placeholder.png')); ?>" alt="<?php echo e($product['title']); ?>" />
                <h3><?php echo e($product['title']); ?></h3>
            </a>
            <?php if ($showPrice): ?>
                <?php if ($showPrice): ?>
    <p class="price">
        <?php 
        $price = $product['price'] ?? 0;
        echo e(number_format((float)$price, 2)); 
        ?> INR
    </p>
<?php endif; ?>

            <?php endif; ?>
            <?php if ($showAddToCart): ?>
                <form method="POST" action="/cart/add">
                    <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>" />
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<style>
.featured-product .product-card {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
    max-width: 300px;
    margin: 0 auto;
}
.featured-product img {
    max-width: 100%;
    height: auto;
}
.featured-product .price {
    font-weight: bold;
    margin: 10px 0;
}
.btn-primary {
    background-color: <?= htmlspecialchars($buttonBg) ?>;
    color: <?= htmlspecialchars($buttonColor) ?>;
    padding: 8px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-primary:hover {
    opacity: 0.9;
}
</style>
