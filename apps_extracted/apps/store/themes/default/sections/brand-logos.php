<?php
$id = $id ?? 'brand-logos-' . uniqid();
$heading = $settings['heading'] ?? 'Trusted by Top Brands';
$layoutStyle = $settings['layout_style'] ?? 'slider'; // options: slider / grid
$maxLogos = intval($settings['max_logos'] ?? 10);
$autoplay = $settings['autoplay'] ?? true;
$slidesPerView = intval($settings['slides_per_view'] ?? 4);
$slideSpeed = intval($settings['slide_speed'] ?? 3000);
$grayscaleHover = $settings['grayscale_hover'] ?? true;
$textAlign = $settings['text_align'] ?? 'center';

$logos = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'logo') {
            $logos[] = [
                'image' => $block['settings']['brand_image'] ?? '',
                'link'  => $block['settings']['brand_link'] ?? ''
            ];
        }
    }
}
if (empty($logos)) {
    $logos = $settings['logos'] ?? [];
}

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<?php if ($layoutStyle === 'slider'): ?>
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<?php endif; ?>

<style>
#<?= $id ?> {
  padding: 50px 20px;
  text-align: <?= escape_html($textAlign) ?>;
  font-family: Arial, sans-serif;
}

#<?= $id ?> h2 {
  font-size: 2rem;
  margin-bottom: 2rem;
}

#<?= $id ?> .logo-item {
  padding: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

#<?= $id ?> .logo-item img {
  max-height: 60px;
  max-width: 100%;
  object-fit: contain;
  <?php if ($grayscaleHover): ?>
  filter: grayscale(100%);
  transition: filter 0.3s ease;
  <?php endif; ?>
}

<?php if ($grayscaleHover): ?>
#<?= $id ?> .logo-item img:hover {
  filter: grayscale(0%);
}
<?php endif; ?>

#<?= $id ?> .brand-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 20px;
  justify-items: center;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>

  <?php if ($layoutStyle === 'grid'): ?>
    <div class="brand-grid">
      <?php foreach (array_slice($logos, 0, $maxLogos) as $logo): ?>
        <div class="logo-item">
          <?php if (!empty($logo['link'])): ?>
            <a href="<?= escape_html($logo['link']) ?>">
              <img src="<?= escape_html($logo['image'] ?? '') ?>" alt="Brand Logo">
            </a>
          <?php else: ?>
            <img src="<?= escape_html($logo['image'] ?? '') ?>" alt="Brand Logo">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

  <?php else: ?>
    <div class="swiper <?= $id ?>-swiper">
      <div class="swiper-wrapper">
        <?php foreach (array_slice($logos, 0, $maxLogos) as $logo): ?>
          <div class="swiper-slide logo-item">
            <?php if (!empty($logo['link'])): ?>
              <a href="<?= escape_html($logo['link']) ?>">
                <img src="<?= escape_html($logo['image'] ?? '') ?>" alt="Brand Logo">
              </a>
            <?php else: ?>
              <img src="<?= escape_html($logo['image'] ?? '') ?>" alt="Brand Logo">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-pagination"></div>
    </div>
  <?php endif; ?>
</section>

<?php if ($layoutStyle === 'slider'): ?>
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script>
new Swiper('.<?= $id ?>-swiper', {
  slidesPerView: <?= $slidesPerView ?>,
  spaceBetween: 20,
  loop: true,
  autoplay: <?= $autoplay ? '{ delay: ' . $slideSpeed . ' }' : 'false' ?>,
  pagination: {
    el: '.swiper-pagination',
    clickable: true
  },
  breakpoints: {
    640: { slidesPerView: Math.max(2, <?= $slidesPerView ?> - 1) },
    768: { slidesPerView: Math.max(3, <?= $slidesPerView ?>) }
  }
});
</script>
<?php endif; ?>
