<?php
$id = $id ?? 'promo-grid-' . uniqid();
$heading = $settings['heading'] ?? 'Featured Promotions';
$subheading = $settings['subheading'] ?? '';
$overlayColor = $settings['overlay_color'] ?? 'rgba(0,0,0,0.4)';
$columns = $settings['columns'] ?? 3;
$promos = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'promo') {
            $promos[] = $block['settings'];
        }
    }
}
if (empty($promos)) {
    $promos = $settings['promos'] ?? [];
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
  text-align: center;
}

#<?= $id ?> h2 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

#<?= $id ?> .subheading {
  color: #666;
  margin-bottom: 2rem;
}

#<?= $id ?> .promo-grid {
  display: grid;
  grid-template-columns: repeat(var(--cols, 3), 1fr);
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
}

#<?= $id ?> .promo-tile {
  position: relative;
  overflow: hidden;
  border-radius: 10px;
  text-decoration: none;
  color: white;
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-size: cover;
  background-position: center;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
}

#<?= $id ?> .promo-tile:hover {
  transform: scale(1.02);
}

#<?= $id ?> .promo-overlay {
  position: absolute;
  inset: 0;
  background: <?= escape_html($overlayColor) ?>;
}

#<?= $id ?> .promo-content {
  position: relative;
  z-index: 2;
  padding: 1rem;
}

#<?= $id ?> .promo-content h3 {
  font-size: 1.4rem;
  margin-bottom: 0.5rem;
}

#<?= $id ?> .promo-content p {
  font-size: 0.95rem;
  margin: 0;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <?php if ($subheading): ?>
    <p class="subheading"><?= escape_html($subheading) ?></p>
  <?php endif; ?>

  <div class="promo-grid" style="--cols: <?= (int)$columns ?>;">
    <?php foreach ($promos as $promo): ?>
      <a href="<?= escape_html($promo['link'] ?? '#') ?>" class="promo-tile" style="background-image: url('<?= escape_html($promo['image']) ?>');">
        <div class="promo-overlay"></div>
        <div class="promo-content">
          <h3><?= escape_html($promo['title']) ?></h3>
          <p><?= escape_html($promo['subtext']) ?></p>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
