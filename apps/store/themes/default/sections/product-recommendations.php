<?php
$id = $id ?? 'product-recommendations-' . uniqid();

$heading = $settings['heading'] ?? 'Recommended Products';
$maxProducts = isset($settings['max_products']) ? intval($settings['max_products']) : 4;
$showAddToCart = $settings['show_add_to_cart'] ?? false;
$columns = $settings['columns'] ?? 4;
$recommendations = $recommendations ?? [];
if (empty($recommendations) && !empty($context['product']['id'])) {
  $pid = $context['product']['id'];
  $collectionId = getFirstCollectionIdForProduct($pid);
  if ($collectionId) {
    $recommendations = getRelatedProductsByCollection($collectionId, $pid, $maxProducts);
  }
  if (empty($recommendations)) {
    $recommendations = getFallbackRecommendedProducts($maxProducts);
  }
}

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 60px 20px;
  font-family: Arial, sans-serif;
  background-color: #fff;
}

#<?= $id ?> h2 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 2rem;
}

#<?= $id ?> .product-grid {
  display: grid;
  gap: 30px;
  grid-template-columns: repeat(var(--cols, 4), 1fr);
  max-width: 1200px;
  margin: 0 auto;
}

#<?= $id ?> .product-card {
  background: #fafafa;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  overflow: hidden;
  text-align: center;
  transition: transform 0.3s;
}

#<?= $id ?> .product-card:hover {
  transform: translateY(-5px);
}

#<?= $id ?> .product-card img {
  width: 100%;
  height: 250px;
  object-fit: cover;
}

#<?= $id ?> .product-info {
  padding: 1rem;
}

#<?= $id ?> .product-info h3 {
  font-size: 1.1rem;
  margin: 0.5rem 0;
}

#<?= $id ?> .product-price {
  font-weight: bold;
  font-size: 1rem;
  color: #000;
}

#<?= $id ?> .product-compare-price {
  color: #888;
  font-size: 0.9rem;
  text-decoration: line-through;
  margin-left: 8px;
}
#<?= $id ?> .btn-add {
  margin-top: 8px;
  padding: 8px 16px;
  background: #000;
  color: #fff;
  border: none;
  border-radius: 4px;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <div class="product-grid" style="--cols: <?= (int)$columns ?>;">
    <?php
    $count = 0;
    foreach ($recommendations as $product):
      if ($count++ >= $maxProducts) break;

      $title = escape_html($product['title'] ?? 'Untitled Product');
      $image = escape_html($product['image'] ?? '/assets/default-product.png');
      $price = isset($product['price']) ? (float)$product['price'] : 0.00;
      $compare_price = isset($product['compare_price']) ? (float)$product['compare_price'] : null;
      $url = escape_html($product['url'] ?? '#');
    ?>
      <div class="product-card">
        <a href="<?= $url ?>">
          <img src="<?= $image ?>" alt="<?= $title ?>">
          <div class="product-info">
            <h3><?= $title ?></h3>
            <div class="product-price">
              ₹<?= number_format($price, 2) ?>
              <?php if (!is_null($compare_price) && $compare_price > $price): ?>
                <span class="product-compare-price">₹<?= number_format($compare_price, 2) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </a>
        <?php if ($showAddToCart): ?>
          <form method="POST" action="/cart/add" style="margin:10px;">
            <input type="hidden" name="product_id" value="<?= (int)($product['id'] ?? 0) ?>">
            <button type="submit" class="btn-add">Add to Cart</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>
