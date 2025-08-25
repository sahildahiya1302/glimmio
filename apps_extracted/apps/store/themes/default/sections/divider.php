<?php
$id         = $id ?? 'divider-' . uniqid();
$orientation = $settings['orientation'] ?? 'horizontal';
$size        = $settings['size'] ?? '1px';
$style       = $settings['style'] ?? 'solid';
$color       = $settings['color'] ?? '#e0e0e0';
$margin      = $settings['margin'] ?? '20px 0';
?>

<style>
#<?= e($id) ?> {
  <?php if ($orientation === 'vertical'): ?>
    width: <?= e($size) ?>;
    height: 100%;
    border-right: <?= e("{$size} {$style} {$color}") ?>;
  <?php else: ?>
    width: 100%;
    height: <?= e($size) ?>;
    border-bottom: <?= e("{$size} {$style} {$color}") ?>;
  <?php endif; ?>
  margin: <?= e($margin) ?>;
}
</style>

<div id="<?= e($id) ?>" aria-hidden="true"></div>
