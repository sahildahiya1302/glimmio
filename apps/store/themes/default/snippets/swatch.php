<?php
// Swatch snippet template

// Load global settings
$settings = json_decode(file_get_contents(__DIR__ . '/../config/settings_data.json'), true);

// Load schema for editor UI
$schema = [
    'name' => 'Swatch',
    'settings' => [
        ['type' => 'text', 'id' => 'label', 'label' => 'Label', 'default' => 'Color'],
        ['type' => 'color', 'id' => 'color', 'label' => 'Color', 'default' => '#000000'],
        ['type' => 'text', 'id' => 'tooltip', 'label' => 'Tooltip', 'default' => ''],
    ],
];

// Extract settings
$label = $settings['label'] ?? 'Color';
$color = $settings['color'] ?? '#000000';
$tooltip = $settings['tooltip'] ?? '';

?>
<div class="swatch" title="<?php echo htmlspecialchars($tooltip); ?>" aria-label="<?php echo htmlspecialchars($label); ?>" style="background-color: <?php echo htmlspecialchars($color); ?>;">
</div>
