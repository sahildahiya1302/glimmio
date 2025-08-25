<?php
$id = $id ?? 'sticky-cart-bar-' . uniqid();
$product = $context['product'] ?? null;
if (!$product || !($settings['enabled'] ?? true)) return;

$title = $product['title'];
$price = number_format($product['price'], 2);
$image = $product['image'];
$productId = $product['id'];

$buttonLabel = $settings['button_label'] ?? 'Add to Cart';
$bgColor = $settings['background_color'] ?? '#ffffff';
$btnBg = $settings['button_color'] ?? '#000000';
$btnText = $settings['button_text_color'] ?? '#ffffff';
$showImage = $settings['show_product_image'] ?? true;
$showOnMobile = $settings['show_on_mobile'] ?? true;
$position = $settings['position'] ?? 'bottom';
$barHeight = intval($settings['bar_height'] ?? 60);
$hideUntilScroll = $settings['hide_until_scroll'] ?? false;

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  position: fixed;
  <?php echo $position === 'top' ? 'top: 0;' : 'bottom: 0;'; ?>
  left: 0;
  right: 0;
  background: <?= escape_html($bgColor) ?>;
  border-top: 1px solid #ddd;
  box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  height: <?= $barHeight ?>px;
  z-index: 9999;
  font-family: Arial, sans-serif;
  transition: transform 0.3s ease;
  <?php if ($hideUntilScroll): ?>transform: translateY(100%);<?php endif; ?>
}

#<?= $id ?> .product-thumb {
  display: flex;
  align-items: center;
  gap: 10px;
}

#<?= $id ?> .product-thumb img {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 6px;
}

#<?= $id ?> .product-info {
  display: flex;
  flex-direction: column;
}

#<?= $id ?> .product-title {
  font-weight: bold;
  font-size: 0.95rem;
}

#<?= $id ?> .product-price {
  color: #222;
  font-size: 0.9rem;
}

#<?= $id ?> .add-to-cart-btn {
  background-color: <?= escape_html($btnBg) ?>;
  color: <?= escape_html($btnText) ?>;
  padding: 10px 18px;
  font-weight: bold;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  transition: background 0.3s ease;
}

#<?= $id ?> .add-to-cart-btn:hover {
  opacity: 0.9;
}

@media (max-width: 480px) {
  #<?= $id ?> {
    <?= $showOnMobile ? '' : 'display: none !important;' ?>
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  #<?= $id ?> .product-thumb {
    width: 100%;
    gap: 12px;
  }
  #<?= $id ?> .add-to-cart-btn {
    width: 100%;
  }
}
</style>

<div id="<?= $id ?>">
  <div class="product-thumb">
    <?php if ($showImage): ?>
      <img src="<?= escape_html($image) ?>" alt="<?= escape_html($title) ?>">
    <?php endif; ?>
    <div class="product-info">
      <div class="product-title"><?= escape_html($title) ?></div>
      <div class="product-price">₹<?= $price ?></div>
    </div>
  </div>
  <button class="add-to-cart-btn" onclick="addToCartSticky<?= $productId ?>()"><?= escape_html($buttonLabel) ?></button>
</div>

<script>
function addToCartSticky<?= $productId ?>() {
  const productId = <?= json_encode($productId) ?>;
  fetch('/api/cart.php?action=add', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: productId, quantity: 1 })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Added to cart!');
    } else {
      alert(data.message || 'Could not add to cart.');
    }
  })
  .catch(() => alert('Something went wrong.'));
}
<?php if ($hideUntilScroll): ?>
document.addEventListener('scroll', function(){
  const bar=document.getElementById('<?= $id ?>');
  if(window.scrollY>300){ bar.style.transform='translateY(0)'; }
  else{ bar.style.transform='translateY(100%)'; }
});
<?php endif; ?>
</script>
