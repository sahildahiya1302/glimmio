<?php
$id = $id ?? 'step-guide-' . uniqid();
$heading = $settings['heading'] ?? 'How It Works';
$subheading = $settings['subheading'] ?? '';
$steps = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'step') {
            $steps[] = $block['settings'];
        }
    }
}
if (empty($steps)) {
    $steps = $settings['steps'] ?? [];
}
$columns = intval($settings['columns'] ?? 3);
$accent = $settings['accent_color'] ?? '#333';
$bgColor = $settings['tile_background'] ?? '#fff';
$iconSize = intval($settings['icon_size'] ?? 60);

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

#<?= $id ?> .step-grid {
  display: grid;
  grid-template-columns: repeat(<?= $columns ?>, 1fr);
  gap: 40px;
  max-width: 1100px;
  margin: 0 auto;
  position: relative;
}

#<?= $id ?> .step-tile {
  background: <?= escape_html($bgColor) ?>;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  padding: 30px 20px;
  position: relative;
  z-index: 1;
}

#<?= $id ?> .step-icon img {
  width: <?= $iconSize ?>px;
  height: <?= $iconSize ?>px;
  object-fit: contain;
  margin-bottom: 20px;
}

#<?= $id ?> .step-number {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 10px;
  color: <?= escape_html($accent) ?>;
}

#<?= $id ?> .step-title {
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 8px;
}

#<?= $id ?> .step-description {
  font-size: 0.95rem;
  color: #555;
  line-height: 1.5;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <?php if ($subheading): ?>
    <p class="subheading"><?= escape_html($subheading) ?></p>
  <?php endif; ?>

  <div class="step-grid">
    <?php foreach ($steps as $index => $step): ?>
      <div class="step-tile">
        <?php if (!empty($step['icon'])): ?>
          <div class="step-icon">
            <img src="<?= escape_html($step['icon']) ?>" alt="Step Icon">
          </div>
        <?php endif; ?>
        <div class="step-number">Step <?= $index + 1 ?></div>
        <div class="step-title"><?= escape_html($step['title']) ?></div>
        <div class="step-description"><?= escape_html($step['description']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
