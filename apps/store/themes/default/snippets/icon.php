<?php
$settings = $snippet['settings'] ?? [];

$name       = $settings['icon_name'] ?? 'star';
$color      = $settings['icon_color'] ?? '#000000';
$size       = $settings['icon_size'] ?? '24';
$hoverColor = $settings['hover_color'] ?? '';
$label      = $settings['label'] ?? '';
$styleAttr  = "color: " . e($color) . "; font-size: " . intval($size) . "px;";
if ($hoverColor) {
    $styleAttr .= " --hover-color: " . e($hoverColor) . ";";
}
?>
<span class="icon" style="<?= $styleAttr ?>" <?php if($label): ?>aria-label="<?= e($label) ?>"<?php endif; ?>>
    <i class="icon-<?= e($name) ?>"></i>
</span>
