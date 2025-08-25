<?php
$id = $id ?? 'recently-viewed-' . uniqid();
$heading = $settings['heading'] ?? 'Recently Viewed';
$maxItems = intval($settings['max_items'] ?? 8);
$showPrices = $settings['show_prices'] ?? true;
$columns = $settings['columns'] ?? 4;
$headingColor = $settings['heading_color'] ?? '#000';

// Get product IDs from cookie
$recentIds = isset($_COOKIE['recently_viewed']) ? json_decode($_COOKIE['recently_viewed'], true) : [];
$recentIds = is_array($recentIds) ? array_slice(array_reverse($recentIds), 0, $maxItems) : [];

$recentProducts = [];

if (!empty($recentIds)) {
  $placeholders = implode(',', array_fill(0, count($recentIds), '?'));
  $sql = "SELECT * FROM products WHERE id IN ($placeholders)";
  $results = db_query($sql, $recentIds);

  // preserve order
  $map = [];
  foreach ($results as $prod) $map[$prod['id']] = $prod;
  foreach ($recentIds as $id) if (isset($map[$id])) $recentProducts[] = $map[$id];
}

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 50px 20px;
  font-family: Arial, sans-serif;
  text-align: center;
}

#<?= $id ?> h2 {
  font-size: 2rem;
  margin-bottom: 2rem;
  color: <?= escape_html($headingColor) ?>;
}

#<?= $id ?> .product-grid {
  display: grid;
  grid-template-columns: repeat(var(--cols, 4), 1fr);
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

#<?= $id ?> .product-card {
  text-decoration: none;
  color: inherit;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  transition: transform 0.2s;
}

#<?= $id ?> .product-card:hover {
  transform: translateY(-4px);
}

#<?= $id ?> .product-card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
}

#<?= $id ?> .product-info {
  padding: 1rem;
  text-align: left;
}

#<?= $id ?> .product-title {
  font-weight: bold;
  font-size: 1rem;
  margin-bottom: 5px;
}

#<?= $id ?> .product-price {
  font-size: 0.95rem;
}
</style>

<?php if (!empty($recentProducts)): ?>
  <section id="<?= $id ?>">
    <h2><?= escape_html($heading) ?></h2>
    <div class="product-grid" style="--cols: <?= (int)$columns ?>;">
      <?php foreach ($recentProducts as $product): ?>
        <a href="/products/<?= escape_html($product['handle']) ?>" class="product-card">
          <img src="<?= escape_html($product['image']) ?>" alt="<?= escape_html($product['title']) ?>">
          <div class="product-info">
            <div class="product-title"><?= escape_html($product['title']) ?></div>
            <?php if ($showPrices): ?>
              <div class="product-price">
                ₹<?= number_format($product['price'], 2) ?>
                <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                  <del>₹<?= number_format($product['compare_price'], 2) ?></del>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>
