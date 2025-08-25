<?php
$id = $id ?? 'image-with-text-' . uniqid();
$image = $settings['image'] ?? '';
$heading = $settings['heading'] ?? 'Discover Timeless Beauty';
$text = $settings['text'] ?? 'Explore our handcrafted pieces made with love.';
$layout = $settings['layout'] ?? 'left';
$ctaText = $settings['cta_text'] ?? 'Shop Now';
$ctaLink = $settings['cta_link'] ?? '#';
$backgroundColor = $settings['background_color'] ?? '#ffffff';
$textColor = $settings['text_color'] ?? '#000';
$buttonBg = $settings['button_bg'] ?? '#000';
$buttonColor = $settings['button_color'] ?? '#fff';
$padding = $settings['padding'] ?? '60px 20px';
?>

<style>
#<?= $id ?> {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 40px;
  padding: <?= escape_html($padding) ?>;
  font-family: Arial, sans-serif;
  flex-direction: <?= $layout === 'right' ? 'row-reverse' : 'row' ?>;
  background-color: <?= escape_html($backgroundColor) ?>;
  color: <?= escape_html($textColor) ?>;
}

#<?= $id ?> .image {
  flex: 1;
}

#<?= $id ?> .content {
  flex: 1;
  max-width: 500px;
}

#<?= $id ?> img {
  width: 100%;
  border-radius: 10px;
}

#<?= $id ?> .cta-button {
  display: inline-block;
  margin-top: 20px;
  background: <?= escape_html($buttonBg) ?>;
  color: <?= escape_html($buttonColor) ?>;
  padding: 12px 24px;
  border-radius: 30px;
  text-decoration: none;
}
</style>

<section id="<?= $id ?>">
  <div class="image"><img src="<?= escape_html($image) ?>" alt="Image" /></div>
  <div class="content">
    <h2><?= escape_html($heading) ?></h2>
    <p><?= escape_html($text) ?></p>
    <a href="<?= escape_html($ctaLink) ?>" class="cta-button"><?= escape_html($ctaText) ?></a>
  </div>
</section>
