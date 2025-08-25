<?php
$id = $id ?? 'before-after-slider-' . uniqid();
$settings = $settings ?? [];

$slides = $settings['slides'] ?? [];
$height = $settings['slider_height'] ?? '500px';
$showArrows = !empty($settings['show_arrows']);
$styleVariant = $settings['slider_style'] ?? 'standard';
$orientation = $settings['orientation'] ?? 'horizontal';
$handleColor = $settings['handle_color'] ?? '#fff';
$autoPlay = !empty($settings['auto_play']);
$autoInterval = intval($settings['auto_interval'] ?? 5) * 1000;


?>

<style>
#<?= $id ?> {
  position: relative;
  width: 100%;
  overflow: hidden;
}

#<?= $id ?> .slide {
  position: relative;
  height: <?= escape_html($height) ?>;
  width: 100%;
  overflow: hidden;
}

#<?= $id ?> .slider-container {
  position: relative;
  width: 100%;
  height: 100%;
  cursor: ew-resize;
}

#<?= $id ?> .after-image,
#<?= $id ?> .before-image {
  position: absolute;
  top: 0; left: 0;
  height: 100%;
  width: 100%;
  object-fit: cover;
}

#<?= $id ?> .before-image {
  z-index: 2;
  <?php if ($orientation === 'vertical'): ?>
  clip-path: inset(50% 0 0 0);
  <?php else: ?>
  clip-path: inset(0 50% 0 0);
  <?php endif; ?>
  transition: clip-path 0.2s ease;
}

#<?= $id ?> .slider-label {
  position: absolute;
  top: 10px;
  font-weight: bold;
  background: rgba(0, 0, 0, 0.6);
  color: #fff;
  padding: 4px 8px;
  font-size: 0.9rem;
  border-radius: 4px;
  z-index: 3;
}

#<?= $id ?> .slider-label.before { left: 10px; }
#<?= $id ?> .slider-label.after { right: 10px; }

#<?= $id ?> .slider-handle {
  position: absolute;
  <?php if ($orientation === 'vertical'): ?>
  left: 0;
  top: 50%;
  width: 100%;
  height: 4px;
  transform: translateY(-50%);
  cursor: ns-resize;
  <?php else: ?>
  top: 0;
  left: 50%;
  width: 4px;
  height: 100%;
  transform: translateX(-50%);
  cursor: ew-resize;
  <?php endif; ?>
  background-color: <?= escape_html($handleColor) ?>;
  z-index: 4;
}

#<?= $id ?> .nav-arrows {
  text-align: center;
  margin: 1rem 0;
}

#<?= $id ?> .nav-arrows button {
  padding: 0.4rem 1rem;
  margin: 0 0.5rem;
  font-size: 1.2rem;
  background-color: #000;
  color: #fff;
  border: none;
  cursor: pointer;
}
</style>

<section id="<?= $id ?>" class="before-after-slider" data-style="<?= escape_html($styleVariant) ?>">
  <div class="slides-wrapper">
    <?php foreach ($slides as $i => $slide): ?>
      <?php
        $slideId = $id . '-slide-' . $i;
        $before = $slide['before_image'] ?? '';
        $after = $slide['after_image'] ?? '';
        $labelBefore = $slide['label_before'] ?? 'Before';
        $labelAfter = $slide['label_after'] ?? 'After';
        $buttonText = $slide['button_text'] ?? '';
        $linkType = $slide['button_link_type'] ?? '';
        $linkValue = $slide['button_link_value'] ?? '';
        $linkHref = '#';
        if ($linkType === 'product') $linkHref = '/products/' . $linkValue;
        if ($linkType === 'collection') $linkHref = '/collections/' . $linkValue;
        if ($linkType === 'dataset') $linkHref = '/dataset/' . $linkValue;
        if ($linkType === 'product_set') $linkHref = '/set/' . $linkValue;
      ?>
      <div class="slide" id="<?= $slideId ?>">
        <div class="slider-container">
          <?php if ($before): ?>
            <img src="<?= escape_html($before) ?>" alt="Before" class="before-image" id="<?= $slideId ?>-before">
          <?php endif; ?>
          <?php if ($after): ?>
            <img src="<?= escape_html($after) ?>" alt="After" class="after-image">
          <?php endif; ?>
          <div class="slider-label before"><?= escape_html($labelBefore) ?></div>
          <div class="slider-label after"><?= escape_html($labelAfter) ?></div>
          <div class="slider-handle" id="<?= $slideId ?>-handle"></div>
        </div>
        <?php if ($buttonText): ?>
          <div style="text-align:center; margin-top: 1rem;">
            <a href="<?= escape_html($linkHref) ?>" class="cta-button" style="background:#000; color:#fff; padding:0.6rem 1.2rem; border-radius:4px; text-decoration:none;"><?= escape_html($buttonText) ?></a>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($showArrows): ?>
    <div class="nav-arrows">
      <button onclick="navigateSlides('<?= $id ?>', -1)">&#8592;</button>
      <button onclick="navigateSlides('<?= $id ?>', 1)">&#8594;</button>
    </div>
  <?php endif; ?>
</section>

<script>
function navigateSlides(wrapperId, direction) {
  const wrapper = document.querySelector(`#${wrapperId} .slides-wrapper`);
  const slides = wrapper.querySelectorAll('.slide');
  const current = [...slides].findIndex(slide => slide.style.display !== 'none');
  const next = (current + direction + slides.length) % slides.length;

  slides.forEach((slide, i) => {
    slide.style.display = (i === next) ? 'block' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.slides-wrapper .slide').forEach((slide, i) => {
    slide.style.display = (i === 0) ? 'block' : 'none';

    const container = slide.querySelector('.slider-container');
    const beforeImg = slide.querySelector('.before-image');
    const handle = slide.querySelector('.slider-handle');

    function updateSlider(pos) {
      const rect = container.getBoundingClientRect();
      let offset;
      if ('<?= $orientation ?>' === 'vertical') {
        offset = pos - rect.top;
        offset = Math.max(0, Math.min(offset, rect.height));
        const percent = offset / rect.height * 100;
        if (beforeImg && handle) {
          beforeImg.style.clipPath = `inset(${100 - percent}% 0 0 0)`;
          handle.style.top = percent + '%';
        }
      } else {
        offset = pos - rect.left;
        offset = Math.max(0, Math.min(offset, rect.width));
        const percent = offset / rect.width * 100;
        if (beforeImg && handle) {
          beforeImg.style.clipPath = `inset(0 ${100 - percent}% 0 0)`;
          handle.style.left = percent + '%';
        }
      }
    }

    if (container && beforeImg && handle) {
      if ('<?= $orientation ?>' === 'vertical') {
        container.addEventListener('mousemove', e => { if (e.buttons === 1) updateSlider(e.clientY); });
        container.addEventListener('mousedown', e => updateSlider(e.clientY));
      } else {
        container.addEventListener('mousemove', e => { if (e.buttons === 1) updateSlider(e.clientX); });
        container.addEventListener('mousedown', e => updateSlider(e.clientX));
      }
    }
  });

  <?php if ($autoPlay): ?>
  setInterval(() => navigateSlides('<?= $id ?>', 1), <?= $autoInterval ?>);
  <?php endif; ?>
});
</script>
