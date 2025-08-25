<?php
$id = $id ?? 'blog-posts-' . uniqid();
$heading = $settings['heading'] ?? 'Latest Articles';
$subheading = $settings['subheading'] ?? '';
$maxPosts = intval($settings['max_posts'] ?? 3);
$showExcerpt = $settings['show_excerpt'] ?? true;
$showDate = $settings['show_date'] ?? true;
$showAuthor = $settings['show_author'] ?? false;
$buttonText = $settings['button_text'] ?? 'Read More';
$textAlign = $settings['text_align'] ?? 'center';

if (!function_exists('escape_html')) {
  function escape_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
  }
}

// Fetch latest posts from the database
$stmt = db_query('SELECT title, excerpt, image_url AS image, slug, author, created_at FROM blogs ORDER BY created_at DESC LIMIT ?', [$maxPosts]);
$blogPosts = $stmt->fetchAll();
?>

<style>
#<?= $id ?> {
  padding: 60px 20px;
  text-align: <?= escape_html($textAlign) ?>;
  font-family: Arial, sans-serif;
}

#<?= $id ?> h2 {
  font-size: 2.2rem;
  margin-bottom: 0.5rem;
}

#<?= $id ?> p.subheading {
  font-size: 1rem;
  color: #666;
  margin-bottom: 2rem;
}

#<?= $id ?> .blog-grid {
  display: grid;
  gap: 30px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

#<?= $id ?> .blog-card {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
  text-align: left;
  transition: transform 0.2s ease;
}

#<?= $id ?> .blog-card:hover {
  transform: translateY(-5px);
}

#<?= $id ?> .blog-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

#<?= $id ?> .blog-card .content {
  padding: 1rem;
}

#<?= $id ?> .blog-card h3 {
  font-size: 1.2rem;
  margin-bottom: 0.5rem;
}

#<?= $id ?> .blog-card .date {
  font-size: 0.85rem;
  color: #999;
  margin-bottom: 0.5rem;
}

#<?= $id ?> .blog-card .excerpt {
  font-size: 0.95rem;
  color: #555;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <?php if (!empty($subheading)): ?>
    <p class="subheading"><?= escape_html($subheading) ?></p>
  <?php endif; ?>
  
  <div class="blog-grid">
    <?php foreach ($blogPosts as $post): ?>
      <div class="blog-card">
        <a href="/blog/<?= escape_html($post['slug']) ?>">
          <img src="<?= escape_html($post['image']) ?>" alt="<?= escape_html($post['title']) ?>">
        </a>
        <div class="content">
          <h3><?= escape_html($post['title']) ?></h3>
          <?php if ($showAuthor && !empty($post['author'])): ?>
            <div class="date">By <?= escape_html($post['author']) ?></div>
          <?php endif; ?>
          <?php if ($showDate): ?>
            <div class="date"><?= date('F j, Y', strtotime($post['created_at'])) ?></div>
          <?php endif; ?>
          <?php if ($showExcerpt): ?>
            <div class="excerpt"><?= escape_html($post['excerpt']) ?></div>
          <?php endif; ?>
          <a href="/blog/<?= escape_html($post['slug']) ?>" class="cta-button" style="display:inline-block;margin-top:0.5rem;background:#000;color:#fff;padding:0.4rem 0.8rem;border-radius:4px;text-decoration:none;">
            <?= escape_html($buttonText) ?>
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
