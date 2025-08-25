<?php
$id = $id ?? 'video-banner-' . uniqid();

$heading = $settings['heading'] ?? 'Experience Luxury Like Never Before';
$subheading = $settings['subheading'] ?? '';
$buttonText = $settings['button_text'] ?? 'Shop Now';
$buttonLink = $settings['button_link'] ?? '#';
$videoType = $settings['video_type'] ?? 'mp4';
$videoUrl = $settings['video_url'] ?? '';
$bannerHeight = intval($settings['banner_height'] ?? 80);
$autoPlay = $settings['autoplay'] ?? true;
$overlayColor = $settings['overlay_color'] ?? 'rgba(0, 0, 0, 0.4)';
$blocks = $blocks ?? [];
if (!empty($blocks)) {
    $b = $blocks[0];
    $videoUrl = $b['settings']['video_url'] ?? $videoUrl;
    $heading = $b['settings']['heading'] ?? $heading;
    $buttonLink = $b['settings']['cta'] ?? $buttonLink;
}
$overlay = $settings['overlay'] ?? true;

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  position: relative;
  overflow: hidden;
  height: <?= $bannerHeight ?>vh;
  min-height: 400px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: Arial, sans-serif;
}

#<?= $id ?> video,
#<?= $id ?> iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 0;
}

#<?= $id ?> .overlay {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: <?= escape_html($overlayColor) ?>;
  z-index: 1;
}

#<?= $id ?> .content {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 800px;
  padding: 20px;
}

#<?= $id ?> .content h1 {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

#<?= $id ?> .content p {
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

#<?= $id ?> .content a {
  display: inline-block;
  background: #fff;
  color: #000;
  padding: 12px 24px;
  border-radius: 5px;
  font-weight: bold;
  text-decoration: none;
}
</style>

<section id="<?= $id ?>">
  <?php if ($videoType === 'mp4'): ?>
    <video <?= $autoPlay ? 'autoplay' : '' ?> muted loop playsinline>
      <source src="<?= escape_html($videoUrl) ?>" type="video/mp4">
    </video>
  <?php elseif ($videoType === 'youtube'): ?>
    <iframe src="https://www.youtube.com/embed/<?= escape_html($videoUrl) ?>?autoplay=<?= $autoPlay ? '1' : '0' ?>&mute=1&loop=1&playlist=<?= escape_html($videoUrl) ?>&controls=0&showinfo=0&rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
  <?php elseif ($videoType === 'vimeo'): ?>
    <iframe src="https://player.vimeo.com/video/<?= escape_html($videoUrl) ?>?autoplay=<?= $autoPlay ? '1' : '0' ?>&muted=1&loop=1&background=1" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
  <?php endif; ?>

  <?php if ($overlay): ?>
    <div class="overlay"></div>
  <?php endif; ?>

  <div class="content">
    <h1><?= escape_html($heading) ?></h1>
    <?php if ($subheading): ?><p><?= escape_html($subheading) ?></p><?php endif; ?>
    <a href="<?= escape_html($buttonLink) ?>"><?= escape_html($buttonText) ?></a>
  </div>
</section>
