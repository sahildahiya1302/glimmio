<?php
$id = $id ?? 'testimonial-slider-' . uniqid();
$heading = $settings['heading'] ?? 'What Our Customers Say';
$autoplaySpeed = intval($settings['autoplay_speed'] ?? 5000);
$slidesDesktop = intval($settings['slides_desktop'] ?? 1);
$slidesMobile = intval($settings['slides_mobile'] ?? 1);
$showArrows = $settings['show_arrows'] ?? true;
$showPagination = $settings['show_pagination'] ?? true;

// Load testimonials from blocks if provided
$testimonials = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'testimonial') {
            $testimonials[] = $block['settings'] ?? [];
        }
    }
}

// Fallback to legacy settings list
if (empty($testimonials)) {
    $rawTestimonials = $settings['testimonials'] ?? [];
    $testimonials = is_array($rawTestimonials) ? $rawTestimonials : json_decode($rawTestimonials, true);
    if (!is_array($testimonials)) $testimonials = [];
}


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
  padding: 60px 20px;
  background: #f9f9f9;
  font-family: Arial, sans-serif;
  text-align: center;
}

#<?= $id ?> h2 {
  font-size: 2rem;
  margin-bottom: 2rem;
}

#<?= $id ?> .swiper {
  max-width: 1000px;
  margin: auto;
}

#<?= $id ?> .testimonial-slide {
  background: #fff;
  padding: 30px 20px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  text-align: center;
}

#<?= $id ?> .testimonial-stars {
  color: #f5b50a;
  margin-bottom: 10px;
}

#<?= $id ?> .testimonial-message {
  font-size: 1rem;
  color: #333;
  margin-bottom: 20px;
  line-height: 1.6;
}

#<?= $id ?> .testimonial-author {
  display: flex;
  flex-direction: column;
  align-items: center;
}

#<?= $id ?> .author-photo {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 50%;
  margin-bottom: 10px;
}

#<?= $id ?> .author-name {
  font-weight: bold;
}

#<?= $id ?> .author-title {
  font-size: 0.85rem;
  color: #777;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>

  <div class="swiper">
    <div class="swiper-wrapper">
      <?php foreach ($testimonials as $item): ?>
        <div class="swiper-slide testimonial-slide">
          <div class="testimonial-stars">
            <?php for ($i = 0; $i < intval($item['rating'] ?? 5); $i++): ?>
              ★
            <?php endfor; ?>
          </div>
          <div class="testimonial-message">“<?= escape_html($item['message'] ?? '') ?>”</div>
          <div class="testimonial-author">
            <?php if (!empty($item['photo'])): ?>
              <img src="<?= escape_html($item['photo']) ?>" class="author-photo" alt="<?= escape_html($item['name']) ?>">
            <?php endif; ?>
            <div class="author-name"><?= escape_html($item['name'] ?? '') ?></div>
            <div class="author-title"><?= escape_html($item['title'] ?? '') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($showPagination): ?>
    <div class="swiper-pagination"></div>
    <?php endif; ?>
    <?php if ($showArrows): ?>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <?php endif; ?>
  </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {
  new Swiper("#<?= $id ?> .swiper", {
    loop: true,
    autoplay: { delay: <?= $autoplaySpeed ?> },
    slidesPerView: <?= $slidesDesktop ?>,
    breakpoints: { 0: { slidesPerView: <?= $slidesMobile ?> } },
    <?php if ($showPagination): ?>pagination: { el: '.swiper-pagination', clickable: true },<?php endif; ?>
    <?php if ($showArrows): ?>navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },<?php endif; ?>
  });
});
</script>
