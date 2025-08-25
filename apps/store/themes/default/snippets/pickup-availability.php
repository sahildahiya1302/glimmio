<?php
// Pickup Availability snippet template

// Load global settings
$settings = json_decode(file_get_contents(__DIR__ . '/../config/settings_data.json'), true);

// Load schema for editor UI
$schema = [
    'name' => 'Pickup Availability',
    'settings' => [
        ['type' => 'text', 'id' => 'location', 'label' => 'Pickup Location', 'default' => 'Store Pickup'],
        ['type' => 'text', 'id' => 'availability_text', 'label' => 'Availability Text', 'default' => 'Available for pickup'],
        ['type' => 'color', 'id' => 'text_color', 'label' => 'Text Color', 'default' => '#000000'],
    ],
];

// Extract settings
$location = $settings['location'] ?? 'Store Pickup';
$availabilityText = $settings['availability_text'] ?? 'Available for pickup';
$textColor = $settings['text_color'] ?? '#000000';

?>
<div class="pickup-availability" style="color: <?php echo htmlspecialchars($textColor); ?>;">
    <strong><?php echo htmlspecialchars($location); ?>:</strong> <?php echo htmlspecialchars($availabilityText); ?>
</div>
