<?php
$settings = $snippet['settings'] ?? [];

$price       = isset($settings['price']) ? (float)$settings['price'] : 0;
$salePrice   = isset($settings['sale_price']) && $settings['sale_price'] !== '' ? (float)$settings['sale_price'] : null;
$currency    = $settings['currency_symbol'] ?? storeCurrency();
$format      = $settings['format'] ?? 'prefix';
$cssClass    = $settings['class'] ?? '';

$formatted = function($value) use ($currency, $format) {
    $formatted = number_format($value, 2);
    return $format === 'prefix' ? e($currency) . $formatted : $formatted . e($currency);
};
?>
<span class="price <?= e($cssClass) ?>">
<?php if ($salePrice !== null): ?>
    <span class="original" style="text-decoration:line-through;">
        <?= $formatted($price) ?>
    </span>
    <span class="sale"><?= $formatted($salePrice) ?></span>
<?php else: ?>
    <?= $formatted($price) ?>
<?php endif; ?>
</span>
