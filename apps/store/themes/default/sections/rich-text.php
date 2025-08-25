<?php
$id = $id ?? 'rich-text-' . uniqid();
$title = $settings['title'] ?? 'Tell your brand story';
$content = $settings['content'] ?? '<p>This is a customizable rich text section. You can use it to share meaningful content with your customers.</p>';
$showButton  = $settings['show_button'] ?? false;
$buttonText  = $settings['button_text'] ?? 'Learn More';
$buttonUrl   = $settings['button_url'] ?? '#';
$textAlign   = $settings['text_align'] ?? 'center';
$bgColor     = $settings['background'] ?? '#fff';
$btnBg       = $settings['button_bg'] ?? '#000';
$btnColor    = $settings['button_color'] ?? '#fff';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 60px 20px;
  font-family: Arial, sans-serif;
  text-align: <?= escape_html($textAlign) ?>;
  max-width: 900px;
  margin: 0 auto;
  background: <?= escape_html($bgColor) ?>;
}

#<?= $id ?> h2 {
  font-size: 2rem;
  margin-bottom: 1rem;
}

#<?= $id ?> .rich-content {
  font-size: 1rem;
  color: #444;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

#<?= $id ?> .cta-button {
  display: inline-block;
  padding: 10px 25px;
  background-color: <?= escape_html($btnBg) ?>;
  color: <?= escape_html($btnColor) ?>;
  font-weight: bold;
  border-radius: 4px;
  text-decoration: none;
  transition: background 0.3s ease;
}

#<?= $id ?> .cta-button:hover {
  opacity: 0.8;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($title) ?></h2>
  <div class="rich-content">
    <?= $content ?>
  </div>
  <?php if ($showButton): ?>
    <a href="<?= escape_html($buttonUrl) ?>" class="cta-button"><?= escape_html($buttonText) ?></a>
  <?php endif; ?>
</section>
