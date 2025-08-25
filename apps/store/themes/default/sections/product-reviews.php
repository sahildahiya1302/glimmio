<?php
$id = $id ?? 'product-reviews-' . uniqid();
$heading = $settings['heading'] ?? 'Customer Reviews';
$maxReviews = intval($settings['max_reviews'] ?? 10);
$showReviewForm = $settings['show_review_form'] ?? true;
$sortOrder = $settings['sort_order'] ?? 'latest';
$minRating = intval($settings['min_rating'] ?? 1);

$product = $context['product'] ?? null;
$reviews = $product ? getProductReviews($product['id']) : [];

// Filter by rating
$reviews = array_filter($reviews, function ($r) use ($minRating) {
    return intval($r['rating']) >= $minRating;
});

// Sort reviews
usort($reviews, function ($a, $b) use ($sortOrder) {
    if ($sortOrder === 'rating') {
        return intval($b['rating']) <=> intval($a['rating']);
    }
    return strtotime($b['date']) <=> strtotime($a['date']);
});
$reviews = array_slice($reviews, 0, $maxReviews);

$avgRating = $reviews ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;

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
  max-width: 1000px;
  margin: 0 auto;
}

#<?= $id ?> h2 {
  font-size: 2rem;
  margin-bottom: 2rem;
  text-align: center;
}

#<?= $id ?> .review-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
}

#<?= $id ?> .review-card {
  background: #fdfdfd;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

#<?= $id ?> .review-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

#<?= $id ?> .review-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #ccc;
  object-fit: cover;
}

#<?= $id ?> .review-name {
  font-weight: bold;
}

#<?= $id ?> .review-date {
  font-size: 0.85rem;
  color: #777;
}

#<?= $id ?> .review-title {
  font-size: 1rem;
  font-weight: 600;
  margin-top: 8px;
  margin-bottom: 5px;
}

#<?= $id ?> .review-body {
  font-size: 0.95rem;
  color: #444;
}

#<?= $id ?> .review-stars {
  color: #ffc107;
  font-size: 1rem;
}

#<?= $id ?> .review-form {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid #eee;
}

#<?= $id ?> .review-form input,
#<?= $id ?> .review-form textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}

#<?= $id ?> .review-form button {
  padding: 12px 20px;
  background: #000;
  color: white;
  border: none;
  border-radius: 30px;
  cursor: pointer;
}
</style>

<section id="<?= $id ?>">
  <h2><?= escape_html($heading) ?></h2>
  <?php if ($avgRating > 0): ?>
  <p style="text-align:center;margin-top:-10px;margin-bottom:20px;">Average rating: <?= number_format($avgRating,1) ?> / 5</p>
  <?php endif; ?>

  <?php if (!empty($reviews)): ?>
    <div class="review-grid">
      <?php foreach ($reviews as $review): ?>
        <div class="review-card">
          <div class="review-header">
            <img src="<?= escape_html($review['avatar'] ?? '/assets/user.png') ?>" class="review-avatar" alt="Avatar">
            <div>
              <div class="review-name"><?= escape_html($review['name']) ?></div>
              <div class="review-date"><?= date('F j, Y', strtotime($review['date'])) ?></div>
            </div>
          </div>
          <div class="review-stars">
            <?= str_repeat('★', intval($review['rating'])) ?>
            <?= str_repeat('☆', 5 - intval($review['rating'])) ?>
          </div>
          <div class="review-title"><?= escape_html($review['title']) ?></div>
          <div class="review-body"><?= nl2br(escape_html($review['body'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="text-align: center;">No reviews yet. Be the first to review this product!</p>
  <?php endif; ?>

  <?php if ($showReviewForm && $product): ?>
    <div class="review-form">
      <form action="/submit-review.php" method="POST">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="text" name="name" placeholder="Your name" required />
        <input type="email" name="email" placeholder="Your email" required />
        <input type="text" name="title" placeholder="Review title" required />
        <textarea name="body" placeholder="Your review..." rows="5" required></textarea>
        <input type="number" name="rating" min="1" max="5" placeholder="Rating (1-5)" required />
        <button type="submit">Submit Review</button>
      </form>
    </div>
  <?php endif; ?>
</section>
