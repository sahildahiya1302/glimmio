<?php
$id = $id ?? 'quick-links-' . uniqid();
$heading = $settings['heading'] ?? 'Quick Links';
$layout = $settings['layout'] ?? 'inline'; // inline or grid
$links = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'link') {
            $links[] = [
                'label' => $block['settings']['label'] ?? '',
                'icon' => $block['settings']['icon'] ?? '',
                'link' => $block['settings']['link'] ?? '#'
            ];
        }
    }
}
if (empty($links)) {
    $links = $settings['links'] ?? [];
}
$backgroundColor = $settings['background_color'] ?? '#ffffff';
$textColor = $settings['text_color'] ?? '#000';
$linkColor = $settings['link_color'] ?? '#000';
$iconSize = $settings['icon_size'] ?? '40px';
$columns = intval($settings['columns'] ?? 4);

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: 40px 20px;
  text-align: center;
  font-family: Arial, sans-serif;
  background: <?= escape_html($backgroundColor) ?>;
  color: <?= escape_html($textColor) ?>;
}

#<?= $id ?> h2 {
  font-size: 1.6rem;
  margin-bottom: 1rem;
}

#<?= $id ?> .quick-links {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
}

#<?= $id ?>.grid .quick-links {
  display: grid;
  grid-template-columns: repeat(<?= $columns ?>, 1fr);
  gap: 25px;
  max-width: 900px;
  margin: 0 auto;
}

#<?= $id ?> .quick-link-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-decoration: none;
  color: <?= escape_html($linkColor) ?>;
  transition: transform 0.2s ease;
}

#<?= $id ?> .quick-link-item:hover {
  transform: translateY(-4px);
}

#<?= $id ?> .quick-link-icon {
  width: <?= escape_html($iconSize) ?>;
  height: <?= escape_html($iconSize) ?>;
  margin-bottom: 8px;
}

#<?= $id ?> .quick-link-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

#<?= $id ?> .quick-link-label {
  font-size: 0.95rem;
}
</style>

<section id="<?= $id ?>" class="<?= $layout === 'grid' ? 'grid' : '' ?>">
  <h2><?= escape_html($heading) ?></h2>
  <div class="quick-links">
    <?php foreach ($links as $link): ?>
      <a href="<?= escape_html($link['url'] ?? '#') ?>" class="quick-link-item">
        <?php if (!empty($link['icon'])): ?>
          <div class="quick-link-icon">
            <img src="<?= escape_html($link['icon']) ?>" alt="<?= escape_html($link['label']) ?>">
          </div>
        <?php endif; ?>
        <div class="quick-link-label"><?= escape_html($link['label']) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
