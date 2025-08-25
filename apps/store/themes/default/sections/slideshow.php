<?php
$id = $id ?? 'slideshow-' . uniqid();

// Load slides from blocks if provided
$slides = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'slide') {
            $slides[] = $block['settings'] ?? [];
        }
    }
}

// Fallback to legacy settings
if (empty($slides)) {
    $slides = $settings['slides'] ?? [];
}
$effect = $settings['effect'] ?? 'slide';
$autoplaySpeed = intval($settings['autoplay_speed'] ?? 4000);
$overlayGradient = $settings['overlay_gradient'] ?? '';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

<style>
#<?= $id ?> {
  position: relative;
  font-family: Arial, sans-serif;
}

#<?= $id ?> .swiper {
  width: 100%;
  height: 100%;
}

#<?= $id ?> .swiper-slide {
  position: relative;
  background-size: cover;
  background-position: center;
  min-height: 400px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-align: center;
}

#<?= $id ?> .slide-overlay {
  background: rgba(0, 0, 0, 0.4);
  <?php if ($overlayGradient): ?>
  background: <?= escape_html($overlayGradient) ?>, rgba(0,0,0,0.4);
  <?php endif; ?>
  padding: 40px 20px;
  max-width: 800px;
  margin: auto;
  border-radius: 12px;
}

#<?= $id ?> .slide-title {
  font-size: 2.2rem;
  font-weight: bold;
  margin-bottom: 10px;
}

#<?= $id ?> .slide-subtitle {
  font-size: 1.1rem;
  margin-bottom: 20px;
}

#<?= $id ?> .slide-button {
  display: inline-block;
  padding: 10px 20px;
  background-color: white;
  color: black;
  font-weight: bold;
  border-radius: 5px;
  text-decoration: none;
  transition: background 0.3s;
}

#<?= $id ?> .slide-button:hover {
  background-color: #f0f0f0;
}
</style>

<section id="<?= $id ?>">
  <div class="swiper">
    <div class="swiper-wrapper">
      <?php foreach ($slides as $slide): ?>
        <div class="swiper-slide" style="background-image: url('<?= escape_html($slide['image']) ?>')">
          <div class="slide-overlay">
            <?php if (!empty($slide['title'])): ?>
              <div class="slide-title"><?= escape_html($slide['title']) ?></div>
            <?php endif; ?>
            <?php if (!empty($slide['subtitle'])): ?>
              <div class="slide-subtitle"><?= escape_html($slide['subtitle']) ?></div>
            <?php endif; ?>
            <?php if (!empty($slide['button_text']) && !empty($slide['button_link'])): ?>
              <a href="<?= escape_html($slide['button_link']) ?>" class="slide-button"><?= escape_html($slide['button_text']) ?></a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
  </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {
  new Swiper("#<?= $id ?> .swiper", {
    loop: true,
    effect: '<?= $effect ?>',
    pagination: { el: '.swiper-pagination', clickable: true },
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    autoplay: { delay: <?= $autoplaySpeed ?>, disableOnInteraction: false }
  });
});
</script>
