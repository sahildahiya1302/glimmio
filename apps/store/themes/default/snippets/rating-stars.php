<?php
// Rating Stars snippet template

// Load global settings
$settings = json_decode(file_get_contents(__DIR__ . '/../config/settings_data.json'), true);

// Load schema for editor UI
$schema = [
    'name' => 'Rating Stars',
    'settings' => [
        ['type' => 'number', 'id' => 'rating', 'label' => 'Rating', 'default' => 5],
        ['type' => 'number', 'id' => 'max_rating', 'label' => 'Max Rating', 'default' => 5],
        ['type' => 'color', 'id' => 'star_color', 'label' => 'Star Color', 'default' => '#FFD700'],
        ['type' => 'color', 'id' => 'empty_star_color', 'label' => 'Empty Star Color', 'default' => '#CCCCCC'],
    ],
];

// Extract settings
$rating = $settings['rating'] ?? 5;
$maxRating = $settings['max_rating'] ?? 5;
$starColor = $settings['star_color'] ?? '#FFD700';
$emptyStarColor = $settings['empty_star_color'] ?? '#CCCCCC';

?>
<div class="rating-stars" role="img" aria-label="Rating: <?php echo htmlspecialchars($rating); ?> out of <?php echo htmlspecialchars($maxRating); ?>">
    <?php for ($i = 1; $i <= $maxRating; $i++): ?>
        <?php if ($i <= $rating): ?>
            <span style="color: <?php echo htmlspecialchars($starColor); ?>;">&#9733;</span>
        <?php else: ?>
            <span style="color: <?php echo htmlspecialchars($emptyStarColor); ?>;">&#9733;</span>
        <?php endif; ?>
    <?php endfor; ?>
</div>
