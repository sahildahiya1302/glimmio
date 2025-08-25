<?php
$id = $id ?? 'popup-' . uniqid();
$heading = $settings['heading'] ?? 'Get 10% Off!';
$subheading = $settings['subheading'] ?? 'Subscribe and receive a 10% coupon.';
$image = '';
$cta = '#';
$delay = intval($settings['delay_seconds'] ?? 3);
$background = $settings['background_color'] ?? '#fff';
$textColor = $settings['text_color'] ?? '#000';
$position = $settings['position'] ?? 'bottom-right';
$width = $settings['width'] ?? '320px';
$borderRadius = $settings['border_radius'] ?? '10px';
$overlay = !empty($settings['overlay']);
$overlayClose = !empty($settings['overlay_close']);

if (!empty($blocks)) {
    $block = $blocks[0];
    $heading = $block['settings']['heading'] ?? $heading;
    $image = $block['settings']['image'] ?? '';
    $subheading = $block['settings']['text'] ?? $subheading;
    $cta = $block['settings']['cta'] ?? $cta;
}
?>

<style>
#<?= $id ?> {
  position: fixed;
  <?php
  $posStyle = 'bottom:20px;right:20px;';
  if ($position === 'bottom-left') $posStyle = 'bottom:20px;left:20px;';
  elseif ($position === 'top-right') $posStyle = 'top:20px;right:20px;';
  elseif ($position === 'top-left') $posStyle = 'top:20px;left:20px;';
  echo $posStyle;
  ?>
  width: <?= escape_html($width) ?>;
  background: <?= escape_html($background) ?>;
  color: <?= escape_html($textColor) ?>;
  padding: 20px;
  border-radius: <?= escape_html($borderRadius) ?>;
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
  display: none;
  z-index: 9999;
  font-family: Arial, sans-serif;
}

#<?= $id ?> .close-btn {
  position: absolute;
  top: 8px;
  right: 12px;
  cursor: pointer;
  font-weight: bold;
}
</style>

<?php if ($overlay): ?>
<div class="popup-overlay" id="<?= $id ?>-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9998;"></div>
<?php endif; ?>
<div id="<?= $id ?>">
  <div class="close-btn" onclick="document.getElementById('<?= $id ?>').style.display='none'">×</div>
  <?php if ($image): ?>
    <img src="<?= escape_html($image) ?>" alt="Popup" style="max-width:100%;margin-bottom:10px;">
  <?php endif; ?>
  <h3><?= escape_html($heading) ?></h3>
  <p><?= escape_html($subheading) ?></p>
  <?php if ($cta): ?>
    <a href="<?= escape_html($cta) ?>">Learn more</a>
  <?php endif; ?>
</div>

<script>
setTimeout(() => {
  const pop=document.getElementById('<?= $id ?>');
  pop.style.display='block';
  <?php if ($overlay): ?>document.getElementById('<?= $id ?>-overlay').style.display='block';<?php endif; ?>
}, <?= $delay * 1000 ?>);
<?php if ($overlay && $overlayClose): ?>
document.getElementById('<?= $id ?>-overlay').addEventListener('click',()=>{document.getElementById('<?= $id ?>').style.display='none';document.getElementById('<?= $id ?>-overlay').style.display='none';});
<?php endif; ?>
</script>
