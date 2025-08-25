<?php
$settings = $snippet['settings'] ?? [];

$label  = $settings['label'] ?? 'Click Me';
$url    = $settings['url'] ?? '#';
$style  = $settings['style'] ?? 'primary';
$size   = $settings['size'] ?? 'md';
$icon   = $settings['icon'] ?? '';
$target = !empty($settings['new_tab']) ? '_blank' : '_self';
?>
<a href="<?= e($url) ?>" class="btn btn-<?= e($style) ?> btn-<?= e($size) ?>" target="<?= e($target) ?>">
    <?php if ($icon): ?><i class="icon-<?= e($icon) ?>" aria-hidden="true"></i> <?php endif; ?>
    <?= e($label) ?>
</a>
