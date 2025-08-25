<?php
$id = $id ?? 'search-results-' . uniqid();
$query = trim($_GET['q'] ?? '');
$products = [];
$columnsDesktop = intval($settings['columns_desktop'] ?? 4);
$columnsMobile = intval($settings['columns_mobile'] ?? 2);
$showAddToCart = $settings['show_add_to_cart'] ?? false;
$notFoundText = $settings['not_found_text'] ?? 'No results found.';

if ($query !== '') {
  $sql = "SELECT * FROM products WHERE title LIKE ? OR description LIKE ? LIMIT 30";
  $products = db_query($sql, ["%$query%", "%$query%"]);
}

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 60px 20px;
  font-family: Arial, sans-serif;
}

#<?= $id ?> h2 {
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
}

#<?= $id ?> .results-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(<?php echo $columnsDesktop; ?>, 1fr);
}
@media (max-width: 768px) {
  #<?= $id ?> .results-grid {
    grid-template-columns: repeat(<?php echo $columnsMobile; ?>, 1fr);
  }
}

#<?= $id ?> .product-card {
  text-decoration: none;
  color: inherit;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  overflow: hidden;
  transition: transform 0.2s ease;
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
  padding: 12px;
}

#<?= $id ?> .product-title {
  font-size: 1rem;
  font-weight: bold;
  margin-bottom: 4px;
}

#<?= $id ?> .product-price {
  font-size: 0.95rem;
  color: #333;
}
</style>

<section id="<?= $id ?>">
  <h2>
    <?php if ($query): ?>
      Search Results for “<?= escape_html($query) ?>”
    <?php else: ?>
      Start typing to search
    <?php endif; ?>
  </h2>

  <?php if ($query && empty($products)): ?>
    <p><?= escape_html($notFoundText) ?></p>
  <?php elseif (!empty($products)): ?>
    <div class="results-grid">
      <?php foreach ($products as $product): ?>
        <a href="/products/<?= escape_html($product['handle']) ?>" class="product-card">
          <img src="<?= escape_html($product['image']) ?>" alt="<?= escape_html($product['title']) ?>">
          <div class="product-info">
            <div class="product-title"><?= escape_html($product['title']) ?></div>
            <div class="product-price">₹<?= number_format($product['price'], 2) ?></div>
          <?php if ($showAddToCart): ?>
            <button class="btn btn-sm" onclick="addToCart(<?= (int)$product['id'] ?>);return false;">Add to Cart</button>
          <?php endif; ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php if ($showAddToCart): ?>
<script>
function addToCart(id){
  fetch('/api/cart.php?action=add', {
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({product_id:id, quantity:1})
  }).then(r=>r.json()).then(d=>alert(d.success?'Added':'Error'));
}
</script>
<?php endif; ?>
