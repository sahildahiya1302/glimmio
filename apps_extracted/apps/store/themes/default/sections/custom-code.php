<?php
$id         = $id ?? 'custom-code-' . uniqid();
$html       = $settings['custom_html'] ?? '';
$css        = $settings['custom_css'] ?? '';
$js         = $settings['custom_js'] ?? '';
$loadAsync  = !empty($settings['load_async']);
?>

<section id="<?= e($id) ?>">
  <?= $html ?>
</section>

<?php if (!empty($css)): ?>
  <style id="<?= e($id) ?>-css"><?= $css ?></style>
<?php endif; ?>

<?php if (!empty($js)): ?>
  <script id="<?= e($id) ?>-js"<?= $loadAsync ? ' async' : '' ?>><?= $js ?></script>
<?php endif; ?>
