<?php
$id            = $id ?? 'spacer-' . uniqid();
$heightDesktop = intval($settings['height_desktop'] ?? ($settings['height'] ?? 40));
$heightTablet  = intval($settings['height_tablet'] ?? $heightDesktop);
$heightMobile  = intval($settings['height_mobile'] ?? $heightTablet);
$bgColor       = $settings['background_color'] ?? 'transparent';
?>

<style>
#<?= e($id) ?> {
  height: <?= e($heightDesktop) ?>px;
  background-color: <?= e($bgColor) ?>;
}
@media (max-width: 991px) {
  #<?= e($id) ?> { height: <?= e($heightTablet) ?>px; }
}
@media (max-width: 575px) {
  #<?= e($id) ?> { height: <?= e($heightMobile) ?>px; }
}
</style>

<div id="<?= e($id) ?>"></div>
