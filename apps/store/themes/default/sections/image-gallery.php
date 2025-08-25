<?php
$id = $id ?? 'image-gallery-' . uniqid();
$heading = $settings['heading'] ?? 'Gallery';
$images = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'image') {
            $images[] = $block['settings'];
        }
    }
}
if (empty($images)) {
    $images = $settings['images'] ?? [];
}
$columns = intval($settings['columns'] ?? 4);
$gap = $settings['gap'] ?? '20px';
$enableLightbox = !empty($settings['lightbox']);
?>

<style>
#<?= $id ?> {
  padding: 50px 20px;
  text-align: center;
  font-family: Arial, sans-serif;
}

#<?= $id ?> .gallery-grid {
  display: grid;
  grid-template-columns: repeat(<?= $columns ?>, 1fr);
  gap: <?= escape_html($gap) ?>;
  margin-top: 20px;
}

#<?= $id ?> img {
  width: 100%;
  height: auto;
  border-radius: 8px;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <div class="gallery-grid">
    <?php foreach ($images as $img): ?>
      <img src="<?= escape_html($img['image']) ?>" alt="Gallery Image" <?= $enableLightbox ? 'class="lightbox-trigger"' : '' ?>>
    <?php endforeach; ?>
  </div>
  <?php if ($enableLightbox): ?>
  <div class="lightbox" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);justify-content:center;align-items:center;z-index:9999;">
    <img src="" alt="Preview" style="max-width:90%;max-height:90%;border-radius:8px;">
  </div>
  <?php endif; ?>
</section>

<?php if ($enableLightbox): ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
  const box=document.querySelector('#<?= $id ?> .lightbox');
  const img=box.querySelector('img');
  document.querySelectorAll('#<?= $id ?> .lightbox-trigger').forEach(el=>{
    el.style.cursor='pointer';
    el.addEventListener('click',()=>{img.src=el.src;box.style.display='flex';});
  });
  box.addEventListener('click',()=>{box.style.display='none';});
});
</script>
<?php endif; ?>
