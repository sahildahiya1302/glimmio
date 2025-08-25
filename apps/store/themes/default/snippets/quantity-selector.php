<?php
$settings = $snippet['settings'] ?? [];

$name  = $settings['name'] ?? 'quantity';
$value = isset($settings['value']) ? (int)$settings['value'] : 1;
$min   = isset($settings['min']) ? (int)$settings['min'] : 1;
$max   = isset($settings['max']) ? (int)$settings['max'] : 100;
$step  = isset($settings['step']) ? (int)$settings['step'] : 1;
$showLabel = !empty($settings['show_label']);
?>
<div class="quantity-selector">
    <?php if ($showLabel): ?><label><?= e(ucfirst($name)) ?>:</label><?php endif; ?>
    <button type="button" class="qty-decrease" aria-label="Decrease quantity">-</button>
    <input type="number" name="<?= e($name) ?>" value="<?= $value ?>" min="<?= $min ?>" max="<?= $max ?>" step="<?= $step ?>">
    <button type="button" class="qty-increase" aria-label="Increase quantity">+</button>
</div>
<script>
(function(){
  const wrapper = document.currentScript.previousElementSibling;
  if(!wrapper) return;
  const input = wrapper.querySelector('input');
  wrapper.querySelector('.qty-decrease').addEventListener('click',()=>{ input.stepDown(); });
  wrapper.querySelector('.qty-increase').addEventListener('click',()=>{ input.stepUp(); });
})();
</script>
