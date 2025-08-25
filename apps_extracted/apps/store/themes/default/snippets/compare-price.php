<?php
$settings = $snippet['settings'] ?? [];

$original = isset($settings['original_price']) ? (float)$settings['original_price'] : 0;
$sale     = isset($settings['sale_price']) ? (float)$settings['sale_price'] : 0;
$currency = $settings['currency'] ?? '$';
$showPerc = !empty($settings['show_percentage']);

$percent = $original > 0 ? round(100 - ($sale / $original * 100)) : 0;
?>
<div class="compare-price">
  <span class="original-price" style="text-decoration:line-through;">
    <?= e($currency) ?><?= number_format($original, 2) ?>
  </span>
  <span class="sale-price">
    <?= e($currency) ?><?= number_format($sale, 2) ?>
  </span>
  <?php if ($showPerc && $percent > 0): ?>
    <span class="savings">(<?= $percent ?>% off)</span>
  <?php endif; ?>
</div>
