<?php
$id = $id ?? 'product-features-' . uniqid();
$heading = $settings['heading'] ?? 'Product Features';
$subheading = $settings['subheading'] ?? '';
$features = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'feature') {
            $features[] = $block['settings'];
        }
    }
}
if (empty($features)) {
    $features = $settings['features'] ?? [];
}
$textAlign = $settings['text_align'] ?? 'center';
$columns = intval($settings['columns'] ?? 4);
$bgColor = $settings['tile_background'] ?? '#fff';
$iconSize = intval($settings['icon_size'] ?? 50);
$hoverShadow = ($settings['hover_shadow'] ?? true) ? 'translateY(-5px)' : 'none';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 60px 20px;
  text-align: <?= escape_html($textAlign) ?>;
  font-family: Arial, sans-serif;
}

#<?= $id ?> h2 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

#<?= $id ?> .subheading {
  color: #666;
  margin-bottom: 2rem;
}

#<?= $id ?> .feature-grid {
  display: grid;
  grid-template-columns: repeat(<?= $columns ?>, 1fr);
  gap: 30px;
  max-width: 1100px;
  margin: 0 auto;
}

#<?= $id ?> .feature-tile {
  background: <?= escape_html($bgColor) ?>;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  padding: 30px 20px;
  transition: transform 0.3s ease;
  text-align: center;
}

#<?= $id ?> .feature-tile:hover {
  transform: <?= $hoverShadow ?>;
}

#<?= $id ?> .feature-icon img {
  width: <?= $iconSize ?>px;
  height: <?= $iconSize ?>px;
  object-fit: contain;
  margin-bottom: 15px;
}

#<?= $id ?> .feature-title {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 10px;
}

#<?= $id ?> .feature-description {
  font-size: 0.95rem;
  color: #555;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <?php if ($subheading): ?>
    <p class="subheading"><?= escape_html($subheading) ?></p>
  <?php endif; ?>

  <div class="feature-grid">
    <?php foreach ($features as $feature): ?>
      <div class="feature-tile">
        <?php if (!empty($feature['icon'])): ?>
          <div class="feature-icon">
            <img src="<?= escape_html($feature['icon']) ?>" alt="<?= escape_html($feature['title']) ?>">
          </div>
        <?php endif; ?>
        <div class="feature-title"><?= escape_html($feature['title']) ?></div>
        <div class="feature-description"><?= escape_html($feature['description']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php if (!empty($context['product']['id'])): ?>
<script>
(function() {
  const productId = <?= json_encode($context['product']['id']) ?>;
  if (!productId) return;

  let viewed = JSON.parse(localStorage.getItem('recently_viewed') || '[]');

  // Remove existing if already viewed
  viewed = viewed.filter(id => id !== productId);
  viewed.push(productId);

  // Keep only last 20
  viewed = viewed.slice(-20);

  // Save in localStorage and cookie
  localStorage.setItem('recently_viewed', JSON.stringify(viewed));
  document.cookie = "recently_viewed=" + JSON.stringify(viewed) + "; path=/; max-age=2592000";
})();
</script>
<?php endif; ?>
