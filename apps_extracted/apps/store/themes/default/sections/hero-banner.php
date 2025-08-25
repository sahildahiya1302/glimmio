<?php
$id = $id ?? 'hero-banner-' . uniqid();
$backgroundImage = $settings['background_image'] ?? '';
$heading = $settings['heading'] ?? 'Your Hero Heading';
$subheading = $settings['subheading'] ?? 'Your supporting text or tagline goes here.';
$buttonText = $settings['button_text'] ?? 'Shop Now';
$buttonLink = $settings['button_link'] ?? '#';
$buttons = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'button') {
            $buttons[] = [
                'text' => $block['settings']['button_text'] ?? $buttonText,
                'link' => $block['settings']['button_link'] ?? $buttonLink
            ];
        }
    }
}
if (empty($buttons)) {
    $buttons[] = [ 'text' => $buttonText, 'link' => $buttonLink ];
}
$textColor = $settings['text_color'] ?? '#ffffff';
$textAlign = $settings['text_align'] ?? 'center';
$overlayColor = $settings['overlay_color'] ?? 'rgba(0, 0, 0, 0.4)';
$overlayGradient = $settings['overlay_gradient'] ?? '';
$height = $settings['height'] ?? '600px';
$videoUrl = $settings['video_url'] ?? '';
$buttonStyle = $settings['button_style'] ?? 'primary';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  position: relative;
  width: 100%;
  height: <?= escape_html($height) ?>;
  background-image: url('<?= escape_html($backgroundImage) ?>');
  background-size: cover;
  background-position: center;
  color: <?= escape_html($textColor) ?>;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: <?= escape_html($textAlign) ?>;
  font-family: Arial, sans-serif;
  overflow: hidden;
}

#<?= $id ?> .hero-overlay {
  position: absolute;
  top: 0; left: 0;
  width: 100%;
  height: 100%;
  background: <?= escape_html($overlayColor) ?>;
  <?php if ($overlayGradient): ?>
  background: <?= escape_html($overlayGradient) ?>, <?= escape_html($overlayColor) ?>;
  <?php endif; ?>
  z-index: 1;
}

#<?= $id ?> .hero-content {
  position: relative;
  z-index: 2;
  padding: 2rem;
  max-width: 800px;
}

#<?= $id ?> .hero-heading {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

#<?= $id ?> .hero-subheading {
  font-size: 1.25rem;
  margin-bottom: 2rem;
}

#<?= $id ?> .hero-button {
  padding: 0.75rem 1.5rem;
  border-radius: 30px;
  font-weight: bold;
  text-decoration: none;
  transition: background 0.3s ease;
}

#<?= $id ?> .hero-button.primary {
  background-color: #ffffff;
  color: #000000;
}

#<?= $id ?> .hero-button.outline {
  background-color: transparent;
  color: #ffffff;
  border: 2px solid #ffffff;
}

#<?= $id ?> .hero-button.link {
  background: none;
  color: #ffffff;
  text-decoration: underline;
}

#<?= $id ?> .hero-button:hover {
  opacity: 0.9;
}

@media (max-width: 768px) {
  #<?= $id ?> .hero-heading {
    font-size: 2rem;
  }

  #<?= $id ?> .hero-subheading {
    font-size: 1rem;
  }

  #<?= $id ?> {
    height: 400px;
  }
}
</style>

<section id="<?= $id ?>" role="region" aria-label="Hero Banner">
  <?php if ($videoUrl): ?>
    <video src="<?= escape_html($videoUrl) ?>" autoplay muted loop playsinline style="position:absolute;width:100%;height:100%;object-fit:cover"></video>
  <?php endif; ?>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-heading"><?= escape_html($heading) ?></div>
    <div class="hero-subheading"><?= escape_html($subheading) ?></div>
    <?php foreach ($buttons as $btn): ?>
      <?php if (!empty($btn['text']) && !empty($btn['link'])): ?>
        <a href="<?= escape_html($btn['link']) ?>" class="hero-button <?= htmlspecialchars($buttonStyle) ?>">
          <?= escape_html($btn['text']) ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</section>
