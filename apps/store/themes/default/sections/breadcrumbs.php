<?php
$id = $id ?? 'breadcrumbs-' . uniqid();
$showHomeIcon = $settings['show_home_icon'] ?? true;
$textColor = $settings['text_color'] ?? '#333';
$linkColor = $settings['link_color'] ?? '#555';
$separator = $settings['separator'] ?? '›';
$backgroundColor = $settings['background_color'] ?? 'transparent';
$fontSize = $settings['font_size'] ?? '0.95rem';
$fontWeight = $settings['font_weight'] ?? 'normal';
$padding = $settings['padding'] ?? '1rem 20px';

// Use dynamic breadcrumbs source if available, else fallback to default static mock path
if (!isset($breadcrumbs)) {
  // Example: fetch breadcrumbs dynamically from current URL or context
  $breadcrumbs = [];

  // Home breadcrumb
  $breadcrumbs[] = ['title' => 'Home', 'url' => '/'];

  // Parse current URL path segments
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $segments = array_filter(explode('/', trim($path, '/')));

  $accumulatedPath = '';
  foreach ($segments as $index => $segment) {
    $accumulatedPath .= '/' . $segment;
    $title = ucfirst(str_replace(['-', '_'], ' ', $segment));
    $url = ($index === count($segments) - 1) ? '' : $accumulatedPath;
    $breadcrumbs[] = ['title' => $title, 'url' => $url];
  }
}

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
  }
}
?>

<style>
#<?= $id ?> {
  padding: <?= escape_html($padding) ?>;
  font-size: <?= escape_html($fontSize) ?>;
  font-weight: <?= escape_html($fontWeight) ?>;
  font-family: Arial, sans-serif;
  color: <?= escape_html($textColor) ?>;
  background: <?= escape_html($backgroundColor) ?>;
}

#<?= $id ?> nav {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}

#<?= $id ?> a {
  color: <?= escape_html($linkColor) ?>;
  text-decoration: none;
  transition: color 0.3s ease;
}

#<?= $id ?> a:hover {
  text-decoration: underline;
}

#<?= $id ?> .separator {
  margin: 0 0.5rem;
  color: <?= escape_html($textColor) ?>;
}

#<?= $id ?> .home-icon {
  font-size: 1rem;
  margin-right: 0.3rem;
}
</style>

<section id="<?= $id ?>" aria-label="Breadcrumbs">
  <nav role="navigation" aria-label="Breadcrumb">
    <?php foreach ($breadcrumbs as $index => $crumb): ?>
      <?php if ($index > 0): ?>
        <span class="separator"><?= escape_html($separator) ?></span>
      <?php endif; ?>

      <?php if (!empty($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
        <a href="<?= escape_html($crumb['url']) ?>">
          <?php if ($index === 0 && $showHomeIcon): ?>
            <span class="home-icon">🏠</span>
          <?php endif; ?>
          <?= escape_html($crumb['title']) ?>
        </a>
      <?php else: ?>
        <span>
          <?php if ($index === 0 && $showHomeIcon): ?>
            <span class="home-icon">🏠</span>
          <?php endif; ?>
          <?= escape_html($crumb['title']) ?>
        </span>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>
</section>
