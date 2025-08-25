<?php
$id = $id ?? 'trust-badges-' . uniqid();
$badges = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'badge') {
            $badges[] = $block['settings'];
        }
    }
}
if (empty($badges)) {
    $badges = $settings['badges'] ?? [];
}
$columns = intval($settings['columns'] ?? 4);
$background = $settings['background'] ?? '#f5f5f5';
$iconSize = intval($settings['icon_size'] ?? 50);
$rounded = intval($settings['border_radius'] ?? 0);
$grayscale = $settings['grayscale'] ?? false;

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 40px 20px;
  background: <?= escape_html($background) ?>;
  font-family: Arial, sans-serif;
}

#<?= $id ?> .badge-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 30px;
  max-width: 1000px;
  margin: auto;
  text-align: center;
}

#<?= $id ?> .badge-tile {
  display: flex;
  flex-direction: column;
  align-items: center;
}

#<?= $id ?> .badge-icon {
  width: <?= $iconSize ?>px;
  height: <?= $iconSize ?>px;
  object-fit: contain;
  margin-bottom: 10px;
  border-radius: <?= $rounded ?>px;
  <?php if ($grayscale): ?>filter: grayscale(100%);<?php endif; ?>
}

#<?= $id ?> .badge-label {
  font-weight: 600;
  font-size: 0.95rem;
  color: #333;
}
</style>

<section id="<?= $id ?>">
  <div class="badge-grid" style="grid-template-columns: repeat(<?= $columns ?>, 1fr);">
    <?php foreach ($badges as $badge): ?>
      <div class="badge-tile">
        <?php if (!empty($badge['icon'])): ?>
          <img src="<?= escape_html($badge['icon']) ?>" alt="<?= escape_html($badge['label']) ?>" class="badge-icon">
        <?php endif; ?>
        <div class="badge-label"><?= escape_html($badge['label']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
