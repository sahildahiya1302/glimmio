<?php
$id = $id ?? 'icon-list-' . uniqid();
$heading = $settings['heading'] ?? 'Why Shop With Us';
$icons = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'icon') {
            $icons[] = $block['settings'];
        }
    }
}
if (empty($icons)) {
    $icons = $settings['icons'] ?? [];
}
$columns = intval($settings['columns'] ?? 4);
$iconSize = $settings['icon_size'] ?? '50px';
$textColor = $settings['text_color'] ?? '#333';
$textAlign = $settings['text_align'] ?? 'center';
?>

<style>
#<?= $id ?> {
  padding: 50px 20px;
  font-family: Arial, sans-serif;
  text-align: <?= escape_html($textAlign) ?>;
}

#<?= $id ?> .icon-grid {
  display: grid;
  grid-template-columns: repeat(<?= $columns ?>, 1fr);
  gap: 30px;
  margin-top: 30px;
}

#<?= $id ?> .icon-item img {
  height: <?= escape_html($iconSize) ?>;
  margin-bottom: 10px;
}

#<?= $id ?> .icon-item .label {
  font-size: 1rem;
  font-weight: 600;
  color: <?= escape_html($textColor) ?>;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <div class="icon-grid">
    <?php foreach ($icons as $icon): ?>
      <div class="icon-item">
        <img src="<?= escape_html($icon['image']) ?>" alt="<?= escape_html($icon['label']) ?>">
        <div class="label"><?= escape_html($icon['label']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
