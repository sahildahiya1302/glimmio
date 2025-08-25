<?php
// Social Icons snippet template

// Load global settings
$settings = json_decode(file_get_contents(__DIR__ . '/../config/settings_data.json'), true);

// Load schema for editor UI
$schema = [
    'name' => 'Social Icons',
    'settings' => [
        ['type' => 'text', 'id' => 'facebook_url', 'label' => 'Facebook URL', 'default' => 'https://facebook.com'],
        ['type' => 'text', 'id' => 'twitter_url', 'label' => 'Twitter URL', 'default' => 'https://twitter.com'],
        ['type' => 'text', 'id' => 'instagram_url', 'label' => 'Instagram URL', 'default' => 'https://instagram.com'],
        ['type' => 'text', 'id' => 'linkedin_url', 'label' => 'LinkedIn URL', 'default' => 'https://linkedin.com'],
    ],
];

// Extract settings
$facebookUrl = $settings['facebook_url'] ?? 'https://facebook.com';
$twitterUrl = $settings['twitter_url'] ?? 'https://twitter.com';
$instagramUrl = $settings['instagram_url'] ?? 'https://instagram.com';
$linkedinUrl = $settings['linkedin_url'] ?? 'https://linkedin.com';

?>
<div class="social-icons" role="navigation" aria-label="Social media links">
    <a href="<?php echo htmlspecialchars($facebookUrl); ?>" target="_blank" rel="noopener" aria-label="Facebook">
        <i class="icon-facebook"></i>
    </a>
    <a href="<?php echo htmlspecialchars($twitterUrl); ?>" target="_blank" rel="noopener" aria-label="Twitter">
        <i class="icon-twitter"></i>
    </a>
    <a href="<?php echo htmlspecialchars($instagramUrl); ?>" target="_blank" rel="noopener" aria-label="Instagram">
        <i class="icon-instagram"></i>
    </a>
    <a href="<?php echo htmlspecialchars($linkedinUrl); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
        <i class="icon-linkedin"></i>
    </a>
</div>
