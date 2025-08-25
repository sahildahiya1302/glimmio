<?php
$id = $id ?? 'footer-' . uniqid();
$logo = $settings['logo'] ?? '';
$copyright = $settings['copyright'] ?? '';
$bgColor = $settings['background_color'] ?? '#111';
$textColor = $settings['text_color'] ?? '#eee';
$socialTitle = $settings['social_title'] ?? 'Follow Us';
$socialLinks = $settings['social_links'] ?? [];
$menus = [];
if (!empty($blocks)) {
    foreach ($blocks as $block) {
        if (($block['type'] ?? '') === 'menu') {
            $menus[] = $block['settings'] ?? [];
        }
    }
}
if (empty($menus)) {
    $menus = $settings['menus'] ?? [];
}

// Get published pages to show in footer when no menu links
$footerPages = [];
if (function_exists('db')) {
    try {
        $footerPages = db_query('SELECT title, slug FROM pages WHERE is_published = 1 ORDER BY title')->fetchAll();
    } catch (Throwable $e) {
        $footerPages = [];
    }
}

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  background: <?= escape_html($bgColor) ?>;
  color: <?= escape_html($textColor) ?>;
  padding: 40px 20px;
  font-family: Arial, sans-serif;
}

#<?= $id ?> .footer-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 40px;
  justify-content: space-between;
  margin-bottom: 20px;
}

#<?= $id ?> .footer-col {
  flex: 1;
  min-width: 180px;
}

#<?= $id ?> .social-icons {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

#<?= $id ?> a {
  color: <?= escape_html($textColor) ?>;
  text-decoration: none;
  display: block;
  margin-bottom: 8px;
}

#<?= $id ?> a:hover {
  text-decoration: underline;
}

#<?= $id ?> .footer-bottom {
  text-align: center;
  font-size: 0.85rem;
  color: #888;
}
</style>

<footer id="<?= $id ?>">
  <div class="footer-grid">
    <?php if ($logo): ?>
      <div class="footer-col">
        <img src="<?= escape_html($logo) ?>" alt="Logo" style="max-height: 60px;">
      </div>
    <?php endif; ?>

    <?php foreach ($menus as $menu): ?>
      <div class="footer-col">
        <h4><?= escape_html($menu['title']) ?></h4>
        <?php foreach ($menu['links'] as $link): ?>
          <a href="<?= escape_html($link['url']) ?>"><?= escape_html($link['label']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <?php if (empty($menus) && !empty($footerPages)): ?>
      <div class="footer-col">
        <h4>Pages</h4>
        <?php foreach ($footerPages as $p): ?>
          <a href="/page/<?= escape_html($p['slug']) ?>"><?= escape_html($p['title']) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php if (!empty($socialLinks)): ?>
    <div class="footer-col">
      <h4><?= escape_html($socialTitle) ?></h4>
      <div class="social-icons">
        <?php foreach ($socialLinks as $link): ?>
          <a href="<?= escape_html($link['url']) ?>" target="_blank" rel="noopener">
            <img src="<?= escape_html($link['icon']) ?>" alt="icon" style="width:24px;height:24px;">
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="footer-bottom">
    <?= escape_html($copyright) ?>
  </div>
</footer>
